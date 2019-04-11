<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterCustomer;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MasterCustomerTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/master/master-customer')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-customer')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-customer')->seePageIs('operational/master/master-customer');
    }

    public function testUrlAdd()
    {
        $this->visit('operational/master/master-customer/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-customer/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-customer/add')->seePageIs('operational/master/master-customer/add');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/master/master-customer/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-customer/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/master/master-customer/edit/0')->seeStatusCode(404);

        $model = MasterCustomer::first();
        if ($model !== null){
            $this->visit('operational/master/master-customer/edit/' . $model->customer_id)->seePageIs('operational/master/master-customer/edit/' . $model->customer_id);
        }
    }
}
