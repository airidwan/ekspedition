<?php

namespace App\Modules\Operational\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Transaction\TransactionResiLine;
use App\Modules\Operational\Model\Transaction\TransactionResiNego;
use App\Modules\Marketing\Model\Transaction\PickupRequest;
use App\Modules\Operational\Model\Master\MasterRoute;
use App\Modules\Operational\Model\Master\MasterShippingPrice;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Model\Transaction\ResiStock;
use App\Modules\Accountreceivables\Model\Transaction\Invoice;
use App\Modules\Operational\Service\Master\CustomerService;
use App\Modules\Operational\Service\Master\RouteService;
use App\Modules\Operational\Service\Master\UnitService;
use App\Modules\Operational\Service\Transaction\HistoryResiService;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Generalledger\Service\Master\AccountCombinationService;
use App\Modules\Generalledger\Service\Master\JournalService;
use App\Modules\Generalledger\Model\Master\SettingJournal;
use App\Service\Penomoran;
use App\Service\NotificationService;
use App\Notification;
use App\Role;

class ApproveNegoResiController extends Controller
{
    const RESOURCE = 'Operational\Transaction\ApproveNegoResi';
    const URL      = 'operational/transaction/approve-nego-resi';

    protected $now;

    public function __construct()
    {
        $this->middleware('auth');
        $this->now = new \DateTime();
    }

