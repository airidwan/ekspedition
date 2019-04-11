<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterDriverSalary;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MasterDriverSalaryTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/master/master-driver-salary')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-driver-salary')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-driver-salary')->seePageIs('operational/master/master-driver-salary');
    }

    public function testUrlAdd()
    {
        $this->visit('operational/master/master-driver-salary/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-driver-salary/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-driver-salary/add')->seePageIs('operational/master/master-driver-salary/add');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/master/master-driver-salary/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-driver-salary/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/master/master-driver-salary/edit/0')->seeStatusCode(404);

        $model = MasterDriverSalary::first();
        if ($model !== null){
            $this->visit('operational/master/master-driver-salary/edit/' . $model->driver_salary_id)->seePageIs('operational/master/master-driver-salary/edit/' . $model->driver_salary_id);
        }
    }
}
