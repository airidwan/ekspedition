<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterRentCar;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MasterRentCarTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/master/master-rent-car')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-rent-car')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-rent-car')->seePageIs('operational/master/master-rent-car');
    }

    public function testUrlAdd()
    {
        $this->visit('operational/master/master-rent-car/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-rent-car/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-rent-car/add')->seePageIs('operational/master/master-rent-car/add');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/master/master-rent-car/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-rent-car/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/master/master-rent-car/edit/0')->seeStatusCode(404);

        $model = MasterRentCar::first();
        if ($model !== null){
            $this->visit('operational/master/master-rent-car/edit/' . $model->rent_car_id)->seePageIs('operational/master/master-rent-car/edit/' . $model->rent_car_id);
        }
    }
}
