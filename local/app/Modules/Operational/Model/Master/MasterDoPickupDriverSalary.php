<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;

class MasterDoPickupDriverSalary extends Model
{
    protected $connection = 'operational';
    protected $table      = 'mst_do_pickup_driver_salary';
    public $timestamps    = false;

    protected $primaryKey = 'do_pickup_driver_salary_id';

    public function deliveryArea()
    {
        return $this->belongsTo(MasterDeliveryArea::class, 'delivery_area_id');
    }
}
