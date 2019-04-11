<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;

class DetailTruckRent extends Model
{
    protected $connection = 'operational';
    protected $table      = 'dt_truck_rent';
    public $timestamps    = false;

    protected $primaryKey = 'truck_rent_id';

}
