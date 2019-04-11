<?php

namespace App\Modules\Operational\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Transaction\ResiStock;
use App\Modules\Operational\Model\Transaction\OfficialReport;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;

class StockResiController extends Controller
{
    const RESOURCE = 'Operational\Transaction\StockResi';
    const URL      = 'operational/transaction/stock-resi';
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
        return view('operational::transaction.stock-resi.index', [
            'paginate'          => $query->paginate(10),
            'models'            => $arrayModel,
            'filters'           => $filters,
            'resource'          => self::RESOURCE,
            'url'               => self::URL,
            'urlBa'             => self::URL_BA,
            'optionArea'        => $this->getOptionArea(),
            'optionsRoute'      => $this->getOptionsRoute(),
            'optionsRegion'     => $this->getOptionsRegion(),
        ]);
    }

    protected function getQuery(Request $request, $filters){
        $query = \DB::table('op.mst_stock_resi')
                        ->select(
                            'mst_stock_resi.stock_resi_id', 
                            'mst_stock_resi.resi_header_id', 
                            'mst_stock_resi.coly as coly_wh', 
                            'mst_stock_resi.last_updated_date', 
                            'trans_resi_header.resi_number',
                            'trans_resi_header.created_date',
                            'trans_resi_header.item_name',
                            'trans_resi_header.sender_name',
                            'trans_resi_header.receiver_name',
                            'trans_resi_header.receiver_address',
                            'trans_resi_header.receiver_phone',
                            'mst_route.route_code',
                            'mst_region.region_name'
                        )
                        ->join('op.trans_resi_header', 'mst_stock_resi.resi_header_id', '=', 'trans_resi_header.resi_header_id')
                        ->leftJoin('op.mst_route', 'trans_resi_header.route_id', '=', 'mst_route.route_id')
                        ->leftJoin('op.mst_city', 'mst_route.city_end_id', '=', 'mst_city.city_id')
                        ->leftJoin('op.dt_region_city', 'mst_city.city_id', '=', 'dt_region_city.city_id')
                        ->leftJoin('op.mst_region', 'dt_region_city.region_id', '=', 'mst_region.region_id')
                        ->groupBy(
                            'mst_stock_resi.stock_resi_id', 
                            'mst_stock_resi.resi_header_id', 
                            'mst_route.route_id',
                            'trans_resi_header.resi_header_id',
                            'mst_region.region_id'
                            )
                        ->orderBy('trans_resi_header.resi_number', 'asc');

        if (!empty($filters['resiNumber'])) {
            $query->where('trans_resi_header.resi_number', 'ilike', '%'.$filters['resiNumber'].'%');
        }

        if (!empty($filters['route'])) {
            $query->whereRaw('mst_route.route_id IN ('. implode(', ', $filters['route']) .')');
        }

        if (!empty($filters['region'])) {
            $query->whereNotNull('mst_region.region_id')->whereRaw('mst_region.region_id IN ('. implode(', ', $filters['region']) .')');
        }

        $query->where('mst_stock_resi.branch_id', '=', \Session::get('currentBranch')->branch_id);

        return $query;
    }

    public function printPdfChecklist(Request $request)
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
        $filters['stringRegion'] = $stringRegion;

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
        $filters['stringRoute'] = $stringRoute;

        $header = view('print.header-pdf', ['title' => trans('operational/menu.resi-stock-checklist')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('operational::transaction.stock-resi.print-pdf-checklist', [
            'models'  => $query->get(),
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('operational/menu.resi-stock-checklist'));
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output(trans('operational/menu.resi-stock-checklist').' '.\Session::get('currentBranch')->branch_code.'.pdf');
        \PDF::reset();
    }

    public function printExcelChecklist(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters);

        \Excel::create(trans('operational/menu.stock-resi'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.stock-resi'));
                });

                $sheet->cells('A3:Q3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('operational/fields.resi-number'),
                    trans('operational/fields.date'),
                    trans('operational/fields.route'),
                    trans('operational/fields.region'),
                    trans('operational/fields.item-name'),
                    trans('operational/fields.customer'),
                    trans('operational/fields.sender'),
                    trans('operational/fields.customer'),
                    trans('operational/fields.receiver'),
                    trans('operational/fields.total-coly'),
                    trans('operational/fields.coly-wh'),
                    trans('operational/fields.total-weight'),
                    trans('operational/fields.total-volume'),
                    trans('operational/fields.total-unit'),
                    trans('operational/fields.check'),
                    trans('shared/common.status'),
                ]);

                $currentRow = 4;
                foreach($query->get() as $model) {
                    $modelStock = ResiStock::find($model->stock_resi_id);
                    $resi       = $modelStock->resi;
                    $resiDate   = new \DateTime($model->created_date);
                    $units      = [];

                    foreach($resi->lineUnit as $lineUnit) {
                        $units[] = $lineUnit->coly.' '.$lineUnit->item_name;
                    }

                    $data = [
                        $model->resi_number,
                        $resiDate !== null ? $resiDate->format('d-m-Y') : '',
                        $model->route_code,
                        $model->region_name,
                        $modelStock->resi !== null ? $modelStock->resi->itemName() : '',
                        !empty($modelStock->resi->customer) ? $modelStock->resi->customer->customer_name : '',
                        $model->sender_name,
                        !empty($modelStock->resi->customerReceiver) ? $modelStock->resi->customerReceiver->customer_name : '',
                        $model->receiver_name,
                        $modelStock->resi !== null ? $modelStock->resi->totalColy() : '',
                        $model->coly_wh,
                        $resi->totalWeight(),
                        $resi->totalVolume(),
                        implode(', ', $units),
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

    public function printPdfReport(Request $request)
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
        $filters['stringRegion'] = $stringRegion;

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
        $filters['stringRoute'] = $stringRoute;

        $header = view('print.header-pdf', ['title' => trans('operational/menu.resi-stock-report')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('operational::transaction.stock-resi.print-pdf-report', [
            'models'  => $query->get(),
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('operational/menu.resi-stock-report'));
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output(trans('operational/menu.resi-stock-report').' '.\Session::get('currentBranch')->branch_code.'.pdf');
        \PDF::reset();
    }

    public function printExcelReport(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters);

        \Excel::create(trans('operational/menu.stock-resi'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.stock-resi'));
                });

                $sheet->cells('A3:P3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('operational/fields.resi-number'),
                    trans('operational/fields.date'),
                    trans('operational/fields.route'),
                    trans('operational/fields.region'),
                    trans('operational/fields.item-name'),
                    trans('operational/fields.customer'),
                    trans('operational/fields.sender'),
                    trans('operational/fields.customer'),
                    trans('operational/fields.receiver'),
                    trans('operational/fields.total-coly'),
                    trans('operational/fields.coly-wh'),
                    trans('operational/fields.weight'),
                    trans('operational/fields.volume'),
                    trans('operational/fields.total-weight'),
                    trans('operational/fields.total-volume'),
                    trans('operational/fields.total-unit'),
                ]);

                $currentRow = 4;
                $totalColy      = 0;
                $totalColyWh    = 0;
                $totalWeight    = 0;
                $totalVolume    = 0;
                $totalWeightAll = 0;
                $totalVolumeAll = 0;
                $totalUnit      = 0;

                foreach($query->get() as $model) {
                    $modelStock = ResiStock::find($model->stock_resi_id);
                    $resi       = $modelStock->resi;
                    $resiDate   = new \DateTime($model->created_date);
                    $units          = [];
                    $totalColy      += !empty($modelStock->resi) ? $modelStock->resi->totalColy() : 0;
                    $totalColyWh    += $model->coly_wh;
                    $totalWeight    += !empty($modelStock->resi) ? $modelStock->resi->totalWeight() : 0;
                    $totalVolume    += !empty($modelStock->resi) ? $modelStock->resi->totalVolume() : 0;
                    $totalWeightAll += !empty($modelStock->resi) ? $modelStock->resi->totalWeightAll() : 0;
                    $totalVolumeAll += !empty($modelStock->resi) ? $modelStock->resi->totalVolumeAll() : 0;
                    foreach($resi->lineUnit as $index => $lineUnit) {
                        $totalUnit += $lineUnit->coly;
                        $units[] = $lineUnit->coly.' '.$lineUnit->item_name;
                    }

                    $data = [
                        $model->resi_number,
                        $resiDate !== null ? $resiDate->format('d-m-Y') : '',
                        $model->route_code,
                        $model->region_name,
                        $modelStock->resi !== null ? $modelStock->resi->itemName() : '',
                        !empty($modelStock->resi->customer) ? $modelStock->resi->customer->customer_name : '',
                        $model->sender_name,
                        !empty($modelStock->resi->customerReceiver) ? $modelStock->resi->customerReceiver->customer_name : '',
                        $model->receiver_name,
                        $modelStock->resi !== null ? $modelStock->resi->totalColy() : '',
                        $model->coly_wh,
                        $resi->totalWeight(),
                        $resi->totalVolume(),
                        $resi->totalWeightAll(),
                        $resi->totalVolumeAll(),
                        implode(', ', $units),
                    ];

                    $sheet->row($currentRow++, $data);
                }

                $sheet->cells('A'.$currentRow.':P'.$currentRow, function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $data = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    trans('shared/common.total'),
                    $totalColy,
                    $totalColyWh,
                    $totalWeight,
                    $totalVolume,
                    $totalWeightAll,
                    $totalVolumeAll,
                    $totalUnit,
                ];
                $sheet->row($currentRow++, $data);

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
}
