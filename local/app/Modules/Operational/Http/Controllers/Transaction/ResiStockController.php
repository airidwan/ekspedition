<?php

namespace App\Modules\Operational\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Transaction\ResiStock;
use App\Modules\Operational\Model\Transaction\OfficialReport;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;

class ResiStockController extends Controller
{
    const RESOURCE = 'Operational\Transaction\WarehouseDeliveryList';
    const URL      = 'operational/transaction/wdl';
    const URL_BA   = 'operational/transaction/official-report';

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
        $query = $this->getQuery($request, $filters);

        $arrayModel = [];
        foreach ($query->paginate(10) as $model) {
            $model->official_report = OfficialReport::where('resi_header_id', '=', $model->resi_header_id)->get();
            $arrayModel[]           = $model;
        }

        return view('operational::transaction.resi-stock.index', [
            'paginate'          => $query->paginate(10),
            'models'            => $arrayModel,
            'filters'           => $filters,
            'resource'          => self::RESOURCE,
            'url'               => self::URL,
            'urlBa'             => self::URL_BA,
            'optionArea'        => $this->getOptionArea(),
            'optionsWarehouse'  => $this->getOptionsWarehouse(),
            'optionsRoute'      => $this->getOptionsRoute(),
            'optionsRegion'     => $this->getOptionsRegion(),
        ]);
    }

    protected function getQuery(Request $request, $filters)
    {
        $query = \DB::table('op.mst_stock_resi')
                        ->select('mst_stock_resi.*', 'mst_stock_resi.is_ready_delivery', 'trans_resi_header.delivery_area_id')
                        ->join('op.trans_resi_header', 'mst_stock_resi.resi_header_id', '=', 'trans_resi_header.resi_header_id')
                        ->leftJoin('op.mst_route', 'trans_resi_header.route_id', '=', 'mst_route.route_id')
                        ->leftJoin('op.mst_city', 'mst_route.city_end_id', '=', 'mst_city.city_id')
                        ->leftJoin('op.dt_region_city', 'mst_city.city_id', '=', 'dt_region_city.city_id')
                        ->leftJoin('op.mst_region', 'dt_region_city.region_id', '=', 'mst_region.region_id')
                        ->orderBy('trans_resi_header.created_date', 'asc')
                        ->orderBy('trans_resi_header.resi_header_id', 'asc');

        if (!empty($filters['resiNumber'])) {
            $query->where('trans_resi_header.resi_number', 'ilike', '%'.$filters['resiNumber'].'%');
        }

        if (!empty($filters['warehouse'])) {
            $query->where('wh_id', '=', $filters['warehouse']);
        }

        if (!empty($filters['status'])) {
            $query->where('mst_stock_resi.is_ready_delivery', '=', $filters['status']);
        }

        if (!empty($filters['route'])) {
            $query->whereRaw('mst_route.route_id IN ('. implode(', ', $filters['route']) .')');
        }

        if (!empty($filters['region'])) {
            $query->whereNotNull('mst_region.region_id')->whereRaw('mst_region.region_id IN ('. implode(', ', $filters['region']) .')');
        }

        if (!empty($filters['deliveryArea'])) {
            $query->whereNotNull('delivery_area_id')->whereRaw('delivery_area_id IN ('. implode(', ', $filters['deliveryArea']) .')');
        }

        $query->where('mst_stock_resi.branch_id', '=', \Session::get('currentBranch')->branch_id);

        return $query;
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $modelStock = ResiStock::where('stock_resi_id', '=', $id)->first();
        $model      = TransactionResiHeader::where('resi_header_id', '=', $modelStock->resi_header_id)->first();

        if ($model === null) {
            abort(404);
        }

        if ($request->user()->cannot('accessBranch', $modelStock->branch_id)) {
            abort(403);
        }

        return view('operational::transaction.resi-stock.add', [
            'title'       => trans('shared/common.detail'),
            'model'       => $model,
            'modelStock'  => $modelStock,
            'optionArea'  => $this->getOptionArea(),
            'url'         => self::URL,
            'resource'    => self::RESOURCE,
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id', 0));
        $modelStock = ResiStock::find($id);
        $model      = TransactionResiHeader::where('resi_header_id', '=', $modelStock->resi_header_id)->first();
        if ($model === null) {
            abort(404);
        }

        $this->validate($request, [
            'deliveryArea' => 'required|max:250',
        ]);

        $model->delivery_area_id = $request->get('deliveryArea');
        $model->wdl_note         = $request->get('note');

        $model->last_updated_date = new \DateTime();
        $model->last_updated_by   = \Auth::user()->id;
        $model->save();
        
        if ($request->get('ready') != 'TRUE') {
            $modelStock->is_ready_delivery= FALSE;
        }else{
            $modelStock->is_ready_delivery= TRUE;
        }

        $modelStock->last_updated_date = new \DateTime();
        $modelStock->last_updated_by   = \Auth::user()->id;
        $modelStock->save();

        $request->session()->flash(
            'successMessage', trans('shared/common.rejected-message', ['variable' => trans('operational/menu.resi-stock').' '.$model->resi_number])
        );

        return redirect(self::URL);
    }

    protected function getOptionArea(){
        return \DB::table('op.mst_delivery_area')
                            ->select('mst_delivery_area.*')
                            ->join('op.dt_delivery_area_branch', 'mst_delivery_area.delivery_area_id', '=', 'dt_delivery_area_branch.delivery_area_id')
                            ->where('dt_delivery_area_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->where('dt_delivery_area_branch.active', '=', 'Y')
                            ->where('mst_delivery_area.active', '=', 'Y')
                            ->orderBy('mst_delivery_area.created_date', 'desc')
                            ->distinct()->get();
    }

    protected function getOptionsWarehouse()
    {
        return \DB::table('inv.mst_warehouse')
                    ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('wh_code', 'asc')
                    ->get();
    }

    protected function getOptionsRoute()
    {
        return \DB::table('op.mst_route')
                    ->where('active', '=', 'Y')
                    ->orderBy('route_code', 'asc')
                    ->get();
    }

    protected function getOptionsRegion()
    {
        return \DB::table('op.mst_region')
                    ->where('active', '=', 'Y')
                    ->orderBy('region_name', 'asc')
                    ->get();
    }

    public function printPdf(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters);

        $stringRegion = '';
        if (!empty($filters['region'])) {
            foreach ($filters['region'] as $filterRegion) {
                foreach ($this->getOptionsRegion() as $region) {
                    if ($region->region_id == $filterRegion) {
                        $stringRegion .= $region->region_name.', ';
                    }
                }
            }
        }
        $filters['region'] = substr($stringRegion, 0, -2);

        $stringRoute = '';
        if (!empty($filters['route'])) {
            foreach ($filters['route'] as $filtersRoute) {
                foreach ($this->getOptionsRoute() as $route) {
                    if ($route->route_id == $filtersRoute) {
                        $stringRoute .= $route->route_code.', ';
                    }
                }
            }
        }
        $filters['route'] = substr($stringRoute, 0, -2);

        $stringDeliveryArea = '';
        if (!empty($filters['deliveryArea'])) {
            foreach ($filters['deliveryArea'] as $filter) {
                foreach ($this->getOptionArea() as $deliveryArea) {
                    if ($deliveryArea->delivery_area_id == $filter) {
                        $stringDeliveryArea .= $deliveryArea->delivery_area_name.', ';
                    }
                }
            }
        }
        $filters['deliveryArea'] = substr($stringDeliveryArea, 0, -2);

        if (!empty($filters['status'])) {
            $filters['status'] = $filters['status'] == 'TRUE' ? 'Ready To Delivery' : 'Not Ready';
        }

        $header = view('print.header-pdf', ['title' => trans('operational/menu.resi-stock')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('operational::transaction.resi-stock.print-pdf', [
            'models'  => $query->get(),
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle('Warehouse Delivery List');
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output('wdl-'.\Session::get('currentBranch')->branch_code.'.pdf');
        \PDF::reset();
    }

    public function printExcel(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters);

        \Excel::create(trans('operational/menu.stock-resi'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.stock-resi'));
                });

                $sheet->cells('A3:T3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('operational/fields.resi-number'),
                    trans('operational/fields.date'),
                    trans('operational/fields.payment'),
                    trans('operational/fields.route'),
                    trans('operational/fields.region'),
                    trans('operational/fields.item-name'),
                    trans('shared/common.customer'),
                    trans('operational/fields.receiver'),
                    trans('operational/fields.address'),
                    trans('shared/common.telepon'),
                    trans('operational/fields.total-coly'),
                    trans('operational/fields.coly-wh'),
                    trans('operational/fields.total-weight'),
                    trans('operational/fields.total-volume'),
                    trans('operational/fields.total-unit'),
                    trans('operational/fields.delivery-area'),
                    trans('operational/fields.is-ready'),
                    trans('shared/common.note'),
                    trans('accountreceivables/fields.remaining'),
                    trans('accountreceivables/fields.bill'),
                ]);

                $currentRow = 4;
                foreach($query->get() as $model) {
                    $model = ResiStock::find($model->stock_resi_id);
                    $resi  = $model->resi;
                    $area  = !empty($resi) ? $resi->deliveryArea : null;
                    $resiDate = $model->resi !== null ? new \DateTime($model->resi->created_date) : null;
                    $warehouse = $model->warehouse;
                    $route = $model->resi !== null ? $model->resi->route : null;
                    $endCity = $route !== null ? $route->cityEnd : null;
                    $region = $endCity !== null ? $endCity->region() : null;

                    $units      = [];
                    foreach($resi->lineUnit as $lineUnit) {
                        $units[] = $lineUnit->coly.' '.$lineUnit->item_name;
                    }

                    $data = [
                        $model->resi !== null ? $model->resi->resi_number : '',
                        $resiDate !== null ? $resiDate->format('d-m-Y') : '',
                        $model->resi->getSingkatanPayment(),
                        $route !== null ? $route->route_code : '',
                        $region !== null ? $region->region_name : '',
                        $model->resi !== null ? $model->resi->itemName() : '',
                        !empty($model->resi->customerReceiver) ? $model->resi->customerReceiver->customer_name : '',
                        $model->resi->receiver_name,
                        $model->resi !== null ? $resi->receiver_address : '',
                        $model->resi !== null ? $resi->receiver_phone : '',
                        $model->resi !== null ? $model->resi->totalColy() : '',
                        $model->coly,
                        $model->resi !== null ? $model->resi->totalWeight() : '',
                        $model->resi !== null ? $model->resi->totalVolume() : '',
                        implode(', ', $units),
                        $area !== null ? $area->delivery_area_name : '',
                        $model->is_ready_delivery ? 'V' : 'X',
                        $model->resi !== null ? $resi->wdl_note : '',
                        !empty($model->resi) ? $model->resi->totalRemainingInvoice() : 0,
                        !empty($model->resi) && $model->resi->isTagihan() ? 'V' : 'X',
                    ];

                    $sheet->row($currentRow++, $data);
                }

                $lastDataRow = $currentRow;
                $currentRow = $lastDataRow + 1;
                if (!empty($filters['resiNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.resi-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['resiNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['route'])) {
                    $routes = \DB::table('op.mst_route')->whereIn('route_id', $filters['route'])->get();
                    $routesCode = [];
                    foreach($routes as $route) {
                        $routesCode[] = $route->route_code;
                    }

                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.route'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  implode(', ', $routesCode), 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['region'])) {
                    $regions = \DB::table('op.mst_region')->whereIn('region_id', $filters['region'])->get();
                    $regionsName = [];
                    foreach($regions as $region) {
                        $regionsName[] = $region->region_name;
                    }

                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.region'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  implode(', ', $regionsName), 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['deliveryArea'])) {
                    $deliveryAreas = \DB::table('op.mst_delivery_area')->whereIn('delivery_area_id', $filters['deliveryArea'])->get();
                    $deliveryAreasName = [];
                    foreach($deliveryAreas as $deliveryArea) {
                        $deliveryAreasName[] = $deliveryArea->delivery_area_name;
                    }

                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.delivery-area'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  implode(', ', $deliveryAreasName), 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['status'])) {
                    $status = $filters['status'] == FALSE ? 'Not Ready' : 'Ready to Delivery';
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.is-ready'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $status, 'C', $currentRow);
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
}
