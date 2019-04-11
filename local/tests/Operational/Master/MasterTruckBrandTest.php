<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;
use App\MasterLookupValues;

class MasterTruckBrandTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/master/master-truck-brand')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-truck-brand')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-truck-brand')->seePageIs('operational/master/master-truck-brand');
    }

    public function testUrlAdd()
    {
        $this->visit('operational/master/master-truck-brand/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-truck-brand/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-truck-brand/add')->seePageIs('operational/master/master-truck-brand/add');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/master/master-truck-brand/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-truck-brand/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/master/master-truck-brand/edit/0')->seeStatusCode(404);

        $model = MasterLookupValues::where('lookup_type', '=', MasterLookupValues::MERK_KENDARAAN)->first();
        if ($model !== null){
            $this->visit('operational/master/master-truck-brand/edit/' . $model->id)->seePageIs('operational/master/master-truck-brand/edit/' . $model->id);
        }
    }
}
