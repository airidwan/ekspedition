<?php

namespace App\Service;
use App\Role;
use App\User;

class CurrentRoleService
{
    public static function initCurrentRole()
    {
        $role = null;
        if(!empty(\Auth::user()->last_role_id)){
            $role = Role::find(\Auth::user()->last_role_id);
        }
        
        if ($role === null){
            $role = self::getQueryUserRole()->first();
        }

        if ($role !== null) {
            \Session::put('currentRole', $role);
        }
    }

    public static function changeCurrentRole($roleId)
    {
        $user   = User::find(\Auth::user()->id);
        $user->last_role_id = $roleId;
        $user->save();
        
        $role = self::getQueryUserRole()->where('roles.id', '=', $roleId)->first();

        if ($role !== null) {
            \Session::put('currentRole', $role);
        }
    }

    protected static function getQueryUserRole()
    {
        return \DB::table('adm.roles')
                    ->select('roles.*')
                    ->join('adm.user_role', 'roles.id', '=', 'user_role.role_id')
                    ->where('user_role.user_id', '=', \Auth::user()->id)
                    ->where('roles.active', '=', 'Y')
                    ->orderBy('roles.name', 'asc');
    }
}
