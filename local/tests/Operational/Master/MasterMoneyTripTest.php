<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterMoneyTrip;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MasterMoneyTripTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/master/master-money-trip')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-money-trip')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-money-trip')->seePageIs('operational/master/master-money-trip');
    }

    public function testUrlAdd()
    {
        $this->visit('operational/master/master-money-trip/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-money-trip/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-money-trip/add')->seePageIs('operational/master/master-money-trip/add');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/master/master-money-trip/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-money-trip/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/master/master-money-trip/edit/0')->seeStatusCode(404);

        $model = MasterMoneyTrip::first();
        if ($model !== null){
            $this->visit('operational/master/master-money-trip/edit/' . $model->money_trip_id)->seePageIs('operational/master/master-money-trip/edit/' . $model->money_trip_id);
        }
    }
}
