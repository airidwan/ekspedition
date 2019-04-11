<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;

class MasterRoute extends Model
{
    protected $connection = 'operational';
    protected $table      = 'mst_route';
    protected $primaryKey = 'route_id';

    public $timestamps = false;

    public function routeBranch()
    {
        return $this->hasMany(DetailRouteBranch::class, 'route_id');
    }

    public function details()
    {
        return $this->hasMany(DetailRoute::class, 'route_id');
    }

     public function cityStart()
    {
        return $this->belongsTo(MasterCity::class, 'city_start_id');
    }

    public function cityEnd()
    {
        return $this->belongsTo(MasterCity::class, 'city_end_id');
    }

    public function moneyTrip()
    {
        return $this->hasOne(MasterMoneyTrip::class, 'route_id');
    }
}
