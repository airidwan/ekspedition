<?php 

namespace App\Modules\Operational\Service\Master;

class CityService
{
    public static function getActiveCity()
    {
        return \DB::table('op.v_mst_city')->where('active', '=', 'Y')
                    ->orderBy('city_name')->get();
    }

    public static function getAllCity()
    {
        return \DB::table('op.v_mst_city')
                    ->orderBy('city_name')->get();
    }
}