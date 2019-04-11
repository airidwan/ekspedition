<?php

namespace App\Modules\Payable\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Purchasing\Model\Transaction\PurchaseOrderHeader;
use App\Modules\Payable\Model\Transaction\InvoiceHeader;
use App\Modules\Payable\Model\Transaction\InvoiceLine;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Modules\Generalledger\Model\Master\MasterAccountCombination;
use App\Modules\Generalledger\Service\Master\AccountCombinationService;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Generalledger\Model\Master\SettingJournal;
use App\Modules\Payable\Model\Master\MasterApType;
use App\Modules\Payable\Http\Controllers\Transaction\PaymentController;
use App\Notification;
use App\Service\Penomoran;
use App\Service\NotificationService;
use App\Role;

class DpInvoiceController extends Controller
{
    const RESOURCE = 'Payable\Transaction\DpInvoice';
    const URL      = 'payable/transaction/dp-invoice';

    public function __construct()
    {
        $this->middleware('auth');
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
        $query   = \DB::table('ap.invoice_header')
                    ->select(
                        'invoice_header.header_id',
                        'invoice_header.invoice_number',
                        'invoice_header.total_amount',
                        'invoice_header.description',
                        'invoice_header.status',
                        'invoice_header.created_date',
                        'po_headers.po_number',
                        'po_headers.header_id as po_header_id',
                        'mst_vendor.vendor_code',
                        'mst_vendor.vendor_name',
                        'mst_vendor.address',
                        'mst_vendor.phone_number'
                      )
                    ->leftJoin('ap.invoice_line', 'invoice_line.header_id', '=', 'invoice_header.header_id')
                    ->leftJoin('po.po_headers', 'po_headers.header_id', '=', 'invoice_line.po_header_id')
                    ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'invoice_header.vendor_id')
                    ->where('invoice_header.type_id', '=', InvoiceHeader::DOWN_PAYMENT)
                    ->where('invoice_header.branch_id', '=', $request->session()->get('currentBranch')->branch_id)
                    ->orderBy('invoice_header.created_date', 'desc')
                    ->distinct();

        if (!empty($filters['invoiceNumber'])) {
            $query->where('invoice_header.invoice_number', 'ilike', '%'.$filters['invoiceNumber'].'%');
        }

        if (!empty($filters['vendorName'])) {
            $query->where('mst_vendor.vendor_name', 'ilike', '%'.$filters['vendorName'].'%');
        }

        if (!empty($filters['vendorCode'])) {
            $query->where('mst_vendor.vendor_code', 'ilike', '%'.$filters['vendorCode'].'%');
        }

        if (!empty($filters['description'])) {
            $query->where('invoice_header.description', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['poNumber'])) {
            $query->where('po_headers.po_number', 'ilike', '%'.$filters['poNumber'].'%');
        }

