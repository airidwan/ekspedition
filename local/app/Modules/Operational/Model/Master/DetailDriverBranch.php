<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;

class DetailDriverBranch extends Model
{
    protected $connection = 'operational';
    protected $table      = 'dt_driver_branch';
    public $timestamps    = false;

    protected $primaryKey = 'driver_branch_id';

    public function branch()
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id');
    }
}
