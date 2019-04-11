<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;

class MasterDeliveryAreaMoneyTrip extends Model
{
    protected $connection = 'operational';
    protected $table      = 'mst_delivery_area_money_trip';
    public    $timestamps     = false;

    protected $primaryKey = 'delivery_area_money_trip_id';

    public function deliveryArea()
    {
        return $this->belongsTo(MasterDeliveryArea::class, 'delivery_area_id');
    }

    public function branch()
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id');
    }
}
