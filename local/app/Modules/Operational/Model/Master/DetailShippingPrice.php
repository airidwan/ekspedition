<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;

class DetailShippingPrice extends Model
{
    protected $connection = 'operational';
    protected $table = 'dt_shipping_price';
    protected $primaryKey = 'dt_shipping_price_id';

    public $timestamps = false;

    public function shippingPrice()
    {
        return $this->belongsTo(MasterShippingPrice::class, 'shipping_price_id');
    }

    public function detailRoute()
    {
        return $this->belongsTo(DetailRoute::class, 'dt_route_id');
    }
}
