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
use App\Modules\Asset\Model\Transaction\ServiceAsset;
use App\Modules\Payable\Model\Master\MasterApType;
use App\Modules\Payable\Model\Master\MasterVendor;
use App\Modules\Payable\Service\Master\VendorService;
use App\Modules\Payable\Http\Controllers\Transaction\PaymentController;
use App\Notification;
use App\Service\Penomoran;
use App\Service\NotificationService;
use App\Role;

class ServiceInvoiceController extends Controller
{
    const RESOURCE = 'Payable\Transaction\ServiceInvoice';
    const URL      = 'payable/transaction/service-invoice';

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
                            'mst_vendor.vendor_code',
                            'mst_vendor.vendor_name',
                            'mst_vendor.address as vendor_address',
                            'mst_vendor.phone_number as vendor_phone_number',
                            'mst_ap_type.type_id',
                            'mst_ap_type.type_name'
                          )
                        ->leftJoin('ap.invoice_line', 'invoice_line.header_id', '=', 'invoice_header.header_id')
                        ->leftJoin('ap.mst_ap_type', 'mst_ap_type.type_id', '=', 'invoice_header.type_id')
                        ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'invoice_header.vendor_id')
                        ->leftJoin('ast.service_asset', 'service_asset.service_asset_id', '=', 'invoice_line.service_id')
                        ->leftJoin('ast.addition_asset', 'addition_asset.asset_id', '=', 'service_asset.asset_id')
                        ->where('invoice_header.type_id', '=', InvoiceHeader::SERVICE)
                        ->where('invoice_header.branch_id', '=', $request->session()->get('currentBranch')->branch_id)
                        ->orderBy('invoice_header.created_date', 'desc')
                        ->distinct();
        }else{
            $query   = \DB::table('ap.invoice_line')
                        ->select(
                            'invoice_line.description',
                            'service_asset.service_number',
                            'addition_asset.asset_number',
                            'mst_item.description as item_name',
                            'invoice_header.header_id',
                            'invoice_header.invoice_number',
                            'invoice_header.total_amount',
                            'invoice_header.description',
                            'invoice_header.status',
                            'invoice_header.created_date',
                            'mst_vendor.vendor_code',
                            'mst_vendor.vendor_name',
                            'mst_vendor.address as vendor_address',
                            'mst_vendor.phone_number as vendor_phone_number',
                            'mst_ap_type.type_id',
                            'mst_ap_type.type_name'
                          )
                        ->leftJoin('ap.invoice_header', 'invoice_header.header_id', '=', 'invoice_line.header_id')
                        ->leftJoin('ap.mst_ap_type', 'mst_ap_type.type_id', '=', 'invoice_header.type_id')
                        ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'invoice_header.vendor_id')
                        ->leftJoin('ast.service_asset', 'service_asset.service_asset_id', '=', 'invoice_line.service_id')
                        ->leftJoin('ast.addition_asset', 'addition_asset.asset_id', '=', 'service_asset.asset_id')
                        ->leftJoin('inv.mst_item', 'mst_item.item_id', '=', 'addition_asset.item_id')
                        ->where('invoice_header.type_id', '=', InvoiceHeader::SERVICE)
                        ->where('invoice_header.branch_id', '=', $request->session()->get('currentBranch')->branch_id)
                        ->orderBy('invoice_header.created_date', 'desc')
                        ->distinct();
        }

        if (!empty($filters['invoiceNumber'])) {
            $query->where('invoice_header.invoice_number', 'ilike', '%'.$filters['invoiceNumber'].'%');
        }

        if (!empty($filters['vendorCode'])) {
            $query->where('mst_vendor.vendor_code', 'ilike', '%'.$filters['vendorCode'].'%');
        }

        if (!empty($filters['vendorName'])) {
            $query->where('mst_vendor.vendor_name', 'ilike', '%'.$filters['vendorName'].'%');
        }

        if (!empty($filters['serviceNumber'])) {
            $query->where('service_asset.service_number', 'ilike', '%'.$filters['serviceNumber'].'%');
        }

        if (!empty($filters['assetNumber'])) {
            $query->where('addition_asset.asset_number', 'ilike', '%'.$filters['assetNumber'].'%');
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

        $query->orderBy('invoice_header.invoice_number', 'desc');

        return view('payable::transaction.service-invoice.index', [
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

        return view('payable::transaction.service-invoice.add', [
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
            'optionTax'         => $this->optionTax(),
        ];

        if ($request->user()->can('access', [self::RESOURCE, 'update'])) {
            return view('payable::transaction.service-invoice.add', $data);
        } else {
            return view('payable::transaction.service-invoice.detail', $data);
        }
    }

    public function save(Request $request)
    {
        // var_dump($request->all());exit();
        $id = intval($request->get('id'));
        $model = !empty($id) ? InvoiceHeader::find($id) : new InvoiceHeader();

        $this->validate($request, [
            'vendorId'          => 'required',
            'totalAmount'       => 'required',
            'descriptionHeader' => 'required',
        ]);


        if (empty($request->get('lineId'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Lines are required']);
        }

        $totalAmount = 0;
        $lines = $model->lines()->get();
        $lineIds = $request->get('lineId');
       
        if (empty($model->branch_id)) {
            $model->branch_id = $request->session()->get('currentBranch')->branch_id;
        }

        if (empty($model->status)) {
            $model->status = InvoiceHeader::INCOMPLETE;
        }

        if (!empty($request->get('vendorId'))) {
            $model->vendor_id = $request->get('vendorId');
            $model->vendor_address = $request->get('vendorAddress');
        }

        $now = new \DateTime();

        if (empty($model->invoice_number)) {
            $model->invoice_number = $this->getInvoiceNumber($model);
        }

        $model->description = $request->get('descriptionHeader');

        $model->type_id = InvoiceHeader::SERVICE;

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

        $lines = $model->lines()->delete();

        for ($i = 0; $i < count($request->get('lineId')); $i++) {
            $line = new InvoiceLine();
            $line->service_id      = intval($request->get('serviceId')[$i]);
            $line->header_id       = intval($model->header_id);
            $line->description     = $request->get('description')[$i];
            $line->amount          = intval(str_replace(',', '', $request->get('amount')[$i]));
            $line->tax             = intval(str_replace(',', '', $request->get('tax')[$i]));

            $modelPayableType = $model->type;
            $account          = $modelPayableType->coaD;
            $accountCode      = $account->coa_code;

            $modelService     = ServiceAsset::find($line->service_id);
            
            if($modelService->service_type == ServiceAsset::ASSET){
                $modelAsset       = $modelService->addAsset;
                $modelTruck       = $modelAsset->truck;
            }else{
                $modelTruck       = $modelService->truck;
            }

            
            if (!empty($modelTruck)) {
                $subAccountCode = $modelTruck->subaccount_code;
            }else{
                $defaultSubAccount = SettingJournal::where('setting_name', '=', SettingJournal::DEFAULT_SUB_ACCOUNT)->first();
                $subAccount        = MasterCoa::find($defaultSubAccount->coa_id);
                $subAccountCode    = $subAccount->coa_code;
            }
            $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

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
                $modelService    = ServiceAsset::find($line->service_id);
                if($modelService->service_type == ServiceAsset::ASSET){
                    $modelAsset       = $modelService->addAsset;
                    $modelTruck       = $modelAsset->truck;
                }else{
                    $modelTruck       = $modelService->truck;
                }

                // insert journal
                $journalHeader           = new JournalHeader();
                $journalHeader->category = JournalHeader::INVOICE_SERVICE;
                $journalHeader->status   = JournalHeader::OPEN;

                $now                    = new \DateTime();
                $period                 = new \DateTime($now->format('Y-m-1'));
                $journalHeader->period  = $period;

                $journalHeader->description = $model->invoice_number.' - '.$modelService->service_number;
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

            if ($request->get('btn-approve') !== null) {
                $model->status = InvoiceHeader::APPROVED;

                $userNotif = NotificationService::getUserNotification([Role::CASHIER]);

                foreach ($userNotif as $user) {
                    $notif             = new Notification();
                    $notif->branch_id  = \Session::get('currentBranch')->branch_id;
                    $notif->category   = 'Invoice Service Approved';
                    $notif->message    = 'Invoice Service - '.$model->invoice_number.' is ready to be paid.';
                    $notif->url        = PaymentController::URL.'/add/'.$model->header_id;
                    $notif->created_at = new \DateTime();
                    $notif->user_id    = $user->id;
                    $notif->role_id    = $user->role_id;
                    $notif->save();
                }
            }
        }
        
        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(self::URL.'/edit/'.$model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('payable/menu.service-invoice').' '.$model->invoice_number])
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
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => trans('payable/menu.service-invoice').' '.$model->invoice_number.' '.trans('shared/common.cannot-cancel-payment-exist'). '. Payment exist on '.number_format($model->getTotalPayment()) ]);
        }

        if ($model->status == InvoiceHeader::APPROVED) {
            foreach ($model->lines as $line) {
                $modelService    = ServiceAsset::find($line->service_id);
                if($modelService->service_type == ServiceAsset::ASSET){
                    $modelAsset       = $modelService->addAsset;
                    $modelTruck       = $modelAsset->truck;
                }else{
                    $modelTruck       = $modelService->truck;
                }

                if (!empty($modelTruck)) {
                    $subAccountCode = $modelTruck->subaccount_code;
                }else{
                    $defaultSubAccount = SettingJournal::where('setting_name', '=', SettingJournal::DEFAULT_SUB_ACCOUNT)->first();
                    $subAccount        = MasterCoa::find($defaultSubAccount->coa_id);
                    $subAccountCode    = $subAccount->coa_code;
                }
                
                // insert journal
                $journalHeader           = new JournalHeader();
                $journalHeader->category = JournalHeader::CANCEL_INVOICE_SERVICE;
                $journalHeader->status   = JournalHeader::OPEN;

                $now                    = new \DateTime();
                $period                 = new \DateTime($now->format('Y-m-1'));
                $journalHeader->period  = $period;

                $journalHeader->description = $model->invoice_number.' - '.$modelService->service_number;
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
            $notif->category   = 'Invoice Service Canceled';
            $notif->message    = 'Invoice Service Canceled '.$model->invoice_number.' Canceled reason is "'.$request->get('reason').'"'; 
            // $notif->url        = self::URL.'/edit/'.$model->header_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->save();
        }

        $request->session()->flash(
            'successMessage',
            trans('purchasing/fields.canceled-message', ['variable' => trans('payable/menu.service-invoice').' '.$model->invoice_number])
        );

        return redirect(self::URL);
    }

    public function printPdfDetail(Request $request, $id)
    {
        $filters = \Session::get('filters');
        $model= InvoiceHeader::find($id);

        $header = view('print.header-pdf', ['title' => trans('payable/menu.service-invoice')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('payable::transaction.service-invoice.print-pdf-detail', [
            'model'  => $model,
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('payable/menu.service-invoice'));
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

    public function getJsonService(Request $request)
    {
        $search = $request->get('search');
        $query  =\DB::table('ast.service_asset')
                ->select(
                    'service_asset.*', 
                    'addition_asset.asset_number', 
                    'mst_item.description as item_description', 
                    'mst_truck.police_number', 
                    'mst_truck.police_number', 
                    'mst_truck.owner_name'
                )
                ->leftJoin('ast.addition_asset', 'addition_asset.asset_id', '=', 'service_asset.asset_id' )
                ->leftJoin('inv.mst_item', 'mst_item.item_id', '=', 'addition_asset.item_id' )
                ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'service_asset.truck_id')
                ->leftJoin('ap.invoice_line', 'invoice_line.service_id', '=', 'service_asset.service_asset_id' )
                ->leftJoin('ap.invoice_header', 'invoice_header.header_id', '=', 'invoice_line.header_id' )
                ->where('service_asset.finished', '=', FALSE)
                ->where(function ($query) use ($search) {
                    $query->where('invoice_line.service_id', '=', null)
                            ->orWhere('invoice_header.status', '=', InvoiceHeader::CANCELED);
                })
                // ->where('service_asset.branch_id', '=', \Session::get('currentBranch')->branch_id)
                ->orderBy('service_asset.created_date', 'desc')
                ->where(function ($query) use ($search) {
                    $query->where('service_asset.service_number', 'ilike', '%'.$search.'%')
                          ->orWhere('addition_asset.asset_number', 'ilike', '%'.$search.'%')
                          ->orWhere('mst_item.description', 'ilike', '%'.$search.'%')
                          ->orWhere('mst_truck.police_number', 'ilike', '%'.$search.'%')
                          ->orWhere('mst_truck.owner_name', 'ilike', '%'.$search.'%');
                })
                ->take(10);

        return response()->json($query->get());
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