        if (!empty($filters['status'])) {
            $query->where('invoice_header.status', '=', $filters['status']);
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = new \DateTime($filters['dateFrom']);
            $query->where('invoice_header.created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = new \DateTime($filters['dateTo']);
            $query->where('invoice_header.created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }


        return view('payable::transaction.dp-invoice.index', [
            'models'       => $query->paginate(10),
            'filters'      => $filters,
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
            'optionStatus' => $this->getStatus(),
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new InvoiceHeader();
        $model->status = InvoiceHeader::INCOMPLETE;

        return view('payable::transaction.dp-invoice.add', [
            'title'             => trans('shared/common.add'),
            'model'             => $model,
            'optionTax'         => $this->optionTax(),
            'url'               => self::URL,
            'resource'          => self::RESOURCE,
            'optionPo'          => $this->optionPo(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        $model = InvoiceHeader::find($id);
        if ($model === null) {
            abort(404);
        }

        $data = [
            'title'     => trans('shared/common.edit'),
            'model'     => $model,
            'url'       => self::URL,
            'resource'  => self::RESOURCE,
            'optionTax' => $this->optionTax(),
            'optionPo'  => $this->optionPo(),
        ];

        if ($request->user()->can('access', [self::RESOURCE, 'update'])) {
            return view('payable::transaction.dp-invoice.add', $data);
        } else {
            return view('payable::transaction.dp-invoice.detail', $data);
        }
    }

    public function save(Request $request)
    {
        // var_dump($request->all());exit();
        $id = intval($request->get('id'));
        $model = !empty($id) ? InvoiceHeader::find($id) : new InvoiceHeader();

        $this->validate($request, [
            'poNumber'    => 'required',
            'totalAmount' => 'required',
            'description' => 'required',
        ]);

        if (empty($model->branch_id)) {
            $model->branch_id = $request->session()->get('currentBranch')->branch_id;
        }

        $now = new \DateTime();

        if (empty($model->invoice_number)) {
            $model->invoice_number = $this->getInvoiceNumber($model);
        }

        $model->type_id = InvoiceHeader::DOWN_PAYMENT;
        $model->vendor_id = $request->get('vendorId');
        $model->vendor_address = $request->get('address');
        $model->description = $request->get('description');
        $model->total_amount = intval(str_replace(',', '', $request->get('totalAmount')));

        if (empty($id)) {
            $model->status = InvoiceHeader::INCOMPLETE;
            $model->created_date = $now;
            $model->created_by = \Auth::user()->id;
        } else {
            $model->last_updated_date = $now;
            $model->last_updated_by = \Auth::user()->id;
        }

        try {
            $model->save();  
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $type   = MasterApType::find($model->type_id);
        $vendor = $model->vendor;

        $account       = MasterCoa::find($type->coa_id_d);
        $accountCode   = $account->coa_code;
        $subAccountCode= $vendor->subaccount_code;

        $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

        $modelLine = !empty($model->lineOne) ? $model->lineOne : new InvoiceLine;
        $modelLine->header_id       = $model->header_id;
        $modelLine->po_header_id    = $request->get('poHeaderId');
        $modelLine->description     = $request->get('descriptionPo');
        $modelLine->tax             = intval($request->get('tax'));
        $modelLine->amount          = intval(str_replace(',', '', $request->get('totalAmount')));
        $modelLine->account_comb_id = $accountCombination->account_combination_id;

        if (empty($id)) {
            $modelLine->created_date = $now;
            $modelLine->created_by = \Auth::user()->id;
        } else {
            $modelLine->last_updated_date = $now;
            $modelLine->last_updated_by = \Auth::user()->id;
        }

        try {
            $modelLine->save();
        }catch (\Exception $e) {
            return redirect(self::URL.'/edit/'.$model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        if ($request->get('btn-approve') !== null) {
            $model->approved_by = \Auth::user()->id;
            $model->approved_date = $now;
            $model->status = InvoiceHeader::APPROVED;
            $model->save();

            $modelPO = PurchaseOrderHeader::find($modelLine->po_header_id);

            // insert journal
            $journalHeader           = new JournalHeader();
            $journalHeader->category = JournalHeader::INVOICE_DP;
            $journalHeader->status   = JournalHeader::OPEN;

            $now                    = new \DateTime();
            $period                 = new \DateTime($now->format('Y-m-1'));
            $journalHeader->period  = $period;

            $journalHeader->description = $model->invoice_number.' - '.$modelPO->po_number;;
            $journalHeader->branch_id   = \Session::get('currentBranch')->branch_id;

            $journalHeader->journal_date = $now;
            $journalHeader->created_date = $now;
            $journalHeader->created_by = \Auth::user()->id;
            $journalHeader->journal_number = $this->getJournalNumber($journalHeader);

            try {
                $journalHeader->save();
            } catch (\Exception $e) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            // insert journal line debit

            $journalLine      = new JournalLine();
            $modelPayableType = $model->type;
            $account          = $modelPayableType->coaD;
            $accountCode      = $account->coa_code;
            $subAccount       = $model->vendor;
            $subAccountCode   = $subAccount->subaccount_code;

            $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

            $journalLine->journal_header_id      = $journalHeader->journal_header_id;
            $journalLine->account_combination_id = $accountCombination->account_combination_id;
            $journalLine->debet                  = $modelLine->amount;
            $journalLine->credit                 = 0;
            $journalLine->description            = 'Account Debit AP Type';

            $journalLine->created_date = $now;
            $journalLine->created_by = \Auth::user()->id;

            try {
                $journalLine->save();
            } catch (\Exception $e) {
                return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            //insert gl tax if exist debet
            if (!empty($modelLine->tax)) {
                $journalLine      = new JournalLine();
                $modelPayableType = $model->type;
                $defaultJournal   = SettingJournal::where('setting_name', '=', SettingJournal::PPN_MASUKAN)->first();
                $account          = MasterCoa::find($defaultJournal->coa_id);
                $accountCode      = $account->coa_code;

                $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

                $journalLine->journal_header_id      = $journalHeader->journal_header_id;
                $journalLine->account_combination_id = $accountCombination->account_combination_id;
                $journalLine->debet                  = $modelLine->amount * $modelLine->tax / 100;
                $journalLine->credit                 = 0;
                $journalLine->description            = 'Account Debit Tax';

                $journalLine->created_date = $now;
                $journalLine->created_by = \Auth::user()->id;

                try {
                    $journalLine->save();
                } catch (\Exception $e) {
                    return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }
            }

            // insert journal line credit
            $journalLine      = new JournalLine();
            $modelPayableType = $model->type;
            $account          = $modelPayableType->coaC;
            $accountCode      = $account->coa_code;

            $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

            $journalLine->journal_header_id      = $journalHeader->journal_header_id;
            $journalLine->account_combination_id = $accountCombination->account_combination_id;
            $journalLine->debet                  = 0;
            $journalLine->credit                 = ($modelLine->amount * $modelLine->tax / 100) + $modelLine->amount;
            $journalLine->description            = 'Account Credit AP Type';

            $journalLine->created_date = $now;
            $journalLine->created_by = \Auth::user()->id;

            try {
                $journalLine->save();
            } catch (\Exception $e) {
                return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            $userNotif = NotificationService::getUserNotification([Role::CASHIER]);
            foreach ($userNotif as $user) {
                $notif             = new Notification();
                $notif->branch_id  = \Session::get('currentBranch')->branch_id;
                $notif->category   = 'Invoice DP PO Approved';
                $notif->message    = 'Invoice DP PO - '.$model->invoice_number.' is ready to be paid.';
                $notif->url        = PaymentController::URL.'/add/'.$model->header_id;
                $notif->created_at = new \DateTime();
                $notif->user_id    = $user->id;
                $notif->role_id    = $user->role_id;
                $notif->save();
            }
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('payable/menu.dp-invoice').' '.$model->invoice_number])
        );

        return redirect(self::URL);
    }

    public function cancel(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'cancel'])) {
            abort(403);
        }

        $model = InvoiceHeader::find($request->get('id'));
        if ($model === null) {
            abort(404);
        }

        if ($model->getTotalPayment() > 0) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => trans('payable/menu.dp-invoice').' '.$model->invoice_number.' '.trans('shared/common.cannot-cancel-payment-exist'). '. Payment exist on '.number_format($model->getTotalPayment()) ]);
        }

        if ($model->status == InvoiceHeader::APPROVED) {
            $modelLine = $model->lineOne;
            $modelPO   = PurchaseOrderHeader::find($modelLine->po_header_id);

            // insert journal
            $journalHeader           = new JournalHeader();
            $journalHeader->category = JournalHeader::CANCEL_INVOICE_DP;
            $journalHeader->status   = JournalHeader::OPEN;

            $now                    = new \DateTime();
            $period                 = new \DateTime($now->format('Y-m-1'));
            $journalHeader->period  = $period;

            $journalHeader->description = $model->invoice_number.' - '.$modelPO->po_number;;
            $journalHeader->branch_id   = \Session::get('currentBranch')->branch_id;

            $journalHeader->journal_date = $now;
            $journalHeader->created_date = $now;
            $journalHeader->created_by = \Auth::user()->id;
            $journalHeader->journal_number = $this->getJournalNumber($journalHeader);

            try {
                $journalHeader->save();
            } catch (\Exception $e) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            // insert journal line debet
            $journalLine      = new JournalLine();
            $modelPayableType = $model->type;
            $account          = $modelPayableType->coaC;
            $accountCode      = $account->coa_code;
            $subAccount       = $model->vendor;
            $subAccountCode   = $subAccount->subaccount_code;

            $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

            $journalLine->journal_header_id      = $journalHeader->journal_header_id;
            $journalLine->account_combination_id = $accountCombination->account_combination_id;
            $journalLine->debet                  = ($modelLine->amount * $modelLine->tax / 100) + $modelLine->amount;
            $journalLine->credit                 = 0;
            $journalLine->description            = 'Account Credit AP Type';

            $journalLine->created_date = $now;
            $journalLine->created_by = \Auth::user()->id;

            try {
                $journalLine->save();
            } catch (\Exception $e) {
                return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            // insert journal line credit
            $journalLine      = new JournalLine();
            $modelPayableType = $model->type;
            $account          = $modelPayableType->coaD;
            $accountCode      = $account->coa_code;

            $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

            $journalLine->journal_header_id      = $journalHeader->journal_header_id;
            // $journalLine->account_combination_id = $accountCombination->account_combination_id;
            $journalLine->account_combination_id = $modelLine->account_comb_id;
            $journalLine->debet                 = 0;
            $journalLine->credit                  = $modelLine->amount;
            $journalLine->description            = 'Account Debit AP Type';

            $journalLine->created_date = $now;
            $journalLine->created_by = \Auth::user()->id;

            try {
                $journalLine->save();
            } catch (\Exception $e) {
                return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            //insert gl tax if exist credit
            if (!empty($modelLine->tax)) {
                $journalLine      = new JournalLine();
                $modelPayableType = $model->type;
                $defaultJournal   = SettingJournal::where('setting_name', '=', SettingJournal::PPN_MASUKAN)->first();
                $account          = MasterCoa::find($defaultJournal->coa_id);
                $accountCode      = $account->coa_code;

                $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

                $journalLine->journal_header_id      = $journalHeader->journal_header_id;
                $journalLine->account_combination_id = $accountCombination->account_combination_id;
                $journalLine->debet                 = 0;
                $journalLine->credit                  = $modelLine->amount * $modelLine->tax / 100;
                $journalLine->description            = 'Account Debit Tax';

                $journalLine->created_date = $now;
                $journalLine->created_by = \Auth::user()->id;

                try {
                    $journalLine->save();
                } catch (\Exception $e) {
                    return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }
            }
        }

        $model->description = $model->description.'. Canceled reason is "'.$request->get('reason').'"'; 
        $model->status = InvoiceHeader::CANCELED;
        $model->last_updated_by   = \Auth::user()->id;
        $model->last_updated_date = new \DateTime;
        $model->save();

        $userNotif = NotificationService::getUserNotification([Role::CASHIER, Role::BRANCH_MANAGER]);
        foreach ($userNotif as $user) {
            $notif             = new Notification();
            $notif->branch_id  = \Session::get('currentBranch')->branch_id;
            $notif->category   = 'Invoice DP PO Canceled';
            $notif->message    = 'Invoice DP PO Canceled '.$model->invoice_number.' Canceled reason is "'.$request->get('reason').'"'; 
            // $notif->url        = self::URL.'/edit/'.$model->header_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->save();
        }

        $request->session()->flash(
            'successMessage',
            trans('purchasing/fields.canceled-message', ['variable' => trans('payable/menu.dp-invoice').' '.$model->invoice_number])
        );

        return redirect(self::URL);
    }

    public function printPdfDetail(Request $request, $id)
    {
        $filters = \Session::get('filters');
        $model= InvoiceHeader::find($id);

        $header = view('print.header-pdf', ['title' => trans('payable/menu.dp-invoice')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('payable::transaction.dp-invoice.print-pdf-detail', [
            'model'  => $model,
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('payable/menu.dp-invoice'));
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A5');
        \PDF::writeHTML($html);
        \PDF::Output($model->invoice_number.'.pdf');
        \PDF::reset();
    }

    protected function getJournalNumber(JournalHeader $model)
    {
        $branch      = MasterBranch::find($model->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('gl.trans_journal_header')
                            ->where('branch_id', '=', $model->branch_id)
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->count();

        return 'J.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 5);
    }

    public function optionTax()
    {
        return [
            '5',
            '10',
            '15',
            '20',
        ];
    }

    protected function getCoa($coaCode, $segmentName){
        $coa = MasterCoa::where('coa_code', '=', $coaCode)->where('segment_name', '=', $segmentName)->first();
        return [
            'coaId'             => $coa->coa_id,
            'coaDescription'    => $coa->description,
        ];
    }

    protected function getInvoiceNumber(InvoiceHeader $model)
    {
        $date = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $branch = MasterBranch::find($model->branch_id);
        $count = \DB::table('ap.invoice_header')
                        ->where('branch_id', '=', $model->branch_id)
                        ->where('created_date', '>=', $date->format('Y-1-1 00:00:00'))
                        ->where('created_date', '<=', $date->format('Y-12-31 23:59:59'))
                        ->count();

        return 'IAP.'.$branch->branch_code.'.'.$date->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }

    public static function optionPo()
    {
        return \DB::table('po.po_headers')
                ->select('po_headers.*', 'mst_vendor.vendor_code', 'mst_vendor.vendor_name', 'mst_vendor.address')
                ->join('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'po_headers.supplier_id')
                ->where('po_headers.branch_id', '=', \Session::get('currentBranch')->branch_id)
                ->where(function($query){
                    $query->where('po_headers.status', '=', PurchaseOrderHeader::APPROVED)
                    ->orWhere('po_headers.status', '=', PurchaseOrderHeader::CLOSED);
                })
                ->whereRaw('po_headers.header_id NOT IN (SELECT po_header_id FROM ap.invoice_line join ap.invoice_header on invoice_header.header_id = invoice_line.header_id where invoice_header.status != \''.InvoiceHeader::CANCELED.'\' and po_header_id is not null)')
                ->distinct()
                ->get();
    }

    protected function getStatus(){
        return [
            InvoiceHeader::INCOMPLETE,
            InvoiceHeader::APPROVED,
            InvoiceHeader::CLOSED,
            InvoiceHeader::CANCELED,
        ];
    }
 
}
