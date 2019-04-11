<?php

namespace App\Modules\Payable\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Payable\Http\Controllers\Transaction\OtherInvoiceController;
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

class ApproveOtherInvoiceController extends Controller
{
    const RESOURCE = 'Payable\Transaction\ApproveOtherInvoice';
    const URL      = 'payable/transaction/approve-other-invoice';

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
                       'mst_vendor.vendor_code',
                        'mst_vendor.vendor_name',
                        'mst_vendor.address as vendor_address',
                        'mst_driver.driver_code',
                        'mst_driver.driver_name',
                        'mst_driver.address as driver_address',
                        'mst_ap_type.type_id',
                        'mst_ap_type.type_name'
                      )
                    ->leftJoin('ap.invoice_line', 'invoice_line.header_id', '=', 'invoice_header.header_id')
                    ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'invoice_header.vendor_id')
                    ->leftJoin('op.mst_driver', 'mst_driver.driver_id', '=', 'invoice_header.vendor_id')
                    ->leftJoin('ap.mst_ap_type', 'mst_ap_type.type_id', '=', 'invoice_header.type_id')
                    ->where(function ($query) {
                            $query->where('invoice_header.type_id', '=', InvoiceHeader::OTHER_VENDOR)
                                  ->orWhere('invoice_header.type_id', '=', InvoiceHeader::OTHER_DRIVER);
                        })
                    ->where('invoice_header.branch_id', '=', $request->session()->get('currentBranch')->branch_id)
                    ->where('invoice_header.status', '=', InvoiceHeader::INPROCESS)
                    ->orderBy('invoice_header.created_date', 'desc')
                    ->distinct();

        if (!empty($filters['invoiceNumber'])) {
            $query->where('invoice_header.invoice_number', 'ilike', '%'.$filters['invoiceNumber'].'%');
        }

        if (!empty($filters['vendor'])) {
            $query->where(function ($query) use ($filters) {
                            $query->where('mst_vendor.vendor_name', 'ilike', '%'.$filters['vendor'].'%')
                                  ->orWhere('mst_driver.driver_name', 'ilike', '%'.$filters['vendor'].'%');
                        });
        }

        if (!empty($filters['vendorCode'])) {
            $query->where(function ($query) use ($filters) {
                            $query->where('mst_vendor.vendor_code', 'ilike', '%'.$filters['vendorCode'].'%')
                                  ->orWhere('mst_driver.driver_code', 'ilike', '%'.$filters['vendorCode'].'%');
                        });
        }

        if (!empty($filters['description'])) {
            $query->where('invoice_header.description', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['type'])) {
            $query->where('invoice_header.type_id', '=', $filters['type']);
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

        return view('payable::transaction.approve-other-invoice.index', [
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

        return view('payable::transaction.approve-other-invoice.add', [
            'title'             => trans('shared/common.edit'),
            'model'             => $model,
            'url'               => self::URL,
            'resource'          => self::RESOURCE,
            'optionType'        => $this->optionType(),
            'optionTax'         => $this->optionTax(),
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
            $lines = $model->lines;
            foreach ($lines as $line) {
                $modelCombination = MasterAccountCombination::find($line->account_comb_id);
                $account          = $modelCombination->account;
                $accountCode      = $account->coa_code;
                $subAccount       = $modelCombination->subAccount;
                $subAccountCode   = $subAccount->coa_code;

                // insert journal
                $journalHeader           = new JournalHeader();
                $journalHeader->category = JournalHeader::INVOICE_OTHER;
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

                if (!empty($line->tax)) {
                    $totalTax += $line->amount * $line->tax / 100;
                }

                // insert journal line debit

                $journalLine        = new JournalLine();
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
            

            $userNotif = NotificationService::getUserNotification([Role::CASHIER]);

            foreach ($userNotif as $user) {
                $notif             = new Notification();
                $notif->branch_id  = \Session::get('currentBranch')->branch_id;
                $notif->category   = 'Invoice Other Approved';
                $notif->message    = 'Invoice Other - '.$model->invoice_number.' is ready to be paid. Note : '.$request->get('note') ;
                $notif->url        = PaymentController::URL.'/add/'.$model->header_id;
                $notif->created_at = new \DateTime();
                $notif->user_id    = $user->id;
                $notif->role_id    = $user->role_id;
                $notif->save();
            }
            $model->status         = InvoiceHeader::APPROVED;
            $model->approved_date  = new \DateTime();
            $model->approved_by    = \Auth::user()->id;
        } elseif ($request->get('btn-reject') !== null) {
            $userNotif = NotificationService::getUserNotification([Role::FINANCE_ADMIN]);
            foreach ($userNotif as $user) {
                $notif             = new Notification();
                $notif->branch_id  = \Session::get('currentBranch')->branch_id;
                $notif->url        = OtherInvoiceController::URL.'/edit/'.$model->header_id;
                $notif->created_at = new \DateTime();
                $notif->user_id    = $user->id;
                $notif->role_id    = $user->role_id;
                $notif->message    = $model->invoice_number.' - '.$request->get('note');
                $notif->category   = 'Invoice Other Rejected';
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
            trans('shared/common.saved-message', ['variable' => trans('payable/menu.approve-other-invoice').' '.$model->invoice_number])
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
    
    public function optionType()
    {
        return \DB::table('ap.mst_ap_type')
                    ->where(function ($query) {
                            $query->where('mst_ap_type.type_id', '=', InvoiceHeader::OTHER_VENDOR)
                                  ->orWhere('mst_ap_type.type_id', '=', InvoiceHeader::OTHER_DRIVER);
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
