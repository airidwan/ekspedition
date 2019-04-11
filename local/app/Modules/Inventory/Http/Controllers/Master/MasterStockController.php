<?php

namespace App\Modules\Inventory\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Inventory\Model\Master\MasterStock;
use App\Modules\Inventory\Model\Master\MasterWarehouse;

class MasterStockController extends Controller
{
    const RESOURCE = 'Inventory\Transaction\StockItem';
    const URL      = 'inventory/transaction/stock-item';
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
        } elseif (empty($request->get('page'))) {
            $request->session()->forget('filters');
        }

        $filters = $request->session()->get('filters');
        $query = $this->getQuery($request, $filters);
        
        return view('inventory::master.master-stock.index', [
            'models'            => $query->paginate(10),
            'filters'           => $filters,
            'resource'          => self::RESOURCE,
            'url'               => self::URL,
            'optionsCategory'   => $this->getOptionsCategory(),
            'optionsWarehouse'  => $this->getOptionsWarehouse(),
        ]);
    }

    public function printPdfIndex(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters);

        $header = view('print.header-pdf', ['title' => trans('inventory/menu.master-stock')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('inventory::master.master-stock.print-pdf-index', [
            'models'  => $query->get(),
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('inventory/menu.master-stock').' '.\Session::get('currentBranch')->branch_code);
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('P', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output(trans('inventory/menu.master-stock').' '.\Session::get('currentBranch')->branch_code.'.pdf');
        \PDF::reset();
    }

    public function printExcelIndex(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters)->get();

        \Excel::create(trans('inventory/menu.master-stock').' '.\Session::get('currentBranch')->branch_code, function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('inventory/menu.master-stock'));
                });

                $sheet->cells('A3:I3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('inventory/fields.item-code'),
                    trans('shared/common.description'),
                    trans('shared/common.category'),
                    trans('inventory/fields.wh-code'),
                    trans('inventory/fields.warehouse'),
                    trans('inventory/fields.stock'),
                    trans('inventory/fields.uom'),
                    trans('inventory/fields.average-cost'),
                ]);
                foreach($query as $index => $model) {
                    $date = !empty($model->date) ? new \DateTime($model->date) : null;

                    $data = [
                        $index + 1,
                        $model->item_code,
                        $model->item_description,
                        $model->category_description,
                        $model->wh_code,
                        $model->warehouse_description,
                        $model->stock,
                        $model->uom_code,
                        $model->average_cost,
                    ];
                    $sheet->row($index + 4, $data);
                }

                $currentRow = count($query) + 5;
                if (!empty($filters['itemCode'])) {
                    $this->addLabelDescriptionCell($sheet, trans('inventory/fields.item-code'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['itemCode'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['description'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.description'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['description'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['warehouse'])) {
                    $warehouse = MasterWarehouse::find($filters['warehouse']);
                    $this->addLabelDescriptionCell($sheet, trans('inventory/fields.warehouse'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, !empty($warehouse) ? $warehouse->description : '', 'C', $currentRow);
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

    protected function getQuery(Request $request, $filters){
        $query = \DB::table('inv.v_mst_stock_item')
                    ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where('stock', '>', 0 );

        if (!empty($filters['itemCode'])) {
            $query->where('item_code', 'ilike', '%'.$filters['itemCode'].'%');
        }

        if (!empty($filters['description'])) {
            $query->where('item_description', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['category'])) {
            $query->where('category_id', '=', $filters['category']);
        }

        if (!empty($filters['warehouse'])) {
            $query->where('wh_id', '=', $filters['warehouse']);
        }

        return $query;
    }

    protected function getOptionsCategory()
    {
        return \DB::table('inv.mst_category')
                    ->where('active', '=', 'Y')
                    ->orderBy('description', 'asc')
                    ->get();
    }

    protected function getOptionsWarehouse()
    {
        return \DB::table('inv.mst_warehouse')
                    ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('wh_code', 'asc')
                    ->get();
    }

}
