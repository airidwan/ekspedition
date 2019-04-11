<?php

namespace App\Modules\Accountreceivables\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
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

class ApproveInvoiceController extends Controller
{
    const RESOURCE = 'Accountreceivables\Transaction\ApproveInvoice';
    const URL = 'accountreceivables/transaction/approve-invoice';

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
        $query   = \DB::table('ar.invoice')
                        ->select('invoice.*')
                        ->leftJoin('op.mst_customer', 'invoice.customer_id', '=', 'mst_customer.customer_id')
                        ->leftJoin('op.trans_resi_header', 'invoice.resi_header_id', '=', 'trans_resi_header.resi_header_id')
                        ->where('invoice.status', '=', Invoice::INPROCESS)
                        ->where('invoice.approved_branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->orderBy('invoice.created_date', 'desc')
                        ->distinct();

        if (!empty($filters['invoiceNumber'])) {
            $query->where('invoice_number', 'ilike', '%'.$filters['invoiceNumber'].'%');
        }

        if (!empty($filters['resiNumber'])) {
            $query->where('trans_resi_header.resi_number', 'ilike', '%'.$filters['resiNumber'].'%');
        }

        if (!empty($filters['customer'])) {
            $query->where('mst_customer.customer_name', 'ilike', '%'.$filters['customer'].'%');
        }

        if (!empty($filters['billTo'])) {
            $query->where('bill_to', 'ilike', '%'.$filters['billTo'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $query->where('invoice.created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['status'])) {
            $query->where('invoice.status', '=', $filters['status']);
        }

        return view('accountreceivables::transaction.approve-invoice.index', [
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

        $model = Invoice::where('invoice_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        if ($request->user()->cannot('accessBranch', $model->approved_branch_id)) {
            abort(403);
        }

        return view('accountreceivables::transaction.approve-invoice.add', [
            'title' => trans('shared/common.add'),
            'model' => $model,
            'url' => self::URL,
            'resource' => self::RESOURCE,
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? Invoice::find($id) : new Invoice();

        $this->validate($request, [
            'approveNote' => 'required',
        ]);

        $discount = 0;
        if ($model->current_discount == 1) {
            $discount = $model->discount_1;
        } elseif ($model->current_discount == 2) {
            $discount = $model->discount_2;
        } elseif ($model->current_discount == 3) {
            $discount = $model->discount_3;
        }

        if ($request->get('btn-approve') !== null && $discount > $model->remaining()) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(
                ['errorMessage' => 'discount exceed remaining invoice '.number_format($model->remaining())]
            );
        }

        $model->status             = Invoice::APPROVED;
        if ($request->get('btn-approve') !== null) {
            $model->approved           = true;
            $model->approved_note      = 'Approved : '.$request->get('approveNote');
            $model->current_discount  += 1;
            $model->req_approve_note   = null;
            $model->approved_branch_id = null;

        } elseif ($request->get('btn-reject') !== null) {
            $model->approved           = false;
            $model->approved_note      = 'Rejected : '.$request->get('approveNote');
            $model->req_approve_note   = null;
            $model->approved_branch_id = null;
        }

        $model->approved_by = \Auth::user()->id;
        $model->approved_date = new \DateTime(); 

        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(self::URL.'/edit/'.$model->invoice_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        if ($model->approved) {
            $error = $this->createJournalDiskon($model, $discount);
            if (!empty($error)) {
                return redirect(self::URL.'/edit/'.$model->invoice_id)->withInput($request->all())->withErrors(['errorMessage' => $error]);
            }
        }

        $notifCategory = $model->approved ? 'Invoice Approved' : 'Invoice Rejected';
        NotificationService::createNotification(
            $notifCategory,
            'Invoice ' . $model->invoice_number . ' - ' . $model->approved_note,
            InvoiceController::URL.'/edit/'.$model->invoice_id,
            [Role::FINANCE_ADMIN]
        );

        HistoryResiService::saveHistory(
            $model->resi_header_id,
            $notifCategory,
            $model->type.' Number ' . $model->invoice_number . ' - ' . $model->approved_note
        );

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('accountreceivables/menu.approve-invoice').' '.$model->invoice_number])
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
