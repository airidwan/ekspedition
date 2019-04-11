<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class UserTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('sys-admin/master/user')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('sys-admin/master/user')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('sys-admin/master/user')->seePageIs('sys-admin/master/user');
    }

    public function testUrlAdd()
    {
        $this->visit('sys-admin/master/user/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('sys-admin/master/user/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('sys-admin/master/user/add')->seePageIs('sys-admin/master/user/add');
    }

    public function testUrlEdit()
    {
        $this->visit('sys-admin/master/user/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('sys-admin/master/user/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('sys-admin/master/user/edit/0')->seeStatusCode(404);

        $user = App\User::first();
        if ($user !== null) {
            $this->visit('sys-admin/master/user/edit/' . $user->id)->seePageIs('sys-admin/master/user/edit/' . $user->id);
        }
    }
}
