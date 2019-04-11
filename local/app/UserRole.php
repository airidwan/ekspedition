<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $connection = 'adm';
    protected $table      = 'user_role';
    protected $primaryKey = 'user_role_id';

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function userRoleBranch()
    {
        return $this->hasMany(UserRoleBranch::class, 'user_role_id');
    }
}
