<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Payable\Model\Master\MasterVendor;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MasterVendorTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('payable/master/master-vendor')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('payable/master/master-vendor')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('payable/master/master-vendor')->seePageIs('payable/master/master-vendor');
    }

    public function testUrlAdd()
    {
        $this->visit('payable/master/master-vendor/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('payable/master/master-vendor/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('payable/master/master-vendor/add')->seePageIs('payable/master/master-vendor/add');
    }

    public function testUrlEdit()
    {
        $this->visit('payable/master/master-vendor/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('payable/master/master-vendor/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('payable/master/master-vendor/edit/0')->seeStatusCode(404);

        $model = MasterVendor::first();
        if ($model !== null){
            $this->visit('payable/master/master-vendor/edit/' . $model->vendor_id)->seePageIs('payable/master/master-vendor/edit/' . $model->vendor_id);
        }
    }
}
