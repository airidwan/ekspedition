<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MasterBranchTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/master/master-branch')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-branch')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-branch')->seePageIs('operational/master/master-branch');
    }

    public function testUrlAdd()
    {
        $this->visit('operational/master/master-branch/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-branch/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-branch/add')->seePageIs('operational/master/master-branch/add');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/master/master-branch/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-branch/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/master/master-branch/edit/0')->seeStatusCode(404);

        $model = MasterBranch::first();
        if ($model !== null) {
            $this->visit('operational/master/master-branch/edit/' . $model->branch_id)->seePageIs('operational/master/master-branch/edit/' . $model->branch_id);
        }
    }
}
