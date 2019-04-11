<?php

namespace App\Modules\Operational\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Master\MasterMoneyTrip;
use App\Modules\Operational\Model\Master\MasterCity;
use App\Modules\Operational\Service\Master\CityService;
use App\Modules\Operational\Service\Master\RouteService;

class MasterMoneyTripController extends Controller
{
    const RESOURCE = 'Operational\Master\MasterManifestMoneyTrip';
    const URL      = 'operational/master/master-money-trip';
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

        return view('operational::master.master-money-trip.index', [
            'models'     => $query->paginate(10),
            'filters'    => $filters,
            'optionCity' => CityService::getActiveCity(),
            'resource'   => self::RESOURCE,
            'url'        => self::URL
            ]);
    }

    public function getQuery(Request $request, $filters){
        $query   = \DB::table('op.v_mst_money_trip')
                    ->select('v_mst_money_trip.*')
                    ->leftJoin('op.dt_route_branch', 'v_mst_money_trip.route_id', '=', 'dt_route_branch.route_id')
                    ->where('dt_route_branch.branch_id', '=', $request->session()->get('currentBranch')->branch_id)
                    ->where('dt_route_branch.active', '=', 'Y')
                    ->distinct();

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('v_mst_money_trip.active', '=', 'Y');
        } else {
            $query->where('v_mst_money_trip.active', '=', 'N');
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

        $model         = new MasterMoneyTrip();
        $model->active = 'Y';

        return view('operational::master.master-money-trip.add', [
            'title'        => trans('shared/common.add'),
            'startCity'    => $model,
            'endCity'      => $model,
            'routeCode'    => '',
            'optionType'   => $this->getTypeVehicle(),
            'model'        => $model,
            'url'          => self::URL,
            'optionRoute'  => RouteService::getActiveRoute(),
        ]);
    }

    public function edit(Request $request, $id)
    {
         if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterMoneyTrip::where('money_trip_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        if (!$this->checkAccessBranch($model)) {
            abort(403);
        }

        $startCity = $model->startCity()->first();
        $endCity   = $model->endCity()->first();
        $routeCode = $model->routeCode();

        return view('operational::master.master-money-trip.add', [
            'title'       => trans('shared/common.edit'),
            'model'       => $model,
            'startCity'   => $startCity,
            'endCity'     => $endCity,
            'routeCode'   => $routeCode,
            'optionType'  => $this->getTypeVehicle(),
            'url'         => self::URL,
            'optionRoute' => RouteService::getActiveRoute(),
        ]);
    }

    public function save(Request $request)
    {
        $id    = intval($request->get('id'));
        $model = !empty($id) ? MasterMoneyTrip::find($id) : new MasterMoneyTrip();

        $this->validate($request, [
            'routeCode'  => 'required|max:55',
            'moneyTrip'  => 'required|max:55',
            'type'       => 'required',
        ]);

        $status='Y';
        if ($request->get('status')!="Y") {
            $status="N";
        }

        $opr = empty($model->money_trip_id) ? 'I' : 'U';
        $model->route_id         = $request->get('routeId');
        $model->money_trip_rate  = intval(str_replace(',', '', $request->get('moneyTrip')));
        $model->description      = $request->get('description');
        $model->vehicle_type     = $request->get('type');
        $model->branch_id_insert = $request->session()->get('currentBranch')->branch_id;
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
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.money-trip') .' ' . $request->get('routeCode')])
        );

        return redirect(self::URL);
    }

    public function printExcelIndex(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters)->get();

        \Excel::create(trans('operational/menu.money-trip').' '.\Session::get('currentBranch')->branch_name, function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.money-trip'));
                });

                $sheet->cells('A3:H3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('operational/fields.vehicle-type'),
                    trans('operational/fields.route-code'),
                    trans('operational/fields.kota-asal'),
                    trans('operational/fields.kota-tujuan'),
                    trans('operational/fields.money-trip'),
                    trans('shared/common.description'),
                    trans('shared/common.active'),
                ]);
                foreach($query as $index => $model) {
                    $data = [
                        $index + 1,
                        $model->vehicle_type_meaning,
                        $model->route_code,
                        $model->city_start_name,
                        $model->city_end_name,
                        $model->money_trip_rate,
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

    protected function checkAccessBranch(MasterMoneyTrip $model)
    {
        $rute = $model->rute;
        if ($rute === null) {
            return false;
        }

        $canAccessBranch = false;
        foreach ($rute->routeBranch as $routeBranch) {
            $branch = $routeBranch->branch;
            if ($branch !== null && \Auth::user()->can('accessBranch', $branch->branch_id)) {
                $canAccessBranch = true;
            }
        }

        return $canAccessBranch;
    }

    function getTypeVehicle(){
        return \DB::table('adm.v_mst_lookup_values')
                ->where('lookup_type', 'TIPE_KENDARAAN')
                ->get();
    }
}
