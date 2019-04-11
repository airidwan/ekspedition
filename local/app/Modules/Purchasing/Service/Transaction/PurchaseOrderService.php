<?php 

namespace App\Modules\Purchasing\Service\Transaction;

use App\Modules\Purchasing\Model\Transaction\PurchaseOrderHeader;
use App\Modules\Purchasing\Model\Master\MasterTypePo;

class PurchaseOrderService
{
    
    public static function getPurchaseOrderVendorAjax()
    {
        return \DB::table('po.v_po_invoice')->get();
    }

    public static function getPurchaseOrderVendor($supplierId)
    {
        return \DB::table('po.v_po_headers')
            ->select('v_po_headers.*', 'v_dp_invoice.total_amount')
            ->join('po.v_po_lines', 'v_po_lines.header_id', '=', 'v_po_headers.header_id')
            ->leftjoin('ap.v_dp_invoice', 'v_dp_invoice.po_header_id', '=', 'v_po_headers.header_id')
            ->where('v_po_headers.supplier_id', '=', $supplierId)
            ->where('v_po_headers.branch_id', '=', \Session::get('currentBranch')->branch_id)->distinct()
            ->get();
    }

    public static function getPurchaseOrderActive()
    {
        return \DB::table('po.v_po_headers')
                ->join('po.v_po_lines', 'v_po_lines.header_id', '=', 'v_po_headers.header_id')
                ->where('v_po_lines.quantity_remain', '<>', 0)
                ->where('v_po_headers.branch_id', '=', \Session::get('currentBranch')->branch_id)
                ->where('v_po_headers.status', '=', PurchaseOrderHeader::APPROVED)
                ->get();
    }

    public static function getPurchaseOrderClosed()
    {
        return \DB::table('po.v_po_headers')
                ->join('po.v_po_lines', 'v_po_lines.header_id', '=', 'v_po_headers.header_id')
                ->where('v_po_lines.quantity_remain', '=', 0)
                ->where('v_po_headers.branch_id', '=', \Session::get('currentBranch')->branch_id)
                ->where('v_po_headers.status', '=', PurchaseOrderHeader::CLOSED)
                ->get();
    }

    public static function getPurchaseOrderApproved()
    {
        return \DB::table('po.v_po_headers')
                ->join('po.v_po_lines', 'v_po_lines.header_id', '=', 'v_po_headers.header_id')
                ->where('v_po_headers.branch_id', '=', \Session::get('currentBranch')->branch_id)
                ->where(function ($query) {
                    $query->where('v_po_headers.status', '=', PurchaseOrderHeader::APPROVED)
                          ->orWhere('v_po_headers.status', '=', PurchaseOrderHeader::CLOSED);
                })
                ->get();
    }

    public static function getQueryPurchaseOrderTruckRent()
    {
        return \DB::table('po.v_po_headers')
                ->join('po.v_po_lines', 'v_po_lines.header_id', '=', 'v_po_headers.header_id')
                ->where('v_po_headers.status', '=', PurchaseOrderHeader::APPROVED)
                ->where('v_po_headers.type_id', '=', MasterTypePo::TRUCK_RENT);
    }
}