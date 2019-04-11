<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;

class MasterRegion extends Model
{
    protected $connection = 'operational';
    protected $table      = 'mst_region';
    protected $primaryKey = 'region_id';

    public $timestamps = false;

    public function regionCity()
    {
        return $this->hasMany(DetailRegionCity::class, 'region_id');
    }
}
