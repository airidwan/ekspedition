<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;

class MasterCity extends Model
{
    protected $connection = 'operational';
    protected $table      = 'mst_city';
    public $timestamps     = false;

    protected $primaryKey = 'city_id';

    public function regionCity()
    {
        return $this->hasOne(DetailRegionCity::class, 'city_id');
    }

    public function region()
    {
        if ($this->regionCity !== null) {
            return $this->regionCity->region;
        }
    }
}
