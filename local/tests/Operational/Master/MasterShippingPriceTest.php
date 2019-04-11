<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterShippingPrice;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MasterShippingPriceTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/master/master-shipping-price')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-shipping-price')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-shipping-price')->seePageIs('operational/master/master-shipping-price');
    }

    public function testUrlAdd()
    {
        $this->visit('operational/master/master-shipping-price/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-shipping-price/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-shipping-price/add')->seePageIs('operational/master/master-shipping-price/add');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/master/master-shipping-price/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-shipping-price/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/master/master-shipping-price/edit/0')->seeStatusCode(404);

        $model = MasterShippingPrice::first();
        if ($model !== null){
            $this->visit('operational/master/master-shipping-price/edit/' . $model->shipping_price_id)->seePageIs('operational/master/master-shipping-price/edit/' . $model->shipping_price_id);
        }
    }

    public function testUrlPrintExcelDetail()
    {
        $this->visit('operational/master/master-shipping-price/print-excel-index')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-shipping-price/print-excel-index')->seeStatusCode(403);
    }
}
