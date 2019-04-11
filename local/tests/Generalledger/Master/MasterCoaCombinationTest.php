<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Generalledger\Model\Master\MasterAccountCombination;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MasterCoaCombinationTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('general-ledger/master/master-coa-combination')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('general-ledger/master/master-coa-combination')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('general-ledger/master/master-coa-combination')->seePageIs('general-ledger/master/master-coa-combination');
    }

    public function testUrlAdd()
    {
        $this->visit('general-ledger/master/master-coa-combination/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());
        
        $this->get('general-ledger/master/master-coa-combination/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('general-ledger/master/master-coa-combination/add')->seePageIs('general-ledger/master/master-coa-combination/add');
    }

    public function testUrlEdit()
    {
        $this->visit('general-ledger/master/master-coa-combination/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());
        
        $this->get('general-ledger/master/master-coa-combination/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('general-ledger/master/master-coa-combination/edit/0')->seeStatusCode(404);

        $model = MasterAccountCombination::first();
        if ($model !== null) {
            $this->visit('general-ledger/master/master-coa-combination/edit/' . $model->account_combination_id)->seePageIs('general-ledger/master/master-coa-combination/edit/' . $model->account_combination_id);
        }
    }
}
