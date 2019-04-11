<?php

namespace App\Modules\Operational\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Master\MasterDeliveryAreaMoneyTrip;
use App\Modules\Operational\Service\Master\CityService;
use App\Modules\Operational\Service\Master\DeliveryAreaService;

class MasterDeliveryAreaMoneyTripController extends Controller
{
    const RESOURCE = 'Operational\Master\MasterDeliveryAreaMoneyTrip';
    const URL      = 'operational/master/master-delivery-area-money-trip';
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
            return redirect(self::URL.'?page=1');
        } elseif (empty($request->get('page'))) {
            $request->session()->forget('filters');
        }

        $filters = $request->session()->get('filters');
        $query   = $this->getQuery($request, $filters);

        return view('operational::master.master-delivery-area-money-trip.index', [
            'models'              => $query->paginate(10),
            'filters'             => $filters,
            'resource'            => self::RESOURCE,
            'url'                 => self::URL,
            'optionType'          => $this->getOptionType(),
            'optionDeliveryArea'  => DeliveryAreaService::getActiveDeliveryArea(),
            ]);
    }

    public function getQuery(Request $request, $filters){
        $query   = \DB::table('op.mst_delivery_area_money_trip')
                    ->select('mst_delivery_area_money_trip.*', 'mst_delivery_area.delivery_area_name', 'type.meaning as type_vehicle')
                    ->leftJoin('op.mst_delivery_area', 'mst_delivery_area.delivery_area_id', '=', 'mst_delivery_area_money_trip.delivery_area_id')
                    ->leftJoin('adm.mst_lookup_values as type', 'type.lookup_code', '=', 'mst_delivery_area_money_trip.vehicle_type')
                    ->where('mst_delivery_area_money_trip.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where('mst_delivery_area_money_trip.active', '=', 'Y')
                    ->distinct();

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('mst_delivery_area_money_trip.active', '=', 'Y');
        } else {
            $query->where('mst_delivery_area_money_trip.active', '=', 'N');
        }

        if (!empty($filters['deliveryArea'])) {
            $query->where('mst_delivery_area.delivery_area_name', 'ilike', '%'.$filters['deliveryArea'].'%');
        }
        return $query;
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model         = new MasterDeliveryAreaMoneyTrip();
        $model->active = 'Y';

        return view('operational::master.master-delivery-area-money-trip.add', [
            'title'               => trans('shared/common.add'),
            'model'               => $model,
            'url'                 => self::URL,
            'optionType'          => $this->getOptionType(),
            'optionDeliveryArea'  => DeliveryAreaService::getActiveDeliveryArea(),
        ]);
    }

    public function edit(Request $request, $id)
    {
         if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterDeliveryAreaMoneyTrip::where('delivery_area_money_trip_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        if (!$this->checkAccessBranch($model)) {
            abort(403);
        }

        return view('operational::master.master-delivery-area-money-trip.add', [
            'model'              => $model,
            'title'              => trans('shared/common.edit'),
            'url'                => self::URL,
            'optionType'         => $this->getOptionType(),
            'optionDeliveryArea' => DeliveryAreaService::getActiveDeliveryArea(),
        ]);
    }

    public function save(Request $request)
    {
        $id    = intval($request->get('id'));
        $model = !empty($id) ? MasterDeliveryAreaMoneyTrip::find($id) : new MasterDeliveryAreaMoneyTrip();

        $this->validate($request, [
            'deliveryAreaId'  => 'required',
            'type'            => 'required',
            'moneyTrip'       => 'required|max:55',
        ]);

        if (!$this->uniqueAreaType($id, $request->get('type'), $request->get('deliveryAreaId'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Area and this vehicle type on exist!']);
        }

        $status='Y';
        if ($request->get('status')!="Y") {
            $status="N";
        }

        $opr = empty($model->money_trip_id) ? 'I' : 'U';
        $model->delivery_area_id = $request->get('deliveryAreaId');
        $model->money_trip_rate  = intval(str_replace(',', '', $request->get('moneyTrip')));
        $model->description      = $request->get('description');
        $model->vehicle_type     = $request->get('type');
        $model->branch_id        = $request->session()->get('currentBranch')->branch_id;
        $model->active           = $status;
        $now                     = new \DateTime();

        if ($opr == 'I') {
            $model->created_date  = $now;
            $model->created_by    = \Auth::user()->id;
        }else{
            $model->last_updated_date = $now;
            $model->last_updated_by   = \Auth::user()->id;
        }

        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.delivery-area-money-trip') .' ' . $request->get('routeCode')])
        );

        return redirect(self::URL);
    }

    public function printExcelIndex(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters)->get();

        \Excel::create(trans('operational/menu.delivery-area-money-trip').' '.\Session::get('currentBranch')->branch_name, function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.delivery-area-money-trip'));
                });

                $sheet->cells('A3:F3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('operational/fields.delivery-area'),
                    trans('operational/fields.vehicle-type'),
                    trans('operational/fields.money-trip'),
                    trans('shared/common.description'),
                    trans('shared/common.active'),
                ]);
                foreach($query as $index => $model) {
                    $data = [
                        $index + 1,
                        $model->delivery_area_name,
                        $model->type_vehicle,
                        $model->money_trip_rate,
                        $model->description,
                        $model->active == 'Y' ? 'v' : 'x',
                    ];
                    $sheet->row($index + 4, $data);
                }

                $currentRow = count($query) + 5;
                if (!empty($filters['deliveryArea'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.kota-asal'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['deliveryArea'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['status'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.status'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['status'], 'C', $currentRow);
                    $currentRow++;
                }

                $currentRow = count($query) + 5;
                $this->addLabelDescriptionCell($sheet, trans('shared/common.date'), 'E', $currentRow);
                $this->addValueDescriptionCell($sheet, $this->now->format('d-m-Y'), 'F', $currentRow);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.user'), 'E', $currentRow + 1);
                $this->addValueDescriptionCell($sheet, \Auth::user()->full_name, 'F', $currentRow + 1);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.branch'), 'E', $currentRow + 2);
                $this->addValueDescriptionCell($sheet, \Session::get('currentBranch')->branch_name, 'F', $currentRow + 2);
            });

        })->export('xlsx');
    }

    protected function addLabelDescriptionCell($sheet, $value, $column, $row)
    {
        $sheet->cell($column.$row, function($cell) use($value) {
            $cell->setFont(['bold' => true]);
            $cell->setValue($value);
        });
    }

    protected function addValueDescriptionCell($sheet, $value, $column, $row)
    {
        $sheet->cell($column.$row, function($cell) use($value) {
            $cell->setValue($value);
        });
    }

    function getOptionType(){
        return \DB::table('adm.v_mst_lookup_values')
                ->where('lookup_type', 'TIPE_KENDARAAN')
                ->get();
    }

    protected function uniqueAreaType($id, $vehicleType, $deliveryAreaId){
        $unique = true;
        $model = \DB::table('op.mst_delivery_area_money_trip')
                        ->where('vehicle_type', '=', $vehicleType)
                        ->where('delivery_area_id', '=', $deliveryAreaId)
                        ->where('delivery_area_money_trip_id', '<>', $id)
                        ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->first();
        if (!empty($model)) {
            $unique = false;
        }
        return $unique;
    }

    protected function checkAccessBranch(MasterDeliveryAreaMoneyTrip $model)
    {
        $deliveryArea = $model->deliveryArea;
        if ($deliveryArea === null) {
            return false;
        }

        $canAccessBranch = false;
        foreach ($deliveryArea->deliveryAreaBranch as $branch) {
            if ($branch !== null && \Auth::user()->can('accessBranch', $branch->branch_id)) {
                $canAccessBranch = true;
            }
        }

        return $canAccessBranch;
    }
}
