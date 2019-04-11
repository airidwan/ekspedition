<?php

namespace App\Modules\Payable\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Payable\Http\Controllers\Transaction\ApproveDebtEmployeeInvoiceController;
use App\Modules\Payable\Model\Transaction\InvoiceHeader;
use App\Modules\Payable\Model\Transaction\InvoiceLine;
use App\Modules\Payable\Service\Master\VendorService;
use App\Modules\Operational\Service\Master\DriverService;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Modules\Generalledger\Model\Master\SettingJournal;
use App\Modules\Generalledger\Model\Master\MasterAccountCombination;
use App\Modules\Generalledger\Service\Master\AccountCombinationService;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Payable\Model\Master\MasterApType;
use App\Modules\Payable\Http\Controllers\Transaction\PaymentController;
use App\Notification;
use App\Service\Penomoran;
use App\Service\NotificationService;
use App\Role;

class DebtEmployeeInvoiceController extends Controller
{
    const RESOURCE = 'Payable\Transaction\KasbonInvoice';
    const URL      = 'payable/transaction/debt-employee-invoice';

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
                        'invoice_header.type_id',
                        'invoice_header.invoice_number',
                        'invoice_header.total_amount',
                        'invoice_header.description',
                        'invoice_header.status',
                        'invoice_header.created_date',
                        'mst_ap_type.type_name',
                        'mst_vendor.vendor_code',
                        'mst_vendor.vendor_name',
                        'mst_vendor.address as vendor_address',
                        'mst_vendor.phone_number as vendor_phone_number',
                        'mst_driver.driver_code',
                        'mst_driver.driver_name',
                        'mst_driver.address as driver_address',
                        'mst_driver.phone_number as driver_phone_number'
                      )
                    ->leftJoin('ap.invoice_line', 'invoice_line.header_id', '=', 'invoice_header.header_id')
                    ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'invoice_header.vendor_id')
                    ->leftJoin('op.mst_driver', 'mst_driver.driver_id', '=', 'invoice_header.vendor_id')
                    ->join('ap.mst_ap_type', 'mst_ap_type.type_id', '=', 'invoice_header.type_id')
                    ->where(function ($query) {
                            $query->where('invoice_header.type_id', '=', InvoiceHeader::KAS_BON_EMPLOYEE)
                                  ->orWhere('invoice_header.type_id', '=', InvoiceHeader::KAS_BON_DRIVER);
                        })
                    ->where('invoice_header.branch_id', '=', $request->session()->get('currentBranch')->branch_id)
                    ->distinct();

        if (!empty($filters['invoiceNumber'])) {
            $query->where('invoice_number', 'ilike', '%'.$filters['invoiceNumber'].'%');
        }

        if (!empty($filters['status'])) {
            $query->where('invoice_header.status', '=', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('invoice_header.type_id', '=', $filters['type']);
        }

        if (!empty($filters['vendor'])) {
            $query->where(function ($query) use ($filters) {
                            $query->where('vendor_name', 'ilike', '%'.$filters['vendor'].'%')
                                  ->orWhere('driver_name', 'ilike', '%'.$filters['vendor'].'%');
                        });
        }

        if (!empty($filters['vendorCode'])) {
            $query->where(function ($query) use ($filters) {
                            $query->where('vendor_code', 'ilike', '%'.$filters['vendorCode'].'%')
                                  ->orWhere('driver_code', 'ilike', '%'.$filters['vendorCode'].'%');
                        });
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = new \DateTime($filters['dateFrom']);
            $query->where('invoice_header.created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = new \DateTime($filters['dateTo']);
            $query->where('invoice_header.created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        $query->orderBy('invoice_header.created_date', 'desc');

        return view('payable::transaction.debt-employee-invoice.index', [
            'models'        => $query->paginate(10),
            'filters'       => $filters,
            'resource'      => self::RESOURCE,
            'url'           => self::URL,
            'optionStatus'  => $this->getStatus(),
            'optionType'    => $this->optionType(),
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new InvoiceHeader();
        $model->status = InvoiceHeader::INCOMPLETE;

        return view('payable::transaction.debt-employee-invoice.add', [
            'title'             => trans('shared/common.add'),
            'model'             => $model,
            'url'               => self::URL,
            'resource'          => self::RESOURCE,
            'optionTax'         => $this->optionTax(),
            'optionType'        => $this->optionType(),
            'optionVendor'      => $this->optionVendor(),
            'optionDriver'      => $this->optionDriver(),
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
            'optionType'        => $this->optionType(),
            'optionVendor'      => $this->optionVendor(),
            'optionDriver'      => $this->optionDriver(),
        ];

        if ($request->user()->can('access', [self::RESOURCE, 'update'])) {
            return view('payable::transaction.debt-employee-invoice.add', $data);
        } else {
            return view('payable::transaction.debt-employee-invoice.detail', $data);
        }
    }

    public function save(Request $request)
    {
        // var_dump($request->all());exit();
        $id = intval($request->get('id'));
        $model = !empty($id) ? InvoiceHeader::find($id) : new InvoiceHeader();

        $this->validate($request, [
            'type'        => 'required',
            'vendorCode'  => 'required',
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

        $model->type_id = $request->get('type');
        $model->vendor_id = $request->get('vendorId');
        $model->vendor_address = $request->get('address');
        $model->description = str_replace("'", "`", $request->get('description'));
        $model->total_amount = intval(str_replace(',', '', $request->get('totalAmount')));
        $model->is_invoice = !empty($request->get('isInvoice'));

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
        if ($model->type_id == InvoiceHeader::KAS_BON_EMPLOYEE) {
            $vendor = $model->vendor;
        }elseif($model->type_id == InvoiceHeader::KAS_BON_DRIVER) {
            $vendor = $model->driver;
        }

        $account            = MasterCoa::find($type->coa_id_d);
        $accountCode        = $account->coa_code;
        $subAccountCode     = $vendor->subaccount_code;
        $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

        $modelLine = !empty($model->lineOne) ? $model->lineOne : new InvoiceLine;
        $modelLine->header_id       = $model->header_id;
        $modelLine->description     = $request->get('description');
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

        if ($request->get('btn-approve-admin') !== null) {
            $model->status = InvoiceHeader::APPROVED;
            $model->approved_date = $now;
            $model->approved_by   = \Auth::user()->id;

            // insert journal
            $journalHeader           = new JournalHeader();
            $journalHeader->category = JournalHeader::INVOICE_KAS_BON;
            $journalHeader->status   = JournalHeader::OPEN;

            $now                    = new \DateTime();
            $period                 = new \DateTime($now->format('Y-m-1'));
            $journalHeader->period  = $period;

            $journalHeader->description = $model->invoice_number;
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

            if (!empty($modelLine->tax)) {
                $totalTax += $modelLine->amount * $modelLine->tax / 100;
            }

            // insert journal line debit

            $journalLine      = new JournalLine();
            $modelPayableType = $model->type;
            $account          = $modelPayableType->coaD;
            $accountCode      = $account->coa_code;

            if ($modelPayableType->type_id == InvoiceHeader::KAS_BON_DRIVER) {
                $subAccount   = $model->driver;
            }else{
                $subAccount   = $model->vendor;
            }

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

            //insert gl tax if exist
            if (!empty($modelLine->tax)) {
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
            $journalLine->credit                 = $totalTax + $modelLine->amount;
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
                $notif->category   = 'Invoice Kas Bon Approved';
                $notif->message    = 'Invoice Kas Bon - '.$model->invoice_number.' is ready to be paid.';
                $notif->url        = PaymentController::URL.'/add/'.$model->header_id;
                $notif->created_at = new \DateTime();
                $notif->user_id    = $user->id;
                $notif->role_id    = $user->role_id;
                $notif->save();
            }
        } elseif ($request->get('btn-approve-kacab') !== null) {
            $model->status = InvoiceHeader::INPROCESS;

            $userNotif = NotificationService::getUserNotification([Role::BRANCH_MANAGER]);

            foreach ($userNotif as $user) {
                $notif             = new Notification();
                $notif->branch_id  = \Session::get('currentBranch')->branch_id;
                $notif->category   = 'Invoice Kas Bon Approve Manager';
                $notif->message    = 'Need approval Invoice Kas Bon - '.$model->invoice_number. ' Rp. '.number_format($modelLine->amount);
                $notif->url        = ApproveDebtEmployeeInvoiceController::URL.'/edit/'.$model->header_id;
                $notif->created_at = new \DateTime();
                $notif->user_id    = $user->id;
                $notif->role_id    = $user->role_id;
                $notif->save();
            }
        } elseif (empty($model->status)) {
            $model->status = InvoiceHeader::INCOMPLETE;
        }

        try {
            $model->save();
            $modelLine->save();
        }catch (\Exception $e) {
            return redirect(self::URL.'/edit/'.$model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('payable/menu.debt-employee-invoice').' '.$model->invoice_number])
        );

        return redirect(self::URL);
    }

    public function printPdfDetail(Request $request, $id)
    {
        $filters = \Session::get('filters');
        $model= InvoiceHeader::find($id);

        $header = view('print.header-pdf', ['title' => trans('payable/menu.debt-employee-invoice')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('payable::transaction.debt-employee-invoice.print-pdf-detail', [
            'model'  => $model,
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('payable/menu.debt-employee-invoice'));
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
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => trans('payable/menu.debt-employee-invoice').' '.$model->invoice_number.' '.trans('shared/common.cannot-cancel-payment-exist'). '. Payment exist on '.number_format($model->getTotalPayment()) ]);
        }

        if ($model->status == InvoiceHeader::APPROVED) {
            foreach ($model->lines as $modelLine) {
                // insert journal
                $journalHeader           = new JournalHeader();
                $journalHeader->category = JournalHeader::CANCEL_INVOICE_KAS_BON;
                $journalHeader->status   = JournalHeader::OPEN;

                $now                    = new \DateTime();
                $period                 = new \DateTime($now->format('Y-m-1'));
                $journalHeader->period  = $period;

                $journalHeader->description = $model->invoice_number;
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

                if (!empty($modelLine->tax)) {
                    $totalTax += $modelLine->amount * $modelLine->tax / 100;
                }

                // insert journal line debet
                $journalLine      = new JournalLine();
                $modelPayableType = $model->type;
                $account          = $modelPayableType->coaC;
                $accountCode      = $account->coa_code;
                if ($modelPayableType->type_id == InvoiceHeader::KAS_BON_DRIVER) {
                    $subAccount   = $model->driver;
                }else{
                    $subAccount   = $model->vendor;
                }

                $subAccountCode   = $subAccount->subaccount_code;
                $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

                $journalLine->journal_header_id      = $journalHeader->journal_header_id;
                $journalLine->account_combination_id = $accountCombination->account_combination_id;
                $journalLine->debet                  = $totalTax + $modelLine->amount;
                $journalLine->credit                  = 0;
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
                $journalLine->debet                  = 0;
                $journalLine->credit                 = $modelLine->amount;
                $journalLine->description            = 'Account Debit AP Type';

                $journalLine->created_date = $now;
                $journalLine->created_by = \Auth::user()->id;

                try {
                    $journalLine->save();
                } catch (\Exception $e) {
                    return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }

                //insert gl tax if exist
                if (!empty($modelLine->tax)) {
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
            $notif->category   = 'Invoice Kas Bon Canceled';
            $notif->message    = 'Invoice Kas Bon Canceled '.$model->invoice_number.' Canceled reason is "'.$request->get('reason').'"'; 
            // $notif->url        = self::URL.'/edit/'.$model->header_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->save();
        }

        $request->session()->flash(
            'successMessage',
            trans('purchasing/fields.canceled-message', ['variable' => trans('payable/menu.debt-employee-invoice').' '.$model->invoice_number])
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

    protected function getStatus(){
        return [
            InvoiceHeader::INCOMPLETE,
            InvoiceHeader::APPROVED,
            InvoiceHeader::CLOSED,
            InvoiceHeader::CANCELED,
        ];
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

    public function optionType()
    {
        return \DB::table('ap.mst_ap_type')
                    ->where(function ($query) {
                            $query->where('mst_ap_type.type_id', '=', InvoiceHeader::KAS_BON_EMPLOYEE)
                                  ->orWhere('mst_ap_type.type_id', '=', InvoiceHeader::KAS_BON_DRIVER);
                        })
                    ->get();
    }

    public function optionVendor()
    {
        return VendorService::getQueryVendorEmployee()->get();
    }

    public function optionDriver()
    {
        return DriverService::getActiveDriverAsistantDistinct();
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
}
