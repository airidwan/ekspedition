<?php

namespace App\Modules\Inventory\Model\Master;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Master\MasterBranch;

class MasterWarehouse extends Model
{
    protected $connection = 'inventory';
    protected $table      = 'mst_warehouse';
    protected $primaryKey = 'wh_id';

    public $timestamps = false;

    public function branch()
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id');
    }
}
