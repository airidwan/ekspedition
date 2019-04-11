<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class ResiTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/transaction/transaction-resi')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/transaction-resi')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/transaction/transaction-resi')->seePageIs('operational/transaction/transaction-resi');
    }

    public function testUrlAdd()
    {
        $this->visit('operational/transaction/transaction-resi/add')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/transaction-resi/add')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/transaction/transaction-resi/add')->seePageIs('operational/transaction/transaction-resi/add');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/transaction/transaction-resi/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/transaction-resi/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/transaction/transaction-resi/edit/0')->seeStatusCode(404);

        $model = TransactionResiHeader::first();
        if ($model !== null){
            $this->visit('operational/transaction/transaction-resi/edit/' . $model->resi_header_id)->seePageIs('operational/transaction/transaction-resi/edit/' . $model->resi_header_id);
        }
    }

    public function testUrlPrintExcelIndex()
    {
        $this->visit('operational/transaction/transaction-resi/print-excel-index')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/transaction-resi/print-excel-index')->seeStatusCode(403);
    }

    public function testUrlPrintPdfDetail()
    {
        $this->visit('operational/transaction/transaction-resi/print-pdf/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/transaction-resi/print-pdf/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/transaction/transaction-resi/print-pdf/0')->seeStatusCode(404);
    }

    public function testUrlPrintPdfTanpaBiaya()
    {
        $this->visit('operational/transaction/transaction-resi/print-pdf-tanpa-biaya/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/transaction-resi/print-pdf-tanpa-biaya/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/transaction/transaction-resi/print-pdf-tanpa-biaya/0')->seeStatusCode(404);
    }

    public function testUrlPrintPdfVoucher()
    {
        $this->visit('operational/transaction/transaction-resi/print-voucher/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/transaction-resi/print-voucher/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/transaction/transaction-resi/print-voucher/0')->seeStatusCode(404);
    }
}
