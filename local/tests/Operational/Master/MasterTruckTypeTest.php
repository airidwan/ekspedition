<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;
use App\MasterLookupValues;

class MasterTruckTypeTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/master/master-truck-type')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-truck-type')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-truck-type')->seePageIs('operational/master/master-truck-type');
    }

    public function testUrlAdd()
    {
        $this->visit('operational/master/master-truck-type/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-truck-type/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-truck-type/add')->seePageIs('operational/master/master-truck-type/add');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/master/master-truck-type/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-truck-type/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/master/master-truck-type/edit/0')->seeStatusCode(404);

        $model = MasterLookupValues::where('lookup_type', '=', MasterLookupValues::TIPE_KENDARAAN)->first();
        if ($model !== null){
            $this->visit('operational/master/master-truck-type/edit/' . $model->id)->seePageIs('operational/master/master-truck-type/edit/' . $model->id);
        }
    }
}
