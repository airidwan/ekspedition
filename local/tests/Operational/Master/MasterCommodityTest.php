<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterCommodity;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MasterCommodityTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/master/master-commodity')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-commodity')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-commodity')->seePageIs('operational/master/master-commodity');
    }

    public function testUrlAdd()
    {
        $this->visit('operational/master/master-commodity/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-commodity/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-commodity/add')->seePageIs('operational/master/master-commodity/add');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/master/master-commodity/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-commodity/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/master/master-commodity/edit/0')->seeStatusCode(404);

        $model = MasterCommodity::first();
        if ($model !== null){
            $this->visit('operational/master/master-commodity/edit/' . $model->commodity_id)->seePageIs('operational/master/master-commodity/edit/' . $model->commodity_id);
        }
    }
}
