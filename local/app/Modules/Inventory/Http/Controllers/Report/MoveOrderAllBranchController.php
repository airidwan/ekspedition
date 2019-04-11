<?php

namespace App\Modules\Inventory\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Inventory\Model\Transaction\MoveOrderHeader;
use App\Modules\Inventory\Model\Transaction\MoveOrderLine;
use App\Modules\Inventory\Model\Transaction\BranchTransferHeader;
use App\Modules\Inventory\Model\Transaction\WarehouseTransferHeader;
use App\Modules\Operational\Service\Master\DriverService;
use App\Modules\Operational\Service\Master\TruckService;
use App\Modules\Asset\Service\Transaction\AssetService;
use App\Modules\Asset\Model\Transaction\ServiceAsset;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Modules\Generalledger\Model\Master\SettingJournal;
use App\Modules\Generalledger\Model\Master\MasterAccountCombination;
use App\Modules\Generalledger\Service\Master\AccountCombinationService;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Model\Master\MasterTruck;
use App\Modules\Inventory\Model\Master\MasterStock;
use App\Modules\Inventory\Model\Master\MasterItem;
use App\Modules\Payable\Model\Master\MasterVendor;
use App\Service\Penomoran;
use App\Notification;
use App\Service\NotificationService;
use App\Modules\Payable\Service\Master\VendorService;
use App\Role;

class MoveOrderAllBranchController extends Controller
{
    const RESOURCE = 'Inventory\Report\MoveOrderAllBranch';
    const URL      = 'inventory/report/move-order-all-branch';

