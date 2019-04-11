<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;

class MasterCommodity extends Model
{
    protected $connection = 'operational';
    protected $table      = 'mst_commodity';
    protected $primaryKey = 'commodity_id';

    public $timestamps = false;

    public function shippingPrice()
    {
        return $this->hasMany(MasterShippingPrice::class, 'commodity_id');
    }
}