    public function index(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        if ($request->isMethod('post')) {
            $request->session()->put('filters', $request->all());
            return redirect(self::URL.'?page=1');
        } elseif (empty($request->get('page'))) {
            $request->session()->forget('filters');
        }

        $filters = $request->session()->get('filters');
        $query   = \DB::table('op.trans_resi_header')
                        ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->where('status', '=', TransactionResiHeader::INPROCESS)
                        ->orderBy('created_date', 'desc');

        if (!empty($filters['resiNnumber'])) {
            $query->where('resi_number', 'ilike', '%'.$filters['resiNnumber'].'%');
        }

        if (!empty($filters['customer'])) {
            $query->where('customer_name', 'ilike', '%'.$filters['customer'].'%');
        }

        if (!empty($filters['sender'])) {
            $query->where('sender_name', 'ilike', '%'.$filters['sender'].'%');
        }

        if (!empty($filters['receiver'])) {
            $query->where('receiver_name', 'ilike', '%'.$filters['receiver'].'%');
        }

        if (!empty($filters['item'])) {
            $query->where('item_name', 'ilike', '%'.$filters['item'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $query->where('created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $query->where('created_date', '<=', $date->format('Y-m-d 23:59:59'));
        }

        return view('operational::transaction.approve-nego-resi.index', [
            'models' => $query->paginate(10),
            'filters' => $filters,
            'resource' => self::RESOURCE,
            'url' => self::URL,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $model = TransactionResiHeader::where('resi_header_id', '=', $id)->first();

        if ($model === null || !$model->isInprocess()) {
            abort(404);
        }

        if ($request->user()->cannot('accessBranch', $model->branch_id)) {
            abort(403);
        }

        return view('operational::transaction.approve-nego-resi.add', [
            'title' => trans('shared/common.edit'),
            'model' => $model,
            'url' => self::URL,
            'resource' => self::RESOURCE,
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id', 0));
        $model = TransactionResiHeader::find($id);
        if ($model === null) {
            abort(404);
        }

        $nego = $model->nego()->whereNull('approved')->first();
        if ($nego === null) {
            abort(404);
        }

        $this->validate($request, [
            'approvedNotes' => 'required|max:250',
        ]);

        /** save nego **/
        $nego->approved = $request->get('btn-approve') !== null;
        $nego->approved_notes = $request->get('approvedNotes');

        if ($nego->approved) {
            $model->discount = $model->totalAmount() - $nego->nego_price;
            $model->status = TransactionResiHeader::APPROVED;
        } else {
            $model->status = TransactionResiHeader::INCOMPLETE;
        }

        /** save model **/
        $model->last_updated_date = $this->now;
        $model->last_updated_by = \Auth::user()->id;
        $model->save();

        $nego->last_updated_date = $this->now;
        $nego->last_updated_by = \Auth::user()->id;
        $nego->save();

        /** save resi stock **/
        if ($nego->approved) {
            $resiStock = new ResiStock();
            $resiStock->resi_header_id = $model->resi_header_id;
            $resiStock->branch_id = $model->branch_id;
            $resiStock->coly = $model->totalColy();
            $resiStock->created_date = $this->now;
            $resiStock->created_by = \Auth::user()->id;
            $resiStock->last_updated_date = $this->now;
            $resiStock->last_updated_by = \Auth::user()->id;

            try {
                $resiStock->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }

            $error = $this->createInvoiceResi($model);
            if (!empty($error)) {
                return $error;
            }

            $error = $this->createInvoicePickup($model);
            if (!empty($error)) {
                return $error;
            }

            $message = trans('shared/common.approved-message', ['variable' => trans('operational/menu.resi').' '.$model->resi_number]);
        } else {
            $message = trans('shared/common.rejected-message', ['variable' => trans('operational/menu.resi').' '.$model->resi_number]);
        }

        $process = $nego->approved ? 'Approve Nego Resi' : 'Reject Nego Resi';
        HistoryResiService::saveHistory($id, $process, 'Note: '.$nego->approved_notes);

        /** notification **/
        $categoryNotif = $nego->approved ? 'Resi Negotiation Approved' : 'Resi Negotiation Rejected';
        NotificationService::createNotification(
            $categoryNotif,
            'Resi ' . $model->resi_number . '. '.$nego->approved_notes,
            TransactionResiController::URL.'/edit/'.$model->resi_header_id,
            [Role::OPERATIONAL_ADMIN]
        );

        $request->session()->flash('successMessage', $message);

        return redirect(self::URL);
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
        $invoice->created_by       = \Auth::user()->id;
        $invoice->invoice_number   = $this->getInvoiceNumber($invoice);
        $invoice->resi_header_id   = $model->resi_header_id;
        $invoice->amount           = $resi->total();
        $invoice->current_discount = 1;

        try {
            $invoice->save();
        } catch (\Exception $e) {
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
        $journalHeader->created_by     = \Auth::user()->id;
        $journalHeader->journal_number = JournalService::getJournalNumber($journalHeader);

        try {
            $journalHeader->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        /** PIUTANG USAHA **/
        $settingCoa  = SettingJournal::where('setting_name', SettingJournal::PIUTANG_USAHA)->first();
        $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

        $line = new JournalLine();
        $line->journal_header_id      = $journalHeader->journal_header_id;
        $line->account_combination_id = $combination->account_combination_id;
        $line->debet                  = $resi->total();
        $line->credit                 = 0;
        $line->created_date           = $this->now;
        $line->created_by             = \Auth::user()->id;

        try {
            $line->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        /** DISKON **/
        if (!empty($resi->discount)) {
            $settingCoa  = SettingJournal::where('setting_name', SettingJournal::DISKON)->first();
            $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

            $line = new JournalLine();
            $line->journal_header_id      = $journalHeader->journal_header_id;
            $line->account_combination_id = $combination->account_combination_id;
            $line->debet                  = $resi->discount;
            $line->credit                 = 0;
            $line->created_date           = $this->now;
            $line->created_by             = \Auth::user()->id;

            try {
                $line->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }

        /** PENDAPATAN UTAMA **/
        $persentase      = $this->getPersentasePendapatanUtama($resi->route);
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
            $line->created_by             = \Auth::user()->id;

            try {
                $line->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }

        /** PEMBULATAN **/
        $pembulatan = $totalPendapatan - $resi->totalAmount();
        if ($pembulatan != 0) {
            $settingCoa  = SettingJournal::where('setting_name', SettingJournal::PEMBULATAN)->first();
            $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

            $line = new JournalLine();
            $line->journal_header_id      = $journalHeader->journal_header_id;
            $line->account_combination_id = $combination->account_combination_id;
            $line->debet                  = $pembulatan;
            $line->credit                 = 0;
            $line->created_date           = $this->now;
            $line->created_by             = \Auth::user()->id;

            try {
                $line->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
    }

    protected function getPersentasePendapatanUtama(MasterRoute $route)
    {
        $persentase    = [];
        $currentBranch = \Session::get('currentBranch');

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
        $invoice->created_by        = \Auth::user()->id;
        $invoice->invoice_number    = $this->getInvoiceNumber($invoice);
        $invoice->resi_header_id    = !empty($pickupRequest->resi) ? $pickupRequest->resi->resi_header_id : null;
        $invoice->pickup_request_id = $pickupRequest->pickup_request_id;
        $invoice->amount            = $pickupRequest->pickup_cost;
        $invoice->current_discount  = 1;

        try {
            $invoice->save();
        } catch (\Exception $e) {
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
        $journalHeader->created_by     = \Auth::user()->id;
        $journalHeader->journal_number = JournalService::getJournalNumber($journalHeader);

        try {
            $journalHeader->save();
        } catch (\Exception $e) {
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
        $line->created_by             = \Auth::user()->id;

        try {
            $line->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        /** PENDAPATAN UTAMA **/
        $settingCoa  = SettingJournal::where('setting_name', SettingJournal::PENDAPATAN_UTAMA)->first();
        $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

        $line = new JournalLine();
        $line->journal_header_id      = $journalHeader->journal_header_id;
        $line->account_combination_id = $combination->account_combination_id;
        $line->debet                  = 0;
        $line->credit                 = $pickupRequest->pickup_cost;
        $line->created_date           = $this->now;
        $line->created_by             = \Auth::user()->id;

        try {
            $line->save();
        } catch (\Exception $e) {
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

        return 'IAR.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }
}
