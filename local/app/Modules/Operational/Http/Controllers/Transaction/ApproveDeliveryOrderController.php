<?php

namespace App\Modules\Operational\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader;
use App\Modules\Operational\Model\Transaction\DeliveryOrderLine;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Transaction\ResiStock;
use App\Modules\Operational\Service\Master\DriverService;
use App\Modules\Operational\Service\Transaction\PickupService;
use App\Modules\Marketing\Model\Transaction\PickupRequest;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Service\Penomoran;
use App\Notification;
use App\Service\NotificationService;
use App\Role;
use App\Service\TimezoneDateConverter;

class ApproveDeliveryOrderController extends Controller
{
    const RESOURCE = 'Operational\Transaction\ApproveDeliveryOrder';
    const URL      = 'operational/transaction/approve-delivery-order';

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
        $query = \DB::table('op.v_trans_delivery_order_header')
                ->where('branch_id', '=', \Session::get('currentBranch')->branch_id);

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
        
        $query->where('status', '=', DeliveryOrderHeader::REQUEST_APPROVAL);

        return view('operational::transaction.approve-delivery-order.index', [
            'models'       => $query->paginate(10),
            'filters'      => $filters,
            'optionType'   => $this->getOptionsType(),
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

        return view('operational::transaction.approve-delivery-order.add', [
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
        // var_dump($request->all());exit();
        $id = intval($request->get('id'));
        $model = !empty($id) ? DeliveryOrderHeader::where('delivery_order_header_id', '=', $id)->first() : new DeliveryOrderHeader();

        $this->validate($request, [
            'note'   => 'required|max:250',
        ]);

        if ($request->get('btn-approve') !== null) {

            $userNotif = NotificationService::getUserNotification([Role::OPERATIONAL_ADMIN]);
            foreach ($userNotif as $user) {
                $notif             = new Notification();
                $notif->branch_id  = \Session::get('currentBranch')->branch_id;
                $notif->url        = CostDeliveryOrderController::URL.'/edit/'.$model->delivery_order_header_id;
                $notif->created_at = new \DateTime();
                $notif->user_id    = $user->id;
                $notif->role_id    = $user->role_id;
                $notif->category   = 'Approved Delivery Order';
                $notif->message    = 'Approved Delivery Order '.$model->delivery_order_number. '. ' . $request->get('note');
                $notif->save();
            }
            $model->status     = DeliveryOrderHeader::APPROVED;
        }elseif ($request->get('btn-reject') !== null){
            $userNotif = NotificationService::getUserNotification([Role::WAREHOUSE_ADMIN]);
            foreach ($userNotif as $user) {
                $notif             = new Notification();
                $notif->branch_id  = \Session::get('currentBranch')->branch_id;
                $notif->url        = DeliveryOrderController::URL.'/edit/'.$model->delivery_order_header_id;
                $notif->created_at = new \DateTime();
                $notif->user_id    = $user->id;
                $notif->role_id    = $user->role_id;
                $notif->category   = 'Rejected Approval Delivery Order';
                $notif->message    = 'Rejected Approval Delivery Order '.$model->delivery_order_number. '. ' . $request->get('note');
                $notif->save();    
                }
            $model->status     = DeliveryOrderHeader::OPEN;
        }

        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(self::URL.'/edit/'.$model->delivery_order_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.delivery-order').' '.$model->delivery_order_number])
        );

        return redirect(self::URL);
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

    protected function getOptionsStatus()
    {
        return [
            DeliveryOrderHeader::OPEN,
            DeliveryOrderHeader::APPROVED,
            DeliveryOrderHeader::CLOSED,
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
