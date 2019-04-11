<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class DummyTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('sys-admin/master/dummy')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('sys-admin/master/dummy')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('sys-admin/master/dummy')->seePageIs('sys-admin/master/dummy');
    }

    public function testUrlAdd()
    {
        $this->visit('sys-admin/master/dummy/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('sys-admin/master/dummy/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('sys-admin/master/dummy/add')->seePageIs('sys-admin/master/dummy/add');
    }

    public function testUrlEdit()
    {
        $this->visit('sys-admin/master/dummy/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('sys-admin/master/dummy/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('sys-admin/master/dummy/edit/0')->seeStatusCode(404);

        $header = App\DummyHeader::first();
        if ($header !== null) {
            $this->visit('sys-admin/master/dummy/edit/' . $header->id)->seePageIs('sys-admin/master/dummy/edit/' . $header->id);
        }
    }
}
