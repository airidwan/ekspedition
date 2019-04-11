<?php

namespace App\Modules\Inventory\Model\Master;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Inventory\Model\Master\MasterUom;
use App\Modules\Inventory\Model\Master\MasterCategory;


class MasterItem extends Model
{
    protected $connection = 'inventory';
    protected $table      = 'mst_item';
    protected $primaryKey = 'item_id';

    public $timestamps = false;

    public function uom()
    {
        return $this->belongsTo(MasterUom::class, 'uom_id');
    }

    public function category()
    {
        return $this->belongsTo(MasterCategory::class, 'category_id');
    }
}
