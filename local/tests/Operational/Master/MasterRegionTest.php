<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterRegion;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MasterRegionTest extends TestCase
{
    public function testUrlIndex()
    {

        $this->visit('operational/master/master-region')->seePageIs('/login');
        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-region')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-region')->seePageIs('operational/master/master-region');
    }

    public function testUrlAdd()
    {
        $this->visit('operational/master/master-region/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-region/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-region/add')->seePageIs('operational/master/master-region/add');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/master/master-region/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-region/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/master/master-region/edit/0')->seeStatusCode(404);

        $model = MasterRegion::first();
        if ($model !== null) {
            $this->visit('operational/master/master-region/edit/' . $model->region_id)->seePageIs('operational/master/master-region/edit/' . $model->region_id);
        }
    }
}
