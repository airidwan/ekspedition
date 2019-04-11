<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Transaction\ReturnManifestHeader;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class ReturnManifestTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/transaction/return-manifest')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/return-manifest')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/transaction/return-manifest')->seePageIs('operational/transaction/return-manifest');
    }

    public function testUrlAdd()
    {
        $this->visit('operational/transaction/return-manifest/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/return-manifest/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/transaction/return-manifest/add')->seePageIs('operational/transaction/return-manifest/add');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/transaction/return-manifest/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/return-manifest/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/transaction/return-manifest/edit/0')->seeStatusCode(404);

        $model = ReturnManifestHeader::first();
        if ($model !== null){
            $this->visit('operational/transaction/return-manifest/edit/' . $model->manifest_return_header_id)->seePageIs('operational/transaction/return-manifest/edit/' . $model->manifest_return_header_id);
        }
    }
}
