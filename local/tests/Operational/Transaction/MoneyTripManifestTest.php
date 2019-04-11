<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MoneyTripManifestTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/transaction/money-trip-manifest')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/money-trip-manifest')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/transaction/money-trip-manifest')->seePageIs('operational/transaction/money-trip-manifest');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/transaction/money-trip-manifest/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/money-trip-manifest/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/transaction/money-trip-manifest/edit/0')->seeStatusCode(404);

        $model = ManifestHeader::where('status', '=', ManifestHeader::APPROVED)->first();
        if ($model !== null){
            $this->visit('operational/transaction/money-trip-manifest/edit/' . $model->manifest_header_id)->seePageIs('operational/transaction/money-trip-manifest/edit/' . $model->manifest_header_id);
        }
    }
}
