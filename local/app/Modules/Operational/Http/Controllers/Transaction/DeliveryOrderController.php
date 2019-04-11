<?php

namespace App\Modules\Operational\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader;
use App\Modules\Operational\Model\Transaction\DeliveryOrderLine;
use App\Modules\Operational\Model\Transaction\DraftDeliveryOrderHeader;
use App\Modules\Operational\Model\Transaction\DraftDeliveryOrderLine;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Http\Controllers\Transaction\CostDeliveryOrderController;
use App\Modules\Operational\Model\Transaction\ResiStock;
use App\Modules\Operational\Service\Master\DriverService;
use App\Modules\Operational\Service\Master\TruckService;
use App\Modules\Payable\Service\Master\VendorService;
use App\Modules\Operational\Service\Transaction\PickupService;
use App\Modules\Operational\Service\Master\DeliveryAreaService;
use App\Modules\Marketing\Model\Transaction\PickupRequest;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Service\Penomoran;
use App\Notification;
use App\Service\NotificationService;
use App\Service\TimezoneDateConverter;
use App\Role;

class DeliveryOrderController extends Controller
{
    const RESOURCE = 'Operational\Transaction\DeliveryOrder';
    const URL      = 'operational/transaction/delivery-order';

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
        } elseif (empty($request->get('page'))) {
            $request->session()->forget('filters');
        }

        $filters = $request->session()->get('filters');

        if (empty($filters['jenis']) || $filters['jenis'] == 'headers') {
            $query = \DB::table('op.trans_delivery_order_header')
                            ->select(
                                'trans_delivery_order_header.delivery_order_header_id',
                                'trans_delivery_order_header.delivery_order_number',
                                'trans_delivery_order_header.delivery_start_time',
                                'trans_delivery_order_header.delivery_end_time',
                                'trans_delivery_order_header.status',
                                'trans_delivery_order_header.type',
                                'trans_delivery_order_header.created_date',
                                'driver.driver_name',
                                'assistant.driver_name as assistant_name',
                                'mst_truck.police_number',
                                'mst_vendor.vendor_name',
                                'trans_draft_delivery_order_header.draft_delivery_order_number'
                                )
                            ->leftJoin('op.trans_delivery_order_line', 'trans_delivery_order_line.delivery_order_header_id', '=', 'trans_delivery_order_header.delivery_order_header_id')
                            ->leftJoin('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_delivery_order_line.resi_header_id')
                            ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'trans_delivery_order_header.partner_id')
                            ->leftJoin('op.mst_driver as driver', 'driver.driver_id', '=', 'trans_delivery_order_header.driver_id')
                            ->leftJoin('op.mst_driver as assistant', 'assistant.driver_id', '=', 'trans_delivery_order_header.assistant_id')
                            ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_delivery_order_header.truck_id')
                            ->leftJoin('op.trans_draft_delivery_order_header', 'trans_draft_delivery_order_header.draft_delivery_order_header_id', '=', 'trans_delivery_order_header.draft_delivery_order_header_id')
                            ->where('trans_delivery_order_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->distinct()
                            ->orderBy('trans_delivery_order_header.created_date', 'desc');
        }else{
            $query = \DB::table('op.trans_delivery_order_line')
                            ->select(
                                'trans_delivery_order_line.total_coly as coly_send',
                                'trans_delivery_order_header.delivery_order_header_id',
                                'trans_delivery_order_header.delivery_order_number',
                                'trans_delivery_order_header.created_date',
                                'trans_resi_header.resi_number',
                                'trans_resi_header.item_name',
                                'trans_resi_header.receiver_name',
                                'trans_resi_header.receiver_address',
                                'trans_resi_header.receiver_phone'
                                )
                            ->leftJoin('op.trans_delivery_order_header', 'trans_delivery_order_header.delivery_order_header_id', '=', 'trans_delivery_order_line.delivery_order_header_id')
                            ->leftJoin('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_delivery_order_line.resi_header_id')
                            ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'trans_delivery_order_header.partner_id')
                            ->leftJoin('op.mst_driver as driver', 'driver.driver_id', '=', 'trans_delivery_order_header.driver_id')
                            ->leftJoin('op.mst_driver as assistant', 'assistant.driver_id', '=', 'trans_delivery_order_header.assistant_id')
                            ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_delivery_order_header.truck_id')
                            ->where('trans_delivery_order_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->distinct()
                            ->orderBy('trans_delivery_order_header.created_date', 'desc');
        }

        if (!empty($filters['deliveryOrderNumber'])) {
            $query->where('trans_delivery_order_header.delivery_order_number', 'ilike', '%'.$filters['deliveryOrderNumber'].'%');
        }

        if (!empty($filters['partnerName'])) {
            $query->where('mst_vendor.vendor_name', 'ilike', '%'.$filters['partnerName'].'%');
        }

        if (!empty($filters['resiNumber'])) {
            $query->where('trans_resi_header.resi_number', 'ilike', '%'.$filters['resiNumber'].'%');
        }

        if (!empty($filters['itemName'])) {
            $query->where('trans_resi_header.item_name', 'ilike', '%'.$filters['itemName'].'%');
        }

        if (!empty($filters['receiverName'])) {
            $query->where('trans_resi_header.receiver_name', 'ilike', '%'.$filters['receiverName'].'%');
        }

        if (!empty($filters['driver'])) {
            $query->where('driver.driver_name', 'ilike', '%'.$filters['driver'].'%');
        }

        if (!empty($filters['type'])) {
            $query->where('trans_delivery_order_header.type', '=', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('trans_delivery_order_header.status', '=', $filters['status']);
        }

        if (!empty($filters['policeNumber'])) {
            $query->where('mst_truck.police_number', 'ilike', '%'.$filters['policeNumber'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = TimezoneDateConverter::getServerDateTime($filters['dateFrom']);
            $query->where('trans_delivery_order_header.delivery_start_time', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = TimezoneDateConverter::getServerDateTime($filters['dateTo']);
            $query->where('trans_delivery_order_header.delivery_start_time', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        return view('operational::transaction.delivery-order.index', [
            'models'       => $query->paginate(10),
            'filters'      => $filters,
            'optionStatus' => $this->getOptionsStatus(),
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
        $model->status = DeliveryOrderHeader::OPEN;

        return view('operational::transaction.delivery-order.add', [
            'title'        => trans('shared/common.add'),
            'model'        => $model,
            'optionResi'   => $this->getOptionsResi(),
            'optionStatus' => $this->getOptionsStatus(),
            'optionDraftDo'   => $this->getOptionsDraftDo(),
            'optionType'   => $this->getOptionsType(),
            'optionDriver' => DriverService::getActiveDriverAsistant(),
            'optionDeliveryArea' => DeliveryAreaService::getActiveDeliveryArea(),
            'optionTruck'  => TruckService::getAllActiveTruckNonService(),
            'optionPartner'=> VendorService::getQueryVendorMitra()->get(),
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $model = DeliveryOrderHeader::where('delivery_order_header_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        $data = [
            'title'        => trans('shared/common.edit'),
            'model'        => $model,
            'optionResi'   => $this->getOptionsResi(),
            'optionStatus' => $this->getOptionsStatus(),
            'optionType'   => $this->getOptionsType(),
            'optionDraftDo'=> $this->getOptionsDraftDo($id),
            'optionDriver' => DriverService::getActiveDriverAsistant(),
            'optionTruck'  => TruckService::getAllActiveTruckNonService(),
            'optionDeliveryArea' => DeliveryAreaService::getActiveDeliveryArea(),
            'optionPartner'=> VendorService::getQueryVendorMitra()->get(),
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
        ];

        if ($request->user()->can('access', [self::RESOURCE, 'update'])) {
            return view('operational::transaction.delivery-order.add', $data);
        } else {
            return view('operational::transaction.delivery-order.detail', $data);
        }
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? DeliveryOrderHeader::where('delivery_order_header_id', '=', $id)->first() : new DeliveryOrderHeader();

        $this->validate($request, [
            'driverId'      => 'required',
            'noteHeader'    => 'required|max:250',
            'truckId'       => 'required',
            'type'       => 'required',
        ]);

        if (empty($request->get('resiId'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Lines are required']);
        }

        $lines = $model->lines()->get();
        $lineIds = $request->get('lineId');
        $resiIds = $request->get('resiId');

        $message = [];
        foreach ($request->get('resiId') as $resiId) {
            $i = array_search($resiId, $resiIds);
            $colySent        = intval($request->get('totalSend')[$i]);
            $colyResiStock   = $this->getColyResiStock($resiId);
            $colyResiDoExist = $this->getColyResiDoExist($id, $resiId);
            $max = $colyResiStock->coly - $colyResiDoExist->total_coly;

            if ($colySent > $max) {
                $message [] = $request->get('resiNumber')[$i]. ' exist on '. $max .' coly.';
            }
        }

        if (!empty($message)) {
            $string = '';
            foreach ($message as $mess) {
                $string = $string.' '.$mess;
            }
            $stringMessage = 'Coly exceed!'. $string;
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $stringMessage]);
        }
        $lines = $model->lines()->get();


        if ($request->get('type') == DeliveryOrderHeader::TRANSITION) {
            $this->validate($request, [
                'partnerName'  => 'required',
            ]);     
        }

        $startTimeString = $request->get('startDate').' '.$request->get('startHours').':'.$request->get('startMinute');
        $startTime       = !empty($startTimeString) ? TimezoneDateConverter::getServerDateTime($startTimeString) : null;

        $model->delivery_start_time = !empty($startTime) ? $startTime->format('Y-m-d H:i:s'):null;
        $model->draft_delivery_order_header_id = intval($request->get('draftDoHeaderId'));
        $model->driver_id           = intval($request->get('driverId'));
        $model->assistant_id        = intval($request->get('assistantId'));
        $model->truck_id            = intval($request->get('truckId'));
        $model->status              = DeliveryOrderHeader::OPEN;
        $model->type                = $request->get('type');
        $model->note                = $request->get('noteHeader');

        if (!empty($request->get('deliveryAreaHeader'))) {
            $model->delivery_area_id = $request->get('deliveryAreaHeader');
        }else{
            $model->delivery_area_id = NULL;
        }

        if (!empty($request->get('partnerId'))) {
            $model->partner_id      = $request->get('partnerId');
        }

        if (empty($model->branch_id)) {
            $model->branch_id = $request->session()->get('currentBranch')->branch_id;
        }

        $now = new \DateTime();
        if (empty($id)) {
            $model->delivery_order_number = $this->getDeliveryOrderNumber($model);
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


        foreach ($lines as $line) {
            $i = array_search($line->delivery_order_line_id, $lineIds);
            if (in_array($line->delivery_order_line_id, $lineIds)) {
                $line->resi_header_id    = intval($request->get('resiId')[$i]);
                $line->last_updated_date = new \DateTime();
                $line->last_updated_by   = \Auth::user()->id;
                $line->total_coly        = intval($request->get('totalSend')[$i]);
                $line->delivery_cost = intval(str_replace(',', '', $request->get('deliveryCost')[$i]));
                $line->description = $request->get('descriptionLine')[$i];

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

        for ($i = 0; $i < count($request->get('lineId')); $i++) {
            if (empty($request->get('lineId')[$i])) {
                $line = new DeliveryOrderLine();
                $line->delivery_order_header_id = $model->delivery_order_header_id;
                $line->resi_header_id = intval($request->get('resiId')[$i]);
                $line->delivery_cost  = intval(str_replace(',', '', $request->get('deliveryCost')[$i]));
                $line->total_coly     = intval($request->get('totalSend')[$i]);
                $line->created_date   = new \DateTime();
                $line->created_by     = \Auth::user()->id;
                $line->description    = $request->get('descriptionLine')[$i];

                try {
                    $line->save();
                } catch (\Exception $e) {
                    return redirect(self::URL.'/edit/'.$model->delivery_order_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }
            }
        }

        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(self::URL.'/edit/'.$model->delivery_order_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        if ($request->get('btn-request-approval') !== null) {
            $userNotif = NotificationService::getUserNotification([Role::WAREHOUSE_MANAGER]);
            foreach ($userNotif as $user) {
                $notif             = new Notification();
                $notif->branch_id  = \Session::get('currentBranch')->branch_id;
                $notif->url        = ApproveDeliveryOrderController::URL.'/edit/'.$model->delivery_order_header_id;
                $notif->created_at = new \DateTime();
                $notif->user_id    = $user->id;
                $notif->role_id    = $user->role_id;
                $notif->category   = 'Request to Approve Delivery Order';
                $notif->message    = 'Need approval Delivery Order '.$model->delivery_order_number. '. ' . $request->get('note');
                $notif->save();
            }
            $model->status     = DeliveryOrderHeader::REQUEST_APPROVAL;

            if(!empty($model->draft_delivery_order_header_id)){
                $draftDo = DraftDeliveryOrderHeader::find($model->draft_delivery_order_header_id);
                $draftDo->status = DraftDeliveryOrderHeader::CLOSED;
                $draftDo->save();
            }
        }

        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(self::URL.'/edit/'.$model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }
        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.delivery-order').' '.$model->delivery_order_number])
            );

        return redirect(self::URL);
    }

    public function cancelDo(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'cancel'])) {
            abort(403);
        }

        $model = DeliveryOrderHeader::find($request->get('id'));
        if ($model === null) {
            abort(404);
        }

        if ($model->getTotalInvoiceMoneyTrip() > 0) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $model->delivery_order_number.' '.trans('shared/common.cannot-cancel-invoice-exist'). ' on invoice do money trip. Invoice exist on '.number_format($model->getTotalInvoiceMoneyTrip()) ]);
        }elseif ($model->getTotalInvoicePartner() > 0) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $model->delivery_order_number.' '.trans('shared/common.cannot-cancel-invoice-exist'). ' on invoice do partner. Invoice exist on '.number_format($model->getTotalInvoiceMoneyTrip()) ]);
        }

        $model->status = DeliveryOrderHeader::CANCELED;
        $model->canceled_date = new \DateTime();
        $model->canceled_by = \Auth::user()->id;
        $model->canceled_reason = $request->get('reason', '');

        $model->save();

        $userNotif = NotificationService::getUserNotification([Role::WAREHOUSE_ADMIN, Role::WAREHOUSE_MANAGER]);
        foreach ($userNotif as $user) {
            $notif             = new Notification();
            $notif->branch_id  = \Session::get('currentBranch')->branch_id;
            $notif->url        = DeliveryOrderController::URL.'/edit/'.$model->delivery_order_header_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->category   = 'Canceled Delivery Order';
            $notif->message    = 'Canceled Delivery Order'.$model->delivery_order_number. '. ' . $model->canceled_reason;
            $notif->save();
        }

        $request->session()->flash(
            'successMessage',
            trans('purchasing/fields.canceled-message', ['variable' => trans('operational/menu.delivery-order').' '.$model->delivery_order_number])
            );

        return redirect(self::URL);
    }

    public function printPdfDetail(Request $request, $id)
    {
        $filters = \Session::get('filters');
        $model   = DeliveryOrderHeader::find($id);

        $header = view('print.header-pdf', ['title' => trans('operational/menu.delivery-order')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('operational::transaction.delivery-order.print-pdf-detail', [
            'model'  => $model,
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('operational/menu.delivery-order').' - '.$model->delivery_order_number);
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output($model->delivery_order_number.'.pdf');
        \PDF::reset();
    }

    protected function getColyResiStock($resiId){
        return \DB::table('op.mst_stock_resi')
        ->selectRaw('sum(coly) as coly')
        ->where('resi_header_id', '=', $resiId)
        ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
        ->first();
    }

    protected function getColyResiDoExist($doId, $resiId){
        return \DB::table('op.trans_delivery_order_line')
        ->selectRaw('sum(total_coly) as total_coly')
        ->join('op.trans_delivery_order_header', 'trans_delivery_order_header.delivery_order_header_id', '=', 'trans_delivery_order_line.delivery_order_header_id')
        ->where('trans_delivery_order_header.delivery_order_header_id', '!=', $doId)
        ->where('resi_header_id', '=', $resiId)
        ->where('trans_delivery_order_header.status', '!=', DeliveryOrderHeader::CLOSED)
        ->where('trans_delivery_order_header.status', '!=', DeliveryOrderHeader::CANCELED)
	->where('trans_delivery_order_header.status', '!=', DeliveryOrderHeader::ON_THE_ROAD)
        ->where('trans_delivery_order_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
        ->first();
    }

    protected function getDeliveryOrderNumber(DeliveryOrderHeader $model)
    {
        $branch      = MasterBranch::find(\Session::get('currentBranch')->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('op.trans_delivery_order_header')
        ->where('branch_id', '=', $branch->branch_id)
        ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
        ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
        ->count();

        return 'DO.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }

    protected function getOptionsStatus()
    {
        return [
            DeliveryOrderHeader::OPEN,
            DeliveryOrderHeader::REQUEST_APPROVAL,
            DeliveryOrderHeader::APPROVED,
            DeliveryOrderHeader::CONFIRMED,
            DeliveryOrderHeader::ON_THE_ROAD,
            DeliveryOrderHeader::CLOSED,
            DeliveryOrderHeader::CANCELED,
        ];
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
        ->select(
            'trans_resi_header.resi_header_id','mst_customer.customer_id','trans_resi_header.receiver_name', 'trans_resi_header.receiver_address', 'trans_resi_header.wdl_note', 
            'trans_resi_header.receiver_phone','mst_customer.customer_name','trans_resi_header.resi_number','trans_resi_header.item_name',
            'trans_resi_header.description', 'v_mst_route.route_code', 'v_mst_route.city_start_name', 'v_mst_route.city_end_name',
            'mst_delivery_area.delivery_area_name'
            )
        ->join('op.v_mst_route', 'v_mst_route.route_id', '=', 'trans_resi_header.route_id')
        ->join('op.mst_stock_resi', 'op.mst_stock_resi.resi_header_id', '=', 'trans_resi_header.resi_header_id')
        ->join('op.mst_delivery_area', 'mst_delivery_area.delivery_area_id', '=', 'trans_resi_header.delivery_area_id')
        ->leftJoin('op.mst_customer', 'mst_customer.customer_id', '=', 'trans_resi_header.customer_id')
        ->where('mst_stock_resi.branch_id', '=', \Session::get('currentBranch')->branch_id)
        ->where('mst_stock_resi.is_ready_delivery', '=', TRUE)
        ->get();

        $arrResi = [];
        foreach($listResi as $resi) {
            $modelResi = TransactionResiHeader::find($resi->resi_header_id);
            $resi->customer_name = $modelResi->getCustomerName();
            $resi->total_coly   = $modelResi->totalColy();
            $resi->total_weight = $modelResi->totalWeightAll();
            $resi->total_receipt = $modelResi->totalReceipt(); 
            $resi->total_volume = $modelResi->totalVolumeAll();
            $resi->total_available = $modelResi->totalAvailable(); 
            if ($modelResi->totalAvailable() == 0) {
                continue;
            }
            $arrResi [] = $resi;
        }
        return $arrResi;
    }

    protected function getOptionsDraftDo($doHeaderId = null){
        $listDraftDo = \DB::table('op.trans_draft_delivery_order_header')
        ->select(
            'trans_draft_delivery_order_header.*',
            'mst_truck.police_number', 
            'driver.driver_name',
            'assistant.driver_name as assistant_name'
            )
        ->join('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_draft_delivery_order_header.truck_id')
        ->join('op.mst_driver as driver', 'driver.driver_id', '=', 'trans_draft_delivery_order_header.driver_id')
        ->leftJoin('op.trans_delivery_order_header', 'trans_delivery_order_header.draft_delivery_order_header_id', '=', 'trans_draft_delivery_order_header.draft_delivery_order_header_id')
        ->leftJoin('op.mst_driver as assistant', 'assistant.driver_id', '=', 'trans_draft_delivery_order_header.assistant_id')
        ->where('trans_draft_delivery_order_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
        ->where('trans_draft_delivery_order_header.status', '=', DraftDeliveryOrderHeader::OPEN)
        ->where(function ($query) use ($doHeaderId) {
            $query->whereNull('trans_delivery_order_header.delivery_order_header_id')
                ->orWhere('trans_delivery_order_header.delivery_order_header_id', '=', $doHeaderId);
        });

        $arrDraftDo = [];
        foreach($listDraftDo->get() as $draftDo) {
            $modelHeaders = DraftDeliveryOrderHeader::find($draftDo->draft_delivery_order_header_id);
            $arrLineDraftDo = [];
            $countResi = 0;
            $countAvailable = 0;
            foreach($modelHeaders->lines as $modelLine) {
                $draftDo->count_resi          = ++$countResi;
                if ($modelLine->resi->totalAvailableExcept($doHeaderId) == 0 || !$modelLine->resi->isReadyDelivery()) {
                    continue;
                }
                $modelLine->resi_number       = $modelLine->resi->resi_number;
                $modelLine->item_name         = $modelLine->resi->item_name;
                $modelLine->receiver_name     = $modelLine->resi->receiver_name;
                $modelLine->receiver_phone    = $modelLine->resi->receiver_phone;
                $modelLine->receiver_address  = $modelLine->resi->receiver_address;
                $modelLine->delivery_area     = !empty($modelLine->resi->deliveryArea) ? $modelLine->resi->deliveryArea->delivery_area_name : '';
                $modelLine->customer_name     = $modelLine->resi->getCustomerName();
                $modelLine->total_coly_resi   = $modelLine->resi->totalColy();
                $modelLine->total_weight      = $modelLine->resi->totalWeightAll();
                $modelLine->total_receipt     = $modelLine->resi->totalReceipt(); 
                $modelLine->total_volume      = $modelLine->resi->totalVolumeAll();
                $modelLine->total_available   = $modelLine->resi->totalAvailableExcept($doHeaderId);
                $draftDo->count_available     = ++$countAvailable;
                
                $arrLineDraftDo [] = $modelLine;
            }    
            if ($arrLineDraftDo == []) {
                continue;
            }
            $draftDo->lines = $arrLineDraftDo;
            $arrDraftDo [] = $draftDo;
        }
        return $arrDraftDo;
    }
}
