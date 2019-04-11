<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class ShipmentManifestTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/transaction/shipment-manifest')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/shipment-manifest')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/transaction/shipment-manifest')->seePageIs('operational/transaction/shipment-manifest');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/transaction/shipment-manifest/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/shipment-manifest/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/transaction/shipment-manifest/edit/0')->seeStatusCode(404);

        $model = ManifestHeader::where('status', '=', ManifestHeader::APPROVED)->first();
        if ($model !== null){
            $this->visit('operational/transaction/shipment-manifest/edit/' . $model->manifest_header_id)->seePageIs('operational/transaction/shipment-manifest/edit/' . $model->manifest_header_id);
        }
    }
}
