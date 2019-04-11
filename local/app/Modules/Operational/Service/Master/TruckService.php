<?php 

namespace App\Modules\Operational\Service\Master;

use App\Modules\Asset\Model\Transaction\AdditionAsset;
use App\MasterLookupValues;

class TruckService
{
    public static function getActiveRentTruck()
    {
        return \DB::table('op.v_mst_truck')
                    ->select('v_mst_truck.*')
                    ->leftJoin('op.dt_truck_branch', 'v_mst_truck.truck_id', '=', 'dt_truck_branch.truck_id')
                    ->where('dt_truck_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where('v_mst_truck.category', '=', 'SEWA_TRIP')
                    ->where('v_mst_truck.active', '=', 'Y')
                    ->where('dt_truck_branch.active', '=', 'Y')
                    ->distinct()
                    ->get();
    }

    public static function getActiveTruck()
    {
        return \DB::table('op.v_mst_truck')
                    ->select('v_mst_truck.*')
                    ->leftJoin('op.dt_truck_branch', 'v_mst_truck.truck_id', '=', 'dt_truck_branch.truck_id')
                    ->where('dt_truck_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where('v_mst_truck.active', '=', 'Y')
                    ->where('dt_truck_branch.active', '=', 'Y')
                    ->distinct()
                    ->get();
    }

    public static function getActiveTruckMonthly()
    {
        return \DB::table('op.v_mst_truck')
                    ->select('v_mst_truck.*')
                    ->leftJoin('op.dt_truck_branch', 'v_mst_truck.truck_id', '=', 'dt_truck_branch.truck_id')
                    ->where('dt_truck_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where('v_mst_truck.category', '=', 'SEWA_BULANAN')
                    ->where('v_mst_truck.active', '=', 'Y')
                    ->where('dt_truck_branch.active', '=', 'Y')
                    ->distinct()
                    ->get();
    }


    public static function getAllActiveTruckNonService()
    {
        return \DB::table('op.mst_truck')
                    ->select('mst_truck.*', 'brand.meaning as truck_brand', 'type.meaning as truck_type', 'category.meaning as truck_category')
                    ->leftJoin('op.dt_truck_branch', 'mst_truck.truck_id', '=', 'dt_truck_branch.truck_id')
                    ->leftJoin('ast.addition_asset', 'mst_truck.asset_id', '=', 'addition_asset.asset_id')
                    ->leftJoin('adm.mst_lookup_values as brand', 'brand.lookup_code', '=', 'mst_truck.brand')
                    ->leftJoin('adm.mst_lookup_values as type', 'type.lookup_code', '=', 'mst_truck.type')
                    ->leftJoin('adm.mst_lookup_values as category', 'category.lookup_code', '=', 'mst_truck.category')
                    ->where('dt_truck_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where(function($query) {
                        $query->whereNull('mst_truck.asset_id')->orWhere('addition_asset.status_id', '<>', AdditionAsset::ONSERVICE);
                    })
                    ->where('mst_truck.active', '=', 'Y')
                    ->where('dt_truck_branch.active', '=', 'Y')
                    ->distinct()
                    ->get();
    }
}