<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;

class DetailRoute extends Model
{
    protected $connection = 'operational';
    protected $table      = 'dt_route';
    protected $primaryKey = 'dt_route_id';

    public $timestamps = false;

    public function cityStart()
    {
        return $this->belongsTo(MasterCity::class, 'city_start_id');
    }

    public function cityEnd()
    {
        return $this->belongsTo(MasterCity::class, 'city_end_id');
    }
}
