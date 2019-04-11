<?php

namespace App\Modules\Payable\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader;
use App\Modules\Operational\Model\Transaction\DeliveryOrderLine;
use App\Modules\Payable\Model\Transaction\InvoiceHeader;
use App\Modules\Payable\Model\Transaction\InvoiceLine;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Modules\Generalledger\Model\Master\SettingJournal;
use App\Modules\Generalledger\Model\Master\MasterAccountCombination;
use App\Modules\Generalledger\Service\Master\AccountCombinationService;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Payable\Model\Master\MasterApType;
use App\Modules\Payable\Model\Master\MasterVendor;
use App\Modules\Operational\Service\Transaction\DeliveryOrderService;
use App\Modules\Payable\Http\Controllers\Transaction\PaymentController;
use App\Notification;
use App\Service\Penomoran;
use App\Service\NotificationService;
use App\Role;

class DoPartnerInvoiceController extends Controller
{
    const RESOURCE = 'Payable\Transaction\DoPartnerInvoice';
    const URL      = 'payable/transaction/do-partner-invoice';

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

        if (empty($filters['jenis']) || $filters['jenis'] == 'headers') {

            $query   = \DB::table('ap.invoice_header')
                        ->select(
                            'invoice_header.header_id',
                            'invoice_header.invoice_number',
                            'invoice_header.total_amount',
                            'invoice_header.description',
                            'invoice_header.status',
                            'invoice_header.created_date',
                            'trans_delivery_order_header.delivery_order_number',
                            'mst_vendor.vendor_code',
                            'mst_vendor.vendor_name',
                            'mst_vendor.address',
                            'mst_vendor.phone_number',
                            'mst_ap_type.type_id',
                            'mst_ap_type.type_name'
                          )
                        ->leftJoin('ap.invoice_line', 'invoice_line.header_id', '=', 'invoice_header.header_id')
                        ->leftJoin('op.trans_delivery_order_header', 'trans_delivery_order_header.delivery_order_header_id', '=', 'invoice_header.do_header_id')
                        ->leftJoin('op.trans_delivery_order_line', 'trans_delivery_order_line.delivery_order_line_id', '=', 'invoice_line.do_line_id')
                        ->leftJoin('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_delivery_order_line.resi_header_id')
                        ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'invoice_header.vendor_id')
                        ->leftJoin('ap.mst_ap_type', 'mst_ap_type.type_id', '=', 'invoice_header.type_id')
                        ->where('invoice_header.type_id', '=', InvoiceHeader::DO_PARTNER)
                        ->where('invoice_header.branch_id', '=', $request->session()->get('currentBranch')->branch_id)
                        ->orderBy('invoice_header.created_date', 'desc')
                        ->distinct();
        }else{
            $query   = \DB::table('ap.invoice_line')
                        ->select(
                            'invoice_line.description',
                            'invoice_header.header_id', 
                            'invoice_header.invoice_number',
                            'invoice_header.total_amount',
                            'invoice_header.status',
                            'invoice_header.created_date',
                            'trans_delivery_order_header.delivery_order_number',
                            'trans_delivery_order_line.total_coly as coly_send',
                            'trans_resi_header.resi_header_id',
                            'trans_resi_header.resi_number',
                            'mst_vendor.vendor_code',
                            'mst_vendor.vendor_name',
                            'mst_vendor.address',
                            'mst_vendor.phone_number',
                            'mst_ap_type.type_id',
                            'mst_ap_type.type_name'
                          )
                        ->leftJoin('ap.invoice_header', 'invoice_header.header_id', '=', 'invoice_line.header_id')
                        ->leftJoin('op.trans_delivery_order_header', 'trans_delivery_order_header.delivery_order_header_id', '=', 'invoice_header.do_header_id')
                        ->leftJoin('op.trans_delivery_order_line', 'trans_delivery_order_line.delivery_order_line_id', '=', 'invoice_line.do_line_id')
                        ->leftJoin('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_delivery_order_line.resi_header_id')
                        ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'invoice_header.vendor_id')
                        ->leftJoin('ap.mst_ap_type', 'mst_ap_type.type_id', '=', 'invoice_header.type_id')
                        ->where('invoice_header.type_id', '=', InvoiceHeader::DO_PARTNER)
                        ->where('invoice_header.branch_id', '=', $request->session()->get('currentBranch')->branch_id)
                        ->orderBy('invoice_header.created_date', 'desc')
                        ->distinct();
        }

        if (!empty($filters['invoiceNumber'])) {
            $query->where('invoice_number', 'ilike', '%'.$filters['invoiceNumber'].'%');
        }

        if (!empty($filters['doNumber'])) {
            $query->where('trans_delivery_order_header.delivery_order_number', 'ilike', '%'.$filters['doNumber'].'%');
        }

        if ((empty($filters['jenis']) || $filters['jenis'] == 'headers') && !empty($filters['description'])) {
            $query->where('invoice_header.description', 'ilike', '%'.$filters['description'].'%');
        }elseif(!empty($filters['description'])){
            $query->where('invoice_line.description', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['resiNumber'])) {
            $query->where('trans_resi_header.resi_number', 'ilike', '%'.$filters['resiNumber'].'%');
        }

        if (!empty($filters['vendor'])) {
            $query->where('vendor_name', 'ilike', '%'.$filters['vendor'].'%');
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

        return view('payable::transaction.do-partner-invoice.index', [
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

        return view('payable::transaction.do-partner-invoice.add', [
            'title'             => trans('shared/common.add'),
            'model'             => $model,
            'optionTax'         => $this->optionTax(),
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
            'title'             => trans('shared/common.edit'),
            'model'             => $model,
            'url'               => self::URL,
            'resource'          => self::RESOURCE,
            'optionType'        => $this->getType(),
            'optionTax'         => $this->optionTax(),
        ];

        if ($request->user()->can('access', [self::RESOURCE, 'update'])) {
            return view('payable::transaction.do-partner-invoice.add', $data);
        } else {
            return view('payable::transaction.do-partner-invoice.detail', $data);
        }
    }

    public function save(Request $request)
    {
        // var_dump($request->all());exit();
        $id = intval($request->get('id'));
        $model = !empty($id) ? InvoiceHeader::find($id) : new InvoiceHeader();

        $this->validate($request, [
            'doHeaderId'        => 'required',
            'totalInvoice'       => 'required',
            'descriptionHeader' => 'required',
        ]);


        if (empty($request->get('lineId'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Lines are required']);
        }

        $totalAmount = 0;
        $lines = $model->lines()->get();
        $lineIds = $request->get('lineId');
        $doHeaderIds = $request->get('doHeaderId');

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

        $model->do_header_id = $request->get('doHeaderId');
        $model->description = $request->get('descriptionHeader');
        if (!empty($request->get('vendorId'))) {
            $model->vendor_id = $request->get('vendorId');
            $model->vendor_address = $request->get('vendorAddress');
        }

        $model->type_id = InvoiceHeader::DO_PARTNER;

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
            $line->do_line_id      = intval($request->get('doLineId')[$i]);
            $line->description     = $request->get('description')[$i];
            $line->amount          = intval(str_replace(',', '', $request->get('amount')[$i]));
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
                $modelDoHeader   = DeliveryOrderHeader::find($model->do_header_id);
                $modelDoLine     = DeliveryOrderLine::find($line->do_line_id);
                $modelResi       = $modelDoLine->resi;

                // insert journal
                $journalHeader           = new JournalHeader();
                $journalHeader->category = JournalHeader::INVOICE_DO_PARTNER;
                $journalHeader->status   = JournalHeader::OPEN;

                $now                    = new \DateTime();
                $period                 = new \DateTime($now->format('Y-m-1'));
                $journalHeader->period  = $period;

                $journalHeader->description = $model->invoice_number.' - '.$modelDoHeader->delivery_order_number.' - '.$modelResi->resi_number;
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

                if (!empty($line->tax)) {
                    $totalTax += $line->amount * $line->tax / 100;
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
                $journalLine->debet                  = $line->amount;
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
            }
        }

        $model->total_amount = $totalAmount;

        if ($request->get('btn-approve') !== null) {
            $model->status = InvoiceHeader::APPROVED;
            $model->approved_date = new \DateTime();
            $model->approved_by   = \Auth::user()->id;

            $userNotif = NotificationService::getUserNotification([Role::CASHIER]);

            foreach ($userNotif as $user) {
                $notif             = new Notification();
                $notif->branch_id  = \Session::get('currentBranch')->branch_id;
                $notif->category   = 'Invoice DO Partner Approved';
                $notif->message    = 'Invoice DO Partner - '.$model->invoice_number.' is ready to be paid.';
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
            trans('shared/common.saved-message', ['variable' => trans('payable/menu.do-partner-invoice').' '.$model->invoice_number])
        );

        return redirect(self::URL);
    }

    public function printPdfDetail(Request $request, $id)
    {
        $filters = \Session::get('filters');
        $model= InvoiceHeader::find($id);

        $header = view('print.header-pdf', ['title' => trans('payable/menu.do-partner-invoice')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('payable::transaction.do-partner-invoice.print-pdf-detail', [
            'model'  => $model,
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('payable/menu.do-partner-invoice'));
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A5');
        \PDF::writeHTML($html);
        \PDF::Output($model->invoice_number.'.pdf');
        \PDF::reset();
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
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => trans('payable/menu.do-partner-invoice').' '.$model->invoice_number.' '.trans('shared/common.cannot-cancel-payment-exist'). '. Payment exist on '.number_format($model->getTotalPayment()) ]);
        }

       

        if ($model->status == InvoiceHeader::APPROVED) {
            foreach ($model->lines as $line) {
                $modelDoHeader   = DeliveryOrderHeader::find($model->do_header_id);
                $modelDoLine     = DeliveryOrderLine::find($line->do_line_id);
                $modelResi       = $modelDoLine->resi;

                // insert journal
                $journalHeader           = new JournalHeader();
                $journalHeader->category = JournalHeader::CANCEL_INVOICE_DO_PARTNER;
                $journalHeader->status   = JournalHeader::OPEN;

                $now                    = new \DateTime();
                $period                 = new \DateTime($now->format('Y-m-1'));
                $journalHeader->period  = $period;

                $journalHeader->description = $model->invoice_number.' - '.$modelDoHeader->delivery_order_number.' - '.$modelResi->resi_number;
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

                if (!empty($line->tax)) {
                    $totalTax += $line->amount * $line->tax / 100;
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
                $journalLine->credit                 = $line->amount;
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
            $notif->category   = 'Invoice DO Partner Canceled';
            $notif->message    = 'Invoice DO Partner Canceled '.$model->invoice_number.' Canceled reason is "'.$request->get('reason').'"'; 
            // $notif->url        = self::URL.'/edit/'.$model->header_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->save();
        }

        $request->session()->flash(
            'successMessage',
            trans('purchasing/fields.canceled-message', ['variable' => trans('payable/menu.do-partner-invoice').' '.$model->invoice_number])
        );

        return redirect(self::URL);
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

    public function getJsonDo(Request $request)
    {
        $search = $request->get('search');
        $query = DeliveryOrderService::getQueryDeliveryOrderTransitionClosed();

        $query->select(
                    'trans_delivery_order_header.delivery_order_header_id',
                    'trans_delivery_order_header.delivery_order_number',
                    'trans_delivery_order_header.created_date',
                    'mst_vendor.vendor_id', 
                    'mst_vendor.vendor_name', 
                    'mst_vendor.vendor_code', 
                    'mst_vendor.address', 
                    'mst_vendor.phone_number'
                    )
                ->orderBy('trans_delivery_order_header.created_date', 'desc')
                ->whereRaw('trans_delivery_order_header.delivery_order_header_id not in (select do_header_id from ap.invoice_header where status <> \'' .InvoiceHeader::CANCELED. '\' and do_header_id is not null)')
                ->where(function ($query) use ($search) {
                    $query->where('trans_delivery_order_header.delivery_order_number', 'ilike', '%'.$search.'%')
                          ->orWhere('mst_vendor.vendor_code', 'ilike', '%'.$search.'%')
                          ->orWhere('mst_vendor.vendor_name', 'ilike', '%'.$search.'%')
                          ->orWhere('mst_vendor.address', 'ilike', '%'.$search.'%')
                          ->orWhere('mst_vendor.phone_number', 'ilike', '%'.$search.'%');
                })
                ->take(10);
        $doArray = [];
        foreach ($query->get() as $doArr) {
            $queryLine = \DB::table('op.trans_delivery_order_line')
                                ->select('trans_delivery_order_line.*', 'trans_resi_header.resi_number', 'trans_resi_header.item_name', 'trans_resi_header.description')
                                ->join('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_delivery_order_line.resi_header_id')
                                ->where('delivery_order_header_id', '=', $doArr->delivery_order_header_id)
                                ->get();
            $lineArray = [];
            foreach ($queryLine as $lineArr) {
                $resi = TransactionResiHeader::find($lineArr->resi_header_id);
                $lineArr->total_coly_resi    = $resi->totalColy(); 
                $lineArr->total_weight  = $resi->totalWeight(); 
                $lineArr->total_volume  = $resi->totalVolume(); 
                $lineArr->total_unit    = $resi->totalUnit(); 
                $lineArray []           = $lineArr; 
            } 

            $doArr->lines = $lineArray;
            $doArray[]    = $doArr;
        }

        return response()->json($doArray);
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
