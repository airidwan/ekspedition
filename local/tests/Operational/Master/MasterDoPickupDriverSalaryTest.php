<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterDoPickupDriverSalary;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MasterDoPickupDriverSalaryTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/master/master-do-pickup-driver-salary')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-do-pickup-driver-salary')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-do-pickup-driver-salary')->seePageIs('operational/master/master-do-pickup-driver-salary');
    }

    public function testUrlAdd()
    {
        $this->visit('operational/master/master-do-pickup-driver-salary/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-do-pickup-driver-salary/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-do-pickup-driver-salary/add')->seePageIs('operational/master/master-do-pickup-driver-salary/add');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/master/master-do-pickup-driver-salary/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-do-pickup-driver-salary/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/master/master-do-pickup-driver-salary/edit/0')->seeStatusCode(404);

        $model = MasterDoPickupDriverSalary::where('branch_id', '<>', \Session::get('currentBranch')->branch_id)->first();
        if ($model !== null){
            $this->get('operational/master/master-do-pickup-driver-salary/edit/'.$model->do_pickup_driver_salary_id)->seeStatusCode(403);
        }
        $model = MasterDoPickupDriverSalary::where('branch_id', '=', \Session::get('currentBranch')->branch_id)->first();
        if ($model !== null){
            $this->visit('operational/master/master-do-pickup-driver-salary/edit/' . $model->do_pickup_driver_salary_id)->seePageIs('operational/master/master-do-pickup-driver-salary/edit/' . $model->do_pickup_driver_salary_id);
        }
    }

    public function testUrlPrintExcelIndex()
    {
        $this->visit('operational/master/master-do-pickup-driver-salary/print-excel-index')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-do-pickup-driver-salary/print-excel-index')->seeStatusCode(403);
    }
}
