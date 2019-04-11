<?php 

namespace App\Modules\Operational\Service\Master;

class DeliveryAreaService
{
    public static function getActiveDeliveryArea()
    {
        return \DB::table('op.mst_delivery_area')
                    ->leftJoin('op.dt_delivery_area_branch', 'dt_delivery_area_branch.delivery_area_id', '=', 'mst_delivery_area.delivery_area_id')
                    ->where('mst_delivery_area.active', '=', 'Y')
                    ->where('dt_delivery_area_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('mst_delivery_area.delivery_area_name')->get();
    }

    public static function getAllActiveDeliveryArea()
    {
        return \DB::table('op.mst_delivery_area')
                    ->where('mst_delivery_area.active', '=', 'Y')
                    ->orderBy('mst_delivery_area.delivery_area_name')->get();
    }
}