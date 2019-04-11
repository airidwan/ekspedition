<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;

class MasterRentCar extends Model
{
    protected $connection = 'operational';
    protected $table      = 'mst_rent_car';
    public $timestamps     = false;
    protected $primaryKey = 'rent_car_id';
 
    public function truck()
    {
        return $this->belongsTo(MasterTruck::class, 'truck_id');
    }

    public function route()
    {
        return $this->belongsTo(MasterRoute::class, 'route_id');
    }

    public function subRoute()
    {
        return $this->belongsTo(DetailRoute::class, 'dt_route_id');
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
