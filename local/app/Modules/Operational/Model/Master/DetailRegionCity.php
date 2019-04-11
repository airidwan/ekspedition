<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;

class DetailRegionCity extends Model
{
    protected $connection = 'operational';
    protected $table      = 'dt_region_city';
    protected $primaryKey = 'region_city_id';

    public $timestamps = false;

    public function region()
    {
        return $this->belongsTo(MasterRegion::class, 'region_id');
    }

    public function city()
    {
        return $this->belongsTo(MasterCity::class, 'city_id');
    }
}
