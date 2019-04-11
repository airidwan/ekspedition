<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;

class MasterDeliveryArea extends Model
{
    protected $connection  = 'operational';
    protected $table       = 'mst_delivery_area';
    public $timestamps     = false;

    protected $primaryKey  = 'delivery_area_id';

    public function deliveryAreaBranch()
    {
        return $this->hasMany(DetailDeliveryAreaBranch::class, 'delivery_area_id');
    }
}
