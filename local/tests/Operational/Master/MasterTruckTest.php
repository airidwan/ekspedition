<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterTruck;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MasterTruckTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/master/master-truck')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-truck')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-truck')->seePageIs('operational/master/master-truck');
    }

     public function testUrlAdd()
    {
        $this->visit('operational/master/master-truck/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-truck/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-truck/add')->seePageIs('operational/master/master-truck/add');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/master/master-truck/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-truck/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/master/master-truck/edit/0')->seeStatusCode(404);

        $model = MasterTruck::first();
        if ($model !== null){
            $this->visit('operational/master/master-truck/edit/' . $model->truck_id)->seePageIs('operational/master/master-truck/edit/' . $model->truck_id);
        }
    }
}
