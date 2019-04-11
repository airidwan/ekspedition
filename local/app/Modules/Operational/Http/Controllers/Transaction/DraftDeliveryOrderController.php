<?php

namespace App\Modules\Operational\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Transaction\DraftDeliveryOrderHeader;
use App\Modules\Operational\Model\Transaction\DraftDeliveryOrderLine;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Http\Controllers\Transaction\CostDraftDeliveryOrderController;
use App\Modules\Operational\Model\Transaction\ResiStock;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
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

class DraftDeliveryOrderController extends Controller
{
    const RESOURCE = 'Operational\Transaction\DraftDeliveryOrder';
    const URL      = 'operational/transaction/draft-delivery-order';

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
            $query = \DB::table('op.trans_draft_delivery_order_header')
                            ->select(
                                'trans_draft_delivery_order_header.draft_delivery_order_header_id',
                                'trans_draft_delivery_order_header.draft_delivery_order_number',
                                'trans_draft_delivery_order_header.status',
                                'trans_draft_delivery_order_header.created_date',
                                'driver.driver_name',
                                'assistant.driver_name as assistant_name',
                                'mst_truck.police_number'
                                )
                            ->leftJoin('op.trans_draft_delivery_order_line', 'trans_draft_delivery_order_line.draft_delivery_order_header_id', '=', 'trans_draft_delivery_order_header.draft_delivery_order_header_id')
                            ->leftJoin('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_draft_delivery_order_line.resi_header_id')
                            ->leftJoin('op.mst_driver as driver', 'driver.driver_id', '=', 'trans_draft_delivery_order_header.driver_id')
                            ->leftJoin('op.mst_driver as assistant', 'assistant.driver_id', '=', 'trans_draft_delivery_order_header.assistant_id')
                            ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_draft_delivery_order_header.truck_id')
                            ->where('trans_draft_delivery_order_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->distinct()
                            ->orderBy('trans_draft_delivery_order_header.created_date', 'desc');
        }else{
            $query = \DB::table('op.trans_draft_delivery_order_line')
                            ->select(
                                'trans_draft_delivery_order_line.total_coly as coly_send',
                                'trans_draft_delivery_order_header.draft_delivery_order_header_id',
                                'trans_draft_delivery_order_header.draft_delivery_order_number',
                                'trans_draft_delivery_order_header.created_date',
                                'trans_resi_header.resi_number',
                                'trans_resi_header.item_name',
                                'trans_resi_header.receiver_name',
                                'trans_resi_header.receiver_address',
                                'trans_resi_header.receiver_phone'
                                )
                            ->leftJoin('op.trans_draft_delivery_order_header', 'trans_draft_delivery_order_header.draft_delivery_order_header_id', '=', 'trans_draft_delivery_order_line.draft_delivery_order_header_id')
                            ->leftJoin('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_draft_delivery_order_line.resi_header_id')
                            ->leftJoin('op.mst_driver as driver', 'driver.driver_id', '=', 'trans_draft_delivery_order_header.driver_id')
                            ->leftJoin('op.mst_driver as assistant', 'assistant.driver_id', '=', 'trans_draft_delivery_order_header.assistant_id')
                            ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_draft_delivery_order_header.truck_id')
                            ->where('trans_draft_delivery_order_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->distinct()
                            ->orderBy('trans_draft_delivery_order_header.created_date', 'desc');
        }

        if (!empty($filters['deliveryOrderNumber'])) {
            $query->where('trans_draft_delivery_order_header.draft_delivery_order_number', 'ilike', '%'.$filters['deliveryOrderNumber'].'%');
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

        if (!empty($filters['status'])) {
            $query->where('trans_draft_delivery_order_header.status', '=', $filters['status']);
        }

        if (!empty($filters['policeNumber'])) {
            $query->where('mst_truck.police_number', 'ilike', '%'.$filters['policeNumber'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = TimezoneDateConverter::getServerDateTime($filters['dateFrom']);
            $query->where('trans_draft_delivery_order_header.created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = TimezoneDateConverter::getServerDateTime($filters['dateTo']);
            $query->where('trans_draft_delivery_order_header.created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        return view('operational::transaction.draft-delivery-order.index', [
            'models'       => $query->paginate(10),
            'filters'      => $filters,
            'optionStatus' => $this->getOptionsStatus(),
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new DraftDeliveryOrderHeader();
        $model->status = DraftDeliveryOrderHeader::OPEN;

        return view('operational::transaction.draft-delivery-order.add', [
            'title'        => trans('shared/common.add'),
            'model'        => $model,
            'optionStatus' => $this->getOptionsStatus(),
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
        $model = DraftDeliveryOrderHeader::where('draft_delivery_order_header_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        $data = [
            'title'        => trans('shared/common.edit'),
            'model'        => $model,
            'optionStatus' => $this->getOptionsStatus(),
            'optionDriver' => DriverService::getActiveDriverAsistant(),
            'optionTruck'  => TruckService::getAllActiveTruckNonService(),
            'optionDeliveryArea' => DeliveryAreaService::getActiveDeliveryArea(),
            'optionPartner'=> VendorService::getQueryVendorMitra()->get(),
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
        ];

        if ($request->user()->can('access', [self::RESOURCE, 'update'])) {
            return view('operational::transaction.draft-delivery-order.add', $data);
        } else {
            return view('operational::transaction.draft-delivery-order.detail', $data);
        }
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? DraftDeliveryOrderHeader::where('draft_delivery_order_header_id', '=', $id)->first() : new DraftDeliveryOrderHeader();

        $this->validate($request, [
            'driverId'      => 'required',
            'noteHeader'    => 'required|max:250',
            'truckId'       => 'required',
        ]);

        if (empty($request->get('resiId'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Lines are required']);
        }

        $lines = $model->lines()->get();
        $lineIds = $request->get('lineId');
        $resiIds = $request->get('resiId');

        $lines = $model->lines()->get();

        $model->driver_id           = intval($request->get('driverId'));
        $model->assistant_id        = intval($request->get('assistantId'));
        $model->truck_id            = intval($request->get('truckId'));
        $model->status              = DraftDeliveryOrderHeader::OPEN;
        $model->note                = $request->get('noteHeader');

        if (empty($model->branch_id)) {
            $model->branch_id = $request->session()->get('currentBranch')->branch_id;
        }

        $now = new \DateTime();
        if (empty($id)) {
            $model->draft_delivery_order_number = $this->getDraftDeliveryOrderNumber($model);
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
            $i = array_search($line->draft_delivery_order_line_id, $lineIds);
            if (in_array($line->draft_delivery_order_line_id, $lineIds)) {
                $line->resi_header_id    = intval($request->get('resiId')[$i]);
                $line->last_updated_date = new \DateTime();
                $line->last_updated_by   = \Auth::user()->id;
                $line->total_coly        = intval($request->get('totalSend')[$i]);
                $line->description = $request->get('descriptionLine')[$i];

                try {
                    $line->save();
                } catch (\Exception $e) {
                    return redirect(self::URL.'/edit/'.$model->draft_delivery_order_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }
            } else {
                try {
                    $line->delete();
                } catch (\Exception $e) {
                    return redirect(self::URL.'/edit/'.$model->draft_delivery_order_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }
            }
        }

        for ($i = 0; $i < count($request->get('lineId')); $i++) {
            if (empty($request->get('lineId')[$i])) {
                $line = new DraftDeliveryOrderLine();
                $line->draft_delivery_order_header_id = $model->draft_delivery_order_header_id;
                $line->resi_header_id = intval($request->get('resiId')[$i]);
                $line->total_coly     = intval($request->get('totalSend')[$i]);
                $line->created_date   = new \DateTime();
                $line->created_by     = \Auth::user()->id;
                $line->description    = $request->get('descriptionLine')[$i];

                try {
                    $line->save();
                } catch (\Exception $e) {
                    return redirect(self::URL.'/edit/'.$model->draft_delivery_order_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }
            }
        }

        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(self::URL.'/edit/'.$model->draft_delivery_order_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(self::URL.'/edit/'.$model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }
        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.draft-delivery-order').' '.$model->draft_delivery_order_number])
            );

        return redirect(self::URL);
    }

    public function cancelDraftDo(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'cancel'])) {
            abort(403);
        }

        $model = DraftDeliveryOrderHeader::find($request->get('id'));
        if ($model === null) {
            abort(404);
        }


        $model->status = DraftDeliveryOrderHeader::CANCELED;
        $model->canceled_date = new \DateTime();
        $model->canceled_by = \Auth::user()->id;
        $model->canceled_reason = $request->get('reason', '');

        $model->save();

        $request->session()->flash(
            'successMessage',
            trans('purchasing/fields.canceled-message', ['variable' => trans('operational/menu.draft-delivery-order').' '.$model->draft_delivery_order_number])
            );

        return redirect(self::URL);
    }

    public function printPdfDetail(Request $request, $id)
    {
        $filters = \Session::get('filters');
        $model   = DraftDeliveryOrderHeader::find($id);

        $header = view('print.header-pdf', ['title' => trans('operational/menu.draft-delivery-order')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('operational::transaction.draft-delivery-order.print-pdf', [
            'models'  => $model,
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('operational/menu.draft-delivery-order').' - '.$model->draft_delivery_order_number);
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output($model->draft_delivery_order_number.'.pdf');
        \PDF::reset();
    }

    protected function getColyManifestSent($resiId){
        return \DB::table('op.trans_manifest_line')
        ->selectRaw('sum(coly_sent) as coly')
        ->join('op.trans_manifest_header', 'trans_manifest_header.manifest_header_id', '=', 'trans_manifest_line.manifest_header_id')
        ->where('resi_header_id', '=', $resiId)
        ->where('trans_manifest_header.arrive_branch_id', '=', \Session::get('currentBranch')->branch_id)
        ->first();
    }

    protected function getColyResiDraftDoExist($doId, $resiId){
        return \DB::table('op.trans_draft_delivery_order_line')
        ->selectRaw('sum(total_coly) as total_coly')
        ->join('op.trans_draft_delivery_order_header', 'trans_draft_delivery_order_header.draft_delivery_order_header_id', '=', 'trans_draft_delivery_order_line.draft_delivery_order_header_id')
        ->where('trans_draft_delivery_order_header.draft_delivery_order_header_id', '!=', $doId)
        ->where('resi_header_id', '=', $resiId)
        ->where('trans_draft_delivery_order_header.status', '!=', DraftDeliveryOrderHeader::CLOSED)
        ->where('trans_draft_delivery_order_header.status', '!=', DraftDeliveryOrderHeader::CANCELED)
        ->where('trans_draft_delivery_order_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
        ->first();
    }

    protected function getDraftDeliveryOrderNumber(DraftDeliveryOrderHeader $model)
    {
        $branch      = MasterBranch::find(\Session::get('currentBranch')->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('op.trans_draft_delivery_order_header')
        ->where('branch_id', '=', $branch->branch_id)
        ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
        ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
        ->count();

        return 'DDO.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }

    protected function getOptionsStatus()
    {
        return [
            DraftDeliveryOrderHeader::OPEN,
            DraftDeliveryOrderHeader::CLOSED,
            DraftDeliveryOrderHeader::CANCELED,
        ];
    }

    protected function getJsonResi(Request $request){
        $search   = $request->get('search');

        $listResiManifest = \DB::table('op.trans_resi_header')
        ->select(
            'trans_resi_header.resi_header_id',
            'trans_resi_header.receiver_name',
            'trans_resi_header.receiver_address', 
            'trans_resi_header.receiver_phone',
            'trans_manifest_line.coly_sent',
            'mst_customer.customer_name',
            'trans_resi_header.resi_number',
            'trans_resi_header.item_name',
            'trans_resi_header.description as wdl_note', 
            'v_mst_route.route_code', 
            'v_mst_route.city_start_name', 
            'v_mst_route.city_end_name',
            'mst_delivery_area.delivery_area_name'
            )
        ->join('op.v_mst_route', 'v_mst_route.route_id', '=', 'trans_resi_header.route_id')
        ->join('op.trans_manifest_line', 'op.trans_manifest_line.resi_header_id', '=', 'trans_resi_header.resi_header_id')
        ->join('op.trans_manifest_header', 'op.trans_manifest_header.manifest_header_id', '=', 'trans_manifest_line.manifest_header_id')
        ->leftjoin('op.mst_delivery_area', 'mst_delivery_area.delivery_area_id', '=', 'trans_resi_header.delivery_area_id')
        ->leftJoin('op.mst_customer', 'mst_customer.customer_id', '=', 'trans_resi_header.customer_id')
        ->where('trans_manifest_header.arrive_branch_id', '=', \Session::get('currentBranch')->branch_id)
        ->where('trans_manifest_header.status', '=', ManifestHeader::ARRIVED)
        ->where(function ($query) use ($search) {
            $query->where('trans_resi_header.resi_number', 'ilike', '%'.$search.'%')
              ->orWhere('trans_resi_header.item_name', 'ilike', '%'.$search.'%')
              ->orWhere('trans_resi_header.sender_name', 'ilike', '%'.$search.'%')
              ->orWhere('trans_resi_header.receiver_name', 'ilike', '%'.$search.'%')
              ->orWhere('trans_resi_header.receiver_address', 'ilike', '%'.$search.'%')
              ->orWhere('trans_resi_header.receiver_phone', 'ilike', '%'.$search.'%');
        })
        ->distinct();

        $listResiStock = \DB::table('op.trans_resi_header')
        ->select(
            'trans_resi_header.resi_header_id',
            'trans_resi_header.receiver_name',
            'trans_resi_header.receiver_address', 
            'trans_resi_header.receiver_phone',
            'mst_stock_resi.coly as coly_sent',
            'mst_customer.customer_name',
            'trans_resi_header.resi_number',
            'trans_resi_header.item_name',
            'trans_resi_header.wdl_note', 
            'v_mst_route.route_code', 
            'v_mst_route.city_start_name', 
            'v_mst_route.city_end_name',
            'mst_delivery_area.delivery_area_name'
            )
        ->join('op.mst_stock_resi', 'mst_stock_resi.resi_header_id', '=', 'trans_resi_header.resi_header_id')
        ->join('op.v_mst_route', 'v_mst_route.route_id', '=', 'trans_resi_header.route_id')
        ->join('op.trans_manifest_line', 'op.trans_manifest_line.resi_header_id', '=', 'trans_resi_header.resi_header_id')
        ->join('op.trans_manifest_header', 'op.trans_manifest_header.manifest_header_id', '=', 'trans_manifest_line.manifest_header_id')
        ->leftjoin('op.mst_delivery_area', 'mst_delivery_area.delivery_area_id', '=', 'trans_resi_header.delivery_area_id')
        ->leftJoin('op.mst_customer', 'mst_customer.customer_id', '=', 'trans_resi_header.customer_id')
        ->where('mst_stock_resi.branch_id', '=', \Session::get('currentBranch')->branch_id)
        ->where(function ($query) use ($search) {
            $query->where('trans_resi_header.resi_number', 'ilike', '%'.$search.'%')
              ->orWhere('trans_resi_header.item_name', 'ilike', '%'.$search.'%')
              ->orWhere('trans_resi_header.sender_name', 'ilike', '%'.$search.'%')
              ->orWhere('trans_resi_header.receiver_name', 'ilike', '%'.$search.'%')
              ->orWhere('trans_resi_header.receiver_address', 'ilike', '%'.$search.'%')
              ->orWhere('trans_resi_header.receiver_phone', 'ilike', '%'.$search.'%');
        })
        ->distinct()
        ->union($listResiManifest)
        ->orderBy('resi_number', 'desc');

        $arrResi = [];
        foreach($listResiStock->take(10)->get() as $resi) {
            $modelResi = TransactionResiHeader::find($resi->resi_header_id);
            $resi->customer_name = $modelResi->getCustomerName();
            $resi->total_weight = $modelResi->totalWeightAll();
            $resi->total_coly = $modelResi->totalColy(); 
            $resi->total_available = $modelResi->totalAvailable(); 
            $resi->total_volume = $modelResi->totalVolumeAll();
            $arrResi [] = $resi;
        }

        return response()->json($arrResi);
    }
}
