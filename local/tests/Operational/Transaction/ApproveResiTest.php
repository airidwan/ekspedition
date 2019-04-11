<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class ApproveResiTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/transaction/approve-nego-resi')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/approve-nego-resi')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/transaction/approve-nego-resi')->seePageIs('operational/transaction/approve-nego-resi');
    }

    public function testUrlEdit()
    {
        $this->visit('operational/transaction/approve-nego-resi/edit/0')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/transaction/approve-nego-resi/edit/0')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->get('operational/transaction/approve-nego-resi/edit/0')->seeStatusCode(404);

        $model = TransactionResiHeader::where('status', '=', TransactionResiHeader::INPROCESS)->first();
        if ($model !== null){
            $this->visit('operational/transaction/approve-nego-resi/edit/' . $model->resi_header_id)->seePageIs('operational/transaction/approve-nego-resi/edit/' . $model->resi_header_id);
        }
    }
}
