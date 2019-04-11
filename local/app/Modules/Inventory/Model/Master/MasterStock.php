<?php

namespace App\Modules\Inventory\Model\Master;

use Illuminate\Database\Eloquent\Model;

class MasterStock extends Model
{
    protected $connection = 'inventory';
    protected $table      = 'mst_stock_item';
    protected $primaryKey = 'stock_item_id';

    public $timestamps = false;

    public function item()
    {
        return $this->belongsTo(MasterItem::class, 'item_id');
    }
}
