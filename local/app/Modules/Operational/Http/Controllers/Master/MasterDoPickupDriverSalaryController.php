<?php

namespace App\Modules\Operational\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Master\MasterDoPickupDriverSalary;
use App\Modules\Operational\Model\Master\MasterDeliveryArea;
use App\Modules\Operational\Service\Master\DeliveryAreaService;

class MasterDoPickupDriverSalaryController extends Controller
{
    const RESOURCE = 'Operational\Master\MasterDoPickupDriverSalary';
    const URL      = 'operational/master/master-do-pickup-driver-salary';
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

        return view('operational::master.master-do-pickup-driver-salary.index', [
            'models'                => $query->paginate(10),
            'filters'               => $filters,
            'optionDeliveryArea'    => DeliveryAreaService::getActiveDeliveryArea(),
            'optionPosition'        => $this->getOptionPosition(),
            'optionType'            => $this->getOptionType(),
            'resource'              => self::RESOURCE,
            'url'                   => self::URL
            ]);  
    }

     public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model         = new MasterDoPickupDriverSalary();
        $model->active = 'Y';

        return view('operational::master.master-do-pickup-driver-salary.add', [
            'title'              => trans('shared/common.add'),
            'routeCode'          => '',
            'optionPosition'     => $this->getOptionPosition(),
            'optionType'         => $this->getOptionType(),
            'model'              => $model,
            'url'                => self::URL,
            'optionDeliveryArea' => DeliveryAreaService::getActiveDeliveryArea(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterDoPickupDriverSalary::where('do_pickup_driver_salary_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        if ($model->branch_id != \Session::get('currentBranch')->branch_id) {
            abort(403);
        }

        return view('operational::master.master-do-pickup-driver-salary.add', [
            'title'              => trans('shared/common.edit'),
            'model'              => $model,
            'optionPosition'     => $this->getOptionPosition(),
            'optionType'         => $this->getOptionType(),
            'url'                => self::URL,
            'optionDeliveryArea' => DeliveryAreaService::getActiveDeliveryArea(),
        ]); 
    }

    public function save(Request $request)
    {
        $id    = intval($request->get('id'));
        $model = !empty($id) ? MasterDoPickupDriverSalary::where('do_pickup_driver_salary_id', '=', $id)->first() : new MasterDoPickupDriverSalary();

        $this->validate($request, [
            'deliveryArea'    => 'required',
            'salary'          => 'required',
            'position'        => 'required',
            'type'            => 'required',
        ]);

        $opr = empty($model->do_pickup_driver_salary_id) ? 'I' : 'U';
        if($this->checkExist($request, $id)){
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Driver position with this vehicle type already exist!']);
        }

        $status='Y';
        if ($request->get('status')!="Y") {
            $status="N";
        }

        $model->delivery_area_id = $request->get('deliveryArea');
        $model->driver_position  = $request->get('position');
        $model->vehicle_type     = $request->get('type');
        $model->salary           = str_replace(',', '', $request->get('salary'));
        $model->description      = $request->get('description');
        $model->active           = $status;
        $model->branch_id        = \Session::get('currentBranch')->branch_id;
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
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.do-pickup-driver-salary') .' in position '. $request->get('position')])
        );

        return redirect(self::URL);
    }

    protected function getQuery(Request $request, $filters){
        $query   = \DB::table('op.mst_do_pickup_driver_salary')
                    ->select('mst_do_pickup_driver_salary.*', 'position.meaning as position_driver', 'type.meaning as type_vehicle', 'mst_delivery_area.delivery_area_name')
                    ->leftJoin('op.mst_delivery_area', 'mst_delivery_area.delivery_area_id', '=', 'mst_do_pickup_driver_salary.delivery_area_id')
                    ->leftJoin('adm.mst_lookup_values as position', 'position.lookup_code', '=', 'mst_do_pickup_driver_salary.driver_position')
                    ->leftJoin('adm.mst_lookup_values as type', 'type.lookup_code', '=', 'mst_do_pickup_driver_salary.vehicle_type')
                    ->where('mst_do_pickup_driver_salary.branch_id', '=', $request->session()->get('currentBranch')->branch_id)
                    ->where('mst_do_pickup_driver_salary.active', '=', 'Y')
                    ->distinct();

        if (!empty($filters['deliveryArea'])) {
            $query->where('mst_do_pickup_driver_salary.delivery_area_id', '=', $filters['deliveryArea']);
        }

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('mst_do_pickup_driver_salary.active', '=', 'Y');
        } else {
            $query->where('mst_do_pickup_driver_salary.active', '=', 'N');
        }

        if (!empty($filters['position'])) {
            $query->where('mst_do_pickup_driver_salary.driver_position', 'ilike', '%'.$filters['position'].'%');
        }

        if (!empty($filters['type'])) {
            $query->where('mst_do_pickup_driver_salary.vehicle_type', 'ilike', '%'.$filters['type'].'%');
        }

        return $query;
    }

    public function printExcelIndex(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters)->get();

        \Excel::create(trans('operational/menu.do-pickup-driver-salary'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.do-pickup-driver-salary'));
                });

                $sheet->cells('A3:E3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('operational/fields.position'),
                    trans('operational/fields.vehicle-type'),
                    trans('operational/fields.delivery-area'),
                    trans('operational/fields.salary'),
                    trans('shared/common.description'),
                    trans('shared/common.active'),
                ]);
                foreach($query as $index => $model) {
                    $data = [
                        $index + 1,
                        $model->position_driver,
                        $model->type_vehicle,
                        $model->delivery_area_name,
                        $model->salary,
                        $model->description,
                        $model->active == 'Y' ? 'v' : 'x',
                    ];
                    $sheet->row($index + 4, $data);
                }

                $currentRow = count($query) + 5;
                if (!empty($filters['deliveryArea'])) {
                    $modelDelivery = MasterDeliveryArea::find($filters['deliveryArea']);
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.delivery-area'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  !empty($modelDelivery) ? $modelDelivery->delivery_area_name : '', 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['position'])) {
                    $modelPosition = \DB::table('adm.mst_lookup_values')->where('lookup_code', '=', $filters['position'])->first();
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.position'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  !empty($modelPosition) ? $modelPosition->meaning : '', 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['type'])) {
                    $modelType = \DB::table('adm.mst_lookup_values')->where('lookup_code', '=', $filters['type'])->first();
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.type'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  !empty($modelType) ? $modelType->meaning : '', 'C', $currentRow);
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

    function checkExist(Request $request, $id){
        $exist = \DB::table('op.mst_do_pickup_driver_salary')->where('branch_id', '=', \Session::get('currentBranch')->branch_id)->get();
        foreach ($exist as $data) {
            if ($data->driver_position == $request->get('position') && $data->vehicle_type == $request->get('type') && $data->do_pickup_driver_salary_id != $id) {
                return TRUE;
            }
        }
        return FALSE;
    }

    function getOptionPosition(){
        return \DB::table('adm.v_mst_lookup_values')
                ->where('lookup_type', 'DRIVER_CATEGORY')
                ->get();
    }

    function getOptionType(){
        return \DB::table('adm.v_mst_lookup_values')
                ->where('lookup_type', 'TIPE_KENDARAAN')
                ->get();
    }
}
