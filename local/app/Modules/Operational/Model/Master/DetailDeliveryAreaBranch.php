<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Master\MasterBranch;

class DetailDeliveryAreaBranch extends Model
{
    protected $connection  = 'operational';
    protected $table       = 'dt_delivery_area_branch';
    public $timestamps     = false;

    protected $primaryKey  = 'delivery_area_branch_id';

    public function branch()
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id');
    }
}
