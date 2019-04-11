<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetailUserBranch extends Model
{
    protected $connection  = 'adm';
    protected $table       = 'dt_user_branch';
    public $timestamps     = false;

    protected $primaryKey  = 'user_branch_id';
}
