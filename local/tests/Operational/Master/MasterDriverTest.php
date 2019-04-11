<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterDriver;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MasterDriverTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/master/master-driver')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-driver')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-driver')->seePageIs('operational/master/master-driver');
    }

    public function testUrlAdd()
    {
        $this->visit('operational/master/master-driver/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-driver/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-driver/add')->seePageIs('operational/master/master-driver/add');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/master/master-driver/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-driver/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/master/master-driver/edit/0')->seeStatusCode(404);

        $model = MasterDriver::first();
        if ($model !== null){
            $this->visit('operational/master/master-driver/edit/' . $model->driver_id)->seePageIs('operational/master/master-driver/edit/' . $model->driver_id);
        }
    }
}
