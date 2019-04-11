<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Asset\Model\Transaction\AdditionAsset;
use App\Role;

class AdditionAssetTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('asset/transaction/addition-asset')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('asset/transaction/addition-asset')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('asset/transaction/addition-asset')->seePageIs('asset/transaction/addition-asset');
    }

    public function testUrlAdd()
    {
        $this->visit('asset/transaction/addition-asset/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('asset/transaction/addition-asset/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('asset/transaction/addition-asset/add')->seePageIs('asset/transaction/addition-asset/add');
    }

    public function testUrlEdit()
    {
        $this->visit('asset/transaction/addition-asset/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('asset/transaction/addition-asset/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('asset/transaction/addition-asset/edit/0')->seeStatusCode(404);

        $model = AdditionAsset::first();
        if ($model !== null) {
            $this->visit('asset/transaction/addition-asset/edit/' . $model->asset_id)->seePageIs('asset/transaction/addition-asset/edit/' . $model->asset_id);
        }
    }
}
