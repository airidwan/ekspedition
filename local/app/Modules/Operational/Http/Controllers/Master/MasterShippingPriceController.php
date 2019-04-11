<?php

namespace App\Modules\Operational\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Master\MasterShippingPrice;
use App\Modules\Operational\Model\Master\DetailShippingPrice;
use App\Modules\Operational\Model\Master\MasterRoute;
use App\Modules\Operational\Model\Master\MasterCity;
use App\Modules\Operational\Service\Master\CityService;
use App\Modules\Operational\Service\Master\RouteService;

class MasterShippingPriceController extends Controller
{
    const RESOURCE = 'Operational\Master\MasterShippingPrice';
    const URL = 'operational/master/master-shipping-price';
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
        
        return view('operational::master.master-shipping-price.index', [
            'datas' => $query->paginate(10),
            'filters' => $filters,
            'resource' => self::RESOURCE,
            'url' => self::URL,
            'optionKota' => CityService::getActiveCity(),
        ]);
    }

    protected function getQuery(Request $request, $filters){
        $query = \DB::table('op.mst_shipping_price')
                        ->select('mst_shipping_price.*', 'mst_commodity.commodity_name', 'mst_route.route_code', 'mst_route.city_start_id', 'mst_route.city_end_id')
                        ->join('op.mst_route', 'mst_shipping_price.route_id', '=', 'mst_route.route_id')
                        ->join('op.mst_commodity', 'mst_shipping_price.commodity_id', '=', 'mst_commodity.commodity_id')
                        ->leftJoin('op.dt_route_branch', 'mst_shipping_price.route_id', '=', 'dt_route_branch.route_id')
                        ->where('dt_route_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->distinct()
                        ->orderBy('commodity_name', 'asc')
                        ->orderBy('route_code', 'asc');

         if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('mst_shipping_price.active', '=', 'Y');
        } else {
            $query->where('mst_shipping_price.active', '=', 'N');
        }

        if (!empty($filters['nama'])) {
            $query->where('commodity_name', 'ilike', '%'.$filters['nama'].'%');
        }

        if (!empty($filters['kotaAsal'])) {
            $query->where('city_start_id', '=', $filters['kotaAsal']);
        }

        if (!empty($filters['kotaTujuan'])) {
            $query->where('city_end_id', '=', $filters['kotaTujuan']);
        }
        return $query;
    }

    public function add(Request $request)
    {
         if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new MasterShippingPrice();
        $model->active = 'Y';

        return view('operational::master.master-shipping-price.add', [
            'title' => trans('shared/common.add'),
            'model' => $model,
            'url' => self::URL,
            'optionRoute' => RouteService::getActiveRoute(),
            'optionCommodity' => $this->getOptionCommodity(),
        ]);
    }

    public function edit(Request $request, $id)
    {
         if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterShippingPrice::find($id);
        if ($model === null) {
            abort(404);
        }

         if (!$this->checkAccessBranch($model)) {
            abort(403);
        }

        return view('operational::master.master-shipping-price.add', [
            'title' => trans('shared/common.edit'),
            'model' => $model,
            'url' => self::URL,
            'optionRoute' => RouteService::getActiveRoute(),
            'optionCommodity' => $this->getOptionCommodity(),
        ]);
    }

    public function save(Request $request)
    {
        $id    = intval($request->get('id'));
        $model = !empty($id) ? MasterShippingPrice::find($id) : new MasterShippingPrice();

        $this->validate($request, [
            'commodityId'  => 'required',
            'kodeRute'  => 'required|max:55',
            'tarifKirim'  => 'required',
        ]);

        $model->commodity_id = $request->get('commodityId');

        if (empty($model->shipping_price_id)) {
            $model->route_id = $request->get('idRute');
        }

        $model->delivery_rate = intval(str_replace(',', '', $request->get('tarifKirim')));
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

        if (!$this->checkNamaBarangAndRuteUnique($model)) {
            return redirect(\URL::previous())
                    ->withInput($request->all())
                    ->withErrors(['errorMessage' => trans('operational/fields.nama-barang-and-rute-unique-message')]);
        }

        $route = MasterRoute::find($model->route_id);
        $totalDeliveryRateLine = 0;

        foreach ($route->details()->get() as $routeDetail) {
            $deliveryRateLine = intval(str_replace(',', '', $request->get('tarifDetail-' . $routeDetail->dt_route_id)));
            if (empty($deliveryRateLine)) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Shipment Price Lines is Required']);
            }

            $totalDeliveryRateLine += $deliveryRateLine;
        }

        if ($model->delivery_rate != $totalDeliveryRateLine) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Shipment Price Header and Lines have to be equal']);
        }

        $model->save();

        $model->details()->delete();
        $route = MasterRoute::find($model->route_id);
        foreach ($route->details()->get() as $routeDetail) {
            $detailShippingPrice = new DetailShippingPrice();
            $detailShippingPrice->shipping_price_id = $model->shipping_price_id;
            $detailShippingPrice->dt_route_id = $routeDetail->dt_route_id;
            $detailShippingPrice->delivery_rate = intval(str_replace(',', '', $request->get('tarifDetail-' . $routeDetail->dt_route_id)));

            $now = new \DateTime();
            if (empty($id)) {
                $detailShippingPrice->created_date = $now;
                $detailShippingPrice->created_by = \Auth::user()->id;
            } else {
                $detailShippingPrice->last_updated_date = $now;
                $detailShippingPrice->last_updated_by = \Auth::user()->id;
            }

            $detailShippingPrice->save();
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.shipping-price')])
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

        \Excel::create(trans('operational/menu.shipping-price').' '.\Session::get('currentBranch')->branch_name, function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.shipping-price'));
                });

                $sheet->cells('A3:F3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('operational/fields.nama-barang'),
                    trans('operational/fields.route-code'),
                    trans('operational/fields.tarif-kirim'),
                    trans('shared/common.description'),
                    trans('shared/common.active'),
                ]);
                foreach($query as $index => $model) {
                    $modelShipping = MasterShippingPrice::find($model->shipping_price_id);

                    $data = [
                        $index + 1,
                        $modelShipping->commodity !== null ? $modelShipping->commodity->commodity_name : '',
                        $modelShipping->route !== null ? $modelShipping->route->route_code : '',
                        $model->delivery_rate,
                        $model->description,
                        $model->active == 'Y' ? 'v' : 'x',
                    ];
                    $sheet->row($index + 4, $data);
                }

                $currentRow = count($query) + 5;
                if (!empty($filters['kotaAsal'])) {
                    $modelCity = MasterCity::find($filters['kotaAsal']);
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.kota-asal'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  !empty($modelCity) ? $modelCity->city_name : '', 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['kotaTujuan'])) {
                    $modelCity = MasterCity::find($filters['kotaTujuan']);
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.kota-tujuan'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  !empty($modelCity) ? $modelCity->city_name : '', 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['nama'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.nama-barang'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['nama'], 'C', $currentRow);
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

    protected function checkNamaBarangAndRuteUnique(MasterShippingPrice $model)
    {
        $check = \DB::table('op.mst_shipping_price')
                        ->where('commodity_id', '=', $model->commodity_id)
                        ->where('route_id', '=', $model->route_id)
                        ->where('shipping_price_id', '<>', $model->shipping_price_id)
                        ->first();

        return $check === null;
    }

    protected function checkAccessBranch(MasterShippingPrice $model)
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

    public function getOptionCommodity(){
        $query = \DB::table('op.mst_commodity')
                        ->where('active', '=', 'Y')
                        ->orderBy('commodity_name', 'asc');

        return $query->get();
    }
}
