<?php

namespace App\Service;
use App\User;

class CurrentBranchService
{
    public static function initCurrentBranch()
    {
        $branch = null;
        if(!empty(\Auth::user()->last_branch_id)){
            $branch = self::getQueryUserRoleBranchDefinition(\Auth::user()->last_branch_id)->first();
        }
        
        if ($branch === null) {
            $branch = self::getQueryUserRoleBranch()->first();
        }

        if ($branch !== null) {
            \Session::put('currentBranch', $branch);
        }
    }

    public static function changeCurrentBranch($branchId)
    {
        $user   = User::find(\Auth::user()->id);
        $user->last_branch_id = $branchId;
        $user->save();

        $branch = self::getQueryUserRoleBranch()->where('mst_branch.branch_id', '=', $branchId)->first();

        if ($branch !== null) {
            \Session::put('currentBranch', $branch);
        } else {
            self::initCurrentBranch();
        }
    }

    protected static function getQueryUserRoleBranch()
    {
        $role = \Session::get('currentRole');

        return \DB::table('op.mst_branch')
                    ->select('mst_branch.*')
                    ->join('adm.user_role_branch', 'mst_branch.branch_id', '=', 'user_role_branch.branch_id')
                    ->join('adm.user_role', 'user_role_branch.user_role_id', '=', 'user_role.user_role_id')
                    ->where('user_role.user_id', '=', \Auth::user()->id)
                    ->where('user_role.role_id', '=', $role->id)
                    ->where('mst_branch.active', '=', 'Y')
                    ->orderBy('mst_branch.branch_name', 'asc');
    }

    protected static function getQueryUserRoleBranchDefinition($branchId)
    {
        $role = \Session::get('currentRole');

        return \DB::table('op.mst_branch')
                    ->select('mst_branch.*')
                    ->join('adm.user_role_branch', 'mst_branch.branch_id', '=', 'user_role_branch.branch_id')
                    ->join('adm.user_role', 'user_role_branch.user_role_id', '=', 'user_role.user_role_id')
                    ->where('mst_branch.branch_id', '=', $branchId)
                    ->where('user_role.user_id', '=', \Auth::user()->id)
                    ->where('user_role.role_id', '=', $role->id)
                    ->where('mst_branch.active', '=', 'Y')
                    ->orderBy('mst_branch.branch_name', 'asc');
    }
}
