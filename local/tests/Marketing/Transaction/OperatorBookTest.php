<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Marketing\Model\Transaction\OperatorBook;
use App\Role;

class OperatorBookTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('marketing/transaction/operator-book')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('marketing/transaction/operator-book')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('marketing/transaction/operator-book')->seePageIs('marketing/transaction/operator-book');
    }

    public function testUrlAdd()
    {
        $this->visit('marketing/transaction/operator-book/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('marketing/transaction/operator-book/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('marketing/transaction/operator-book/add')->seePageIs('marketing/transaction/operator-book/add');
    }

    public function testUrlEdit()
    {
        $this->visit('marketing/transaction/operator-book/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('marketing/transaction/operator-book/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('marketing/transaction/operator-book/edit/0')->seeStatusCode(404);

        $model = OperatorBook::first();
        if ($model !== null) {
            $this->visit('marketing/transaction/operator-book/edit/' . $model->obook_id)->seePageIs('marketing/transaction/operator-book/edit/' . $model->obook_id);
        }
    }
}
