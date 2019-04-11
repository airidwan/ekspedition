<?php

namespace App\Modules\Inventory\Service\Master;

class WarehouseService
{
    public static function getActiveWarehouse()
    {
        return \DB::table('inv.mst_warehouse')
                        ->where('active', '=', 'Y')
                        ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->get();
    }
}
