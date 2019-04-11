<?php

namespace App\Modules\Asset\Service\Transaction;

use App\Modules\Asset\Model\Transaction\AdditionAsset;
use App\Modules\Asset\Model\Transaction\ServiceAsset;
use App\Modules\Asset\Model\Master\AssetCategory;
use App\Modules\Payable\Model\Transaction\InvoiceHeader;
use App\Modules\Purchasing\Model\Transaction\PurchaseOrderHeader;
use App\Modules\Inventory\Model\Transaction\MoveOrderHeader;

class AssetService
{
    const INVOICE = 'Invoice';
    const PO      = 'Purchase Order';
    const MO      = 'Move Order';

    public static function getServiceOrder()
    {
        $query = \DB::table('ast.v_service_asset')
            ->select('v_service_asset.*', 'addition_asset.asset_number', 'addition_asset.police_number', 'mst_truck.truck_id', 'mst_truck.truck_code')
            ->join('ast.addition_asset', 'addition_asset.asset_id', '=', 'v_service_asset.asset_id' )
            ->leftJoin('op.mst_truck', 'mst_truck.asset_id', '=', 'addition_asset.asset_id')
            ->leftJoin('ap.invoice_line', 'invoice_line.service_id', '=', 'v_service_asset.service_asset_id' )
            ->leftJoin('ap.invoice_header', 'invoice_header.header_id', '=', 'invoice_line.header_id' )
            ->leftJoin('po.po_lines', 'po_lines.service_asset_id', '=', 'v_service_asset.service_asset_id' )
            ->leftJoin('po.po_headers', 'po_headers.header_id', '=', 'po_lines.header_id' )
            ->leftJoin('inv.trans_mo_header', 'trans_mo_header.service_asset_id', '=', 'v_service_asset.service_asset_id' )
            ->where('v_service_asset.finished', '=', FALSE);
            // ->where('v_service_asset.branch_id', '=', \Session::get('currentBranch')->branch_id);

            // $query->where(function ($query) {
            //           $query->whereNull('invoice_line.service_id')
            //                 ->orWhere('invoice_header.status','=', InvoiceHeader::CANCELED);
            //         });
            // $query->where(function ($query) {
            //           $query->whereNull('po_lines.service_asset_id')
            //                 ->orWhere('po_headers.status','=', PurchaseOrderHeader::CANCELED);
            //         });
            // $query->where(function ($query) {
            //           $query->whereNull('trans_mo_header.service_asset_id')
            //                 ->orWhere('trans_mo_header.status','=', MoveOrderHeader::CANCELED);
            //         });

        return $query->distinct()->get();
    }

    public static function getAllServiceOrder()
    {
        $query = \DB::table('ast.service_asset')
            ->select(
                'service_asset.*', 
                'addition_asset.asset_number', 
                'mst_item.description as item_description', 
                'mst_truck.police_number', 
                'mst_truck.owner_name'
                )
            ->leftJoin('ast.addition_asset', 'addition_asset.asset_id', '=', 'service_asset.asset_id' )
            ->leftJoin('inv.mst_item', 'mst_item.item_id', '=', 'addition_asset.item_id' )
            ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'service_asset.truck_id')
            // ->leftJoin('ap.invoice_line', 'invoice_line.service_id', '=', 'v_service_asset.service_asset_id' )
            // ->leftJoin('ap.invoice_header', 'invoice_header.header_id', '=', 'invoice_line.header_id' )
            // ->leftJoin('po.po_lines', 'po_lines.service_asset_id', '=', 'v_service_asset.service_asset_id' )
            // ->leftJoin('po.po_headers', 'po_headers.header_id', '=', 'po_lines.header_id' )
            // ->leftJoin('inv.trans_mo_header', 'trans_mo_header.service_asset_id', '=', 'v_service_asset.service_asset_id' )
            ->where('service_asset.finished', '=', FALSE);
            // ->where('v_service_asset.branch_id', '=', \Session::get('currentBranch')->branch_id);

            // $query->where(function ($query) {
            //           $query->whereNull('invoice_line.service_id')
            //                 ->orWhere('invoice_header.status','=', InvoiceHeader::CANCELED);
            //         });
            // $query->where(function ($query) {
            //           $query->whereNull('po_lines.service_asset_id')
            //                 ->orWhere('po_headers.status','=', PurchaseOrderHeader::CANCELED);
            //         });
            // $query->where(function ($query) {
            //           $query->whereNull('trans_mo_header.service_asset_id')
            //                 ->orWhere('trans_mo_header.status','=', MoveOrderHeader::CANCELED);
            //         });

        return $query->distinct()->get();
    }

    public static function getAssetKendaraan()
    {
        return \DB::table('ast.v_addition_asset')
            ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
            ->where('asset_category_id', '=', AssetCategory::KENDARAAN)
            ->get();
    }

    public static function getExistAssetKendaraan()
    {
        return \DB::table('ast.v_addition_asset')
            ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
            ->where('asset_category_id', '=', AssetCategory::KENDARAAN)
            ->whereRaw('asset_id not in (select asset_id from op.mst_truck where asset_id is not null)')
            ->get();
    }
}
