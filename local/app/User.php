<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $connection = 'adm';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function userBranch()
    {
        return $this->hasMany(DetailUserBranch::class, 'user_id');
    }

    public function isSuperAdmin()
    {
        return $this->is_super_admin;
    }

    public function userRole()
    {
        return $this->hasMany(UserRole::class, 'user_id');
    }

    public function hasRole($roleId)
    {
        foreach ($this->userRole as $userRole) {
            $role = $userRole->role;
            if ($role !== null && $role->active == 'Y' && $role->id == $roleId) {
                return true;
            }
        }

        return false;
    }

    public function hasBranch($branchId)
    {
        foreach ($this->userRole as $userRole) {
            foreach ($userRole->userRoleBranch as $userRoleBranch) {
                $branch = $userRoleBranch->branch;
                if ($branch !== null && $branch->active == 'Y' && $branch->branch_id == $branchId) {
                    return true;
                }
            }
        }

        return false;
    }
}
