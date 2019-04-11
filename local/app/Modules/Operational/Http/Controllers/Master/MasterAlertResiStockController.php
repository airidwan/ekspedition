<?php

namespace App\Modules\Operational\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Master\MasterCity;
use App\Modules\Operational\Model\Master\MasterAlertResiStock;

class MasterAlertResiStockController extends Controller
{
    const RESOURCE = 'Operational\Master\MasterAlertResiStock';
    const URL      = 'operational/master/master-alert-resi-stock';
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

        return view('operational::master.master-alert-resi-stock.index', [
            'models'     => $query->paginate(10),
            'filters'    => $filters,
            'resource'   => self::RESOURCE,
            'url'        => self::URL,
            'optionProvince' => \DB::table('op.mst_province')->orderBy('province_name')->get(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $modelCity = MasterCity::where('city_id', '=', $id)->first();
        if ($modelCity === null) {
            abort(404);
        }

        $model = MasterAlertResiStock::where('city_end_id', '=', $id)->where('branch_id', '=', \Session::get('currentBranch')->branch_id)->first();

        return view('operational::master.master-alert-resi-stock.add', [
            'title' => trans('shared/common.edit'),
            'modelCity' => $modelCity,
            'model' => $model,
            'url' => self::URL,
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $modelCity = MasterCity::find($id);
        if ($modelCity === null) {
            abort(404);
        }

        $model = MasterAlertResiStock::where('city_end_id', '=', $id)->where('branch_id', '=', \Session::get('currentBranch')->branch_id)->first();
        if ($model === null) {
            $model = new MasterAlertResiStock();
            $model->city_end_id = $id;
            $model->branch_id = \Session::get('currentBranch')->branch_id;
        }

        $model->minimum_days = intval(str_replace(',', '', $request->get('minimumDays')));
        $model->description = $request->get('description');

        $now = new \DateTime();
        if (empty($model->alert_resi_stock_id)) {
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

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.alert-resi-stock').' '.$modelCity->city_name])
        );

        return redirect(self::URL);
    }

    protected function getQuery(Request $request, $filters){
        $query   = \DB::table('op.mst_city')
                        ->select('mst_city.*', 'mst_alert_resi_stock.minimum_days', 'mst_alert_resi_stock.description')
                        ->leftJoin('op.mst_alert_resi_stock', function($join){
                            $join->on('mst_alert_resi_stock.city_end_id', '=', 'mst_city.city_id');
                            $join->on('mst_alert_resi_stock.branch_id', '=', \DB::RAW(\Session::get('currentBranch')->branch_id));
                        })
                        ->join('op.mst_route', 'mst_route.city_end_id', '=', 'mst_city.city_id')
                        ->join('op.dt_route_branch', 'dt_route_branch.route_id', '=', 'mst_route.route_id')
                        ->where('mst_route.active', '=', 'Y')
                        ->where('dt_route_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->orderBy('mst_city.city_code', 'asc')
                        ->distinct();

        if (!empty($filters['code'])) {
            $query->where('city_code', 'ilike', '%'.$filters['code'].'%');
        }

        if (!empty($filters['city'])) {
            $query->where('city_name', 'ilike', '%'.$filters['city'].'%');
        }

        if (!empty($filters['province'])) {
            $query->where('province', 'ilike', '%'.$filters['province'].'%');
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

        \Excel::create(trans('operational/menu.alert-resi-stock'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.alert-resi-stock'));
                });

                $sheet->cells('A3:F3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('shared/common.code'),
                    trans('shared/common.city'),
                    trans('operational/fields.province'),
                    trans('operational/fields.maximum-days'),
                    trans('shared/common.description'),
                ]);
                foreach($query as $index => $model) {
                    $data = [
                        $index + 1,
                        $model->city_code,
                        $model->city_name,
                        $model->minimum_days,
                        $model->description,
                    ];
                    $sheet->row($index + 4, $data);
                }

                $currentRow = count($query) + 5;
                if (!empty($filters['code'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.code'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['code'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['city'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.city'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['city'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['province'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.province'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['province'], 'C', $currentRow);
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
}
