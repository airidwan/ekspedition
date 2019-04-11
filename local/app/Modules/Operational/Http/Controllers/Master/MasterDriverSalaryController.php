<?php

namespace App\Modules\Operational\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Master\MasterDriverSalary;
use App\Modules\Operational\Service\Master\CityService;
use App\Modules\Operational\Service\Master\RouteService;

class MasterDriverSalaryController extends Controller
{
    const RESOURCE = 'Operational\Master\MasterManifestDriverSalary';
    const URL      = 'operational/master/master-driver-salary';
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

        return view('operational::master.master-driver-salary.index', [
            'models'         => $query->paginate(10),
            'filters'        => $filters,
            'optionCity'     => CityService::getActiveCity(),
            'optionPosition' => $this->getOptionPosition(),
            'resource'       => self::RESOURCE,
            'url'            => self::URL
        ]);  
    }

    protected function getQuery(Request $request, $filters){
      
        $query   = \DB::table('op.mst_driver_salary')
                    ->select(
                        'mst_driver_salary.*',
                        'v_mst_route.city_start_name',
                        'v_mst_route.city_end_name',
                        'driver.meaning as position_driver',
                        'vehicle.meaning as type_vehicle'
                        )
                    ->join('op.v_mst_route', 'v_mst_route.route_id', '=', 'mst_driver_salary.route_id')
                    ->join('adm.v_mst_lookup_values as driver', function($join)
                         {
                             $join->on('driver.lookup_code', '=', 'mst_driver_salary.driver_position');
                             $join->on('driver.lookup_type', '=', \DB::raw("'DRIVER_CATEGORY'"));
                         })
                    ->join('adm.v_mst_lookup_values as vehicle', function($join)
                         {
                             $join->on('vehicle.lookup_code', '=', 'mst_driver_salary.vehicle_type');
                             $join->on('vehicle.lookup_type', '=', \DB::raw("'TIPE_KENDARAAN'"));
                         })
                    ->join('op.dt_route_branch', 'mst_driver_salary.route_id', '=', 'dt_route_branch.route_id')
                    ->where('dt_route_branch.branch_id', '=', $request->session()->get('currentBranch')->branch_id)
                    ->where('dt_route_branch.active', '=', 'Y')
                    ->orderBy('v_mst_route.city_start_name', 'v_mst_route.city_end_name')
                    ->distinct();

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('mst_driver_salary.active', '=', 'Y');
        } else {
            $query->where('mst_driver_salary.active', '=', 'N');
        }

        if (!empty($filters['startCity'])) {
            $query->where('city_start_name', 'ilike', '%'.$filters['startCity'].'%');
        }

        if (!empty($filters['endCity'])) {
            $query->where('city_end_name', 'ilike', '%'.$filters['endCity'].'%');
        }

        if (!empty($filters['position'])) {
            $query->where('driver_position', 'ilike', '%'.$filters['position'].'%');
        }
        return $query;
    }

     public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model         = new MasterDriverSalary();
        $model->active = 'Y';

        return view('operational::master.master-driver-salary.add', [
            'title'          => trans('shared/common.add'),
            'startCity'      => $model,
            'endCity'        => $model,
            'routeCode'      => '',
            'optionPosition' => $this->getOptionPosition(),
            'optionType'     => $this->getOptionType(),
            'model'          => $model,
            'url'            => self::URL,
            'optionRoute'    => RouteService::getActiveRoute(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterDriverSalary::where('driver_salary_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        if (!$this->checkAccessBranch($model)) {
            abort(403);
        }

        $startCity = $model->startCity()->first();
        $endCity   = $model->endCity()->first();
        $routeCode = $model->routeCode();

        return view('operational::master.master-driver-salary.add', [
            'title'          => trans('shared/common.edit'),
            'model'          => $model,
            'startCity'      => $startCity,
            'endCity'        => $endCity,
            'routeCode'      => $routeCode,
            'optionPosition' => $this->getOptionPosition(),
            'optionType'     => $this->getOptionType(),
            'url'            => self::URL,
            'optionRoute'    => RouteService::getActiveRoute(),
        ]); 
    }

    public function save(Request $request)
    {
        $id    = intval($request->get('id'));
        $model = !empty($id) ? MasterDriverSalary::where('driver_salary_id', '=', $id)->first() : new MasterDriverSalary();
        $this->validate($request, [
            'routeCode'   => 'required|max:55',
            'salary'      => 'required|max:55',
            'position'    => 'required',
            'type'        => 'required',
        ]);

        $otherDriverSalary = \DB::table('op.mst_driver_salary')
                                    ->where('route_id', '=', $request->get('routeId'))
                                    ->where('driver_position', '=', $request->get('position'))
                                    ->where('vehicle_type', '=', $request->get('type'))
                                    ->where('driver_salary_id', '<>', $id)
                                    ->first();

        if ($otherDriverSalary !== null) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Driver adn Assistant Salary for this is already exist']);
        }

        $status='Y';
        if ($request->get('status')!="Y") {
            $status="N";
        }

        $opr = empty($model->driver_salary_id) ? 'I' : 'U';
        $model->route_id         = $request->get('routeId');
        $model->driver_position  = $request->get('position');
        $model->vehicle_type     = $request->get('type');
        $model->salary           = str_replace(',', '', $request->get('salary'));
        $model->description      = $request->get('description');
        $model->active           = $status;
        $model->branch_id_insert = $request->get('routeId');
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
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.driver-salary') .' in position '. $request->get('position') .' (' . $request->get('startCity') .' - '. $request->get('endCity').')'])
        );

        return redirect(self::URL);
    }

    public function printExcelIndex(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters)->get();

        \Excel::create(trans('operational/menu.driver-salary').' '.\Session::get('currentBranch')->branch_name, function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.driver-salary'));
                });

                $sheet->cells('A3:H3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('operational/fields.position'),
                    trans('operational/fields.vehicle-type'),
                    trans('operational/fields.kota-asal'),
                    trans('operational/fields.kota-tujuan'),
                    trans('operational/fields.salary'),
                    trans('shared/common.description'),
                    trans('shared/common.active'),
                ]);
                foreach($query as $index => $model) {
                    $data = [
                        $index + 1,
                        $model->position_driver,
                        $model->type_vehicle,
                        $model->city_start_name,
                        $model->city_end_name,
                        $model->salary,
                        $model->description,
                        $model->active == 'Y' ? 'v' : 'x',
                    ];
                    $sheet->row($index + 4, $data);
                }

                $currentRow = count($query) + 5;
                if (!empty($filters['startCity'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.kota-asal'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['startCity'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['endCity'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.kota-tujuan'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['endCity'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['position'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.position'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['position'], 'C', $currentRow);
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

    protected function checkAccessBranch(MasterDriverSalary $model)
    {
        $route = $model->route;
        if ($route === null) {
            return false;
        }

        $canAccessBranch = false;
        foreach ($route->routeBranch as $routeBranch) {
            $branch = $routeBranch->branch;
            if ($branch !== null && \Auth::user()->can('accessBranch', $branch->branch_id)) {
                $canAccessBranch = true;
            }
        }

        return $canAccessBranch;
    }
}