    public function __construct()
    {
        $this->middleware('auth');
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
        if (empty($filters['jenis']) || $filters['jenis'] == 'headers') {
            $query = \DB::table('inv.trans_mo_header')
                    ->select(
                        'trans_mo_header.mo_header_id', 
                        'trans_mo_header.mo_number', 
                        'trans_mo_header.type', 
                        'trans_mo_header.pic', 
                        'trans_mo_header.description', 
                        'trans_mo_header.status', 
                        'trans_mo_header.created_date', 
                        'mst_truck.police_number', 
                        'mst_driver.driver_name', 
                        'mst_vendor.vendor_name', 
                        'service_asset.service_number')
                    ->leftJoin('inv.trans_mo_line', 'trans_mo_line.mo_header_id', '=', 'trans_mo_header.mo_header_id')
                    ->leftJoin('inv.mst_warehouse', 'mst_warehouse.wh_id', '=', 'trans_mo_line.wh_id')
                    ->leftJoin('inv.mst_item', 'mst_item.item_id', '=', 'trans_mo_line.item_id')
                    ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_mo_header.truck_id')
                    ->leftJoin('op.mst_driver', 'mst_driver.driver_id', '=', 'trans_mo_header.driver_id')
                    ->leftJoin('ast.service_asset', 'service_asset.service_asset_id', '=', 'trans_mo_header.service_asset_id')
                    ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'trans_mo_header.vendor_id')
                    // ->where('trans_mo_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('trans_mo_header.created_date', 'desc')
                    ->groupBy('trans_mo_header.mo_header_id', 'mst_truck.police_number', 'mst_driver.driver_name', 'mst_vendor.vendor_name', 'service_asset.service_number');
                    // ->distinct();
        } else {
            $query = \DB::table('inv.trans_mo_line')
                    ->select(
                        'trans_mo_header.mo_header_id', 
                        'trans_mo_header.mo_number', 
                        'trans_mo_header.type', 
                        'trans_mo_header.pic', 
                        'trans_mo_header.description', 
                        'trans_mo_header.status', 
                        'trans_mo_header.created_date', 
                        'trans_mo_line.qty_need', 
                        'mst_vendor.vendor_name',
                        'mst_warehouse.wh_code', 
                        'mst_warehouse.wh_id', 
                        'mst_truck.police_number', 
                        'mst_driver.driver_name', 
                        'service_asset.service_number', 
                        'mst_item.item_code', 
                        'mst_item.description as item_name',
                        'trans_mo_line.description as line_description',
                        'mst_uom.uom_code')
                    ->join('inv.trans_mo_header', 'trans_mo_header.mo_header_id', '=', 'trans_mo_line.mo_header_id')
                    ->leftJoin('inv.mst_warehouse', 'mst_warehouse.wh_id', '=', 'trans_mo_line.wh_id')
                    ->leftJoin('inv.mst_item', 'mst_item.item_id', '=', 'trans_mo_line.item_id')
                    ->leftJoin('inv.mst_uom', 'mst_uom.uom_id', '=', 'mst_item.uom_id')
                    ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_mo_header.truck_id')
                    ->leftJoin('op.mst_driver', 'mst_driver.driver_id', '=', 'trans_mo_header.driver_id')
                    ->leftJoin('ast.service_asset', 'service_asset.service_asset_id', '=', 'trans_mo_header.service_asset_id')
                    ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'trans_mo_header.vendor_id')
                    // ->where('trans_mo_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('trans_mo_header.created_date', 'desc')
                    ->distinct();
        }

        if (!empty($filters['moNumber'])) {
            $query->where('mo_number', 'ilike', '%'.$filters['moNumber'].'%');
        }

        if (!empty($filters['pic'])) {
            $query->where(function($query) use ($filters) {
                $query->where('trans_mo_header.pic', 'ilike', '%'.$filters['pic'].'%')
                       ->orWhere('mst_vendor.vendor_name', 'ilike', '%'.$filters['pic'].'%');
            });
        }

        if (!empty($filters['description'])) {
            $query->where('trans_mo_header.description', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['itemCode'])) {
            $query->where('mst_item.item_code', 'ilike', '%'.$filters['itemCode'].'%');
        }

        if (!empty($filters['itemName'])) {
            $query->where('mst_item.description', 'ilike', '%'.$filters['itemName'].'%');
        }

        if (!empty($filters['status'])) {
            $query->where('trans_mo_header.status', '=', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('trans_mo_header.type', '=', $filters['type']);
        }

        if (!empty($filters['warehouse'])) {
            $query->where('mst_warehouse.wh_id', '=', $filters['warehouse']);
        }

        if (!empty($filters['driverName'])) {
            $query->where('mst_driver.driver_name', 'ilike', '%'.$filters['driverName'].'%');
        }

        if (!empty($filters['policeNumber'])) {
            $query->where('mst_truck.police_number', 'ilike', '%'.$filters['policeNumber'].'%');
        }

        if (!empty($filters['serviceNumber'])) {
            $query->where('service_asset.service_number', 'ilike', '%'.$filters['serviceNumber'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = new \DateTime($filters['dateFrom']);
            $query->where('trans_mo_header.created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = new \DateTime($filters['dateTo']);
            $query->where('trans_mo_header.created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        return view('inventory::report.move-order-all-branch.index', [
            'models'          => $query->paginate(10),
            'filters'         => $filters,
            'optionStatus'    => $this->getOptionsStatus(),
            'optionType'      => $this->getOptionsType(),
            'optionWarehouse' => \DB::table('inv.mst_warehouse')->get(),
            'resource'        => self::RESOURCE,
            'url'             => self::URL,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $model = MoveOrderHeader::where('mo_header_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        $data = [
            'title'         => trans('shared/common.edit'),
            'model'         => $model,
            'optionStatus'  => $this->getOptionsStatus(),
            'optionType'    => $this->getOptionsType(),
            'optionItem'    => $this->getOptionsItem(),
            'optionCoa'     => $this->getOptionsCoa(),
            'optionService' => AssetService::getAllServiceOrder(),
            'optionDriver'  => DriverService::getActiveDriverAsistant(),
            'optionTruck'   => TruckService::getActiveTruck(), 
            'optionVendor'  => VendorService::getQueryVendorEmployee(),
            'resource'      => self::RESOURCE,
            'url'           => self::URL,
        ];

        return view('inventory::report.move-order-all-branch.detail', $data);
    }

    

    public function printPdfDetail(Request $request, $id)
    {
        $filters = \Session::get('filters');
        $model= MoveOrderHeader::find($id);

        $header = view('print.header-pdf', ['title' => trans('inventory/menu.move-order')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('inventory::transaction.move-order.print-pdf-detail', [
            'model'  => $model,
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle('Move Order');
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A5');
        \PDF::writeHTML($html);
        \PDF::Output($model->mo_number.'.pdf');
        \PDF::reset();
    }

 

    protected function getOptionsStatus()
    {
        return [
            MoveOrderHeader::INCOMPLETE,
            MoveOrderHeader::COMPLETE,
        ];
    }

    protected function getOptionsType()
    {
        return [
            MoveOrderHeader::STANDART,
            MoveOrderHeader::SERVICE,
        ];
    }

    protected function getOptionsItem(){
        return \DB::table('inv.mst_stock_item')
                    ->select('mst_stock_item.*', 'mst_item.item_code', 'mst_item.description', 'mst_warehouse.wh_code', 'mst_uom.uom_id', 'mst_uom.uom_code')
                    ->join('inv.mst_warehouse', 'mst_warehouse.wh_id', '=', 'mst_stock_item.wh_id')
                    ->join('inv.mst_item', 'mst_item.item_id', '=', 'mst_stock_item.item_id')
                    ->join('inv.mst_uom', 'mst_uom.uom_id', '=', 'mst_item.uom_id')
                    ->where('mst_stock_item.stock', '>', 0)
                    // ->where('mst_warehouse.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->get();
    }

    protected function getOptionsCoa(){
        return \DB::table('gl.mst_coa')
                    ->where('segment_name', '=', MasterCoa::ACCOUNT)
                    ->where('identifier', '=', MasterCoa::EXPENSE)
                    ->where('active', '=', 'Y')
                    ->get();
    }
}
