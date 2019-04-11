<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Model\Master\MasterRoute;
use App\Modules\Accountreceivables\Model\Transaction\Invoice;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Modules\Generalledger\Service\Master\AccountCombinationService;
use App\Modules\Operational\Service\Transaction\HistoryResiService;
use App\Modules\Generalledger\Service\Master\JournalService;
use App\Modules\Generalledger\Model\Master\SettingJournal;
use App\Service\Penomoran;
use App\Role;
use App\User;

class CreateInvoiceResi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:create-invoice-resi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Invoice Resi yang tidak terbentuk';
    protected $now;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->now = new \DateTime();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $now = new \DateTime();
        $this->info('==========================');
        $this->info('Start: '.$now->format('d-m-Y H:i:s').PHP_EOL);

        $this->proses();

        $now = new \DateTime();
        $this->info(PHP_EOL.'End: '.$now->format('d-m-Y H:i:s').PHP_EOL);
        $this->info('==========================');
    }

    protected function proses()
    {
        $models = TransactionResiHeader::select('trans_resi_header.*')
                        ->leftJoin('ar.invoice', 'invoice.resi_header_id', '=', 'trans_resi_header.resi_header_id')
                        ->where('trans_resi_header.status', TransactionResiHeader::APPROVED)
                        ->where('trans_resi_header.branch_id', '<>', -1)
                        ->whereNull('invoice.resi_header_id')
                        ->orderBy('trans_resi_header.resi_header_id')
                        ->get();

        foreach ($models as $model) {
            $this->info($model->resi_number);

            $this->now = new \DateTime($model->created_date);
            var_dump($this->createInvoiceResi($model));
            var_dump($this->createInvoicePickup($model));
        }



    }

    protected function createInvoiceResi(TransactionResiHeader $model)
    {
        $resi            = TransactionResiHeader::find($model->resi_header_id);
        $invoice         = new Invoice();
        $invoice->status = Invoice::APPROVED;
        $invoice->type   = Invoice::INV_RESI;

        if ($model->isBillToReceiver()) {
            if (!empty($model->customer_receiver_id)) {
                    $invoice->customer_id = $model->customer_receiver_id;
            }

            $invoice->bill_to = !empty($model->customerReceiver) ? $model->customerReceiver->customer_name : $model->receiver_name;
            $invoice->bill_to_address = $model->receiver_address;
            $invoice->bill_to_phone = $model->receiver_phone;
        } else {
            if (!empty($model->customer_id)) {
                    $invoice->customer_id = $model->customer_id;
            }

            $invoice->bill_to = !empty($model->customer) ? $model->customer->customer_name : $model->sender_name;
            $invoice->bill_to_address = $model->sender_address;
            $invoice->bill_to_phone = $model->sender_phone;
        }

        $invoice->branch_id        = $model->branch_id;
        $invoice->created_date     = $this->now;
        $invoice->created_by       = $model->created_by;
        $invoice->invoice_number   = $this->getInvoiceNumber($invoice);
        $invoice->resi_header_id   = $model->resi_header_id;
        $invoice->amount           = $resi->total();
        $invoice->current_discount = 1;

        try {
            $invoice->save();
        } catch (\Exception $e) {
            \DB::rollBack();
            return $e->getMessage();
        }
        $error = $this->createJournalInvoiceResi($invoice);
        if (!empty($error)) {
            return $error;
        }
    }

    protected function createJournalInvoiceResi(Invoice $model)
    {
        $invoice       = Invoice::find($model->invoice_id);
        $resi          = $invoice->resi;
        $journalHeader = new JournalHeader();
        
        $journalHeader->category       = JournalHeader::INVOICE_RESI;
        $journalHeader->period         = new \DateTime($this->now->format('Y-m-1'));
        $journalHeader->status         = JournalHeader::OPEN;
        $journalHeader->description    = 'Invoice Number: '.$invoice->invoice_number.'. Resi Number: '.$resi->resi_number;
        $journalHeader->branch_id      = $invoice->branch_id;
        $journalHeader->journal_date   = $this->now;
        $journalHeader->created_date   = $this->now;
        $journalHeader->created_by     = $model->created_by;
        $journalHeader->journal_number = JournalService::getJournalNumber($journalHeader);
        try {
            $journalHeader->save();
        } catch (\Exception $e) {
            \DB::rollBack();
            return $e->getMessage();
        }

        /** PIUTANG USAHA **/
        $settingCoa  = SettingJournal::where('setting_name', SettingJournal::PIUTANG_USAHA)->first();
        $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code, null, $model->branch_id);

        $line = new JournalLine();
        $line->journal_header_id      = $journalHeader->journal_header_id;
        $line->account_combination_id = $combination->account_combination_id;
        $line->debet                  = $resi->total();
        $line->credit                 = 0;
        $line->created_date           = $this->now;
        $line->created_by             = $model->created_by;

        try {
            $line->save();
        } catch (\Exception $e) {
            \DB::rollBack();
            return $e->getMessage();
        }

        /** PENDAPATAN UTAMA **/
        $persentase      = $this->getPersentasePendapatanUtama($resi->route, $model->branch_id);

        $totalPendapatan = 0;
        foreach ($persentase as $branchId => $persen) {
            $settingCoa       = SettingJournal::where('setting_name', SettingJournal::PENDAPATAN_UTAMA)->first();
            $combination      = AccountCombinationService::getCombination($settingCoa->coa->coa_code, null, $branchId);

            $pendapatan       = floor($persen / 100 * $resi->totalAmountAsli());
            $totalPendapatan += $pendapatan;

            $line = new JournalLine();
            $line->journal_header_id      = $journalHeader->journal_header_id;
            $line->account_combination_id = $combination->account_combination_id;
            $line->debet                  = 0;
            $line->credit                 = $pendapatan;
            $line->created_date           = $this->now;
            $line->created_by             = $model->created_by;

            try {
                $line->save();
            } catch (\Exception $e) {
                \DB::rollBack();
                return $e->getMessage();
            }
        }

        /** PEMBULATAN **/
        $pembulatan = $totalPendapatan - $resi->totalAmount();
        if ($pembulatan != 0) {
            $settingCoa  = SettingJournal::where('setting_name', SettingJournal::PEMBULATAN)->first();
            $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code, null, $branchId);
            $line = new JournalLine();
            $line->journal_header_id      = $journalHeader->journal_header_id;
            $line->account_combination_id = $combination->account_combination_id;
            $line->debet                  = $pembulatan;
            $line->credit                 = 0;
            $line->created_date           = $this->now;
            $line->created_by             = $model->created_by;

            try {
                $line->save();
            } catch (\Exception $e) {
                \DB::rollBack();
                return $e->getMessage();
            }
        }
    }

    protected function getPersentasePendapatanUtama(MasterRoute $route, $branchId)
    {
        $persentase    = [];
        $currentBranch = MasterBranch::find($branchId);

        if ($route->details->count() == 0) {
            $persentase[$currentBranch->branch_id] = 100;
        } else {
            foreach ($route->details as $detail) {
                if ($detail->city_start_id == $currentBranch->city_id) {
                    $persentase[$currentBranch->branch_id] = $detail->rate_kg / $route->rate_kg * 100;
                } else {
                    $mainBranch = MasterBranch::where('city_id', '=', $detail->city_start_id)->where('main_branch', '=', true)->first();
                    $persentase[$mainBranch->branch_id] = $detail->rate_kg / $route->rate_kg * 100;
                }
            }
        }

        return $persentase;
    }

    protected function createInvoicePickup(TransactionResiHeader $model)
    {
        $pickupRequest   = !empty($model->pickup_request_id) ? PickupRequest::find($model->pickup_request_id) : null;
        $invoice         = new Invoice();
        $invoice->status = Invoice::APPROVED;
        $invoice->type   = Invoice::INV_PICKUP;

        if ($pickupRequest === null || empty($pickupRequest->pickup_cost)) {
            return;
        }

        if ($model->isBillToReceiver()) {
            if (!empty($model->customer_receiver_id)) {
                    $invoice->customer_id = $model->customer_receiver_id;
            }

            $invoice->bill_to         = !empty($model->customerReceiver) ? $model->customerReceiver->customer_name : $model->receiver_name;
            $invoice->bill_to_address = $model->receiver_address;
            $invoice->bill_to_phone   = $model->receiver_phone;
        } else {
            if (!empty($model->customer_id)) {
                    $invoice->customer_id = $model->customer_id;
            }

            $invoice->bill_to         = !empty($model->customer) ? $model->customer->customer_name : $model->sender_name;
            $invoice->bill_to_address = $model->sender_address;
            $invoice->bill_to_phone   = $model->sender_phone;
        }

        $invoice->branch_id         = \Session::get('currentBranch')->branch_id;
        $invoice->created_date      = new \DateTime();
        $invoice->created_by        = $model->created_by;
        $invoice->invoice_number    = $this->getInvoiceNumber($invoice);
        $invoice->resi_header_id    = !empty($pickupRequest->resi) ? $pickupRequest->resi->resi_header_id : null;
        $invoice->pickup_request_id = $pickupRequest->pickup_request_id;
        $invoice->amount            = $pickupRequest->pickup_cost;
        $invoice->current_discount  = 1;

        try {
            $invoice->save();
        } catch (\Exception $e) {
            \DB::rollBack();
            return $e->getMessage();
        }

        $error = $this->createJournalInvoicePickup($invoice);
        if (!empty($error)) {
            return $error;
        }
    }

    protected function createJournalInvoicePickup(Invoice $model)
    {
        $invoice       = Invoice::find($model->invoice_id);
        $resi          = $invoice->resi;
        $pickupRequest = $invoice->pickupRequest;
        $journalHeader = new JournalHeader();

        $journalHeader->category       = JournalHeader::INVOICE_PICKUP;
        $journalHeader->period         = new \DateTime($this->now->format('Y-m-1'));
        $journalHeader->status         = JournalHeader::OPEN;
        $journalHeader->description    = 'Invoice Number: '.$invoice->invoice_number.'. Resi Number: '.$resi->resi_number;
        $journalHeader->branch_id      = $invoice->branch_id;
        $journalHeader->journal_date   = $this->now;
        $journalHeader->created_date   = $this->now;
        $journalHeader->created_by     = $model->created_by;
        $journalHeader->journal_number = JournalService::getJournalNumber($journalHeader);

        try {
            $journalHeader->save();
        } catch (\Exception $e) {
            \DB::rollBack();
            return $e->getMessage();
        }

        /** PIUTANG USAHA **/
        $settingCoa  = SettingJournal::where('setting_name', SettingJournal::PIUTANG_USAHA)->first();
        $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

        $line = new JournalLine();
        $line->journal_header_id      = $journalHeader->journal_header_id;
        $line->account_combination_id = $combination->account_combination_id;
        $line->debet                  = $pickupRequest->pickup_cost;
        $line->credit                 = 0;
        $line->created_date           = $this->now;
        $line->created_by             = $model->created_by;

        try {
            $line->save();
        } catch (\Exception $e) {
            \DB::rollBack();
            return $e->getMessage();
        }

        /** PENDAPATAN UTAMA **/
        $settingCoa  = SettingJournal::where('setting_name', SettingJournal::PENDAPATAN_LAIN_LAIN)->first();
        $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

        $line = new JournalLine();
        $line->journal_header_id      = $journalHeader->journal_header_id;
        $line->account_combination_id = $combination->account_combination_id;
        $line->debet                  = 0;
        $line->credit                 = $pickupRequest->pickup_cost;
        $line->created_date           = $this->now;
        $line->created_by             = $model->created_by;

        try {
            $line->save();
        } catch (\Exception $e) {
            \DB::rollBack();
            return $e->getMessage();
        }
    }

    protected function getInvoiceNumber(Invoice $model)
    {
        $branch      = MasterBranch::find($model->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('ar.invoice')
                            ->where('branch_id', '=', $model->branch_id)
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->count();
        return 'IAR.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 6);
    }
}
