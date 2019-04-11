<?php

namespace App\Modules\Payable\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Payable\Http\Controllers\Transaction\DebtEmployeeInvoiceController;
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
use App\Modules\Operational\Service\Master\DriverService;
use App\Modules\Payable\Http\Controllers\Transaction\PaymentController;
use App\Notification;
use App\Service\Penomoran;
use App\Service\NotificationService;
use App\Role;

class ApproveDebtEmployeeInvoiceController extends Controller
{
    const RESOURCE = 'Payable\Transaction\ApproveKasbonInvoice';
    const URL      = 'payable/transaction/approve-debt-employee-invoice';

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
                    ->where('invoice_header.status', '=', InvoiceHeader::INPROCESS)
                    ->distinct();

        if (!empty($filters['invoiceNumber'])) {
            $query->where('invoice_number', 'ilike', '%'.$filters['invoiceNumber'].'%');
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

        $query->orderBy('invoice_number', 'desc');

        return view('payable::transaction.approve-debt-employee-invoice.index', [
            'models'       => $query->paginate(10),
            'filters'      => $filters,
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = InvoiceHeader::find($id);
        if ($model === null) {
            abort(404);
        }

        return view('payable::transaction.approve-debt-employee-invoice.add', [
            'title'             => trans('shared/common.edit'),
            'model'             => $model,
            'url'               => self::URL,
            'resource'          => self::RESOURCE,
            'optionTax'         => $this->optionTax(),
            'optionType'        => $this->optionType(),
            'optionVendor'      => $this->optionVendor(),
            'optionDriver'      => $this->optionDriver(),
        ]);
    }

    public function save(Request $request)
    {
        // var_dump($request->all());exit();
        $id = intval($request->get('id'));
        $model = !empty($id) ? InvoiceHeader::find($id) : new InvoiceHeader();

        $this->validate($request, [
            'note' => 'required',
        ]);

        if ($request->get('btn-approve') !== null) {
            $modelLine = $model->lineOne;

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
                $notif->message    = 'Invoice Kas Bon - '.$model->invoice_number.' is ready to be paid. Note : '.$request->get('note') ;
                $notif->url        = PaymentController::URL.'/add/'.$model->header_id;
                $notif->created_at = new \DateTime();
                $notif->user_id    = $user->id;
                $notif->role_id    = $user->role_id;
                $notif->save();
            }
            $model->status         = InvoiceHeader::APPROVED;
            $model->approved_date  = $now;
            $model->approved_by    = \Auth::user()->id;

        } elseif ($request->get('btn-reject') !== null) {
            $userNotif = NotificationService::getUserNotification([Role::FINANCE_ADMIN]);
            foreach ($userNotif as $user) {
                $notif             = new Notification();
                $notif->branch_id  = \Session::get('currentBranch')->branch_id;
                $notif->url        = DebtEmployeeInvoiceController::URL.'/edit/'.$model->header_id;
                $notif->created_at = new \DateTime();
                $notif->user_id    = $user->id;
                $notif->role_id    = $user->role_id;
                $notif->message    = $model->invoice_number.' - '.$request->get('note');
                $notif->category   = 'Invoice Kas Bon Rejected';
                $notif->save();
            }
            $model->status         = InvoiceHeader::INCOMPLETE;
        }
        
        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(self::URL.'/edit/'.$model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('payable/menu.approve-debt-employee-invoice').' '.$model->invoice_number])
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

    public function optionVendor()
    {
        return VendorService::getQueryVendorEmployee()->get();
    }

    public function optionDriver()
    {
        return DriverService::getActiveDriverAsistantDistinct();
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

    public function optionTax()
    {
        return [
            '5',
            '10',
            '15',
            '20',
        ];
    }
}
