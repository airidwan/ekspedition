<?php

namespace App\Modules\Operational\Service\Master;

class RouteService
{
    public static function getActiveRoute()
    {
        return self::getQueryActiveRoute()->select('v_mst_route.*')->get();
    }

    public static function getQueryActiveRoute()
    {
        return \DB::table('op.v_mst_route')
                    ->leftJoin('op.dt_route_branch', 'v_mst_route.route_id', '=', 'dt_route_branch.route_id')
                    ->where('dt_route_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where('v_mst_route.active', '=', 'Y')
                    ->where('dt_route_branch.active', '=', 'Y')
                    ->orderBy('route_code')
                    ->distinct();
    }
}
