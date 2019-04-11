<?php

namespace App\Modules\Operational\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Accountreceivables\Model\Transaction\Invoice;
use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader;
use App\Modules\Operational\Model\Transaction\DeliveryOrderLine;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Http\Controllers\Transaction\CostDeliveryOrderController;
use App\Modules\Operational\Model\Transaction\ResiStock;
use App\Modules\Operational\Service\Master\DriverService;
use App\Modules\Operational\Service\Transaction\PickupService;
use App\Modules\Marketing\Model\Transaction\PickupRequest;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Generalledger\Service\Master\AccountCombinationService;
use App\Modules\Generalledger\Service\Master\JournalService;
use App\Modules\Generalledger\Model\Master\SettingJournal;
use App\Modules\Operational\Service\Transaction\HistoryResiService;
use App\Service\Penomoran;
use App\Notification;
use App\Service\NotificationService;
use App\Role;
use App\Service\TimezoneDateConverter;

class CostDeliveryOrderController extends Controller
{
    const RESOURCE = 'Operational\Transaction\CostDeliveryOrder';
    const URL      = 'operational/transaction/cost-delivery-order';

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
        } elseif (empty($request->get('page'))) {
            $request->session()->forget('filters');
        }

        $filters = $request->session()->get('filters');
        $query = \DB::table('op.v_trans_delivery_order_header')
                        ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->where(function($query){
                            $query->where('status', '=', DeliveryOrderHeader::APPROVED)
                                    ->orWhere('status', '=', DeliveryOrderHeader::CONFIRMED);
                        });

        if (!empty($filters['deliveryOrderNumber'])) {
            $query->where('delivery_order_number', 'ilike', '%'.$filters['deliveryOrderNumber'].'%');
        }

        if (!empty($filters['partnerName'])) {
            $query->where('vendor_name', 'ilike', '%'.$filters['partnerName'].'%');
        }

        if (!empty($filters['driver'])) {
            $query->where('driver_name', 'ilike', '%'.$filters['driver'].'%');
        }

        if (!empty($filters['policeNumber'])) {
            $query->where('police_number', 'ilike', '%'.$filters['policeNumber'].'%');
        }


        if (!empty($filters['dateFrom'])) {
            $dateFrom = TimezoneDateConverter::getServerDateTime($filters['dateFrom']);
            $query->where('delivery_start_time', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = TimezoneDateConverter::getServerDateTime($filters['dateTo']);
            $query->where('delivery_start_time', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }


        return view('operational::transaction.cost-delivery-order.index', [
            'models'       => $query->paginate(10),
            'filters'      => $filters,
            'optionType'   => $this->getOptionsType(),
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }
        $model = new DeliveryOrderHeader();

        return view('operational::transaction.cost-delivery-order.add', [
            'title'        => trans('shared/common.add'),
            'model'        => $model,
            'optionResi'   => $this->getOptionsResi(),
            'optionType'   => $this->getOptionsType(),
            'optionDriver' => DriverService::getActiveDriverAsistant(),
            'optionPickup' => PickupService::getPickupRequestOpen(),
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = DeliveryOrderHeader::where('delivery_order_header_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        return view('operational::transaction.cost-delivery-order.add', [
            'title'        => trans('shared/common.edit'),
            'model'        => $model,
            'optionResi'   => $this->getOptionsResi(),
            'optionType'   => $this->getOptionsType(),
            'optionDriver' => DriverService::getActiveDriverAsistant(),
            'optionPickup' => PickupService::getPickupRequestOpen(),
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? DeliveryOrderHeader::where('delivery_order_header_id', '=', $id)->first() : new DeliveryOrderHeader();

        if ($request->get('btn-confirm') === null) {
            $this->validate($request, [
                'note'   => 'required|max:50',
            ]);
        }

        $lines = $model->lines()->get();
        $lineIds = $request->get('lineId');

        if ($request->get('btn-confirm') !== null && $model->isApproved()) {
            foreach ($lines as $line) {
                $i = array_search($line->delivery_order_line_id, $lineIds);
                if (in_array($line->delivery_order_line_id, $lineIds)) {
                    $line->delivery_cost = intval(str_replace(',', '', $request->get('deliveryCost')[$i]));

                    try {
                        $line->save();
                    } catch (\Exception $e) {
                        return redirect(self::URL.'/edit/'.$model->delivery_order_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                    }
                } else {
                    try {
                        $line->delete();
                    } catch (\Exception $e) {
                        return redirect(self::URL.'/edit/'.$model->delivery_order_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                    }
                }
            }

            $model->status = DeliveryOrderHeader::CONFIRMED;

        } elseif ($request->get('btn-reject') !== null){
            $userNotif = NotificationService::getUserNotification([Role::WAREHOUSE_ADMIN]);
            foreach ($userNotif as $user) {
                $notif             = new Notification();
                $notif->branch_id  = \Session::get('currentBranch')->branch_id;
                $notif->url        = DeliveryOrderController::URL.'/edit/'.$model->delivery_order_header_id;
                $notif->created_at = new \DateTime();
                $notif->user_id    = $user->id;
                $notif->role_id    = $user->role_id;
                $notif->category   = 'Rejected Process Delivery Order';
                $notif->message    = 'Rejected Delivery Order '.$model->delivery_order_number. '. ' . $request->get('note');
                $notif->save();
            }
            $model->status     = DeliveryOrderHeader::OPEN;

        } elseif ($request->get('btn-otr') !== null && $model->isConfirmed()) {
            foreach ($lines as $line) {
                $modelStock = ResiStock::where('resi_header_id', '=', $line->resi_header_id)->where('branch_id', '=',  $model->branch_id)->first();
                if ($modelStock === null) {
                    continue;
                }

                $modelStock->coly = $modelStock->coly - $line->total_coly;
                if ($modelStock->coly <= 0 ) {
                    $modelStock->delete();
                }else{
                    $modelStock->save();
                }
            }
            $userNotif = NotificationService::getUserNotification([Role::WAREHOUSE_ADMIN, Role::WAREHOUSE_MANAGER]);
            foreach ($userNotif as $user) {
                $notif             = new Notification();
                $notif->branch_id  = \Session::get('currentBranch')->branch_id;
                $notif->url        = DeliveryOrderController::URL.'/edit/'.$model->delivery_order_header_id;
                $notif->created_at = new \DateTime();
                $notif->user_id    = $user->id;
                $notif->role_id    = $user->role_id;
                $notif->category   = 'Delivery Order Shipped ';
                $notif->message    = 'Delivery Order Shipped '.$model->delivery_order_number. '. ' . $request->get('note');
                $notif->save();
            }
            $model->status     = DeliveryOrderHeader::ON_THE_ROAD;
        }

        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(self::URL.'/edit/'.$model->delivery_order_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        if ($model->isConfirmed()) {
            $error = $this->createInvoiceDeliveryOrder($model);
            if (!empty($error)) {
                return redirect(self::URL.'/edit/'.$model->delivery_order_header_id)->withInput($request->all())->withErrors(['errorMessage' => $error]);
            }

            $this->saveHistoryResi($model);
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.delivery-order').' '.$model->delivery_order_number])
        );

        return redirect(self::URL);
    }

    protected function saveHistoryResi(DeliveryOrderHeader $model)
    {
        $deliveryOrder = DeliveryOrderHeader::find($model->delivery_order_header_id);
        foreach ($deliveryOrder->lines as $line) {
            HistoryResiService::saveHistory(
                $line->resi_header_id,
                'Shipped Delivery Order',
                'Delivery Order Number: '.$deliveryOrder->delivery_order_number.'. Coly Sent: '.$line->total_coly.'. Delivery Cost '.number_format($line->delivery_cost)
            );
        }
    }

    protected function createInvoiceDeliveryOrder(DeliveryOrderHeader $model)
    {
        $deliveryOrder   = DeliveryOrderHeader::find($model->delivery_order_header_id);
        $invoice         = new Invoice();
        $invoice->status = Invoice::APPROVED;
        $invoice->type   = Invoice::INV_DO;

        foreach ($deliveryOrder->lines as $line) {
            if (empty($line->resi) || empty($line->delivery_cost)) {
                continue;
            }

            if ($line->resi->isBillToReceiver()) {
                if (!empty($line->resi->customer_receiver_id)) {
                    $invoice->customer_id = $line->resi->customer_receiver_id;
                }

                $invoice->bill_to         = !empty($line->resi->customerReceiver) ? $line->resi->customerReceiver->customer_name : $line->resi->receiver_name;
                $invoice->bill_to_address = $line->resi->receiver_address;
                $invoice->bill_to_phone   = $line->resi->receiver_phone;
            } else {
                if (!empty($line->resi->customer_id)) {
                    $invoice->customer_id = $line->resi->customer_id;
                }

                $invoice->bill_to         = !empty($line->resi->customer) ? $line->resi->customer->customer_name : $line->resi->sender_name;
                $invoice->bill_to_address = $line->resi->sender_address;
                $invoice->bill_to_phone   = $line->resi->sender_phone;
            }

            $invoice->branch_id              = \Session::get('currentBranch')->branch_id;
            $invoice->created_date           = new \DateTime();
            $invoice->created_by             = \Auth::user()->id;
            $invoice->invoice_number         = $this->getInvoiceNumber($invoice);
            $invoice->resi_header_id         = !empty($line->resi) ? $line->resi->resi_header_id : null;
            $invoice->delivery_order_line_id = $line->delivery_order_line_id;
            $invoice->amount                 = $line->delivery_cost;
            $invoice->current_discount       = 1;

            try {
                $invoice->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }

            $error = $this->createJournalInvoiceDO($invoice);
            if (!empty($error)) {
                return $error;
            }
        }
    }

    protected function createJournalInvoiceDO(Invoice $model)
    {
        $invoice       = Invoice::find($model->invoice_id);
        $resi          = $invoice->resi;
        $journalHeader = new JournalHeader();

        $journalHeader->category       = JournalHeader::INVOICE_DO;
        $journalHeader->period         = new \DateTime($this->now->format('Y-m-1'));
        $journalHeader->status         = JournalHeader::OPEN;
        $journalHeader->description    = 'Invoice Number: '.$invoice->invoice_number.'. Resi Number: '.$resi->resi_number;
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

        /** PIUTANG USAHA **/
        $settingCoa  = SettingJournal::where('setting_name', SettingJournal::PIUTANG_USAHA)->first();
        $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

        $line = new JournalLine();
        $line->journal_header_id      = $journalHeader->journal_header_id;
        $line->account_combination_id = $combination->account_combination_id;
        $line->debet                  = $invoice->amount;
        $line->credit                 = 0;
        $line->created_date           = $this->now;
        $line->created_by             = \Auth::user()->id;

        try {
            $line->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        /** PENDAPATAN UTAMA **/
        $settingCoa  = SettingJournal::where('setting_name', SettingJournal::PENDAPATAN_LAIN_LAIN)->first();
        $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

        $line = new JournalLine();
        $line->journal_header_id      = $journalHeader->journal_header_id;
        $line->account_combination_id = $combination->account_combination_id;
        $line->debet                  = 0;
        $line->credit                 = $invoice->amount;
        $line->created_date           = $this->now;
        $line->created_by             = \Auth::user()->id;

        try {
            $line->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    protected function getInvoiceNumber(Invoice $model)
    {
        $branch      = MasterBranch::find($model->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('ar.invoice')
                            ->where('branch_id', '=', $model->branch_id)
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->count();

        return 'IAR.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 6);
    }

    protected function getDeliveryOrderNumber(DeliveryOrderHeader $model)
    {
        $branch      = MasterBranch::find(\Session::get('currentBranch')->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('op.trans_delivery_order_header')
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->count();

        return 'DO.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }

    protected function getOptionsType()
    {
        return [
        DeliveryOrderHeader::REGULAR,
        DeliveryOrderHeader::TRANSITION,
        ];
    }

    protected function getOptionsResi(){
        $listResi = \DB::table('op.trans_resi_header')
                    ->select('trans_resi_header.resi_header_id','mst_customer.customer_id','trans_resi_header.receiver_name', 'trans_resi_header.receiver_address' ,'trans_resi_header.receiver_phone','mst_customer.customer_name','trans_resi_header.resi_number','trans_resi_header.item_name', 'trans_resi_header.description', 'v_mst_route.route_code', 'v_mst_route.city_start_name', 'v_mst_route.city_end_name', 'mst_delivery_area.delivery_area_name')
                    ->join('op.v_mst_route', 'v_mst_route.route_id', '=', 'trans_resi_header.route_id')
                    ->join('op.mst_delivery_area', 'mst_delivery_area.delivery_area_id', '=', 'trans_resi_header.delivery_area_id')
                    ->join('op.mst_customer', 'mst_customer.customer_id', '=', 'trans_resi_header.customer_id')
                    ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->get();

        $arrResi = [];
        foreach($listResi as $resi) {
            $modelResi = TransactionResiHeader::find($resi->resi_header_id);
            $resi->total_coly   = $modelResi->totalColy();
            $resi->total_weight = $modelResi->totalWeightAll();
            $resi->total_volume = $modelResi->totalVolumeAll();
            $arrResi [] = $resi;
        }
        return $arrResi;
    }
}
