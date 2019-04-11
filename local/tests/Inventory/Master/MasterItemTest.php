<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Inventory\Model\Master\MasterItem;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MasterItemTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('inventory/master/master-item')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('inventory/master/master-item')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('inventory/master/master-item')->seePageIs('inventory/master/master-item');
    }

    public function testUrlAdd()
    {
        $this->visit('inventory/master/master-item/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('inventory/master/master-item/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('inventory/master/master-item/add')->seePageIs('inventory/master/master-item/add');
    }

    public function testUrlEdit()
    {
        $this->visit('inventory/master/master-item/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('inventory/master/master-item/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('inventory/master/master-item/edit/0')->seeStatusCode(404);

        $model = MasterItem::first();
        if ($model !== null) {
            $this->visit('inventory/master/master-item/edit/' . $model->item_id)->seePageIs('inventory/master/master-item/edit/' . $model->item_id);
        }
    }
}
