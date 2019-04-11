<?php

namespace App\Modules\Asset\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\DummyHeader;
use App\Modules\Asset\Model\Transaction\AdditionAsset;
use App\Modules\Asset\Model\Transaction\AssigmentAsset;
use App\Modules\Asset\Model\Transaction\RetirementAsset;
use App\Modules\Asset\Model\Transaction\DepreciationAsset;
use App\Modules\Payable\Model\Transaction\InvoiceHeader;
use App\Modules\Purchasing\Model\Transaction\PurchaseOrderHeader;
use App\Modules\Inventory\Model\Transaction\MoveOrderHeader;
use App\Modules\Inventory\Model\Master\MasterCategory;
use App\Modules\Inventory\Model\Master\MasterItem;
use App\Modules\Inventory\Model\Transaction\ReceiptLine;
use App\Modules\Operational\Model\Master\MasterBranch;

class AssetMaintenanceController extends Controller
{
    const RESOURCE = 'Asset\Report\AssetMaintenance';
    const URL      = 'asset/report/asset-maintenance';
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

        if (!empty($filters['assetNumber']) || !empty($filters['assetName']) || !empty($filters['policeNumber']) || !empty($filters['employee']) || !empty($filters['category']) || !empty($filters['status']) || !empty($filters['dateTo']) || !empty($filters['dateFrom'])) {
            $queryMoveOrder     = $this->getQueryMoveOrder($request, $filters);
            $queryInvoice       = $this->getQueryInvoice($request, $filters);
            $queryPurchaseOrder = $this->getQueryPurchaseOrder($request, $filters);
        }

