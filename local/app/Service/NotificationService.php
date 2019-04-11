<?php

namespace App\Service;

use App\Notification;
use App\Modules\Operational\Model\Master\MasterBranch;

class NotificationService
{
    public static function getNotifications($userId)
    {
        $notifications = \DB::table('notification')
                                ->where('user_id', '=', $userId)
                                ->whereNull('read_at')
                                ->orderBy('created_at', 'desc')
                                ->take(10)->get();

        $objNotifications = [];
        foreach ($notifications as $notification) {
            $objNotifications[] = Notification::find($notification->notification_id);
        }

        return $objNotifications;
    }

    public static function getCountNotification($userId)
    {
        return \DB::table('notification')
                    ->where('user_id', '=', $userId)
                    ->whereNull('read_at')
                    ->count();
    }

    public static function getUserNotification(array $role){
    	return \DB::table('adm.user_role_branch')
            ->select('adm.users.id','adm.user_role.role_id')
            ->join('adm.user_role', 'user_role.user_role_id', '=', 'user_role_branch.user_role_id')
            ->join('adm.users', 'users.id', '=', 'user_role.user_id')
            ->join('adm.roles', 'roles.id', '=', 'user_role.role_id')
            ->where('user_role_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
            ->whereIn('user_role.role_id', $role)->get();
    }

    public static function getUserNotificationAllBranch(array $role){
        return \DB::table('adm.user_role_branch')
            ->select('adm.users.id','adm.user_role.role_id', 'user_role_branch.branch_id')
            ->join('adm.user_role', 'user_role.user_role_id', '=', 'user_role_branch.user_role_id')
            ->join('adm.users', 'users.id', '=', 'user_role.user_id')
            ->join('adm.roles', 'roles.id', '=', 'user_role.role_id')
            ->whereIn('user_role.role_id', $role)->get();
    }

    public static function getUserNotificationSpesificBranch(array $role, $branch){
        return \DB::table('adm.user_role_branch')
            ->select('adm.users.id','adm.user_role.role_id', 'user_role_branch.branch_id')
            ->join('adm.user_role', 'user_role.user_role_id', '=', 'user_role_branch.user_role_id')
            ->join('adm.users', 'users.id', '=', 'user_role.user_id')
            ->join('adm.roles', 'roles.id', '=', 'user_role.role_id')
            ->where('user_role_branch.branch_id', '=', $branch)
            ->whereIn('user_role.role_id', $role)->get();
    }

    public static function createNotification($category, $message, $url, $roles)
    {
        $userRoles = self::getUserNotification($roles);
        foreach ($userRoles as $userRole) {
            $notif             = new Notification();
            $notif->category   = $category;
            $notif->message    = $message;
            $notif->url        = $url;
            $notif->branch_id  = \Session::get('currentBranch')->branch_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $userRole->id;
            $notif->role_id    = $userRole->role_id;

            $notif->save();
        }
    }

    public static function createSpesificBranchNotification($category, $message, $url, $roles, $branch)
    {
        $userRoles = self::getUserNotificationSpesificBranch($roles, $branch);
        foreach ($userRoles as $userRole) {
            $notif             = new Notification();
            $notif->category   = $category;
            $notif->message    = $message;
            $notif->url        = $url;
            $notif->branch_id  = $branch;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $userRole->id;
            $notif->role_id    = $userRole->role_id;

            $notif->save();
        }
    }

    public static function createAllBranchNotification($category, $message, $url, $roles)
    {
        $userRoles = self::getUserNotificationAllBranch($roles);
        foreach ($userRoles as $userRole) {
            $notif             = new Notification();
            $notif->category   = $category;
            $notif->message    = $message;
            $notif->url        = $url;
            $notif->branch_id  = $userRole->branch_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $userRole->id;
            $notif->role_id    = $userRole->role_id;

            $notif->save();
        }
    }
}
