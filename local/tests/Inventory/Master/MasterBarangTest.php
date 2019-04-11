<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MasterBarangTest extends TestCase
{
    public function testIndex()
    {
        $this->visit('/inventory/master/master-barang');
        $this->visit('/inventory/master/master-barang')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('/inventory/master/master-barang')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('/inventory/master/master-barang')->seePageIs('/inventory/master/master-barang');
    }
}
