<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;

class MasterShippingPrice extends Model
{
    protected $connection = 'operational';
    protected $table = 'mst_shipping_price';
    protected $primaryKey = 'shipping_price_id';

    public $timestamps = false;

    public function details()
    {
        return $this->hasMany(DetailShippingPrice::class, 'shipping_price_id');
    }

    public function route()
    {
        return $this->belongsTo(MasterRoute::class, 'route_id');
    }

    public function commodity()
    {
        return $this->belongsTo(MasterCommodity::class, 'commodity_id');
    }
}
