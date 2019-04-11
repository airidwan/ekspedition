<?php

namespace App\Modules\Payable\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Payable\Model\Transaction\InvoiceHeader;
use App\Modules\Payable\Model\Transaction\InvoiceLine;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
use App\Modules\Payable\Service\Master\VendorService;
use App\Modules\Operational\Service\Transaction\ManifestService;
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

class ManifestMoneyTripInvoiceController extends Controller
{
    const RESOURCE = 'Payable\Transaction\ManifestMoneyTripInvoice';
    const URL      = 'payable/transaction/manifest-money-trip-invoice';

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
                        'mst_driver.driver_code',
                        'mst_driver.driver_name',
                        'mst_driver.address as driver_address',
                        'mst_driver.phone_number as driver_phone_number',
                        'trans_manifest_header.manifest_number'
                      )
                    ->leftJoin('ap.invoice_line', 'invoice_line.header_id', '=', 'invoice_header.header_id')
                    ->leftJoin('op.mst_driver', 'mst_driver.driver_id', '=', 'invoice_header.vendor_id')
                    ->leftJoin('op.trans_manifest_header', 'trans_manifest_header.manifest_header_id', '=', 'invoice_header.manifest_header_id')
                    ->join('ap.mst_ap_type', 'mst_ap_type.type_id', '=', 'invoice_header.type_id')
                    ->where('invoice_header.type_id', '=', InvoiceHeader::MANIFEST_MONEY_TRIP)
                    ->where('invoice_header.branch_id', '=', $request->session()->get('currentBranch')->branch_id)
                    ->distinct();

        if (!empty($filters['invoiceNumber'])) {
            $query->where('invoice_header.invoice_number', 'ilike', '%'.$filters['invoiceNumber'].'%');
        }

        if (!empty($filters['manifestNumber'])) {
            $query->where('trans_manifest_header.manifest_number', 'ilike', '%'.$filters['manifestNumber'].'%');
        }

        if (!empty($filters['status'])) {
            $query->where('invoice_header.status', '=', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('invoice_header.type_id', '=', $filters['type']);
        }

        if (!empty($filters['driver'])) {
            $query->where('driver_name', 'ilike', '%'.$filters['driver'].'%');
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

        return view('payable::transaction.manifest-money-trip-invoice.index', [
            'models'    => $query->paginate(10),
            'filters'    => $filters,
            'resource'   => self::RESOURCE,
            'url'        => self::URL,
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

        return view('payable::transaction.manifest-money-trip-invoice.add', [
            'title'             => trans('shared/common.add'),
            'model'             => $model,
            'url'               => self::URL,
            'resource'          => self::RESOURCE,
            'optionTax'         => $this->optionTax(),
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
            return view('payable::transaction.manifest-money-trip-invoice.add', $data);
        } else {
            return view('payable::transaction.manifest-money-trip-invoice.detail', $data);
        }
    }

    public function save(Request $request)
    {
        // var_dump($request->all());exit();
        $id = intval($request->get('id'));
        $model = !empty($id) ? InvoiceHeader::find($id) : new InvoiceHeader();

        $this->validate($request, [
            'driverId'          => 'required',
            'totalAmount'       => 'required',
            'manifestHeaderId'  => 'required',
            'description'       => 'required',
        ]);

        if (empty($model->branch_id)) {
            $model->branch_id = $request->session()->get('currentBranch')->branch_id;
        }

        $now = new \DateTime();

        if (empty($model->invoice_number)) {
            $model->invoice_number = $this->getInvoiceNumber($model);
        }

        $model->type_id             = InvoiceHeader::MANIFEST_MONEY_TRIP;
        $model->vendor_id           = $request->get('driverId');
        $model->vendor_address      = $request->get('address');
        $model->description         = $request->get('description');
        $model->total_amount        = intval(str_replace(',', '', $request->get('totalAmount')));
        $model->manifest_header_id  = $request->get('manifestHeaderId');

        if (empty($id)) {
            $model->status       = InvoiceHeader::INCOMPLETE;
            $model->created_date = $now;
            $model->created_by   = \Auth::user()->id;
        } else {
            $model->last_updated_date = $now;
            $model->last_updated_by   = \Auth::user()->id;
        }

        try {
            $model->save();  
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $type   = MasterApType::find($model->type_id);
        $vendor = $model->driver;

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

        try {
            $modelLine->save();
        }catch (\Exception $e) {
            return redirect(self::URL.'/edit/'.$model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        if ($request->get('btn-approve') !== null) {
            $model->status = InvoiceHeader::APPROVED;
            $model->approved_date = $now;
            $model->approved_by   = \Auth::user()->id;
            $model->save();

            $modelPayableType = $model->type;
            $modelManifest = ManifestHeader::find($model->manifest_header_id);

            // insert journal
            $journalHeader           = new JournalHeader();
            $journalHeader->category = JournalHeader::INVOICE_MANIFEST_MONEY_TRIP;
            $journalHeader->status   = JournalHeader::OPEN;

            $now                    = new \DateTime();
            $period                 = new \DateTime($now->format('Y-m-1'));
            $journalHeader->period  = $period;

            $journalHeader->description = $model->invoice_number.' - '.$modelManifest->manifest_number;
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
            $account          = $modelPayableType->coaD;
            $accountCode      = $account->coa_code;
            $subAccount       = $model->driver;
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
                $notif->category   = 'Invoice Manifest Money Trip Approved';
                $notif->message    = 'Invoice Manifest Money Trip - '.$model->invoice_number.' is ready to be paid.';
                $notif->url        = PaymentController::URL.'/add/'.$model->header_id;
                $notif->created_at = new \DateTime();
                $notif->user_id    = $user->id;
                $notif->role_id    = $user->role_id;
                $notif->save();
            }
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('payable/menu.manifest-money-trip-invoice').' '.$model->invoice_number])
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
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => trans('payable/menu.manifest-money-trip-invoice').' '.$model->invoice_number.' '.trans('shared/common.cannot-cancel-payment-exist'). '. Payment exist on '.number_format($model->getTotalPayment()) ]);
        }
        $modelLine = $model->lineOne;

        if ($model->status == InvoiceHeader::APPROVED) {
            $modelPayableType = $model->type;
            $modelManifest    = ManifestHeader::find($model->manifest_header_id);

            // insert journal
            $journalHeader           = new JournalHeader();
            $journalHeader->category = JournalHeader::CANCEL_INVOICE_MANIFEST_MONEY_TRIP;
            $journalHeader->status   = JournalHeader::OPEN;

            $now                    = new \DateTime();
            $period                 = new \DateTime($now->format('Y-m-1'));
            $journalHeader->period  = $period;

            $journalHeader->description = $model->invoice_number.' - '.$modelManifest->manifest_number;
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
            $subAccount       = $model->driver;
            $subAccountCode   = $subAccount->subaccount_code;

            $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

            $journalLine->journal_header_id      = $journalHeader->journal_header_id;
            $journalLine->account_combination_id = $accountCombination->account_combination_id;
            $journalLine->debet                  = $totalTax + $modelLine->amount;
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

        $model->description = $model->description.'. Canceled reason is "'.$request->get('reason').'"'; 
        $model->status = InvoiceHeader::CANCELED;
        $model->last_updated_by   = \Auth::user()->id;
        $model->last_updated_date = new \DateTime;
        $model->save();

        $userNotif = NotificationService::getUserNotification([Role::CASHIER, Role::BRANCH_MANAGER]);
        foreach ($userNotif as $user) {
            $notif             = new Notification();
            $notif->branch_id  = \Session::get('currentBranch')->branch_id;
            $notif->category   = 'Invoice Manifest Money Trip Canceled';
            $notif->message    = 'Invoice Manifest Money Trip Canceled '.$model->invoice_number.' Canceled reason is "'.$request->get('reason').'"'; 
            // $notif->url        = self::URL.'/edit/'.$model->header_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->save();
        }

        $request->session()->flash(
            'successMessage',
            trans('purchasing/fields.canceled-message', ['variable' => trans('payable/menu.manifest-money-trip-invoice').' '.$model->invoice_number])
        );

        return redirect(self::URL);
    }

    public function printPdfDetail(Request $request, $id)
    {
        $filters = \Session::get('filters');
        $model= InvoiceHeader::find($id);

        $header = view('print.header-pdf', ['title' => trans('payable/menu.manifest-money-trip-invoice')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('payable::transaction.manifest-money-trip-invoice.print-pdf-detail', [
            'model'  => $model,
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('payable/menu.manifest-money-trip-invoice'));
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

    public function getJsonDriver(Request $request)
    {
        $search = $request->get('search');
        $query = DriverService::getQueryDriver();

        $query->select('mst_driver.driver_id', 'mst_driver.driver_code', 'mst_driver.driver_name', 'mst_driver.address', 'mst_driver.phone_number', 'mst_lookup_values.meaning as position')
                ->orderBy('mst_driver.driver_name', 'asc')
                ->where(function ($query) use ($search) {
                    $query->where('mst_driver.driver_name', 'ilike', '%'.$search.'%')
                          ->orWhere('mst_driver.driver_code', 'ilike', '%'.$search.'%')
                          ->orWhere('mst_driver.address', 'ilike', '%'.$search.'%')
                          ->orWhere('mst_lookup_values.meaning', 'ilike', '%'.$search.'%');
                })
                ->take(10);

        return response()->json($query->get());
    }

    public function getJsonManifest(Request $request)
    {
        $search   = $request->get('search');
        $driverId = $request->get('driverId');
        $query    = ManifestService::getQueryManifestMoneyTrip();

        $query->select(
                    'trans_manifest_header.*', 
                    'driver.driver_code', 
                    'driver.driver_name', 
                    'assistant.driver_code as driver_assistant_code', 
                    'assistant.driver_name as driver_assistant_name', 
                    'mst_truck.truck_code', 
                    'mst_truck.police_number' 
                    )
                ->orderBy('trans_manifest_header.manifest_number', 'desc')
                ->where('trans_manifest_header.driver_id', '=', $driverId)
                ->where('trans_manifest_header.money_trip', '>', 0)
                ->whereRaw('trans_manifest_header.manifest_header_id not in (select invoice_header.manifest_header_id from ap.invoice_header where invoice_header.status <> \''.InvoiceHeader::CANCELED .'\' and invoice_header.manifest_header_id is not null)')
                ->where(function ($query) use ($search) {
                    $query->where('trans_manifest_header.manifest_number', 'ilike', '%'.$search.'%')
                          ->orWhere('driver.driver_name', 'ilike', '%'.$search.'%')
                          ->orWhere('assistant.driver_name', 'ilike', '%'.$search.'%')
                          ->orWhere('mst_truck.police_number', 'ilike', '%'.$search.'%')
                          ->orWhere('trans_manifest_header.description', 'ilike', '%'.$search.'%');
                })
                ->take(10);

        return response()->json($query->get());
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
