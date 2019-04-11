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
use App\Modules\Generalledger\Model\Master\SettingJournal;
use App\Modules\Generalledger\Model\Master\MasterAccountCombination;
use App\Modules\Generalledger\Service\Master\AccountCombinationService;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Payable\Model\Master\MasterApType;
use App\Modules\Payable\Model\Master\MasterVendor;
use App\Modules\Payable\Service\Master\VendorService;
use App\Modules\Payable\Http\Controllers\Transaction\PaymentController;
use App\Notification;
use App\Service\Penomoran;
use App\Service\NotificationService;
use App\Role;

class PurchaseOrderInvoiceController extends Controller
{
    const RESOURCE = 'Payable\Transaction\PurchaseOrderInvoice';
    const URL      = 'payable/transaction/po-invoice';
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

        $query   = $this->getQuery($request, $filters);

        return view('payable::transaction.po-invoice.index', [
            'models'       => $query->paginate(10),
            'filters'      => $filters,
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
            'optionStatus' => $this->getStatus(),
            'optionType'   => $this->getType(),
        ]);
    }

    protected function getQuery(Request $request, $filters){
        if (empty($filters['jenis']) || $filters['jenis'] == 'headers') {
            $query   = \DB::table('ap.invoice_header')
                        ->select(
                            'invoice_header.header_id',
                            'invoice_header.invoice_number',
                            'invoice_header.total_amount',
                            'invoice_header.description',
                            'invoice_header.status',
                            'invoice_header.created_date',
                            'mst_vendor.vendor_code',
                            'mst_vendor.vendor_name',
                            'mst_vendor.address',
                            'mst_vendor.phone_number',
                            'mst_ap_type.type_id',
                            'mst_ap_type.type_name'
                          )
                        ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'invoice_header.vendor_id')
                        ->leftJoin('ap.mst_ap_type', 'mst_ap_type.type_id', '=', 'invoice_header.type_id')
                        ->leftJoin('ap.invoice_line', 'invoice_line.header_id', '=', 'invoice_header.header_id')
                        ->leftJoin('po.po_headers', 'po_headers.header_id', '=', 'invoice_line.po_header_id')
                        ->where(function ($query) {
                              $query->where('invoice_header.type_id', '=', InvoiceHeader::PURCHASE_ORDER)
                                    ->orWhere('invoice_header.type_id', '=', InvoiceHeader::PURCHASE_ORDER_CREDIT);
                          })
                        ->where('invoice_header.branch_id', '=', $request->session()->get('currentBranch')->branch_id)
                        ->orderBy('invoice_header.created_date', 'desc')
                        ->distinct();
        }else{
            $query   = \DB::table('ap.invoice_line')
                        ->select(
                            'invoice_line.*',
                            'invoice_header.header_id',
                            'invoice_header.invoice_number',
                            'invoice_header.status',
                            'invoice_header.created_date',
                            'po_headers.po_number',
                            'po_headers.description',
                            'po_headers.header_id as po_header_id',
                            'mst_vendor.vendor_code',
                            'mst_vendor.vendor_name',
                            'mst_vendor.address',
                            'mst_vendor.phone_number',
                            'mst_ap_type.type_id',
                            'mst_ap_type.type_name'
                          )
                        ->leftJoin('ap.invoice_header', 'invoice_header.header_id', '=', 'invoice_line.header_id')
                        ->leftJoin('po.po_headers', 'po_headers.header_id', '=', 'invoice_line.po_header_id')
                        ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'invoice_header.vendor_id')
                        ->leftJoin('ap.mst_ap_type', 'mst_ap_type.type_id', '=', 'invoice_header.type_id')
                        ->where(function ($query) {
                              $query->where('invoice_header.type_id', '=', InvoiceHeader::PURCHASE_ORDER)
                                    ->orWhere('invoice_header.type_id', '=', InvoiceHeader::PURCHASE_ORDER_CREDIT);
                          })
                        ->where('invoice_header.branch_id', '=', $request->session()->get('currentBranch')->branch_id)
                        ->orderBy('invoice_header.created_date', 'desc')
                        ->distinct();
        }

        if (!empty($filters['invoiceNumber'])) {
            $query->where('invoice_header.invoice_number', 'ilike', '%'.$filters['invoiceNumber'].'%');
        }

        if (!empty($filters['vendor'])) {
            $query->where('vendor_name', 'ilike', '%'.$filters['vendor'].'%');
        }

        if (!empty($filters['poNumber'])) {
            $query->where('po_headers.po_number', 'ilike', '%'.$filters['poNumber'].'%');
        }

        if ((empty($filters['jenis']) || $filters['jenis'] == 'headers') && !empty($filters['description'])) {
            $query->where('invoice_header.description', 'ilike', '%'.$filters['description'].'%');
        }else{
            $query->where('po_headers.description', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['type'])) {
            $query->where('invoice_header.type_id', '=', $filters['type']);
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

        $query->orderBy('invoice_number', 'desc');

        return $query;
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new InvoiceHeader();
        $model->status = InvoiceHeader::INCOMPLETE;

        return view('payable::transaction.po-invoice.add', [
            'title'             => trans('shared/common.add'),
            'model'             => $model,
            'optionTax'         => $this->optionTax(),
            'optionType'        => $this->getType(),
            'url'               => self::URL,
            'resource'          => self::RESOURCE,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $model = InvoiceHeader::find($id);
        if ($model === null) {
            abort(404);
        }

        $data = [
            'title'      => trans('shared/common.edit'),
            'model'      => $model,
            'url'        => self::URL,
            'resource'   => self::RESOURCE,
            'optionType' => $this->getType(),
            'optionTax'  => $this->optionTax(),
        ];

        if ($request->user()->can('access', [self::RESOURCE, 'update'])) {
            return view('payable::transaction.po-invoice.add', $data);
        } else {
            return view('payable::transaction.po-invoice.detail', $data);
        }
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? InvoiceHeader::find($id) : new InvoiceHeader();

        $this->validate($request, [
            'type'              => 'required',
            'vendorId'          => 'required',
            'descriptionHeader' => 'required',
        ]);

        if (empty($request->get('lineId'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Lines are required']);
        }

        $totalAmount = 0;
        $lines = $model->lines()->get();
        $lineIds = $request->get('lineId');
        $poHeaderIds = $request->get('poHeaderId');

        $message = [];
        foreach ($request->get('poHeaderId') as $poHeaderId) {
            $i = array_search($poHeaderId, $poHeaderIds);
            $amountForm   = intval(str_replace(',', '', $request->get('amount')[$i]));
            $amountPo     = $this->getAmountPo($poHeaderId);
            $invoiceExist = $this->getPoInvoiceExist($id, $poHeaderId);
            $max          = intval($amountPo->total - $invoiceExist->amount);
            if ($amountForm > $max)  {
                $message [] = $request->get('poNumber')[$i]. ' invoice remain is '. number_format($max) .'.';
            }
        }
        if(!empty($message)){
            $string = '';
            foreach ($message as $mess) {
                $string = $string.' '.$mess;
            }
            $stringMessage = 'Invoice exceed!'. $string;
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $stringMessage]);
        }
       
        if (empty($model->branch_id)) {
            $model->branch_id = $request->session()->get('currentBranch')->branch_id;
        }

        if (empty($model->status)) {
            $model->status = InvoiceHeader::INCOMPLETE;
        }

        $now = new \DateTime();

        if (empty($model->invoice_number)) {
            $model->invoice_number = $this->getInvoiceNumber($model);
        }

        $model->description = $request->get('descriptionHeader');
        if (!empty($request->get('vendorId'))) {
            $model->vendor_id = $request->get('vendorId');
            $model->vendor_address = $request->get('vendorAddress');
        }

        $model->type_id = $request->get('type');

        if (empty($id)) {
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

        $type = MasterApType::find($model->type_id);
        $vendor         = $model->vendor;

        $account        = MasterCoa::find($type->coa_id_d);
        $accountCode    = $account->coa_code;
        $subAccountCode = $vendor->subaccount_code;

        $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

        $lines = $model->lines()->delete();

        for ($i = 0; $i < count($request->get('lineId')); $i++) {
            $line = new InvoiceLine();
            $line->header_id       = intval($model->header_id);
            $line->po_header_id    = intval($request->get('poHeaderId')[$i]);
            $line->description     = $request->get('description')[$i];
            $line->amount          = intval(str_replace(',', '', $request->get('amount')[$i]));
            $line->interest_bank   = intval(str_replace(',', '', $request->get('interestBank')[$i]));
            $line->tax             = intval(str_replace(',', '', $request->get('tax')[$i]));
            $line->account_comb_id = $accountCombination->account_combination_id;

            $line->created_date = new \DateTime();
            $line->created_by   = \Auth::user()->id;
            
            try {
                $line->save();
            } catch (\Exception $e) {
                return redirect(self::URL.'/edit/'.$model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }
            $totalAmount += $line->amount;

            if ($request->get('btn-approve') !== null) {
                $modelPO                 = PurchaseOrderHeader::find($line->po_header_id);

                // insert journal
                $journalHeader           = new JournalHeader();
                $journalHeader->category = JournalHeader::INVOICE_PO;
                $journalHeader->status   = JournalHeader::OPEN;

                $now                    = new \DateTime();
                $period                 = new \DateTime($now->format('Y-m-1'));
                $journalHeader->period  = $period;

                $journalHeader->description = $model->invoice_number.' - '.$modelPO->po_number;
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

                $totalTax      = 0;
                $totalDp       = 0;
                $totalInterest = 0;

                if (!empty($line->tax)) {
                    $totalTax += $line->amount * $line->tax / 100;
                }

                if (!empty($line->interest_bank)) {
                    $totalInterest += $line->interest_bank;
                }

                if (empty($this->checkDpInclude($line->po_header_id))) {
                    $invoiceDp  = $this->getInvoiceDp($line->po_header_id);
                    if (!empty($invoiceDp)) {
                        $totalDp   += $invoiceDp->amount + ($invoiceDp->amount * $invoiceDp->tax / 100);
                    }
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
                $journalLine->debet                  = $line->amount + $totalDp + $totalInterest;
                $journalLine->credit                 = 0;
                $journalLine->description            = 'Account Debit AP Type';

                $journalLine->created_date = $now;
                $journalLine->created_by = \Auth::user()->id;

                try {
                    $journalLine->save();
                } catch (\Exception $e) {
                    return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }

                //insert gl tax if exist
                if (!empty($line->tax)) {
                    $journalLine      = new JournalLine();
                    $modelPayableType = $model->type;
                    $defaultJournal   = SettingJournal::where('setting_name', '=', SettingJournal::PPN_MASUKAN)->first();
                    $account          = MasterCoa::find($defaultJournal->coa_id);
                    $accountCode      = $account->coa_code;

                    $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

                    $journalLine->journal_header_id      = $journalHeader->journal_header_id;
                    $journalLine->account_combination_id = $accountCombination->account_combination_id;
                    $journalLine->debet                  = $totalTax;
                    $journalLine->credit                 = 0;
                    $journalLine->description            = 'Account Tax';

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
                $journalLine->credit                 = $totalTax + $line->amount;
                $journalLine->description            = 'Account Credit AP Type';

                $journalLine->created_date = $now;
                $journalLine->created_by = \Auth::user()->id;

                try {
                    $journalLine->save();
                } catch (\Exception $e) {
                    return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }

                //insert gl interest if exist
                if (!empty($line->interest_bank)) {
                    $journalLine      = new JournalLine();
                    $modelPayableType = $model->type;
                    $defaultJournal   = SettingJournal::where('setting_name', '=', SettingJournal::INTEREST)->first();
                    $account          = MasterCoa::find($defaultJournal->coa_id);
                    $accountCode      = $account->coa_code;

                    $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

                    $journalLine->journal_header_id      = $journalHeader->journal_header_id;
                    $journalLine->account_combination_id = $accountCombination->account_combination_id;
                    $journalLine->debet                  = 0;
                    $journalLine->credit                 = $totalInterest;
                    $journalLine->description            = 'Account Interest';

                    $journalLine->created_date = $now;
                    $journalLine->created_by = \Auth::user()->id;

                    try {
                        $journalLine->save();
                    } catch (\Exception $e) {
                        return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                    }
                }

                if (empty($this->checkDpInclude($line->po_header_id))) {
                    $invoiceDp = $this->getInvoiceDp($line->po_header_id);
                    if (!empty($invoiceDp)) {
                        $line->invoice_dp_id = $invoiceDp->header_id;
                        try {
                            $line->save();
                        } catch (\Exception $e) {
                            return redirect(self::URL.'/edit/'.$model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                        }

                        $modelDp = InvoiceHeader::find($invoiceDp->header_id);


                        //insert gl dp if exist
                        $journalLine      = new JournalLine();
                        $accountCode      = $modelDp->type->coaD->coa_code;

                        $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

                        $journalLine->journal_header_id      = $journalHeader->journal_header_id;
                        $journalLine->account_combination_id = $accountCombination->account_combination_id;
                        $journalLine->debet                  = 0;
                        $journalLine->credit                 = $totalDp;
                        $journalLine->description            = 'Account Down Payment';

                        $journalLine->created_date = $now;
                        $journalLine->created_by = \Auth::user()->id;

                        try {
                            $journalLine->save();
                        } catch (\Exception $e) {
                            return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                        }
                    }
                }
            }
        }

        $model->total_amount = $totalAmount;

        if ($request->get('btn-approve') !== null) {
            $model->status = InvoiceHeader::APPROVED;
            $model->approved_date = $now;
            $model->approved_by = \Auth::user()->id;
            
            $userNotif = NotificationService::getUserNotification([Role::CASHIER]);

            foreach ($userNotif as $user) {
                $notif             = new Notification();
                $notif->branch_id  = \Session::get('currentBranch')->branch_id;
                $notif->category   = 'Invoice PO Approved';
                $notif->message    = 'Invoice PO - '.$model->invoice_number.' is ready to be paid.';
                $notif->url        = PaymentController::URL.'/add/'.$model->header_id;
                $notif->created_at = new \DateTime();
                $notif->user_id    = $user->id;
                $notif->role_id    = $user->role_id;
                $notif->save();
            }
        }
        
        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(self::URL.'/edit/'.$model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('payable/menu.po-invoice').' '.$model->invoice_number])
        );

        return redirect(self::URL);
    }

    public function printPdfDetail(Request $request, $id)
    {
        $filters = \Session::get('filters');
        $model= InvoiceHeader::find($id);

        $header = view('print.header-pdf', ['title' => trans('payable/menu.po-invoice')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('payable::transaction.po-invoice.print-pdf-detail', [
            'model'  => $model,
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('payable/menu.po-invoice'));
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A5');
        \PDF::writeHTML($html);
        \PDF::Output($model->invoice_number.'.pdf');
        \PDF::reset();
    }

    protected function checkDpInclude($poHeaderId){

        return \DB::table('ap.invoice_line')
                        ->join('ap.invoice_header', 'invoice_header.header_id', '=', 'invoice_line.header_id')
                        ->where('invoice_header.type_id', '=', InvoiceHeader::PURCHASE_ORDER)
                        ->where('invoice_line.po_header_id', '=', $poHeaderId)
                        ->whereRaw('invoice_line.invoice_dp_id is not null')
                        ->where(function ($query) {
                          $query->where('invoice_header.status', '=', InvoiceHeader::APPROVED)
                                ->orWhere('invoice_header.status', '=', InvoiceHeader::CLOSED);
                        })
                        ->first();
    }

    protected function getInvoiceDp($poHeaderId){

        return \DB::table('ap.invoice_line')
                        ->select('invoice_line.header_id', 'invoice_line.amount', 'invoice_line.tax')
                        ->join('ap.invoice_header', 'invoice_header.header_id', '=', 'invoice_line.header_id')
                        ->where('invoice_header.type_id', '=', InvoiceHeader::DOWN_PAYMENT)
                        ->where('invoice_line.po_header_id', '=', $poHeaderId)
                        ->where(function ($query) {
                          $query->where('invoice_header.status', '=', InvoiceHeader::APPROVED)
                                ->orWhere('invoice_header.status', '=', InvoiceHeader::CLOSED);
                        })
                        ->first();
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
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => trans('payable/menu.po-invoice').' '.$model->invoice_number.' '.trans('shared/common.cannot-cancel-payment-exist'). '. Payment exist on '.number_format($model->getTotalPayment()) ]);
        }

        if ($model->status == InvoiceHeader::APPROVED) {
            foreach ($model->lines as $line) {
                $modelPO                 = PurchaseOrderHeader::find($line->po_header_id);
                // insert journal
                $journalHeader           = new JournalHeader();
                $journalHeader->category = JournalHeader::CANCEL_INVOICE_PO;
                $journalHeader->status   = JournalHeader::OPEN;

                $now                    = new \DateTime();
                $period                 = new \DateTime($now->format('Y-m-1'));
                $journalHeader->period  = $period;

                $journalHeader->description = $model->invoice_number.' - '.$modelPO->po_number;
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

                $totalTax      = 0;
                $totalDp       = 0;
                $totalInterest = 0;

                if (!empty($line->tax)) {
                    $totalTax += $line->amount * $line->tax / 100;
                }

                if (!empty($line->interest_bank)) {
                    $totalInterest += $line->interest_bank;
                }

                if (!empty($line->invoice_dp_id)) {
                    $invoiceDp     = InvoiceHeader::find($line->invoice_dp_id);
                    $invoiceDpLine = $invoiceDp->lineOne;
                    $totalDp   += $invoiceDpLine->amount + ($invoiceDpLine->amount * $invoiceDpLine->tax / 100);
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
                $journalLine->debet                  = $totalTax + $line->amount;
                $journalLine->credit                 = 0;
                $journalLine->description            = 'Account Credit AP Type';

                $journalLine->created_date = $now;
                $journalLine->created_by = \Auth::user()->id;

                try {
                    $journalLine->save();
                } catch (\Exception $e) {
                    return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }

                //insert gl interest if exist
                if (!empty($line->interest_bank)) {
                    $journalLine      = new JournalLine();
                    $modelPayableType = $model->type;
                    $defaultJournal   = SettingJournal::where('setting_name', '=', SettingJournal::INTEREST)->first();
                    $account          = MasterCoa::find($defaultJournal->coa_id);
                    $accountCode      = $account->coa_code;

                    $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

                    $journalLine->journal_header_id      = $journalHeader->journal_header_id;
                    $journalLine->account_combination_id = $accountCombination->account_combination_id;
                    $journalLine->debet                  = $totalInterest;
                    $journalLine->credit                 = 0;
                    $journalLine->description            = 'Account Interest';

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
                $account          = $modelPayableType->coaD;
                $accountCode      = $account->coa_code;

                $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

                $journalLine->journal_header_id      = $journalHeader->journal_header_id;
                // $journalLine->account_combination_id = $accountCombination->account_combination_id;
                $journalLine->account_combination_id = $line->account_comb_id;
                $journalLine->debet                  = 0;
                $journalLine->credit                 = $line->amount + $totalDp + $totalInterest;
                $journalLine->description            = 'Account Debit AP Type';

                $journalLine->created_date = $now;
                $journalLine->created_by = \Auth::user()->id;

                try {
                    $journalLine->save();
                } catch (\Exception $e) {
                    return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }

                //insert gl tax if exist
                if (!empty($line->invoice_dp_id)) {
                    $modelDp = InvoiceHeader::find($line->invoice_dp_id);

                    //insert gl dp if exist
                    $journalLine      = new JournalLine();
                    $accountCode      = $modelDp->type->coaC->coa_code;

                    $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

                    $journalLine->journal_header_id      = $journalHeader->journal_header_id;
                    $journalLine->account_combination_id = $accountCombination->account_combination_id;
                    $journalLine->debet                  = $totalDp;
                    $journalLine->credit                 = 0;
                    $journalLine->description            = 'Account Down Payment';

                    $journalLine->created_date = $now;
                    $journalLine->created_by = \Auth::user()->id;

                    try {
                        $journalLine->save();
                    } catch (\Exception $e) {
                        return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                    }
                }
                
                if (!empty($line->tax)) {
                    $journalLine      = new JournalLine();
                    $modelPayableType = $model->type;
                    $defaultJournal   = SettingJournal::where('setting_name', '=', SettingJournal::PPN_MASUKAN)->first();
                    $account          = MasterCoa::find($defaultJournal->coa_id);
                    $accountCode      = $account->coa_code;

                    $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

                    $journalLine->journal_header_id      = $journalHeader->journal_header_id;
                    $journalLine->account_combination_id = $accountCombination->account_combination_id;
                    $journalLine->debet                  = 0;
                    $journalLine->credit                 = $totalTax;
                    $journalLine->description            = 'Account Tax';

                    $journalLine->created_date = $now;
                    $journalLine->created_by = \Auth::user()->id;

                    try {
                        $journalLine->save();
                    } catch (\Exception $e) {
                        return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                    }
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
            $notif->category   = 'Invoice PO Canceled';
            $notif->message    = 'Invoice PO Canceled '.$model->invoice_number.' Canceled reason is "'.$request->get('reason').'"'; 
            // $notif->url        = self::URL.'/edit/'.$model->header_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->save();
        }

        $request->session()->flash(
            'successMessage',
            trans('purchasing/fields.canceled-message', ['variable' => trans('payable/menu.po-invoice').' '.$model->invoice_number])
        );

        return redirect(self::URL);
    }

    protected function getAmountPo($poHeaderId){
        return \DB::table('po.po_headers')
                    ->selectRaw('sum(total) as total')
                    ->where('header_id', '=', $poHeaderId)
                    // ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->first();
    }

    protected function getPoInvoiceExist($headerId, $poHeaderId){

        return \DB::table('ap.invoice_line')
                    ->selectRaw('sum(amount) as amount')
                    ->join('ap.invoice_header', 'invoice_header.header_id', '=', 'invoice_line.header_id')
                    ->where('invoice_header.header_id', '!=', $headerId)
                    ->where('po_header_id', '=', $poHeaderId)
                    ->where(function ($query) {
                          $query->where('invoice_header.status', '=', InvoiceHeader::APPROVED)
                                ->orWhere('invoice_header.status', '=', InvoiceHeader::INCOMPLETE)
                                ->orWhere('invoice_header.status', '=', InvoiceHeader::CLOSED);
                      })
                    // ->where('invoice_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->first();
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

    public function getJsonVendor(Request $request)
    {
        $search = $request->get('search');
        $query = VendorService::getQueryVendorSupplier();

        $query->select('mst_vendor.vendor_id', 'mst_vendor.vendor_code', 'mst_vendor.vendor_name', 'mst_vendor.address', 'mst_vendor.phone_number')
                ->orderBy('mst_vendor.vendor_code', 'asc')
                ->where(function ($query) use ($search) {
                    $query->where('mst_vendor.vendor_name', 'ilike', '%'.$search.'%')
                          ->orWhere('mst_vendor.vendor_code', 'ilike', '%'.$search.'%')
                          ->orWhere('mst_vendor.address', 'ilike', '%'.$search.'%')
                          ->orWhere('mst_vendor.phone_number', 'ilike', '%'.$search.'%');
                })
                ->take(10);

        return response()->json($query->get());
    }

    public function getJsonPo(Request $request)
    {
        $maxData   = 10;
        $iteration = 1;
        $isFull    = false;
        $data      = [];

        while(!$isFull) {
            $dataQuery = $this->getDataQueryPurchaseOrder($request, $maxData, $iteration);
            if (empty($dataQuery)) {
                $isFull = true;
            }

            foreach ($dataQuery as $po) {
                $data[] = $po;

                if (count($data) >= $maxData) {
                    $isFull = true;
                    break;
                }
            }

            $iteration++;
        }

        return response()->json($data);
    }

    protected function getDataQueryPurchaseOrder(Request $request, $maxData, $iteration){
        $search   = $request->get('search');
        $vendorId = $request->get('vendorId');
        $id       = $request->get('id');
        $type     = $request->get('type');

        $query = \DB::table('po.po_headers')
                        ->select('po_headers.header_id', 'po_headers.po_number', 'po_headers.description', 'mst_vendor.vendor_code', 'mst_vendor.vendor_name')
                        ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'po_headers.supplier_id')
                        // ->where('po_headers.branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->where(function ($query){
                            $query->where('po_headers.status', '=', PurchaseOrderHeader::APPROVED)
                              ->orWhere('po_headers.status', '=', PurchaseOrderHeader::CLOSED);
                        })
                        ->where(function ($query) use ($search) {
                            $query->where('mst_vendor.vendor_name', 'ilike', '%'.$search.'%')
                              ->orWhere('mst_vendor.vendor_code', 'ilike', '%'.$search.'%')
                              ->orWhere('po_headers.po_number', 'ilike', '%'.$search.'%')
                              ->orWhere('po_headers.description', 'ilike', '%'.$search.'%');
                        })
                        ->orderBy('po_headers.created_date', 'desc');

        if (!empty($vendorId)) {
            $query->where('po_headers.supplier_id', '=', $vendorId);
        }

        $arrPo = [];
        $skip     = ($iteration - 1) * $maxData;
        foreach($query->take($maxData)->skip($skip)->get() as $po) {
            $modelPo = PurchaseOrderHeader::find($po->header_id);
            $po->total_remain = $modelPo->getTotalRemain();
            $po->total_dp     = $modelPo->getAmountDp();
            $po->total        = $modelPo->total;
            $po->total_dp_incomplete = $modelPo->getAmountDpIncomplete();

            if (($type == InvoiceHeader::PURCHASE_ORDER_CREDIT && $modelPo->getTotalRemainWithoutDp() != $po->total) || $po->total_remain <=0  || !empty($po->total_dp_incomplete)) {
                continue;
            }
            $po->dp =  $modelPo->getAmountDp();
            $po->total_amount = $modelPo->total;
            $arrPo[] = $po;
        }
        return $arrPo;
    }

    public function printExcelIndex(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters);

        \Excel::create(trans('payable/menu.po-invoice'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('payable/menu.po-invoice'));
                });

                $sheet->cells('A3:K3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('payable/fields.invoice-number'),
                    trans('shared/common.type'),
                    trans('payable/fields.trading'),
                    trans('shared/common.total-amount'),
                    trans('shared/common.total-interest'),
                    trans('shared/common.total-tax'),
                    trans('shared/common.total-invoice'),
                    trans('shared/common.description'),
                    trans('shared/common.date'),
                    trans('shared/common.status'),
                ]);

                $currentRow = 4;
                $num = 1;
                $totalAmount = 0;
                $totalInterest = 0;
                $totalTax = 0;
                $totalInvoice = 0;
                foreach($query->get() as $model) {
                    $invoice = InvoiceHeader::find($model->header_id);
                    $date    = !empty($model->created_date) ? new \DateTime($model->created_date) : null;

                    $data = [
                        $num++,
                        $model->invoice_number,
                        $model->type_name,
                        $model->vendor_name,
                        $invoice->getTotalAmount(),
                        $invoice->getTotalInterest(),
                        $invoice->getTotalTax(),
                        $invoice->getTotalInvoice(),
                        $date !== null ? $date->format('d-m-Y') : '',
                        $model->description,
                        $model->status,
                    ];
                    $totalAmount += $invoice->getTotalAmount();
                    $totalInterest += $invoice->getTotalInterest();
                    $totalTax += $invoice->getTotalTax();
                    $totalInvoice += $invoice->getTotalInvoice();

                    $sheet->row($currentRow++, $data);
                }

                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'D', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalAmount, 'E', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalInterest, 'F', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalTax, 'G', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalInvoice, 'H', $currentRow++);

                $lastDataRow = $currentRow;
                $currentRow = $lastDataRow + 1;
                if (!empty($filters['invoiceNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('payable/fields.invoice-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['invoiceNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                // if (!empty($filters['resiNumber'])) {
                //     $this->addLabelDescriptionCell($sheet, trans('operational/fields.resi-number'), 'B', $currentRow);
                //     $this->addValueDescriptionCell($sheet, $filters['resiNumber'], 'C', $currentRow);
                //     $currentRow++;
                // }
                // if (!empty($filters['customer'])) {
                //     $this->addLabelDescriptionCell($sheet, trans('operational/fields.customer'), 'B', $currentRow);
                //     $this->addValueDescriptionCell($sheet, $filters['customer'], 'C', $currentRow);
                //     $currentRow++;
                // }
                // if (!empty($filters['createdBy'])) {
                //     $this->addLabelDescriptionCell($sheet, trans('shared/common.created-by'), 'B', $currentRow);
                //     $this->addValueDescriptionCell($sheet, $filters['createdBy'], 'C', $currentRow);
                //     $currentRow++;
                // }
                // if (!empty($filters['description'])) {
                //     $this->addLabelDescriptionCell($sheet, trans('shared/common.description'), 'B', $currentRow);
                //     $this->addValueDescriptionCell($sheet, $filters['description'], 'C', $currentRow);
                //     $currentRow++;
                // }
                // if (!empty($filters['type'])) { 
                //     $this->addLabelDescriptionCell($sheet, trans('shared/common.type'), 'B', $currentRow);
                //     $this->addValueDescriptionCell($sheet, $filters['type'], 'C', $currentRow);
                //     $currentRow++;
                // }
                // if (!empty($filters['receiptMethod'])) { 
                //     $this->addLabelDescriptionCell($sheet, trans('payable/fields.receipt-method'), 'B', $currentRow);
                //     $this->addValueDescriptionCell($sheet, $filters['receiptMethod'], 'C', $currentRow);
                //     $currentRow++;
                // }
                if (!empty($filters['dateFrom'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.date-from'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['dateFrom'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['dateTo'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.date-to'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['dateTo'], 'C', $currentRow);
                    $currentRow++;
                }
                
                $currentRow = $lastDataRow + 1;
                $this->addLabelDescriptionCell($sheet, trans('shared/common.date'), 'E', $currentRow);
                $this->addValueDescriptionCell($sheet, $this->now->format('d-m-Y'), 'F', $currentRow);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.user'), 'E', $currentRow + 1);
                $this->addValueDescriptionCell($sheet, \Auth::user()->full_name, 'F', $currentRow + 1);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.branch'), 'E', $currentRow + 2);
                $this->addValueDescriptionCell($sheet, \Session::get('currentBranch')->branch_name, 'F', $currentRow + 2);
                
            });

        })->export('xlsx');
    }

    protected function addLabelDescriptionCell($sheet, $value, $column, $row)
    {
        $sheet->cell($column.$row, function($cell) use($value) {
            $cell->setFont(['bold' => true]);
            $cell->setValue($value);
        });
    }

    protected function addValueDescriptionCell($sheet, $value, $column, $row)
    {
        $sheet->cell($column.$row, function($cell) use($value) {
            $cell->setValue($value);
        });
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
   
    protected function getStatus(){
        return [
            InvoiceHeader::INCOMPLETE,
            InvoiceHeader::APPROVED,
            InvoiceHeader::CLOSED,
            InvoiceHeader::CANCELED,
        ];
    }

    protected function getType(){
        return \DB::table('ap.mst_ap_type')
                    ->where(function ($query) {
                          $query->where('mst_ap_type.type_id', '=', InvoiceHeader::PURCHASE_ORDER)
                                ->orWhere('mst_ap_type.type_id', '=', InvoiceHeader::PURCHASE_ORDER_CREDIT);
                      })
                    ->get();
    }
 
}
