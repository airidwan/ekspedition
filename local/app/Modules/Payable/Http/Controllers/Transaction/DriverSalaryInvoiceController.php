<?php

namespace App\Modules\Payable\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
use App\Modules\Operational\Model\Transaction\PickupFormHeader;
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
use App\Modules\Operational\Model\Master\MasterDriver;
use App\Modules\Operational\Service\Master\DriverService;
use App\Modules\Payable\Http\Controllers\Transaction\PaymentController;
use App\Notification;
use App\Service\Penomoran;
use App\Service\NotificationService;
use App\Role;

class DriverSalaryInvoiceController extends Controller
{
    const RESOURCE = 'Payable\Transaction\DriverSalaryInvoice';
    const URL      = 'payable/transaction/driver-salary-invoice';

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
                            'mst_driver.driver_code',
                            'mst_driver.driver_name',
                            'mst_driver.address',
                            'mst_driver.phone_number',
                            'mst_ap_type.type_id',
                            'mst_ap_type.type_name'
                          )
                        ->leftJoin('ap.invoice_line', 'invoice_line.header_id', '=', 'invoice_header.header_id')
                        ->leftJoin('op.trans_manifest_header', 'trans_manifest_header.manifest_header_id', '=', 'invoice_line.manifest_id')
                        ->leftJoin('op.mst_driver', 'mst_driver.driver_id', '=', 'invoice_header.vendor_id')
                        ->leftJoin('ap.mst_ap_type', 'mst_ap_type.type_id', '=', 'invoice_header.type_id')
                        ->where('invoice_header.type_id', '=', InvoiceHeader::DRIVER_SALARY)
                        ->where('invoice_header.branch_id', '=', $request->session()->get('currentBranch')->branch_id)
                        ->orderBy('invoice_header.created_date', 'desc')
                        ->distinct();
        }else{
            $query   = \DB::table('ap.invoice_line')
                    ->select(
                        'invoice_line.line_id',
                        'invoice_header.header_id',
                        'invoice_header.invoice_number',
                        'invoice_header.total_amount',
                        'invoice_header.description',
                        'invoice_header.status',
                        'invoice_header.created_date',
                        'trans_manifest_header.manifest_number',
                        'trans_manifest_header.route_id',
                        'trans_manifest_header.created_date as manifest_date',
                        'mst_route.route_code',
                        'start_city.city_name as start_city',
                        'end_city.city_name as end_city',
                        'mst_driver.driver_code',
                        'mst_driver.driver_name',
                        'mst_driver.address',
                        'mst_driver.phone_number',
                        'mst_ap_type.type_id',
                        'mst_ap_type.type_name'
                      )
                    ->leftJoin('ap.invoice_header', 'invoice_header.header_id', '=', 'invoice_line.header_id')
                    ->leftJoin('op.trans_manifest_header', 'trans_manifest_header.manifest_header_id', '=', 'invoice_line.manifest_id')
                    ->leftJoin('op.mst_route', 'mst_route.route_id', '=', 'trans_manifest_header.route_id')
                    ->leftJoin('op.mst_city as start_city', 'start_city.city_id', '=', 'mst_route.city_start_id')
                    ->leftJoin('op.mst_city as end_city', 'end_city.city_id', '=', 'mst_route.city_end_id')
                    ->leftJoin('op.mst_driver', 'mst_driver.driver_id', '=', 'invoice_header.vendor_id')
                    ->leftJoin('ap.mst_ap_type', 'mst_ap_type.type_id', '=', 'invoice_header.type_id')
                    ->where('invoice_header.type_id', '=', InvoiceHeader::DRIVER_SALARY)
                    ->where('invoice_header.branch_id', '=', $request->session()->get('currentBranch')->branch_id)
                    ->orderBy('invoice_header.created_date', 'desc')
                    ->distinct();
        }

        if (!empty($filters['invoiceNumber'])) {
            $query->where('invoice_header.invoice_number', 'ilike', '%'.$filters['invoiceNumber'].'%');
        }

        if (!empty($filters['manifestNumber'])) {
            $query->where('trans_manifest_header.manifest_number', 'ilike', '%'.$filters['manifestNumber'].'%');
        }

        if (!empty($filters['driver'])) {
            $query->where('mst_driver.driver_name', 'ilike', '%'.$filters['driver'].'%');
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

        return view('payable::transaction.driver-salary-invoice.index', [
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

        return view('payable::transaction.driver-salary-invoice.add', [
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
            'title'     => trans('shared/common.edit'),
            'model'     => $model,
            'url'       => self::URL,
            'resource'  => self::RESOURCE,
            'optionTax' => $this->optionTax(),
            'optionType'        => $this->getType(),
        ];

        if ($request->user()->can('access', [self::RESOURCE, 'update'])) {
            return view('payable::transaction.driver-salary-invoice.add', $data);
        } else {
            return view('payable::transaction.driver-salary-invoice.detail', $data);
        }
    }

    public function save(Request $request)
    {
        // var_dump($request->all());exit();
        $id = intval($request->get('id'));
        $model = !empty($id) ? InvoiceHeader::find($id) : new InvoiceHeader();

        $this->validate($request, [
            'driverId'          => 'required',
            'descriptionHeader' => 'required',
        ]);

        if (empty($request->get('lineId'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Lines are required']);
        }

        $totalAmount = 0;
        $lines       = $model->lines()->get();
        $lineIds     = $request->get('lineId');
        $manifestIds = $request->get('manifestId');
        $pickupIds   = $request->get('pickupId');
        $doIds       = $request->get('doId');

        $message = [];
        foreach ($request->get('manifestId') as $manifestId) {
            if (!empty($manifestId)) {
                $i = array_search($manifestId, $manifestIds);
                $amountForm     = intval(str_replace(',', '', $request->get('amount')[$i]));
                $amountManifest = $this->getAmountManifest($manifestId, $request->get('position')[$i]);
                $invoiceExist   = $this->getManifestInvoiceExist($id, $manifestId, $request->get('position')[$i]);
                $max            = intval($amountManifest->salary - $invoiceExist->amount);
                if ($amountForm > $max)  {
                    $message [] = $request->get('manifestNumber')[$i]. ' invoice remain is '. number_format($max) .'.';
                }
            }
        }

        foreach ($request->get('pickupId') as $pickupId) {
            if (!empty($pickupId)) {
                $i = array_search($pickupId, $pickupIds);
                $amountForm     = intval(str_replace(',', '', $request->get('amount')[$i]));
                $amountPickup   = $this->getAmountPickup($pickupId, $request->get('position')[$i]);
                $invoiceExist   = $this->getPickupInvoiceExist($id, $pickupId, $request->get('position')[$i]);
                $max            = intval($amountPickup->salary - $invoiceExist->amount);
                if ($amountForm > $max)  {
                    $message [] = $request->get('pickupNumber')[$i]. ' invoice remain is '. number_format($max) .'.';
                }
            }
        }

        foreach ($request->get('doId') as $doId) {
            if (!empty($doId)) {
                $i = array_search($doId, $doIds);
                $amountForm     = intval(str_replace(',', '', $request->get('amount')[$i]));
                $amountDo       = $this->getAmountDo($doId, $request->get('position')[$i]);
                $invoiceExist   = $this->getDoInvoiceExist($id, $doId, $request->get('position')[$i]);
                $max            = intval($amountDo->salary - $invoiceExist->amount);
                if ($amountForm > $max)  {
                    $message [] = $request->get('doNumber')[$i]. ' invoice remain is '. number_format($max) .'.';
                }
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

        if (!empty($request->get('driverId'))) {
            $model->vendor_id = $request->get('driverId');
            $model->vendor_address = $request->get('driverAddress');
        }
        $model->type_id = InvoiceHeader::DRIVER_SALARY;

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
        $vendor             = $model->driver;
        $account            = MasterCoa::find($type->coa_id_d);
        $accountCode        = $account->coa_code;
        $subAccountCode     = $vendor->subaccount_code;

        $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);
        
        $lines = $model->lines()->delete();

        // Line ketika diadd
        for ($i = 0; $i < count($request->get('lineId')); $i++) {
            $line = new InvoiceLine();
            $line->header_id       = intval($model->header_id);
            $line->type            = $request->get('type')[$i];
            $line->description     = $request->get('description')[$i];
            $line->position        = $request->get('position')[$i];
            $line->amount          = intval(str_replace(',', '', $request->get('amount')[$i]));
            $line->tax             = intval(str_replace(',', '', $request->get('tax')[$i]));
            $line->account_comb_id = $accountCombination->account_combination_id;

            $line->created_date = new \DateTime();
            $line->created_by   = \Auth::user()->id;

            if(!empty($request->get('manifestId'))){
                $line->manifest_id = intval($request->get('manifestId')[$i]);
            }

            if(!empty($request->get('pickupId'))){
                $line->pickup_form_header_id = intval($request->get('pickupId')[$i]);
            }

            if(!empty($request->get('doId'))){
                $line->do_header_id = intval($request->get('doId')[$i]);
            }

            try {
                $line->save();
            } catch (\Exception $e) {
                return redirect(self::URL.'/edit/'.$model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }
            $totalAmount += $line->amount;

            if ($request->get('btn-approve') !== null) {
                $number = '';
                if($line->type == InvoiceLine::MANIFEST_SALARY){
                    $modelManifest = ManifestHeader::find($line->manifest_id);
                    $number = $modelManifest->manifest_number;
                }elseif($line->type == InvoiceLine::PICKUP_SALARY){
                    $modelPickup = PickupFormHeader::find($line->pickup_form_header_id);
                    $number = $modelPickup->pickup_form_number;
                }elseif($line->type == InvoiceLine::DELIVERY_ORDER_SALARY){
                    $modelDo = DeliveryOrderHeader::find($line->do_header_id);
                    $number = $modelDo->delivery_order_number;
                }

                // insert journal
                $journalHeader           = new JournalHeader();
                $journalHeader->category = JournalHeader::INVOICE_DRIVER_SALARY;
                $journalHeader->status   = JournalHeader::OPEN;

                $now                    = new \DateTime();
                $period                 = new \DateTime($now->format('Y-m-1'));
                $journalHeader->period  = $period;

                $journalHeader->description = $model->invoice_number.' - '.$number;
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
                $subAccount       = $model->driver;
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
                // $journalLine->account_combination_id = $accountCombination->account_combination_id;
                $journalLine->account_combination_id = $line->account_comb_id;
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
                $notif->category   = 'Invoice Driver Salary Approved';
                $notif->message    = 'Invoice Driver Salary - '.$model->invoice_number.' is ready to be paid.';
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
            trans('shared/common.saved-message', ['variable' => trans('payable/menu.driver-salary-invoice').' '.$model->invoice_number])
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
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => trans('payable/menu.driver-salary-invoice').' '.$model->invoice_number.' '.trans('shared/common.cannot-cancel-payment-exist'). '. Payment exist on '.number_format($model->getTotalPayment()) ]);
        }

        if ($model->status == InvoiceHeader::APPROVED) {
            foreach ($model->lines as $line) {
                $number = '';
                if($line->type == InvoiceLine::MANIFEST_SALARY){
                    $modelManifest = ManifestHeader::find($line->manifest_id);
                    $number = $modelManifest->manifest_number;
                }elseif($line->type == InvoiceLine::PICKUP_SALARY){
                    $modelPickup = PickupFormHeader::find($line->pickup_form_header_id);
                    $number = $modelPickup->pickup_form_number;
                }elseif($line->type == InvoiceLine::DELIVERY_ORDER_SALARY){
                    $modelDo = DeliveryOrderHeader::find($line->do_header_id);
                    $number = $modelDo->delivery_order_number;
                }

                // insert journal
                $journalHeader           = new JournalHeader();
                $journalHeader->category = JournalHeader::CANCEL_INVOICE_DRIVER_SALARY;
                $journalHeader->status   = JournalHeader::OPEN;

                $now                    = new \DateTime();
                $period                 = new \DateTime($now->format('Y-m-1'));
                $journalHeader->period  = $period;

                $journalHeader->description = $model->invoice_number.' - '.$number;
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
                $subAccount       = $model->driver;
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
                $journalLine->account_combination_id = $accountCombination->account_combination_id;
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
                    $journalLine->credit                  = $totalTax;
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
            $notif->category   = 'Invoice Driver Salary Canceled';
            $notif->message    = 'Invoice Driver Salary Canceled '.$model->invoice_number.' Canceled reason is "'.$request->get('reason').'"'; 
            // $notif->url        = self::URL.'/edit/'.$model->header_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->save();
        }

        $request->session()->flash(
            'successMessage',
            trans('purchasing/fields.canceled-message', ['variable' => trans('payable/menu.driver-salary-invoice').' '.$model->invoice_number])
        );

        return redirect(self::URL);
    }

    public function printPdfDetail(Request $request, $id)
    {
        $filters = \Session::get('filters');
        $model= InvoiceHeader::find($id);

        $header = view('print.header-pdf', ['title' => trans('payable/menu.driver-salary-invoice')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('payable::transaction.driver-salary-invoice.print-pdf-detail', [
            'model'  => $model,
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('payable/menu.driver-salary-invoice'));
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A5');
        \PDF::writeHTML($html);
        \PDF::Output($model->invoice_number.'.pdf');
        \PDF::reset();
    }

    protected function getAmountManifest($manifestId, $position){
        $query = \DB::table('op.trans_manifest_header')
                    ->select('driver_assistant_salary as salary');
                if ($position == MasterDriver::DRIVER) {
                    $query->select('driver_salary as salary');
                }
        $query->where('manifest_header_id', '=', $manifestId);
        return $query->first();
    }

    protected function getManifestInvoiceExist($headerId, $manifestId, $position){
        return \DB::table('ap.invoice_line')
                    ->selectRaw('sum(amount) as amount')
                    ->join('ap.invoice_header', 'invoice_header.header_id', '=', 'invoice_line.header_id')
                    ->where('invoice_header.header_id', '!=', $headerId)
                    ->where('manifest_id', '=', $manifestId)
                    ->where('invoice_line.position', '=', $position)
                    ->where(function ($query) {
                          $query->where('invoice_header.status', '=', InvoiceHeader::APPROVED)
                                ->orWhere('invoice_header.status', '=', InvoiceHeader::CLOSED)
                                ->orWhere('invoice_header.status', '=', InvoiceHeader::INCOMPLETE);
                      })
                    ->first();
    }

    protected function getAmountPickup($pickupId, $position){
        $query = \DB::table('op.trans_pickup_form_header')
                    ->select('driver_assistant_salary as salary');
                if ($position == MasterDriver::DRIVER) {
                    $query->select('driver_salary as salary');
                }
        $query->where('pickup_form_header_id', '=', $pickupId);
        return $query->first();
    }

    protected function getPickupInvoiceExist($headerId, $pickupId, $position){
        return \DB::table('ap.invoice_line')
                    ->selectRaw('sum(amount) as amount')
                    ->join('ap.invoice_header', 'invoice_header.header_id', '=', 'invoice_line.header_id')
                    ->where('invoice_header.header_id', '!=', $headerId)
                    ->where('pickup_form_header_id', '=', $pickupId)
                    ->where('invoice_line.position', '=', $position)
                    ->where(function ($query) {
                          $query->where('invoice_header.status', '=', InvoiceHeader::APPROVED)
                                ->orWhere('invoice_header.status', '=', InvoiceHeader::CLOSED)
                                ->orWhere('invoice_header.status', '=', InvoiceHeader::INCOMPLETE);
                      })
                    ->first();
    }

    protected function getAmountDo($doId, $position){
        $query = \DB::table('op.trans_delivery_order_header')
                    ->select('driver_assistant_salary as salary');
                if ($position == MasterDriver::DRIVER) {
                    $query->select('driver_salary as salary');
                }
        $query->where('delivery_order_header_id', '=', $doId);
        return $query->first();
    }

    protected function getDoInvoiceExist($headerId, $doId, $position){
        return \DB::table('ap.invoice_line')
                    ->selectRaw('sum(amount) as amount')
                    ->join('ap.invoice_header', 'invoice_header.header_id', '=', 'invoice_line.header_id')
                    ->where('invoice_header.header_id', '!=', $headerId)
                    ->where('invoice_line.do_header_id', '=', $doId)
                    ->where('invoice_line.position', '=', $position)
                    ->where(function ($query) {
                          $query->where('invoice_header.status', '=', InvoiceHeader::APPROVED)
                                ->orWhere('invoice_header.status', '=', InvoiceHeader::CLOSED)
                                ->orWhere('invoice_header.status', '=', InvoiceHeader::INCOMPLETE);
                      })
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

    public function getJsonDriver(Request $request)
    {
        $search = $request->get('search');
        $query = DriverService::getQueryDriver();

        $query->select('mst_driver.*', 'mst_lookup_values.meaning as position')
                ->orderBy('mst_driver.driver_name', 'asc')
                ->where(function ($query) use ($search) {
                    $query->where('mst_driver.driver_name', 'ilike', '%'.$search.'%')
                          ->orWhere('mst_driver.driver_code', 'ilike', '%'.$search.'%')
                          ->orWhere('mst_driver.driver_nickname', 'ilike', '%'.$search.'%')
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
        $id       = $request->get('id');

        $query = \DB::table('op.trans_manifest_header')
                        ->select(
                            'trans_manifest_header.manifest_header_id', 
                            'trans_manifest_header.manifest_number', 
                            'trans_manifest_header.description', 
                            'trans_manifest_header.driver_id', 
                            'trans_manifest_header.driver_assistant_id', 
                            'mst_truck.police_number', 
                            'mst_route.route_code', 
                            'type.meaning as truck_type', 
                            'mst_truck.truck_id', 
                            'start_city.city_name as start_city', 
                            'end_city.city_name as end_city'
                            )
                        ->leftJoin('op.mst_driver as driver', 'driver.driver_id', '=', 'trans_manifest_header.driver_id')
                        ->leftJoin('op.mst_driver as assistant', 'assistant.driver_id', '=', 'trans_manifest_header.driver_assistant_id')
                        ->leftJoin('op.mst_route', 'mst_route.route_id', '=', 'trans_manifest_header.route_id')
                        ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_manifest_header.truck_id')
                        ->leftJoin('adm.mst_lookup_values as type', 'type.lookup_code', '=', 'mst_truck.type')
                        ->leftJoin('op.mst_city as start_city', 'start_city.city_id', '=', 'mst_route.city_start_id')
                        ->leftJoin('op.mst_city as end_city', 'end_city.city_id', '=', 'mst_route.city_end_id')
                        ->where(function ($query) {
                            $query->where('trans_manifest_header.status', '=', ManifestHeader::ARRIVED)
                                    ->orWhere('trans_manifest_header.status', '=', ManifestHeader::CLOSED)
                                    ->orWhere('trans_manifest_header.status', '=', ManifestHeader::CLOSED_WARNING);
                        })
                        ->where(function ($query) use ($driverId) {
                            $query->where('driver.driver_id', '=', $driverId)
                                    ->orWhere('assistant.driver_id', '=', $driverId);
                        })
                        ->where(function ($query) use ($search) {
                            $query->where('trans_manifest_header.manifest_number', 'ilike', '%'.$search.'%')
                              ->orWhere('trans_manifest_header.description', 'ilike', '%'.$search.'%');
                        })
                        ->orderBy('trans_manifest_header.created_date', 'desc')
                        ->take(10);

        $arrManifest = [];
        foreach($query->get() as $manifest) {
            $modelManifest = ManifestHeader::find($manifest->manifest_header_id);
            if ($manifest->driver_id == $driverId) {
                $manifest->salary    = $modelManifest->driver_salary;
                $manifest->total_remain = $modelManifest->getTotalRemain(MasterDriver::DRIVER);
                $manifest->position_meaning = 'Driver';
                $manifest->position = MasterDriver::DRIVER;
            }else{
                $manifest->salary    = $modelManifest->driver_assistant_salary;
                $manifest->total_remain = $modelManifest->getTotalRemain(MasterDriver::ASSISTANT);
                $manifest->position_meaning = 'Assistant';
                $manifest->position = MasterDriver::ASSISTANT;
            }

            if ($manifest->total_remain <=0) {
                continue;
            }

            $arrManifest[] = $manifest;
        }
        return response()->json($arrManifest);
    }

    public function getJsonDo(Request $request)
    {
        $search   = $request->get('search');
        $driverId = $request->get('driverId');
        $id       = $request->get('id');

        $query = \DB::table('op.trans_delivery_order_header')
                        ->select(
                            'trans_delivery_order_header.delivery_order_header_id', 
                            'trans_delivery_order_header.delivery_order_number', 
                            'trans_delivery_order_header.note', 
                            'trans_delivery_order_header.driver_id', 
                            'trans_delivery_order_header.assistant_id', 
                            'mst_truck.police_number', 
                            'mst_delivery_area.delivery_area_name', 
                            'type.meaning as truck_type', 
                            'mst_truck.truck_id'
                            )
                        ->leftJoin('op.mst_driver as driver', 'driver.driver_id', '=', 'trans_delivery_order_header.driver_id')
                        ->leftJoin('op.mst_driver as assistant', 'assistant.driver_id', '=', 'trans_delivery_order_header.assistant_id')
                        ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_delivery_order_header.truck_id')
                        ->leftJoin('adm.mst_lookup_values as type', 'type.lookup_code', '=', 'mst_truck.type')
                        ->leftJoin('op.mst_delivery_area', 'mst_delivery_area.delivery_area_id', '=', 'trans_delivery_order_header.delivery_area_id')
                        ->where(function ($query) {
                            $query->where('trans_delivery_order_header.status', '=', DeliveryOrderHeader::ON_THE_ROAD)
                                    ->orWhere('trans_delivery_order_header.status', '=', DeliveryOrderHeader::CLOSED);
                        })
                        ->where(function ($query) use ($driverId) {
                            $query->where('driver.driver_id', '=', $driverId)
                                    ->orWhere('assistant.driver_id', '=', $driverId);
                        })
                        ->where(function ($query) use ($search) {
                            $query->where('trans_delivery_order_header.delivery_order_number', 'ilike', '%'.$search.'%')
                              ->orWhere('trans_delivery_order_header.note', 'ilike', '%'.$search.'%');
                        })
                        ->orderBy('trans_delivery_order_header.created_date', 'desc')
                        ->take(10);

        $arrDo = [];
        foreach($query->get() as $do) {
            $modelDo = DeliveryOrderHeader::find($do->delivery_order_header_id);
            if ($do->driver_id == $driverId) {
                $do->salary    = $modelDo->driver_salary;
                $do->total_remain = $modelDo->getTotalRemain(MasterDriver::DRIVER);
                $do->position_meaning = 'Driver';
                $do->position = MasterDriver::DRIVER;
            }else{
                $do->salary    = $modelDo->driver_assistant_salary;
                $do->total_remain = $modelDo->getTotalRemain(MasterDriver::ASSISTANT);
                $do->position_meaning = 'Assistant';
                $do->position = MasterDriver::ASSISTANT;
            }

            if ($do->total_remain <=0) {
                continue;
            }

            $arrDo[] = $do;
        }
        return response()->json($arrDo);
    }

    public function getJsonPickup(Request $request)
    {
        $search   = $request->get('search');
        $driverId = $request->get('driverId');
        $id       = $request->get('id');

        $query = \DB::table('op.trans_pickup_form_header')
                        ->select(
                            'trans_pickup_form_header.pickup_form_header_id', 
                            'trans_pickup_form_header.pickup_form_number', 
                            'trans_pickup_form_header.note', 
                            'trans_pickup_form_header.driver_id', 
                            'trans_pickup_form_header.driver_salary', 
                            'mst_truck.police_number', 
                            'mst_delivery_area.delivery_area_name', 
                            'type.meaning as truck_type'
                            )
                        ->leftJoin('op.mst_driver', 'mst_driver.driver_id', '=', 'trans_pickup_form_header.driver_id')
                        ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_pickup_form_header.truck_id')
                        ->leftJoin('adm.mst_lookup_values as type', 'type.lookup_code', '=', 'mst_truck.type')
                        ->leftJoin('op.mst_delivery_area', 'mst_delivery_area.delivery_area_id', '=', 'trans_pickup_form_header.delivery_area_id')
                        ->where('trans_pickup_form_header.status', '=', ManifestHeader::CLOSED)
                        ->where('mst_driver.driver_id', '=', $driverId)
                        ->where(function ($query) use ($search) {
                            $query->where('trans_pickup_form_header.pickup_form_number', 'ilike', '%'.$search.'%')
                              ->orWhere('trans_pickup_form_header.note', 'ilike', '%'.$search.'%');
                        })
                        ->orderBy('trans_pickup_form_header.created_date', 'desc')
                        ->take(10);

        $arrPickup = [];
        foreach($query->get() as $pickup) {
            $modelPickup = PickupFormHeader::find($pickup->pickup_form_header_id);
            $pickup->total_remain     = $modelPickup->getTotalRemain();
            $pickup->position_meaning = 'Driver';
            $pickup->position         = MasterDriver::DRIVER;

            if ($pickup->total_remain <=0) {
                continue;
            }

            $arrPickup[] = $pickup;
        }
        return response()->json($arrPickup);
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
        return [
            InvoiceLine::MANIFEST_SALARY,
            InvoiceLine::PICKUP_SALARY,
            InvoiceLine::DELIVERY_ORDER_SALARY,
        ];
    }
}
