<?php

namespace App\Modules\Operational\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Accountreceivables\Model\Transaction\InvoiceArHeader;
use App\Modules\Accountreceivables\Model\Transaction\InvoiceArLine;
use App\Modules\Operational\Model\Transaction\PickupFormHeader;
use App\Modules\Operational\Model\Transaction\PickupFormLine;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Service\Master\DriverService;
use App\Modules\Operational\Service\Master\TruckService;
use App\Modules\Operational\Service\Transaction\PickupService;
use App\Modules\Marketing\Model\Transaction\PickupRequest;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Model\Master\MasterDriver;
use App\Modules\Operational\Model\Master\MasterTruck;
use App\Service\TimezoneDateConverter;
use App\Modules\Operational\Service\Master\DeliveryAreaService;
use App\Service\Penomoran;

class PickupFormController extends Controller
{
    const RESOURCE    = 'Operational\Transaction\PickupForm';
    const URL         = 'operational/transaction/pickup-form';
    const URL_REQUEST = 'marketing/transaction/pickup-request';

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
        $query = \DB::table('op.trans_pickup_form_header')
                ->select('trans_pickup_form_header.*', 'mst_driver.driver_name', 'mst_driver.driver_nickname', 'mst_truck.police_number')
                ->leftJoin('op.mst_driver', 'mst_driver.driver_id', '=', 'trans_pickup_form_header.driver_id')
                ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_pickup_form_header.truck_id')
                ->where('trans_pickup_form_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                ->orderBy('trans_pickup_form_header.created_date', 'desc');

        if (!empty($filters['pickupFormNumber'])) {
            $query->where('trans_pickup_form_header.pickup_form_number', 'ilike', '%'.$filters['pickupFormNumber'].'%');
        }

        if (!empty($filters['status'])) {
            $query->where('trans_pickup_form_header.status', '=', $filters['status']);
        }

        if (!empty($filters['note'])) {
            $query->where('trans_pickup_form_header.note', 'ilike', '%'.$filters['note'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = TimezoneDateConverter::getServerDateTime($filters['dateFrom']);
            $query->where('trans_pickup_form_header.pickup_form_time', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = TimezoneDateConverter::getServerDateTime($filters['dateTo']);
            $query->where('trans_pickup_form_header.pickup_form_time', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        return view('operational::transaction.pickup-form.index', [
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

        $model         = new PickupFormHeader();
        $model->status = PickupFormHeader::OPEN;

        return view('operational::transaction.pickup-form.add', [
            'title'        => trans('shared/common.add'),
            'model'        => $model,
            // 'optionResi'   => $this->getOptionsResi(),
            'optionStatus' => $this->getOptionsStatus(),
            'optionDeliveryArea' => DeliveryAreaService::getActiveDeliveryArea(),
            'optionDriver' => DriverService::getActiveDriverAsistant(),
            'optionTruck'  => TruckService::getAllActiveTruckNonService(),
            'optionPickup' => PickupService::getPickupRequestApproved(),
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
            'urlRequest'   => self::URL_REQUEST,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = PickupFormHeader::where('pickup_form_header_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        $data = [
            'title'        => trans('shared/common.edit'),
            'model'        => $model,
            // 'optionResi'   => $this->getOptionsResi(),
            'optionStatus' => $this->getOptionsStatus(),
            'optionDeliveryArea' => DeliveryAreaService::getActiveDeliveryArea(),
            'optionDriver' => DriverService::getActiveDriverAsistant(),
            'optionTruck'  => TruckService::getAllActiveTruckNonService(),
            'optionPickup' => PickupService::getPickupRequestApproved(),
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
            'urlRequest'   => self::URL_REQUEST,
        ];

        if ($request->user()->can('access', [self::RESOURCE, 'update'])) {
            return view('operational::transaction.pickup-form.add', $data);
        } else {
            return view('operational::transaction.pickup-form.detail', $data);
        }
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? PickupFormHeader::where('pickup_form_header_id', '=', $id)->first() : new PickupFormHeader();

        $this->validate($request, [
            'driverName'   => 'required|max:50',
            'noteHeader'   => 'required|max:250',
            'truckId'      => 'required',
        ]);

        if ($request->get('status') == PickupFormHeader::OPEN) {
            if (empty($request->get('pickupRequestId'))) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Lines are required']);
            }
        }

        $timeString = $request->get('date').' '.$request->get('hours').':'.$request->get('minute');
        $time       = !empty($timeString) ? TimezoneDateConverter::getServerDateTime($timeString) : null;

        $model->pickup_time   = !empty($time) ? $time->format('Y-m-d H:i:s'):null;
        $model->driver_id     = intval($request->get('driverId'));
        $model->truck_id      = $request->get('truckId');
        $model->status        = $request->get('status');
        $model->note          = $request->get('noteHeader');

        if (!empty($request->get('deliveryArea'))) {
            $model->delivery_area_id = $request->get('deliveryArea');
        }else{
            $model->delivery_area_id = NULL;
        }

        if (empty($model->branch_id)) {
            $model->branch_id = $request->session()->get('currentBranch')->branch_id;
        }

        $now = new \DateTime();
        if (empty($id)) {
            $model->pickup_form_number = $this->getPickupFormNumber($model);
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

        $totalPricePo = 0;
        $lines = $model->lines()->get();
        $lineIds = $request->get('lineId', []);

        foreach ($lines as $line) {
            $i = array_search($line->pickup_form_line_id, $lineIds);
            if (in_array($line->pickup_form_line_id, $lineIds)) {
                $line->pickup_request_id = $request->get('pickupRequestId')[$i];
                // $line->resi_id = $request->get('resiId')[$i];
                $line->last_updated_date = new \DateTime();
                $line->last_updated_by = \Auth::user()->id;

                $modelRequest                   = PickupRequest::find($line->pickup_request_id);
                $modelRequest->pickup_cost      = str_replace(',', '', $request->get('pickupCost')[$i]);
                $modelRequest->dimension_long   = floatval(str_replace(',', '', $request->get('dimensionL')[$i]));
                $modelRequest->dimension_width  = floatval(str_replace(',', '', $request->get('dimensionW')[$i]));
                $modelRequest->dimension_height = floatval(str_replace(',', '', $request->get('dimensionH')[$i]));
                $modelRequest->dimension        = floatval(str_replace(',', '', $request->get('dimension')[$i]));
                $modelRequest->item_name        = $request->get('itemName')[$i];
                $modelRequest->total_coly       = intval($request->get('totalColy')[$i]);
                $modelRequest->weight           = $request->get('weight')[$i];
                $modelRequest->note             = $request->get('note')[$i];
                $modelRequest->note_add         = $request->get('noteAdd')[$i];

                try {
                    $modelRequest->save();
                    $line->save();
                } catch (\Exception $e) {
                    return redirect(self::URL.'/edit/'.$model->pickup_form_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }
            } else {
                $modelRequest              = PickupRequest::find($line->pickup_request_id);
                $modelRequest->status      = PickupRequest::APPROVED;
                try {
                    $modelRequest->save();
                    $line->delete();
                } catch (\Exception $e) {
                    return redirect(self::URL.'/edit/'.$model->pickup_form_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }
            }
        }

        for ($i = 0; $i < count($request->get('lineId')); $i++) {
            if (empty($request->get('lineId')[$i])) {
                $line = new PickupFormLine();
                $line->pickup_form_header_id = $model->pickup_form_header_id;
                $line->pickup_request_id = $request->get('pickupRequestId')[$i];
                $line->created_date = new \DateTime();
                $line->created_by = \Auth::user()->id;

                $modelRequest              = PickupRequest::find($line->pickup_request_id);
                $modelRequest->status      = PickupRequest::CLOSED;
                $modelRequest->pickup_cost = str_replace(',', '', $request->get('pickupCost')[$i]);
                $modelRequest->note        = $request->get('note')[$i];
                try {
                    $modelRequest->save();
                    $line->save();
                } catch (\Exception $e) {
                    return redirect(self::URL.'/edit/'.$model->pickup_form_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }
            }
        }

        if($model->status == PickupFormHeader::CLOSED && !empty($model->delivery_area_id)){
            $truck  = MasterTruck::find($model->truck_id);
            $driver = MasterDriver::find($model->driver_id);
            $salary = $driver !== null && $driver->isTripEmployee() ? $this->getDriverSalary($model->delivery_area_id, $truck->type, MasterDriver::DRIVER) : 0;
            $model->driver_salary = intval(!empty($salary) ? $salary->salary : 0);
        }

        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(self::URL.'/edit/'.$model->pickup_form_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.pickup-form').' '.$model->pickup_form_number])
        );

        return redirect(self::URL);
    }

    public function printPdfDetail(Request $request, $id)
    {
        $filters = \Session::get('filters');
        $model   = PickupFormHeader::find($id);

        $header = view('print.header-pdf', ['title' => trans('operational/menu.pickup-form')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('operational::transaction.pickup-form.print-pdf-detail', [
            'model'  => $model,
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('operational/menu.pickup-form').' - '.$model->pickup_form_number);
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A5');
        \PDF::writeHTML($html);
        \PDF::Output($model->pickup_form_number.'.pdf');
        \PDF::reset();
    }

    public function cancel(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'cancel'])) {
            abort(403);
        }

        $model = PickupFormHeader::find($request->get('id'));
        if ($model === null) {
            abort(404);
        }

        if (!empty($this->isIncludeResi($model))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => trans('operational/menu.pickup-form').' '.$model->pickup_form_number.' '.trans('shared/common.cannot-cancel'). ' because line exist on resi']);
        }

        $model->status = PickupFormHeader::CANCELED;
        $model->last_updated_date = new \DateTime();
        $model->last_updated_by   = \Auth::user()->id;
        $model->save();

        foreach ($model->lines as $line) {
            $pickupRequest = PickupRequest::find($line->pickup_request_id);
            $pickupRequest->status = PickupRequest::APPROVED;
            $pickupRequest->save();
        }

        $request->session()->flash(
            'successMessage',
            trans('purchasing/fields.canceled-message', ['variable' => trans('operational/menu.pickup-form').' '.$model->pickup_form_number])
            );

        return redirect(self::URL);
    }

    protected function getDriverSalary($deliveryAreaId, $vehicleType, $driverPosition){
        return \DB::table('op.mst_do_pickup_driver_salary')
                    ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)        
                    ->where('delivery_area_id', '=', $deliveryAreaId)        
                    ->where('vehicle_type', '=', $vehicleType)
                    ->where('driver_position', '=', $driverPosition)
                    ->first();        
    }

    protected function isIncludeResi(PickupFormHeader $model){
        $include = FALSE;
        foreach ($model->lines as $line) {
            if (!empty($line->pickupRequest->resi)) {
                var_dump($line->pickupRequest->resi);
                $include = TRUE;
            }
        }
        return $include;
    }

    protected function getPickupFormNumber(PickupFormHeader $model)
    {
        $branch      = MasterBranch::find(\Session::get('currentBranch')->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('op.trans_pickup_form_header')
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->where('branch_id', '=', $branch->branch_id)
                            ->count();

        return 'PF.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }

    protected function getOptionsStatus()
    {
        return [
            PickupFormHeader::OPEN,
            PickupFormHeader::CLOSED,
        ];
    }

    protected function getOptionsResi(){
        $listResi = \DB::table('op.trans_resi_header')
                    ->select('trans_resi_header.resi_header_id','mst_customer.customer_id','mst_customer.customer_name','mst_customer.address','mst_customer.phone_number', 'trans_resi_header.resi_number','trans_resi_header.item_name', 'trans_resi_header.description', 'v_mst_route.route_code', 'v_mst_route.city_start_name', 'v_mst_route.city_end_name')
                    ->join('op.v_mst_route', 'v_mst_route.route_id', '=', 'trans_resi_header.route_id')
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
