<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterCity;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MasterCityTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/master/master-city')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-city')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-city')->seePageIs('operational/master/master-city');
    }

    public function testUrlAdd()
    {
        $this->visit('operational/master/master-city/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-city/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-city/add')->seePageIs('operational/master/master-city/add');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/master/master-city/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-city/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/master/master-city/edit/0')->seeStatusCode(404);

        $model = MasterCity::first();
        if ($model !== null) {
            $this->visit('operational/master/master-city/edit/' . $model->city_id)->seePageIs('operational/master/master-city/edit/' . $model->city_id);
        }
    }
}
