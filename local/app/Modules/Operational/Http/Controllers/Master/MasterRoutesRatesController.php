<?php

namespace App\Modules\Operational\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Master\MasterRoute;
use App\Modules\Operational\Model\Master\DetailRoute;
use App\Modules\Operational\Model\Master\DetailRouteBranch;
use App\Modules\Operational\Model\Master\MasterCity;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;

class MasterRoutesRatesController extends Controller
{
    const RESOURCE = 'Operational\Master\MasterRoutesRates';
    const URL = 'operational/master/master-routes-rates';

    public function __construct()
    {
        $this->middleware('auth');
        $this->now = new \DateTime;
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
        $query   = $this->getQuery($request);

        return view('operational::master.master-routes-rates.index', [
            'routes'    => $query->paginate(10),
            'filters'    => $filters,
            'optionKota' => \DB::table('op.mst_city')->where('active', '=', 'Y')->get(),
            'resource'   => self::RESOURCE,
            'url'        => self::URL
        ]);
    }

    protected function getQuery(Request $request)
    {
        $filters = $request->session()->get('filters');
        $query   = \DB::table('op.mst_route')
                        ->select('mst_route.*', 'city_start.city_name as city_start_name', 'city_end.city_name as city_end_name')
                        ->leftJoin('op.mst_city as city_start', 'city_start.city_id', '=', 'mst_route.city_start_id')
                        ->leftJoin('op.mst_city as city_end', 'city_end.city_id', '=', 'mst_route.city_end_id')
                        ->leftJoin('op.dt_route_branch', 'mst_route.route_id', '=', 'dt_route_branch.route_id')
                        ->where('dt_route_branch.branch_id', '=', $request->session()->get('currentBranch')->branch_id)
                        ->where('dt_route_branch.active', '=', 'Y')
                        ->orderBy('mst_route.created_date', 'desc')
                        ->distinct();

         if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('mst_route.active', '=', 'Y');
        } else {
            $query->where('mst_route.active', '=', 'N');
        }

        if (!empty($filters['kode'])) {
            $query->where('route_code', 'ilike', '%'.$filters['kode'].'%');
        }

        if (!empty($filters['kotaAsal'])) {
            $query->where('city_start.city_name', 'ilike', '%'.$filters['kotaAsal'].'%');
        }

        if (!empty($filters['kotaTujuan'])) {
            $query->where('city_end.city_name', 'ilike', '%'.$filters['kotaTujuan'].'%');
        }

