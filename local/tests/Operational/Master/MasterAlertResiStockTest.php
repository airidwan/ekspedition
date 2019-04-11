<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterAlertResiStock;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MasterAlertResiStockTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/master/master-alert-resi-stock')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-alert-resi-stock')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-alert-resi-stock')->seePageIs('operational/master/master-alert-resi-stock');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/master/master-alert-resi-stock/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-alert-resi-stock/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/master/master-alert-resi-stock/edit/0')->seeStatusCode(404);

        $model = MasterAlertResiStock::where('branch_id', '=', \Session::get('currentBranch')->branch_id)->first();
        if ($model !== null){
            $this->visit('operational/master/master-alert-resi-stock/edit/' . $model->alert_resi_stock_id)->seePageIs('operational/master/master-alert-resi-stock/edit/' . $model->alert_resi_stock_id);
        }
    }

    public function testUrlPrintExcelDetail()
    {
        $this->visit('operational/master/master-alert-resi-stock/print-excel-index')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-alert-resi-stock/print-excel-index')->seeStatusCode(403);
    }
}
