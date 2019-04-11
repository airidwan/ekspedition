<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Purchasing\Model\Transaction\PurchaseOrderHeader;
use App\Role;

class PurchaseApproveTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('purchasing/transaction/purchase-approve')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('purchasing/transaction/purchase-approve')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('purchasing/transaction/purchase-approve')->seePageIs('purchasing/transaction/purchase-approve');
    }

    public function testUrlEdit()
    {
        $this->visit('purchasing/transaction/purchase-approve/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());   

        $this->get('purchasing/transaction/purchase-approve/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('purchasing/transaction/purchase-approve/edit/0')->seeStatusCode(404);

        $model = PurchaseOrderHeader::first();
        if ($model !== null) {
            $this->visit('purchasing/transaction/purchase-approve/edit/' . $model->header_id)->seePageIs('purchasing/transaction/purchase-approve/edit/' . $model->header_id);
        }
    }
}
