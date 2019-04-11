<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class ArriveManifestTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/transaction/arrive-manifest')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/arrive-manifest')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/transaction/arrive-manifest')->seePageIs('operational/transaction/arrive-manifest');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/transaction/arrive-manifest/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/arrive-manifest/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/transaction/arrive-manifest/edit/0')->seeStatusCode(404);

        $model = ManifestHeader::where('status', '=', ManifestHeader::OTR)->first();
        if ($model !== null){
            $this->visit('operational/transaction/arrive-manifest/edit/' . $model->manifest_header_id)->seePageIs('operational/transaction/arrive-manifest/edit/' . $model->manifest_header_id);
        }
    }

    public function testUrlPrintPdfDetail()
    {
        $this->visit('operational/transaction/arrive-manifest/print-pdf-checklist/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/arrive-manifest/print-pdf-checklist/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/transaction/arrive-manifest/print-pdf-checklist/0')->seeStatusCode(404);
    }
}
