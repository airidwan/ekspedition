<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class RoleTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('sys-admin/master/role')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('sys-admin/master/role')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('sys-admin/master/role')->seePageIs('sys-admin/master/role');
    }

    public function testUrlAdd()
    {
        $this->visit('sys-admin/master/role/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('sys-admin/master/role/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('sys-admin/master/role/add')->seePageIs('sys-admin/master/role/add');
    }

    public function testUrlEdit()
    {
        $this->visit('sys-admin/master/role/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('sys-admin/master/role/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('sys-admin/master/role/edit/0')->seeStatusCode(404);

        $role = App\Role::first();
        if ($role !== null) {
            $this->visit('sys-admin/master/role/edit/' . $role->id)->seePageIs('sys-admin/master/role/edit/' . $role->id);
        }
    }
}
