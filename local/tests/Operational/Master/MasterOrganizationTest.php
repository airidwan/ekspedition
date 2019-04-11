<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class MasterOrganisasiTest extends TestCase
{
    public function testUrlIndex()
    {
        $this->visit('operational/master/master-organization')->seePageIs('/login');

        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->get('operational/master/master-organization')->seeStatusCode(403);

        $user->is_super_admin = true;
        $this->visit('operational/master/master-organization')->seePageIs('operational/master/master-organization');
    }
}