        return view('asset::report.asset-maintenance.index', [
            'moveOrder'      => !empty($queryMoveOrder) ? $queryMoveOrder : [],
            'invoice'        => !empty($queryInvoice) ? $queryInvoice : [],
            'purchaseOrder'  => !empty($queryPurchaseOrder) ? $queryPurchaseOrder : [],
            'filters'        => $filters,
            'optionCategory' => \DB::table('ast.asset_category')
                                ->where('active', '=', 'Y')
                                ->get(),
            'optionStatus'   => \DB::table('ast.asset_status')->get(),
            'optionType'     => $this->optionType(),
            'resource'       => self::RESOURCE,
            'url'            => self::URL
        ]);
    }

    protected function getQueryMoveOrder(Request $request, $filters){

        $queryMoveOrder   = \DB::table('ast.service_asset')
                                ->select(
                                    'addition_asset.asset_number',
                                    'item.item_code as asset_item_code',
                                    'item.description as asset_name',
                                    'assigment_asset.employee_name',
                                    'mst_truck.truck_code',
                                    'mst_truck.police_number',
                                    'service_asset.service_number',
                                    'service_asset.finish_date',
                                    'service_asset.note as service_description',
                                    'trans_mo_header.mo_number',
                                    'trans_mo_header.created_date as mo_date',
                                    'trans_mo_line.qty_need',
                                    'trans_mo_line.cost',
                                    'mst_warehouse.wh_code',
                                    'mst_driver.driver_code',
                                    'mst_driver.driver_name',
                                    'mst_item.item_code',
                                    'mst_item.description as item_name',
                                    'mst_uom.description as uom'
                                    )
                                ->join('ast.addition_asset','addition_asset.asset_id', '=', 'service_asset.asset_id')
                                ->join('inv.mst_item as item','item.item_id', '=', 'addition_asset.item_id')
                                ->join('ast.asset_category','asset_category.asset_category_id', '=', 'addition_asset.asset_category_id')
                                ->leftJoin('ast.assigment_asset','assigment_asset.asset_id', '=', 'addition_asset.asset_id')
                                ->leftJoin('op.mst_truck','mst_truck.asset_id', '=', 'addition_asset.asset_id')
                                ->leftJoin('inv.trans_mo_header','trans_mo_header.service_asset_id', '=', 'service_asset.service_asset_id')
                                ->leftJoin('op.mst_driver','mst_driver.driver_id', '=', 'trans_mo_header.driver_id')
                                ->leftJoin('inv.trans_mo_line','trans_mo_line.mo_header_id', '=', 'trans_mo_header.mo_header_id')
                                ->leftJoin('inv.mst_warehouse','mst_warehouse.wh_id', '=', 'trans_mo_line.wh_id')
                                ->leftJoin('inv.mst_item','mst_item.item_id', '=', 'trans_mo_line.item_id')
                                ->leftJoin('inv.mst_uom','mst_uom.uom_id', '=', 'mst_item.uom_id')
                                // ->where('addition_asset.branch_id','=', \Session::get('currentBranch')->branch_id)
                                ->where('trans_mo_header.status','=', MoveOrderHeader::COMPLETE);

        if (!empty($filters['assetNumber'])) {
            $queryMoveOrder->where('addition_asset.asset_number', 'ilike', '%'.$filters['assetNumber'].'%');
        }

        if (!empty($filters['assetName'])) {
            $queryMoveOrder->where('mst_item.description', 'ilike', '%'.$filters['assetName'].'%');
        }

        if (!empty($filters['policeNumber'])) {
            $queryMoveOrder->where('mst_truck.police_number', 'ilike', '%'.$filters['policeNumber'].'%');
        }

        if (!empty($filters['employee'])) {
            $queryMoveOrder->where('assigment_asset.employee_name', 'ilike', '%'.$filters['employee'].'%');
        }
        
        if (!empty($filters['category'])) {
            $queryMoveOrder->where('asset_category.asset_category_id', $filters['category']);
        }

        if (!empty($filters['status'])) {
            $queryMoveOrder->where('addition_asset.status_id', '=', $filters['status']);
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = new \DateTime($filters['dateFrom']);
            $queryMoveOrder->where('service_asset.created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = new \DateTime($filters['dateTo']);
            $queryMoveOrder->where('service_asset.created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }
        return $queryMoveOrder->get();
    }

    protected function getQueryPurchaseOrder(Request $request, $filters){

        $queryPurchaseOrder   = \DB::table('ast.service_asset')
                                ->select(
                                    'addition_asset.asset_number',
                                    'item.item_code as asset_item_code',
                                    'item.description as asset_name',
                                    'assigment_asset.employee_name',
                                    'mst_truck.truck_code',
                                    'mst_truck.police_number',
                                    'service_asset.service_number',
                                    'service_asset.finish_date',
                                    'service_asset.note as service_description',
                                    'po_headers.header_id as poId',
                                    'po_headers.po_number',
                                    'po_headers.created_date as po_date',
                                    'po_lines.quantity_need',
                                    'po_lines.total_price',
                                    'mst_warehouse.wh_code',
                                    'mst_item.item_code',
                                    'mst_item.description as item_name',
                                    'mst_uom.description as uom'
                                    )
                                ->join('ast.addition_asset','addition_asset.asset_id', '=', 'service_asset.asset_id')
                                ->join('inv.mst_item as item','item.item_id', '=', 'addition_asset.item_id')
                                ->join('ast.asset_category','asset_category.asset_category_id', '=', 'addition_asset.asset_category_id')
                                ->leftJoin('ast.assigment_asset','assigment_asset.asset_id', '=', 'addition_asset.asset_id')
                                ->leftJoin('op.mst_truck','mst_truck.asset_id', '=', 'addition_asset.asset_id')
                                ->leftJoin('po.po_lines','po_lines.service_asset_id', '=', 'service_asset.service_asset_id')
                                ->leftJoin('po.po_headers','po_headers.header_id', '=', 'po_lines.header_id')
                                ->leftJoin('inv.mst_warehouse','mst_warehouse.wh_id', '=', 'po_lines.wh_id')
                                ->leftJoin('inv.mst_item','mst_item.item_id', '=', 'po_lines.item_id')
                                ->leftJoin('inv.mst_uom','mst_uom.uom_id', '=', 'mst_item.uom_id')
                                // ->where('addition_asset.branch_id','=', \Session::get('currentBranch')->branch_id)
                                ->where(function($query){
                                    $query->where('po_headers.status','=', PurchaseOrderHeader::APPROVED)
                                          ->orWhere('po_headers.status','=', PurchaseOrderHeader::CLOSED);
                                });

        if (!empty($filters['assetNumber'])) {
            $queryPurchaseOrder->where('addition_asset.asset_number', 'ilike', '%'.$filters['assetNumber'].'%');
        }

        if (!empty($filters['assetName'])) {
            $queryPurchaseOrder->where('mst_item.description', 'ilike', '%'.$filters['assetName'].'%');
        }

        if (!empty($filters['policeNumber'])) {
            $queryPurchaseOrder->where('mst_truck.police_number', 'ilike', '%'.$filters['policeNumber'].'%');
        }

        if (!empty($filters['employee'])) {
            $queryPurchaseOrder->where('assigment_asset.employee_name', 'ilike', '%'.$filters['employee'].'%');
        }
        
        if (!empty($filters['category'])) {
            $queryPurchaseOrder->where('asset_category.asset_category_id', $filters['category']);
        }

        if (!empty($filters['status'])) {
            $queryPurchaseOrder->where('addition_asset.status_id', '=', $filters['status']);
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = new \DateTime($filters['dateFrom']);
            $queryPurchaseOrder->where('service_asset.created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = new \DateTime($filters['dateTo']);
            $queryPurchaseOrder->where('service_asset.created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }
        return $queryPurchaseOrder->get();
    }

    protected function getQueryInvoice(Request $request, $filters){

        $queryInvoice   = \DB::table('ast.service_asset')
                                ->select(
                                    'addition_asset.asset_number',
                                    'item.item_code as asset_item_code',
                                    'item.description as asset_name',
                                    'assigment_asset.employee_name',
                                    'mst_truck.truck_code',
                                    'mst_truck.police_number',
                                    'service_asset.service_number',
                                    'service_asset.finish_date',
                                    'service_asset.note as service_description',
                                    'invoice_line.amount',
                                    'invoice_header.invoice_number',
                                    'invoice_header.created_date as invoice_date',
                                    'payment.payment_number',
                                    'payment.payment_method',
                                    'payment.created_date as payment_date',
                                    'payment.total_amount'
                                    )
                                ->join('ast.addition_asset','addition_asset.asset_id', '=', 'service_asset.asset_id')
                                ->join('inv.mst_item as item','item.item_id', '=', 'addition_asset.item_id')
                                ->join('ast.asset_category','asset_category.asset_category_id', '=', 'addition_asset.asset_category_id')
                                ->leftJoin('ast.assigment_asset','assigment_asset.asset_id', '=', 'addition_asset.asset_id')
                                ->leftJoin('op.mst_truck','mst_truck.asset_id', '=', 'addition_asset.asset_id')
                                ->leftJoin('ap.invoice_line','invoice_line.service_id', '=', 'service_asset.service_asset_id')
                                ->leftJoin('ap.invoice_header','invoice_header.header_id', '=', 'invoice_line.header_id')
                                ->join('ap.payment','payment.invoice_header_id', '=', 'invoice_header.header_id')
                                // ->where('addition_asset.branch_id','=', \Session::get('currentBranch')->branch_id)
                                ->where(function($queryInvoice) {
                                        $queryInvoice->where('invoice_header.status','=', InvoiceHeader::APPROVED)
                                                     ->orWhere('invoice_header.status', '=', InvoiceHeader::CLOSED);
                                        }
                                    );

        if (!empty($filters['assetNumber'])) {
            $queryInvoice->where('addition_asset.asset_number', 'ilike', '%'.$filters['assetNumber'].'%');
        }

        if (!empty($filters['assetName'])) {
            $queryInvoice->where('mst_item.description', 'ilike', '%'.$filters['assetName'].'%');
        }

        if (!empty($filters['policeNumber'])) {
            $queryInvoice->where('mst_truck.police_number', 'ilike', '%'.$filters['policeNumber'].'%');
        }

        if (!empty($filters['employee'])) {
            $queryInvoice->where('assigment_asset.employee_name', 'ilike', '%'.$filters['employee'].'%');
        }
        
        if (!empty($filters['category'])) {
            $queryInvoice->where('asset_category.asset_category_id', $filters['category']);
        }

        if (!empty($filters['status'])) {
            $queryInvoice->where('addition_asset.status_id', '=', $filters['status']);
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = new \DateTime($filters['dateFrom']);
            $queryInvoice->where('service_asset.created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = new \DateTime($filters['dateTo']);
            $queryInvoice->where('service_asset.created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }
        return $queryInvoice->get();
    }

    public function printPdf(Request $request)
    {
        $filters = \Session::get('filters');
        if (!empty($filters['assetNumber']) || !empty($filters['assetName']) || !empty($filters['policeNumber']) || !empty($filters['employee']) || !empty($filters['category']) || !empty($filters['status']) || !empty($filters['dateTo']) || !empty($filters['dateFrom'])) {
            $queryPurchaseOrder = $this->getQueryPurchaseOrder($request, $filters);
            $queryMoveOrder     = $this->getQueryMoveOrder($request, $filters);
            $queryInvoice       = $this->getQueryInvoice($request, $filters);
        }

        $header = view('print.header-pdf', ['title' => trans('asset/menu.asset-maintenance')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('asset::report.asset-maintenance.print-pdf', [
            'purchaseOrder' => !empty($queryPurchaseOrder) ? $queryPurchaseOrder : [],
            'moveOrder'     => !empty($queryMoveOrder) ? $queryMoveOrder : [],
            'invoice'       => !empty($queryInvoice) ? $queryInvoice : [],
            'filters'       => $filters,
        ])->render();

        \PDF::SetTitle(trans('asset/menu.asset-maintenance'));
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output(trans('asset/menu.asset-maintenance').' '.\Session::get('currentBranch')->branch_code.'.pdf');
        \PDF::reset();
    }

    public function printExcel(Request $request)
    {
        $filters = \Session::get('filters');
        $queryPurchaseOrder   = [];
        $queryMoveOrder = [];
        $queryInvoice = [];

        if (!empty($filters['assetNumber']) || !empty($filters['assetName']) || !empty($filters['policeNumber']) || !empty($filters['employee']) || !empty($filters['category']) || !empty($filters['status']) || !empty($filters['dateTo']) || !empty($filters['dateFrom'])) {
            $queryPurchaseOrder = $this->getQueryPurchaseOrder($request, $filters);
            $queryMoveOrder     = $this->getQueryMoveOrder($request, $filters);
            $queryInvoice       = $this->getQueryInvoice($request, $filters);
        }

        \Excel::create(trans('asset/menu.asset-maintenance'), function($excel) use ($queryPurchaseOrder, $queryMoveOrder, $queryInvoice, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($queryPurchaseOrder, $queryMoveOrder, $queryInvoice, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('asset/menu.asset-maintenance'));
                });

                $sheet->cells('A3:P3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('asset/fields.asset-number'),
                    trans('asset/fields.asset-name'),
                    trans('operational/fields.truck-code'),
                    trans('operational/fields.police-number'),
                    trans('asset/fields.service-number'),
                    trans('shared/common.description'),
                    trans('asset/fields.finish-date'),
                    trans('purchasing/fields.po-number'),
                    trans('purchasing/fields.po-date'),
                    trans('inventory/fields.item-code'),
                    trans('inventory/fields.item-description'),
                    trans('inventory/fields.wh'),
                    trans('inventory/fields.qty-need'),
                    trans('inventory/fields.uom'),
                    trans('inventory/fields.cost'),
                ]);
                $totalPurchaseOrder   = 0;
                foreach($queryPurchaseOrder as $index => $model) {
                    $totalPurchaseOrder += $model->total_price; 
                    $finishDate = !empty($model->finish_date) ? new \DateTime($model->finish_date) : null;
                    $poDate     = !empty($model->po_date) ? new \DateTime($model->po_date) : null;
                    $data = [
                        $index + 1,
                        $model->asset_number,
                        $model->asset_name,
                        $model->truck_code,
                        $model->police_number,
                        $model->service_number,
                        $model->service_description,
                        !empty($finishDate) ? $finishDate->format('d-M-Y') : '',
                        $model->po_number,
                        !empty($poDate) ? $poDate->format('d-M-Y') : '',
                        $model->item_code,
                        $model->item_name,
                        $model->wh_code,
                        $model->quantity_need,
                        $model->uom,
                        $model->total_price,
                    ];
                    $sheet->row($index + 4, $data);
                }

                $currentRow = count($queryPurchaseOrder) + 4;
                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'O', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalPurchaseOrder, 'P', $currentRow);

                $currentRow += 2;

                $sheet->cells('A'.$currentRow.':R'.$currentRow, function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });
                $sheet->row($currentRow++, [
                    trans('shared/common.num'),
                    trans('asset/fields.asset-number'),
                    trans('asset/fields.asset-name'),
                    trans('operational/fields.truck-code'),
                    trans('operational/fields.police-number'),
                    trans('asset/fields.service-number'),
                    trans('shared/common.description'),
                    trans('asset/fields.finish-date'),
                    trans('inventory/fields.mo-number'),
                    trans('inventory/fields.mo-date'),
                    trans('operational/fields.driver-code'),
                    trans('operational/fields.driver-name'),
                    trans('inventory/fields.item-code'),
                    trans('inventory/fields.item-description'),
                    trans('inventory/fields.wh'),
                    trans('inventory/fields.qty-need'),
                    trans('inventory/fields.uom'),
                    trans('inventory/fields.cost'),
                ]);

                $totalMoveOrder   = 0;
                foreach($queryMoveOrder as $index => $model) {
                    $totalMoveOrder += $model->cost; 
                    $finishDate = !empty($model->finish_date) ? new \DateTime($model->finish_date) : null;
                    $moDate     = !empty($model->mo_date) ? new \DateTime($model->mo_date) : null;
                    $data = [
                        $index + 1,
                        $model->asset_number,
                        $model->asset_name,
                        $model->truck_code,
                        $model->police_number,
                        $model->service_number,
                        $model->service_description,
                        !empty($finishDate) ? $finishDate->format('d-M-Y') : '',
                        $model->mo_number,
                        !empty($moDate) ? $moDate->format('d-M-Y') : '',
                        $model->driver_code,
                        $model->driver_name,
                        $model->item_code,
                        $model->item_name,
                        $model->wh_code,
                        $model->quantity_need,
                        $model->uom,
                        $model->total_price,
                    ];
                    $sheet->row($index + $currentRow, $data);
                }
                $currentRow += count($queryMoveOrder);

                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'Q', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalMoveOrder, 'R', $currentRow);

                $currentRow += 2;

                $sheet->cells('A'.$currentRow.':M'.$currentRow, function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });
                $sheet->row($currentRow++, [
                    trans('shared/common.num'),
                    trans('asset/fields.asset-number'),
                    trans('asset/fields.asset-name'),
                    trans('operational/fields.truck-code'),
                    trans('operational/fields.police-number'),
                    trans('asset/fields.service-number'),
                    trans('shared/common.description'),
                    trans('asset/fields.finish-date'),
                    trans('payable/fields.invoice-number'),
                    trans('payable/fields.payment-number'),
                    trans('payable/fields.payment-method'),
                    trans('payable/fields.payment-date'),
                    trans('payable/fields.total-amount'),
                ]);

                $totalInvoice   = 0;
                foreach($queryInvoice as $index => $model) {
                    $totalInvoice += $model->total_amount; 
                    $finishDate    = !empty($model->finish_date) ? new \DateTime($model->finish_date) : null;
                    $paymentDate   = !empty($model->payment_date) ? new \DateTime($model->payment_date) : null;
                    $data = [
                        $index + 1,
                        $model->asset_number,
                        $model->asset_name,
                        $model->truck_code,
                        $model->police_number,
                        $model->service_number,
                        $model->service_description,
                        !empty($finishDate) ? $finishDate->format('d-M-Y') : '',
                        $model->invoice_number,
                        $model->payment_number,
                        $model->payment_method,
                        !empty($paymentDate) ? $paymentDate->format('d-M-Y') : '',
                        $model->total_amount,
                    ];
                    $sheet->row($index + $currentRow, $data);
                }
                $currentRow += count($queryInvoice);

                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'L', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalInvoice, 'M', $currentRow);
                $currentRow += 2;

                $this->addLabelDescriptionCell($sheet, trans('payable/fields.total-amount'), 'A', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalPurchaseOrder + $totalMoveOrder + $totalInvoice, 'B', $currentRow);
                $currentRow += 2;

                $tempRow     = $currentRow;
                if (!empty($filters['accountCode'])) {
                    $this->addLabelDescriptionCell($sheet, trans('asset/fields.account-code'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['accountCode'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['accountDescription'])) {
                    $this->addLabelDescriptionCell($sheet, trans('asset/fields.account-description'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['accountDescription'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.period'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['periodMonth'].'-'.$filters['periodYear'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['dateFrom'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.date-from'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['dateFrom'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['dateTo'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.date-to'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['dateTo'], 'C', $currentRow);
                    $currentRow++;
                }

                $this->addLabelDescriptionCell($sheet, trans('shared/common.date'), 'E', $tempRow);
                $this->addValueDescriptionCell($sheet, $this->now->format('d-m-Y'), 'F', $tempRow);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.user'), 'E', $tempRow + 1);
                $this->addValueDescriptionCell($sheet, \Auth::user()->full_name, 'F', $tempRow + 1);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.branch'), 'E', $tempRow + 2);
                $this->addValueDescriptionCell($sheet, \Session::get('currentBranch')->branch_name, 'F', $tempRow + 2);
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

    function optionType(){
        return [
            AdditionAsset::EXIST,
            AdditionAsset::PO
        ];
    }
}
