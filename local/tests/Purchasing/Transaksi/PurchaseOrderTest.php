<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Purchasing\Model\Transaction\PurchaseOrderHeader;
use App\Role;

class PurchaseOrderTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('purchasing/transaction/purchase-order')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('purchasing/transaction/purchase-order')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('purchasing/transaction/purchase-order')->seePageIs('purchasing/transaction/purchase-order');
    }

    public function testUrlAdd()
    {
        $this->visit('purchasing/transaction/purchase-order/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('purchasing/transaction/purchase-order/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('purchasing/transaction/purchase-order/add')->seePageIs('purchasing/transaction/purchase-order/add');
    }

    public function testUrlEdit()
    {
        $this->visit('purchasing/transaction/purchase-order/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('purchasing/transaction/purchase-order/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('purchasing/transaction/purchase-order/edit/0')->seeStatusCode(404);

        $model = PurchaseOrderHeader::first();
        if ($model !== null) {
            $this->visit('purchasing/transaction/purchase-order/edit/' . $model->header_id)->seePageIs('purchasing/transaction/purchase-order/edit/' . $model->header_id);
        }
    }
}
