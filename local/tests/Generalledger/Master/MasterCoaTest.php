<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MasterCoaTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('general-ledger/master/master-coa')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('general-ledger/master/master-coa')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('general-ledger/master/master-coa')->seePageIs('general-ledger/master/master-coa');
    }

    public function testUrlAdd()
    {
        $this->visit('general-ledger/master/master-coa/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('general-ledger/master/master-coa/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('general-ledger/master/master-coa/add')->seePageIs('general-ledger/master/master-coa/add');
    }

    public function testUrlEdit()
    {
        $this->visit('general-ledger/master/master-coa/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('general-ledger/master/master-coa/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('general-ledger/master/master-coa/edit/0')->seeStatusCode(404);

        $model = MasterCoa::first();
        if ($model !== null) {
            $this->visit('general-ledger/master/master-coa/edit/' . $model->coa_id)->seePageIs('general-ledger/master/master-coa/edit/' . $model->coa_id);
        }
    }
}
