<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;

class DetailTruckBranch extends Model
{
    protected $connection = 'operational';
    protected $table      = 'dt_truck_branch';
    public $timestamps    = false;

    protected $primaryKey = 'truck_branch_id';

    public function branch()
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id');
    }
}
