<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Transaction\ReceiptManifestHeader;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class ReceiptManifestTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/transaction/receipt-manifest')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/receipt-manifest')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/transaction/receipt-manifest')->seePageIs('operational/transaction/receipt-manifest');
    }

    public function testUrlAdd()
    {
        $this->visit('operational/transaction/receipt-manifest/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/receipt-manifest/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/transaction/receipt-manifest/add')->seePageIs('operational/transaction/receipt-manifest/add');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/transaction/receipt-manifest/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/receipt-manifest/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/transaction/receipt-manifest/edit/0')->seeStatusCode(404);

        $model = ReceiptManifestHeader::first();
        if ($model !== null){
            $this->visit('operational/transaction/receipt-manifest/edit/' . $model->manifest_receipt_header_id)->seePageIs('operational/transaction/receipt-manifest/edit/' . $model->manifest_receipt_header_id);
        }
    }

    public function testUrlPrintPdfDetail()
    {
        $this->visit('operational/transaction/receipt-manifest/print-pdf-detail/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/receipt-manifest/print-pdf-detail/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/transaction/receipt-manifest/print-pdf-detail/0')->seeStatusCode(404);
    }
}
