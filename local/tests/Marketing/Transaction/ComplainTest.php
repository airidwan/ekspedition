<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Marketing\Model\Transaction\Complain;
use App\Role;

class ComplainTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('marketing/transaction/complain')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('marketing/transaction/complain')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('marketing/transaction/complain')->seePageIs('marketing/transaction/complain');
    }

    public function testUrlAdd()
    {
        $this->visit('marketing/transaction/complain/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('marketing/transaction/complain/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('marketing/transaction/complain/add')->seePageIs('marketing/transaction/complain/add');
    }

    public function testUrlEdit()
    {
        $this->visit('marketing/transaction/complain/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('marketing/transaction/complain/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('marketing/transaction/complain/edit/0')->seeStatusCode(404);

        $model = Complain::first();
        if ($model !== null) {
            $this->visit('marketing/transaction/complain/edit/' . $model->complain_id)->seePageIs('marketing/transaction/complain/edit/' . $model->complain_id);
        }
    }
}
