<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\User;
use App\Role;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    protected $userAccess;

    /**
     * Register any application authentication / authorization services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
     * @return void
     */
    public function boot(GateContract $gate)
    {
        $this->registerPolicies($gate);

        $gate->before(function ($user, $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        $gate->define('access', function ($user, $resource, $privilege) {
            if ($this->userAccess === null) {
                $this->buildUserAccess($user);
            }

            return isset($this->userAccess[$resource][$privilege]);
        });

        $gate->define('accessBranch', function ($user, $branchId) {
            return $this->checkAccessUserBranch($user, $branchId);
        });
    }

    protected function buildUserAccess(User $user)
    {
        if (!\Session::has('currentRole')) {
            return;
        }

        if (!$user->hasRole(\Session::get('currentRole')->id)) {
            return;
        }

        $role = Role::find(\Session::get('currentRole')->id);
        foreach ($role->accessControls()->get() as $accessControl) {
            if ($accessControl->access) {
                $this->userAccess[$accessControl->resource][$accessControl->privilege] = $accessControl->access;
            }
        }
    }

    protected function checkAccessUserBranch(User $user, $branchId)
    {
        if (\Session::get('currentBranch')->branch_id != $branchId) {
            return false;
        }

        return $user->hasBranch($branchId);
    }
}
