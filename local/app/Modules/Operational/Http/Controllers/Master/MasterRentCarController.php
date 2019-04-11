<?php

namespace App\Modules\Operational\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Master\MasterRentCar;
use App\Modules\Operational\Model\Master\MasterCity;
use App\Modules\Operational\Service\Master\CityService;
use App\Modules\Operational\Service\Master\RouteService;
use App\Modules\Operational\Service\Master\TruckService;

class MasterRentCarController extends Controller
{
    const RESOURCE = 'Operational\Master\MasterRentCar';
    const URL = 'operational/master/master-rent-car';
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

        return view('operational::master.master-rent-car.index', [
            'models'     => $query->paginate(10),
            'filters'    => $filters,
            'optionCity' => CityService::getActiveCity(),
            'resource'   => self::RESOURCE,
            'url'        => self::URL
            ]);
    }

    protected function getQuery(Request $request, $filters){
        $query   = \DB::table('op.v_mst_rent_car')
                    ->select('v_mst_rent_car.*')
                    ->leftJoin('op.dt_route_branch', 'v_mst_rent_car.route_id', '=', 'dt_route_branch.route_id')
                    ->where('dt_route_branch.branch_id', '=', $request->session()->get('currentBranch')->branch_id)
                    ->where('dt_route_branch.active', '=', 'Y')
                    ->distinct();

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('v_mst_rent_car.active', '=', 'Y');
        } else {
            $query->where('v_mst_rent_car.active', '=', 'N');
        }

        if (!empty($filters['description'])) {
            $query->where('descriptions', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['startCity'])) {
            $query->where('city_start_name', 'ilike', '%'.$filters['startCity'].'%');
        }

        if (!empty($filters['endCity'])) {
            $query->where('city_end_name', 'ilike', '%'.$filters['endCity'].'%');
        }
        return $query;
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model         = new MasterRentCar();
        $model->active = 'Y';

        return view('operational::master.master-rent-car.add', [
            'title'         => trans('shared/common.add'),
            'startCity'     => $model,
            'endCity'       => $model,
            'routeCode'     => '',
            'modelTruck'    => $model,
            'model'         => $model,
            'url'           => self::URL,
            'resource'      => self::RESOURCE,
            'optionRoute'   => RouteService::getActiveRoute(),
            'optionTruck'   => TruckService::getActiveRentTruck(),
        ]);
    }

    public function edit(Request $request, $id)
    {
         if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }
    
        $model = MasterRentCar::where('rent_car_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }
    
        if (!$this->checkAccessBranch($model)) {
            abort(403);
        }

    $startCity  = $model->startCity()->first();
        $endCity    = $model->endCity()->first();
        $routeCode  = $model->routeCode();
        $modelTruck = $model->truck()->first();

        return view('operational::master.master-rent-car.add', [
            'title'       => trans('shared/common.edit'),
            'model'       => $model,
            'startCity'   => $startCity,
            'endCity'     => $endCity,
            'routeCode'   => $routeCode,
            'modelTruck'  => $modelTruck,
            'resource'    => self::RESOURCE,
            'url'         => self::URL,
            'optionRoute' => RouteService::getActiveRoute(),
            'optionTruck' => TruckService::getActiveRentTruck(),
        ]);
    }

    public function save(Request $request){
        $id    = intval($request->get('id'));
        $model = !empty($id) ? MasterRentCar::where('rent_car_id', '=', $id)->first() : new MasterRentCar();

        $this->validate($request, [
            'routeCode'  => 'required|max:55',
            'truckCode'  => 'required|max:55',
            'rentRate'  => 'required|max:55',
        ]);

        $status='Y';
        if ($request->get('status')!="Y") {
            $status="N";
        }

        $opr = empty($model->no_car_rent_route ) ? 'I' : 'U';

        $model->truck_id         = $request->get('truckId');
        $model->route_id         = $request->get('routeId');
        $model->rent_rate        = str_replace(',', '', $request->get('rentRate'));
        $model->description      = $request->get('keterangan');
        $model->active           = $status;
        $model->branch_id_insert = $request->session()->get('currentBranch')->branch_id;
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
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.rent-car').' '. $request->get('routeCode').' - '. $request->get('truckCode') ] )
        );

        return redirect(self::URL);
    }

    public function printExcelIndex(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters)->get();

        \Excel::create(trans('operational/menu.rent-car').' '.\Session::get('currentBranch')->branch_name, function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.rent-car'));
                });

                $sheet->cells('A3:H3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('operational/fields.truck-car'),
                    trans('operational/fields.kota-asal'),
                    trans('operational/fields.kota-tujuan'),
                    trans('operational/fields.uang-jalan'),
                    trans('shared/common.keterangan'),
                    trans('shared/common.description'),
                    trans('shared/common.active'),
                ]);
                foreach($query as $index => $model) {
                    $data = [
                        $index + 1,
                        $model->truck_code,
                        $model->city_start_name,
                        $model->city_end_name,
                        $model->rent_rate,
                        $model->descriptions,
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
                if (!empty($filters['description'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.description'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['description'], 'C', $currentRow);
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

    protected function checkAccessBranch(MasterRentCar $model)
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
