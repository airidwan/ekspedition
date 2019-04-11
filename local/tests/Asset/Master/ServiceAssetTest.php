<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Asset\Model\Transaction\ServiceAsset;
use App\Role;

class ServiceAssetTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('asset/transaction/service-asset')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('asset/transaction/service-asset')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('asset/transaction/service-asset')->seePageIs('asset/transaction/service-asset');
    }

    public function testUrlAdd()
    {
        $this->visit('asset/transaction/service-asset/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('asset/transaction/service-asset/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('asset/transaction/service-asset/add')->seePageIs('asset/transaction/service-asset/add');
    }

    public function testUrlEdit()
    {
        $this->visit('asset/transaction/service-asset/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('asset/transaction/service-asset/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('asset/transaction/service-asset/edit/0')->seeStatusCode(404);

        $model = ServiceAsset::first();
        if ($model !== null) {
            $this->visit('asset/transaction/service-asset/edit/' . $model->service_asset_id)->seePageIs('asset/transaction/service-asset/edit/' . $model->service_asset_id);
        }
    }
}
