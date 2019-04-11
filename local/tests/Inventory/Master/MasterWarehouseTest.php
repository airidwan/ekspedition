<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Inventory\Model\Master\MasterWarehouse;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MasterWarehouseTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('inventory/master/master-warehouse')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('inventory/master/master-warehouse')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('inventory/master/master-warehouse')->seePageIs('inventory/master/master-warehouse');
    }

    public function testUrlAdd()
    {
        $this->visit('inventory/master/master-warehouse/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('inventory/master/master-warehouse/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('inventory/master/master-warehouse/add')->seePageIs('inventory/master/master-warehouse/add');
    }

    public function testUrlEdit()
    {
        $this->visit('inventory/master/master-warehouse/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('inventory/master/master-warehouse/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('inventory/master/master-warehouse/edit/0')->seeStatusCode(404);

        $model = MasterWarehouse::first();
        if ($model !== null) {
            $this->visit('inventory/master/master-warehouse/edit/' . $model->wh_id)->seePageIs('inventory/master/master-warehouse/edit/' . $model->wh_id);
        }
    }
}
