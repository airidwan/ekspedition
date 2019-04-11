<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterDeliveryAreaMoneyTrip;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MasterDeliveryAreaMoneyTripTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/master/master-delivery-area-money-trip')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-delivery-area-money-trip')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-delivery-area-money-trip')->seePageIs('operational/master/master-delivery-area-money-trip');
    }

    public function testUrlAdd()
    {
        $this->visit('operational/master/master-delivery-area-money-trip/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-delivery-area-money-trip/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-delivery-area-money-trip/add')->seePageIs('operational/master/master-delivery-area-money-trip/add');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/master/master-delivery-area-money-trip/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-delivery-area-money-trip/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/master/master-delivery-area-money-trip/edit/0')->seeStatusCode(404);

        $model = MasterDeliveryAreaMoneyTrip::first();
        if ($model !== null){
            $this->visit('operational/master/master-delivery-area-money-trip/edit/' . $model->delivery_area_money_trip_id)->seePageIs('operational/master/master-delivery-area-money-trip/edit/' . $model->delivery_area_money_trip_id);
        }
    }

    public function testUrlPrintExcelDetail()
    {
        $this->visit('operational/master/master-delivery-area-money-trip/print-excel-index')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-delivery-area-money-trip/print-excel-index')->seeStatusCode(403);
    }
}
