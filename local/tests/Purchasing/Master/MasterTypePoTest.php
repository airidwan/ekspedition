<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Purchasing\Model\Master\MasterTypePo;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MasterTypePoTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('purchasing/master/master-type-po')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('purchasing/master/master-type-po')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('purchasing/master/master-type-po')->seePageIs('purchasing/master/master-type-po');
    }

    public function testUrlAdd()
    {
        $this->visit('purchasing/master/master-type-po/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('purchasing/master/master-type-po/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('purchasing/master/master-type-po/add')->seePageIs('purchasing/master/master-type-po/add');
    }

    public function testUrlEdit()
    {
        $this->visit('purchasing/master/master-type-po/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('purchasing/master/master-type-po/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('purchasing/master/master-type-po/edit/0')->seeStatusCode(404);

        $model = MasterTypePo::first();
        if ($model !== null) {
            $this->visit('purchasing/master/master-type-po/edit/' . $model->type_id)->seePageIs('purchasing/master/master-type-po/edit/' . $model->type_id);
        }
    }
}
