<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Inventory\Model\Master\MasterUom;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MasterUomTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('inventory/master/master-uom')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('inventory/master/master-uom')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('inventory/master/master-uom')->seePageIs('inventory/master/master-uom');
    }

    public function testUrlAdd()
    {
        $this->visit('inventory/master/master-uom/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('inventory/master/master-uom/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('inventory/master/master-uom/add')->seePageIs('inventory/master/master-uom/add');
    }

    public function testUrlEdit()
    {
        $this->visit('inventory/master/master-uom/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('inventory/master/master-uom/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('inventory/master/master-uom/edit/0')->seeStatusCode(404);

        $model = MasterUom::first();
        if ($model !== null) {
            $this->visit('inventory/master/master-uom/edit/' . $model->uom_id)->seePageIs('inventory/master/master-uom/edit/' . $model->uom_id);
        }
    }
}
