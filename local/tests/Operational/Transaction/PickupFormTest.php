<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Transaction\PickupFormHeader;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class PickupFormTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/transaction/pickup-form')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/pickup-form')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/transaction/pickup-form')->seePageIs('operational/transaction/pickup-form');
    }

    public function testUrlAdd()
    {
        $this->visit('operational/transaction/pickup-form/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/pickup-form/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/transaction/pickup-form/add')->seePageIs('operational/transaction/pickup-form/add');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/transaction/pickup-form/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/pickup-form/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/transaction/pickup-form/edit/0')->seeStatusCode(404);

        $model = PickupFormHeader::first();
        if ($model !== null){
            $this->visit('operational/transaction/pickup-form/edit/' . $model->pickup_form_header_id)->seePageIs('operational/transaction/pickup-form/edit/' . $model->pickup_form_header_id);
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
