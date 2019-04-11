<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class ManifestTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/transaction/manifest')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/manifest')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/transaction/manifest')->seePageIs('operational/transaction/manifest');
    }

    public function testUrlAdd()
    {
        $this->visit('operational/transaction/manifest/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/manifest/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/transaction/manifest/add')->seePageIs('operational/transaction/manifest/add');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/transaction/manifest/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/manifest/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/transaction/manifest/edit/0')->seeStatusCode(404);

        $model = ManifestHeader::first();
        if ($model !== null){
            $this->visit('operational/transaction/manifest/edit/' . $model->manifest_header_id)->seePageIs('operational/transaction/manifest/edit/' . $model->manifest_header_id);
        }
    }

    public function testUrlPrintExcelIndex()
    {
        $this->visit('operational/transaction/manifest/print-excel-index')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/manifest/print-excel-index')->seeStatusCode(403);
    }

    public function testUrlPrintPdfDetail()
    {
        $this->visit('operational/transaction/manifest/print-pdf-detail/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/manifest/print-pdf-detail/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/transaction/manifest/print-pdf-detail/0')->seeStatusCode(404);
    }

    public function testUrlPrintPdfReport()
    {
        $this->visit('operational/transaction/manifest/print-pdf-report/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/manifest/print-pdf-report/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/transaction/manifest/print-pdf-report/0')->seeStatusCode(404);
    }
}
