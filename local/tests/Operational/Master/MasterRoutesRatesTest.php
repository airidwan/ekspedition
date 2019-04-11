<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterRoute;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MasterRouteTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/master/master-routes-rates')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-routes-rates')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-routes-rates')->seePageIs('operational/master/master-routes-rates');
    }

    public function testUrlAdd()
    {
        $this->visit('operational/master/master-routes-rates/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-routes-rates/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-routes-rates/add')->seePageIs('operational/master/master-routes-rates/add');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/master/master-routes-rates/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-routes-rates/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/master/master-routes-rates/edit/0')->seeStatusCode(404);

        $model = MasterRoute::first();
        if ($model !== null){
            $this->visit('operational/master/master-routes-rates/edit/' . $model->route_id)->seePageIs('operational/master/master-routes-rates/edit/' . $model->route_id);
        }
    }
}
