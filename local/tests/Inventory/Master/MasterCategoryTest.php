<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Inventory\Model\Master\MasterCategory;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MasterCategoryTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('inventory/master/master-category')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('inventory/master/master-category')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('inventory/master/master-category')->seePageIs('inventory/master/master-category');
    }

    public function testUrlAdd()
    {
        $this->visit('inventory/master/master-category/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('inventory/master/master-category/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('inventory/master/master-category/add')->seePageIs('inventory/master/master-category/add');
    }

    public function testUrlEdit()
    {
        $this->visit('inventory/master/master-category/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('inventory/master/master-category/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('inventory/master/master-category/edit/0')->seeStatusCode(404);

        $model = MasterCategory::first();
        if ($model !== null) {
            $this->visit('inventory/master/master-category/edit/' . $model->category_id)->seePageIs('inventory/master/master-category/edit/' . $model->category_id);
        }
    }
}
