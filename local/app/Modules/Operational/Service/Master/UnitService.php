<?php

namespace App\Modules\Operational\Service\Master;

class UnitService
{
    public static function getActiveUnit()
    {
        return \DB::table('op.mst_shipping_price')
            ->select(
                'mst_shipping_price.*', 'mst_route.route_code', 'mst_commodity.commodity_name',
                'mst_route.city_start_id', 'mst_route.city_end_id', 'city_start.city_name as city_start_name', 'city_end.city_name as city_end_name'
            )
            ->join('op.mst_route', 'mst_shipping_price.route_id', '=', 'mst_route.route_id')
            ->join('op.mst_commodity', 'mst_shipping_price.commodity_id', '=', 'mst_commodity.commodity_id')
            ->leftJoin('op.mst_city as city_start', 'mst_route.city_start_id', '=', 'city_start.city_id')
            ->leftJoin('op.mst_city as city_end', 'mst_route.city_end_id', '=', 'city_end.city_id')
            ->leftJoin('op.dt_route_branch', 'mst_shipping_price.route_id', '=', 'dt_route_branch.route_id')
            ->where('dt_route_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
            ->orderBy('commodity_name', 'asc')
            ->orderBy('city_start_name', 'asc')
            ->orderBy('city_end_name', 'asc')
            ->distinct()
            ->get();
    }

    public static function getActiveRouteUnit($route_id, $search = '')
    {
        $query = \DB::table('op.mst_shipping_price')
            ->select(
                'mst_shipping_price.*', 'mst_route.route_code', 'mst_commodity.commodity_name',
                'mst_route.city_start_id', 'mst_route.city_end_id', 'city_start.city_name as city_start_name', 'city_end.city_name as city_end_name'
            )
            ->join('op.mst_route', 'mst_shipping_price.route_id', '=', 'mst_route.route_id')
            ->join('op.mst_commodity', 'mst_shipping_price.commodity_id', '=', 'mst_commodity.commodity_id')
            ->leftJoin('op.mst_city as city_start', 'mst_route.city_start_id', '=', 'city_start.city_id')
            ->leftJoin('op.mst_city as city_end', 'mst_route.city_end_id', '=', 'city_end.city_id')
            ->leftJoin('op.dt_route_branch', 'mst_shipping_price.route_id', '=', 'dt_route_branch.route_id')
            ->where('mst_shipping_price.route_id', '=', $route_id)
            ->where('dt_route_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
            ->orderBy('commodity_name', 'asc')
            ->orderBy('city_start_name', 'asc')
            ->orderBy('city_end_name', 'asc')
            ->distinct();

        if (!empty($search)) {
            $query->where(function($query) use ($search) {
                $query->where('mst_commodity.commodity_name', 'ilike', '%'.$search.'%')
                        ->orWhere('mst_shipping_price.description', 'ilike', '%'.$search.'%');
            });
        }

        return $query->get();
    }
}
