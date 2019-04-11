<?php

namespace App\Modules\Inventory\Model\Master;

use Illuminate\Database\Eloquent\Model;

class MasterUom extends Model
{
    protected $connection = 'inventory';
    protected $table      = 'mst_uom';
    protected $primaryKey = 'uom_id';

    public $timestamps = false;
}
