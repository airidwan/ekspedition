<?php

namespace App\Modules\Purchasing\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Purchasing\Http\Controllers\Transaction\PurchaseApproveController;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
use App\Modules\Purchasing\Model\Transaction\PurchaseOrderHeader;
use App\Modules\Purchasing\Model\Transaction\PurchaseOrderLine;
use App\Modules\Purchasing\Model\Master\MasterTypePo;
use App\Modules\Payable\Model\Transaction\InvoiceHeader;
use App\Modules\Asset\Service\Transaction\AssetService;
use App\Modules\Operational\Service\Transaction\ManifestService;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Payable\Model\Master\MasterVendor;
use App\Notification;
use App\Service\Penomoran;
use App\Service\NotificationService;
use App\Role;

class PurchaseOrderOutstandingController extends Controller
{
    const RESOURCE = 'Purchasing\Report\PurchaseOrderOutstanding';
    const URL      = 'purchasing/report/purchase-order-outstanding';

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

        if (empty($filters['poNumber']) && empty($filters['supplier'])) {
            if ($request->isMethod('post')) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'You must entry po number or supplier']);
            }else{
                return view('purchasing::report.purchase-order-outstanding.index', [
                    'modelsQty'         => [],
                    'modelsInvoice'     => [],
                    'filters'           => $filters,
                    'resource'          => self::RESOURCE,
                    'url'               => self::URL,
                    'optionsBranch'     => $this->getOptionsBranch(),
                    'optionsSupplier'   => $this->getOptionsSupplier(),
                    'optionsType'       => $this->getOptionsType(),
                    'optionsStatus'     => $this->getOptionsStatus(),
                ]);
            }
        }


        $queryQty     = $this->getQueryQtyOutstanding($request, $filters);
        $queryInvoice = $this->getQueryInvoiceOutstanding($request, $filters);

        return view('purchasing::report.purchase-order-outstanding.index', [
            'modelsQty'         => $queryQty->get(),
            'modelsInvoice'     => $queryInvoice->get(),
            'filters'           => $filters,
            'resource'          => self::RESOURCE,
            'url'               => self::URL,
            'optionsBranch'     => $this->getOptionsBranch(),
            'optionsSupplier'   => $this->getOptionsSupplier(),
            'optionsType'       => $this->getOptionsType(),
            'optionsStatus'     => $this->getOptionsStatus(),
        ]);
    }

    protected function getQueryQtyOutstanding(Request $request, $filters){
        $query = \DB::table('po.po_lines')
                    ->select(
                        'po_lines.quantity_need', 
                        'po_lines.unit_price', 
                        'po_lines.total_price', 
                        'po_lines.quantity_remain', 
                        'po_headers.po_number', 
                        'po_headers.po_date',
                        'mst_vendor.vendor_code', 
                        'mst_vendor.vendor_name', 
                        'mst_po_type.type_name', 
                        'mst_item.item_code', 
                        'mst_item.description as item_description', 
                        'mst_uom.uom_code', 
                        'mst_warehouse.wh_code', 
                        'mst_category.description as category_description'
                        )
                    ->leftJoin('po.po_headers', 'po_headers.header_id', '=', 'po_lines.header_id')
                    ->leftJoin('inv.mst_item', 'mst_item.item_id', '=', 'po_lines.item_id')
                    ->leftJoin('inv.mst_warehouse', 'mst_warehouse.wh_id', '=', 'po_lines.wh_id')
                    ->leftJoin('inv.mst_category', 'mst_category.category_id', '=', 'mst_item.category_id')
                    ->leftJoin('inv.mst_uom', 'mst_uom.uom_id', '=', 'mst_item.uom_id')
                    ->leftJoin('po.mst_po_type', 'mst_po_type.type_id', '=', 'po_headers.type_id')
                    ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'po_headers.supplier_id')
                    ->orderBy('po_headers.po_date', 'desc')
                    ->distinct();

        if (!empty($filters['poNumber'])) {
            $query->where('po_headers.po_number', 'ilike', '%'.$filters['poNumber'].'%');
        }

        if (!empty($filters['supplier'])) {
            $query->where('po_headers.supplier_id', '=', $filters['supplier']);
        }

        if (!empty($filters['type'])) {
            $query->where('po_headers.type_id', '=', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('po_headers.status', '=', $filters['status']);
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = new \DateTime($filters['dateFrom']);
            $query->where('po_headers.po_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = new \DateTime($filters['dateTo']);
            $query->where('po_headers.po_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }
        // dd($query->get());

        return $query;
    }
    
    protected function getQueryInvoiceOutstanding(Request $request, $filters){
        $invoice   = \DB::table('ap.invoice_header')
                        ->select(
                            'invoice_header.header_id', 
                            'invoice_header.invoice_number', 
                            'po_headers.po_number', 
                            'po_headers.created_date', 
                            'mst_vendor.vendor_name'
                            )
                        ->leftJoin('ap.invoice_line', 'invoice_line.header_id', '=', 'invoice_header.header_id')
                        ->leftJoin('po.po_headers', 'po_headers.header_id', '=', 'invoice_line.header_id')
                        ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'po_headers.supplier_id')
                        ->where(function($invoice) {
                               $invoice->where('invoice_header.status', '=', InvoiceHeader::APPROVED)
                                    ->orWhere('invoice_header.status', '=', InvoiceHeader::CLOSED);
                          })
                        ->where('invoice_header.type_id', '=', InvoiceHeader::PURCHASE_ORDER)
                        ->orderBy('invoice_header.created_date', 'asc');
        
        if (!empty($filters['poNumber'])) {
            $invoice->where('po_headers.po_number', 'ilike', '%'.$filters['poNumber'].'%');
        }

        if (!empty($filters['supplier'])) {
            $invoice->where('po_headers.supplier_id', '=', $filters['supplier']);
        }

        if (!empty($filters['type'])) {
            $query->where('po_headers.type_id', '=', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('po_headers.status', '=', $filters['status']);
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $invoice->where('invoice_header.created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $invoice->where('invoice_header.created_date', '<=', $date->format('Y-m-d 23:59:59'));
        }
        return $invoice;
    }

   

    protected function getOptionsBranch()
    {
        return \DB::table('op.mst_branch')->where('active', '=', 'Y')->orderBy('branch_name')->get();
    }

    protected function getOptionsSupplier()
    {
        return \DB::table('ap.mst_vendor')
                ->leftJoin('ap.dt_vendor_branch', 'dt_vendor_branch.vendor_id', '=', 'mst_vendor.vendor_id')
                ->where('dt_vendor_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                ->where('mst_vendor.category', '=', MasterVendor::VENDOR)
                ->orderBy('vendor_name')->where('active', '=', 'Y')->get();
    }

    protected function getOptionsType()
    {
        return \DB::table('po.mst_po_type')->orderBy('type_name')->where('active', '=', 'Y')->get();
    }

    protected function getOptionsStatus()
    {
        return [
            PurchaseOrderHeader::INCOMPLETE,
            PurchaseOrderHeader::INPROCESS,
            PurchaseOrderHeader::APPROVED,
            PurchaseOrderHeader::CANCELED,
            PurchaseOrderHeader::CLOSED,
        ];
    }

    protected function getOptionLineType()
    {
        return [
            PurchaseOrderHeader::GOODS,
            PurchaseOrderHeader::SERVICE,
        ];
    }

    protected function getOptionsWarehouse()
    {
        return \DB::table('inv.mst_warehouse')->orderBy('wh_code')->where('active', '=', 'Y')->where('branch_id', '=', \Session::get('currentBranch')->branch_id)->get();
    }

    protected function getOptionsItem()
    {
        return \DB::table('inv.v_mst_item')->orderBy('item_code')->where('active', '=', 'Y')->get();
    }
}
