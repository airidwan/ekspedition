<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;

class MasterMoneyTrip extends Model
{
    protected $connection = 'operational';
    protected $table      = 'mst_money_trip';
    public    $timestamps     = false;

    protected $primaryKey = 'money_trip_id';

    public function rute()
    {
        return $this->belongsTo(MasterRoute::class, 'route_id');
    }

    public function subRute()
    {
        return $this->belongsTo(DetailRoute::class, 'dt_route_id');
    }

    public function startCity(){
        $subRute = $this->subRute()->first();
        if ($subRute !== null) {
            return $subRute->cityStart();
        }

        $rute = $this->rute()->first();
        if ($rute !== null) {
            return $rute->cityStart();
        }
    }

    public function endCity(){
        $subRute = $this->subRute()->first();
        if ($subRute !== null) {
            return $subRute->cityEnd();
        }

        $rute = $this->rute()->first();
        if ($rute !== null) {
            return $rute->cityEnd();
        }
    }

    public function routeCode(){
        $subRute = $this->subRute()->first();
        if ($subRute !== null) {
            return $subRute->dt_route_code;
        }

        $rute = $this->rute()->first();
        if ($rute !== null) {
            return $rute->route_code;
        }
    }
}
