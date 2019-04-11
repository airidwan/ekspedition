<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;

class DetailRouteBranch extends Model
{
    protected $connection  = 'operational';
    protected $table       = 'dt_route_branch';
    public $timestamps     = false;

    protected $primaryKey = 'route_branch_id';

    public function branch()
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id');
    }
}
