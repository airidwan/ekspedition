<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class PermintaanBarangTest extends TestCase
{
    public function testIndex()
    {
        $this->visit('/inventory/transaction/move-order');
        $this->visit('/inventory/transaction/move-order')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('/inventory/transaction/move-order')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('/inventory/transaction/move-order')->seePageIs('/inventory/transaction/move-order');
    }
}
