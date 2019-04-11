<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Marketing\Model\Transaction\PickupRequest;
use App\Role;

class PickupRequestTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('marketing/transaction/pickup-request')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('marketing/transaction/pickup-request')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('marketing/transaction/pickup-request')->seePageIs('marketing/transaction/pickup-request');
    }

    public function testUrlAdd()
    {
        $this->visit('marketing/transaction/pickup-request/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('marketing/transaction/pickup-request/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('marketing/transaction/pickup-request/add')->seePageIs('marketing/transaction/pickup-request/add');
    }

    public function testUrlEdit()
    {
        $this->visit('marketing/transaction/pickup-request/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('marketing/transaction/pickup-request/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('marketing/transaction/pickup-request/edit/0')->seeStatusCode(404);

        $model = PickupRequest::first();
        if ($model !== null) {
            $this->visit('marketing/transaction/pickup-request/edit/' . $model->pickup_request_id)->seePageIs('marketing/transaction/pickup-request/edit/' . $model->pickup_request_id);
        }
    }

    public function testUrlApprove()
    {
        $this->visit('marketing/transaction/pickup-request/approve/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('marketing/transaction/pickup-request/approve/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('marketing/transaction/pickup-request/approve/0')->seeStatusCode(404);

        $model = PickupRequest::first();
        if ($model !== null) {
            $this->visit('marketing/transaction/pickup-request/approve/' . $model->pickup_request_id)->seePageIs('marketing/transaction/pickup-request/approve/' . $model->pickup_request_id);
        }
    }

    public function testUrlPrintPdfDetail()
    {
        $this->visit('marketing/transaction/pickup-request/print-pdf-detail/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('marketing/transaction/pickup-request/print-pdf-detail/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('marketing/transaction/pickup-request/print-pdf-detail/0')->seeStatusCode(404);
    }
}
