<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Master\MasterBranch;

class UserRoleBranch extends Model
{
    protected $connection = 'adm';
    protected $table      = 'user_role_branch';
    protected $primaryKey = 'user_role_branch_id';

    public $timestamps = false;

    public function branch()
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id');
    }
}