        return $query;
    }

    public function printExcel(Request $request)
    {
        $query   = $this->getQuery($request);
        $filters = \Session::get('filters');

        \Excel::create(trans('operational/menu.routes-rates'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.routes-rates'));
                });

                $sheet->cells('A3:I3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.kode'),
                    trans('operational/fields.kota-asal'),
                    trans('operational/fields.kota-tujuan'),
                    trans('operational/fields.min-berat'),
                    trans('operational/fields.min-price'),
                    trans('operational/fields.tarif-kg'),
                    trans('operational/fields.tarif-m3'),
                    trans('operational/fields.delivery-estimation'),
                    trans('shared/common.keterangan'),
                ]);

                $currentRow = 4;
                foreach($query->get() as $route) {
                    $data = [
                        $route->route_code,
                        $route->city_start_name,
                        $route->city_end_name,
                        number_format($route->minimum_weight, 2),
                        number_format($route->minimum_rates),
                        number_format($route->rate_kg),
                        number_format($route->rate_m3),
                        $route->delivery_estimation,
                        $route->description,
                    ];

                    $sheet->row($currentRow++, $data);
                }

                $lastDataRow = $currentRow;
                $currentRow = $lastDataRow + 1;
                if (!empty($filters['kotaAsal'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.kota-asal'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['kotaAsal'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['kotaTujuan'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.kota-tujuan'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['kotaTujuan'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['kode'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.kode'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['kode'], 'C', $currentRow);
                    $currentRow++;
                }

                $currentRow = $lastDataRow + 1;
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

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model         = new MasterRoute();
        $modelDetail   = new DetailRoute();
        $model->active = 'Y';

        return view('operational::master.master-routes-rates.add', [
            'title'        => trans('shared/common.add'),
            'model'        => $model,
            'modelDetail'  => $modelDetail,
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
            'optionKota'   => \DB::table('op.mst_city')->where('active', '=', 'Y')->get(),
            'optionCabang' => \DB::table('op.mst_branch')
                                    ->where('active','=','Y')
                                    ->where('city_id', '=', \Session::get('currentBranch')->city_id)
                                    ->orderBy('branch_name')
                                    ->get(),
            'currentBranchCity' => MasterCity::find(\Session::get('currentBranch')->city_id),
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterRoute::find($id);
        if ($model === null) {
            abort(404);
        }

        if (!$this->checkAccessBranch($model)) {
            abort(403);
        }

        $modelDetail = DetailRoute::where('route_id', '=', $id)->first();
        if ($modelDetail === null) {
            $modelDetail = new DetailRoute();
        }

        return view('operational::master.master-routes-rates.add', [
            'title'        => trans('shared/common.edit'),
            'model'        => $model,
            'modelDetail'  => $modelDetail,
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
            'optionKota'   => \DB::table('op.mst_city')->where('active', '=', 'Y')->get(),
            'optionCabang' => \DB::table('op.mst_branch')
                                    ->where('active','=','Y')
                                    ->where('city_id', '=', \Session::get('currentBranch')->city_id)
                                    ->orderBy('branch_name')
                                    ->get(),
            'currentBranchCity' => MasterCity::find(\Session::get('currentBranch')->city_id),
        ]);
    }

    public function save(Request $request)
    {
        $id    = intval($request->get('id'));
        $model = !empty($id) ? MasterRoute::find($id) : new MasterRoute();

        $this->validate($request, [
            'kotaTujuan' => 'required',
        ]);

        if (empty($request->get('detailCabang'))) {
            return redirect(\URL::previous())
                        ->withInput($request->all())
                        ->withErrors(['errorMessage' => trans('shared/common.required-message', ['variable' => trans('shared/common.branch-activation')])]);
        }

        if (empty($model->city_start_id)) {
            $model->city_start_id = \Session::get('currentBranch')->city_id;
        }

        if (empty($model->city_end_id)) {
            $model->city_end_id = $request->get('kotaTujuan');
        }

        $route = MasterRoute::where('city_start_id', '=', $model->city_start_id)
                                ->where('city_end_id', '=', $model->city_end_id)
                                ->where('route_id', '<>', $model->route_id)
                                ->first();

        if ($route !== null) {
            return redirect(\URL::previous())
                        ->withInput($request->all())
                        ->withErrors(['errorMessage' => trans('operational/fields.star-end-city-registered-message')]);
        }

        $model->route_code = $this->getRouteCode($model);
        $model->minimum_weight = floor(str_replace(',', '', $request->get('minBeratKirim')) * TransactionResiHeader::PEMBULATAN) / TransactionResiHeader::PEMBULATAN;
        $model->rate_kg = intval(str_replace(',', '', $request->get('perKg')));
        $model->rate_m3 = intval(str_replace(',', '', $request->get('perM3')));
        $model->minimum_rates = floor($model->minimum_weight * $model->rate_kg / TransactionResiHeader::PEMBULATAN) * TransactionResiHeader::PEMBULATAN;
        $model->delivery_estimation = $request->get('estimation');
        $model->description = $request->get('keterangan');
        $model->branch_id_insert = \Session::get('currentBranch')->branch_id;
        $model->active = !empty($request->get('status')) ? 'Y' : 'N';

        $now = new \DateTime();
        if (empty($id)) {
            $model->created_date = $now;
            $model->created_by = \Auth::user()->id;
        } else {
            $model->last_updated_date = $now;
            $model->last_updated_by = \Auth::user()->id;
        }

        $model->save();

        $model->routeBranch()->delete();
        foreach ($request->get('detailCabang', []) as $detailCabangId) {
            $routeBranch = new DetailRouteBranch();
            $routeBranch->route_id = $model->route_id;
            $routeBranch->branch_id = $detailCabangId;
            $routeBranch->active = 'Y';

            if (empty($id)) {
                $routeBranch->created_date = $now;
                $routeBranch->created_by = \Auth::user()->id;
            } else {
                $routeBranch->last_updated_date = $now;
                $routeBranch->last_updated_by = \Auth::user()->id;
            }

            $routeBranch->save();
        }

        $idDetail = $request->get('idDetail', []);
        foreach ($model->details()->get() as $detailRoute) {
            $index = array_search($detailRoute->dt_route_id, $idDetail);
            if ($index !== false) {
                $detailRoute->city_start_id = $request->get('kotaAsalDetail')[$index];
                $detailRoute->city_end_id = $request->get('kotaTujuanDetail')[$index];
                $detailRoute->rate_kg = intval(str_replace(',', '', $request->get('perKgDetail')[$index]));
                $detailRoute->rate_m3 = intval(str_replace(',', '', $request->get('perM3Detail')[$index]));
                $detailRoute->description = $request->get('keteranganDetail'[$index]);
                $detailRoute->active = 'Y';

                if (empty($id)) {
                    $detailRoute->created_date = $now;
                    $detailRoute->created_by = \Auth::user()->id;
                } else {
                    $detailRoute->last_updated_date = $now;
                    $detailRoute->last_updated_by = \Auth::user()->id;
                }

                $cekDetailRoute = DetailRoute::where('city_start_id', '=', $detailRoute->city_start_id)
                                                ->where('city_end_id', '=', $detailRoute->city_end_id)
                                                ->where('route_id', '=', $detailRoute->route_id)
                                                ->where('dt_route_id', '<>', $detailRoute->dt_route_id)
                                                ->first();

                if ($cekDetailRoute !== null) {
                    return redirect(self::URL . '/edit/' . $model->route_id)
                                ->withInput($request->all())
                                ->withErrors(['errorMessage' => trans('operational/fields.star-end-city-detail-registered-message')]);
                }

                $detailRoute->save();
            } else {
                $detailRoute->delete();
            }
        }

        $kotaAsalDetail= $request->get('kotaAsalDetail');
        for($index = 0; $index < count($kotaAsalDetail); $index++) {
            if (empty($idDetail[$index])) {
                $detailRoute = new DetailRoute();
                $detailRoute->route_id = $model->route_id;
                $detailRoute->city_start_id = $request->get('kotaAsalDetail')[$index];
                $detailRoute->city_end_id = $request->get('kotaTujuanDetail')[$index];
                $detailRoute->rate_kg = intval(str_replace(',', '', $request->get('perKgDetail')[$index]));
                $detailRoute->rate_m3 = intval(str_replace(',', '', $request->get('perM3Detail')[$index]));
                $detailRoute->description = $request->get('keteranganDetail'[$index]);
                $detailRoute->active = 'Y';

                if (empty($id)) {
                    $detailRoute->created_date = $now;
                    $detailRoute->created_by = \Auth::user()->id;
                } else {
                    $detailRoute->last_updated_date = $now;
                    $detailRoute->last_updated_by = \Auth::user()->id;
                }

                $cekDetailRoute = DetailRoute::where('city_start_id', '=', $detailRoute->city_start_id)
                                                ->where('city_end_id', '=', $detailRoute->city_end_id)
                                                ->where('route_id', '=', $detailRoute->route_id)
                                                ->where('dt_route_id', '<>', $detailRoute->dt_route_id)
                                                ->first();

                if ($cekDetailRoute !== null) {
                    return redirect(self::URL . '/edit/' . $model->route_id)
                                ->withInput($request->all())
                                ->withErrors(['errorMessage' => trans('operational/fields.star-end-city-detail-registered-message')]);
                }

                $detailRoute->save();
            }
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.routes-rates').' '.$model->route_code])
        );

        return redirect(self::URL);
    }

    protected function getRouteCode(MasterRoute $model)
    {
        $cityStart = MasterCity::find($model->city_start_id);
        $cityEnd = MasterCity::find($model->city_end_id);

        return $cityStart->city_code . '-' . $cityEnd->city_code;
    }

    protected function checkAccessBranch(MasterRoute $model)
    {
        $canAccessBranch = false;
        foreach ($model->routeBranch as $routeBranch) {
            $branch = $routeBranch->branch;
            if ($branch !== null && \Auth::user()->can('accessBranch', $branch->branch_id)) {
                $canAccessBranch = true;
            }
        }

        return $canAccessBranch;
    }
}
