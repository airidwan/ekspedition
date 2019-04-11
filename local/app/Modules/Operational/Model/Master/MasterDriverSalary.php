<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;

class MasterDriverSalary extends Model
{
    protected $connection = 'operational';
    protected $table      = 'mst_driver_salary';
    public $timestamps    = false;

    protected $primaryKey = 'driver_salary_id';

    public function route()
    {
        return $this->belongsTo(MasterRoute::class, 'route_id');
    }

    public function subRoute()
    {
        return $this->belongsTo(DetailRoute::class, 'no_sub_route_tariff');
    }

    public function startCity(){
        $subRoute = $this->subRoute()->first();
        if ($subRoute !== null) {
            return $subRoute->cityStart();
        }

        $route = $this->route()->first();
        if ($route !== null) {
            return $route->cityStart();
        }
    }

    public function endCity(){
        $subRoute = $this->subRoute()->first();
        if ($subRoute !== null) {
            return $subRoute->cityEnd();
        }

        $route = $this->route()->first();
        if ($route !== null) {
            return $route->cityEnd();
        }
    }

    public function routeCode(){
        $subRoute = $this->subRoute()->first();
        if ($subRoute !== null) {
            return $subRoute->dt_route_code;
        }

        $route = $this->route()->first();
        if ($route !== null) {
            return $route->route_code;
        }
    }
}
