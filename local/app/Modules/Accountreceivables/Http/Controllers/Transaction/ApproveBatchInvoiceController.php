<?php

namespace App\Modules\Accountreceivables\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Accountreceivables\Model\Transaction\BatchInvoiceHeader;
use App\Modules\Accountreceivables\Model\Transaction\BatchInvoiceLine;
use App\Modules\Accountreceivables\Model\Transaction\Invoice;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Service\Master\CustomerService;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Generalledger\Service\Master\AccountCombinationService;
use App\Modules\Generalledger\Service\Master\JournalService;
use App\Modules\Generalledger\Model\Master\SettingJournal;
use App\Role;
use App\Service\Penomoran;
use App\Service\NotificationService;
use App\Modules\Operational\Service\Transaction\HistoryResiService;

class ApproveBatchInvoiceController extends Controller
{
    const RESOURCE = 'Accountreceivables\Transaction\ApproveBatchInvoice';
    const URL = 'accountreceivables/transaction/approve-batch-invoice';

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
        $query   = \DB::table('ar.batch_invoice_header')
                        ->select('batch_invoice_header.*')
                        ->leftJoin('op.mst_customer', 'batch_invoice_header.customer_id', '=', 'mst_customer.customer_id')
                        ->where('batch_invoice_header.status', '=', BatchInvoiceHeader::INPROCESS)
                        ->where('batch_invoice_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->orderBy('batch_invoice_header.created_date', 'desc')
                        ->distinct();

        if (!empty($filters['batchInvoiceNumber'])) {
            $query->where('batch_invoice_number', 'ilike', '%'.$filters['batchInvoiceNumber'].'%');
        }

        if (!empty($filters['customer'])) {
            $query->where('mst_customer.customer_name', 'ilike', '%'.$filters['customer'].'%');
        }

        if (!empty($filters['billTo'])) {
            $query->where('bill_to', 'ilike', '%'.$filters['billTo'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $query->where('batch-invoice.created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['status'])) {
            $query->where('batch-invoice.status', '=', $filters['status']);
        }

        return view('accountreceivables::transaction.approve-batch-invoice.index', [
            'models' => $query->paginate(10),
            'filters' => $filters,
            'resource' => self::RESOURCE,
            'url' => self::URL,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = BatchInvoiceHeader::where('batch_invoice_header_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        if ($request->user()->cannot('accessBranch', $model->branch_id)) {
            abort(403);
        }

        return view('accountreceivables::transaction.approve-batch-invoice.add', [
            'title' => trans('shared/common.add'),
            'model' => $model,
            'url' => self::URL,
            'resource' => self::RESOURCE,
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? BatchInvoiceHeader::find($id) : new BatchInvoiceHeader();

        $this->validate($request, [
            'approveNote' => 'required',
        ]);

        if ($request->get('btn-approve') !== null) {
            foreach($model->lines as $line) {
                if ($line->invoice->getDiscountInprocess() > $line->invoice->remaining()) {
                    return redirect(\URL::previous())->withInput($request->all())->withErrors(
                        ['errorMessage' => 'discount invoice '.$line->invoice->invoice_number.' exceed remaining '.number_format($line->invoice->remaining())]
                    );
                }
            }
        }

        $model->status = BatchInvoiceHeader::OPEN;
        $model->discount_persen = null;
        $model->request_approve_note = null;

        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(self::URL.'/edit/'.$model->batch_invoice_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $notifCategory = $request->get('btn-approve') !== null ? 'Batch Invoice Approved' : 'Batch Invoice Rejected';

        foreach($model->lines as $line) {
            $invoice = $line->invoice;
            if ($invoice === null) {
                continue;
            }

            $discount = 0;
            if ($invoice->current_discount == 1) {
                $discount = $invoice->discount_1;
            } elseif ($invoice->current_discount == 2) {
                $discount = $invoice->discount_2;
            } elseif ($invoice->current_discount == 3) {
                $discount = $invoice->discount_3;
            }

            $discountInprocess = $invoice->getDiscountInprocess();
            if ($request->get('btn-approve') !== null) {
                $invoice->approved          = true;
                $invoice->approved_note     = 'Approved : '.$request->get('approveNote');
                $invoice->current_discount += 1;
                $invoice->status            = Invoice::APPROVED;

            } elseif ($request->get('btn-reject') !== null) {
                $invoice->approved      = false;
                $invoice->approved_note = 'Rejected : '.$request->get('approveNote');
                $invoice->status        = Invoice::APPROVED;
                $invoice->clearDiscountInprocess();
            }

            $invoice->approved_by = \Auth::user()->id;
            $invoice->approved_date = new \DateTime();

            try {
                $invoice->save();
            } catch (\Exception $e) {
                return redirect(self::URL.'/edit/'.$model->batch_invoice_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            if ($invoice->approved) {
                $error = $this->createJournalDiskon($invoice, $discount);
                if (!empty($error)) {
                    return redirect(self::URL.'/edit/'.$model->invoice_id)->withInput($request->all())->withErrors(['errorMessage' => $error]);
                }
            }

            HistoryResiService::saveHistory(
                $invoice->resi_header_id,
                $notifCategory,
                'Batch Invoice ' . $model->batch_invoice_number . ' - ' . $request->get('approveNote')
            );
        }

        NotificationService::createNotification(
            $notifCategory,
            'Batch Invoice ' . $model->batch_invoice_number . ' - ' . $request->get('approveNote'),
            BatchInvoiceController::URL.'/edit/'.$model->batch_invoice_header_id,
            [Role::FINANCE_ADMIN]
        );


        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('accountreceivables/menu.approve-batch-invoice').' '.$model->batch_invoice_number])
        );

        return redirect(self::URL);
    }

    protected function createJournalDiskon(Invoice $model, $diskon)
    {
        $invoice       = Invoice::find($model->invoice_id);
        $journalHeader = new JournalHeader();

        $journalHeader->category       = JournalHeader::DISCOUNT_INVOICE;
        $journalHeader->period         = new \DateTime($this->now->format('Y-m-1'));
        $journalHeader->status         = JournalHeader::OPEN;
        $journalHeader->description    = 'Invoice Number: '.$invoice->invoice_number.'. Resi Number: '.$invoice->resi->resi_number;
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

        /** DISKON **/
        $settingCoa  = SettingJournal::where('setting_name', SettingJournal::DISKON)->first();
        $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

        $line = new JournalLine();
        $line->journal_header_id      = $journalHeader->journal_header_id;
        $line->account_combination_id = $combination->account_combination_id;
        $line->debet                  = $diskon;
        $line->credit                 = 0;
        $line->created_date           = $this->now;
        $line->created_by             = \Auth::user()->id;

        try {
            $line->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        /** PIUTANG USAHA **/
        $settingCoa  = SettingJournal::where('setting_name', SettingJournal::PIUTANG_USAHA)->first();
        $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

        $line = new JournalLine();
        $line->journal_header_id      = $journalHeader->journal_header_id;
        $line->account_combination_id = $combination->account_combination_id;
        $line->debet                  = 0;
        $line->credit                 = $diskon;
        $line->created_date           = $this->now;
        $line->created_by             = \Auth::user()->id;

        try {
            $line->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
