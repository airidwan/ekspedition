<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;

class ChangePasswordTest extends TestCase
{
    public function testUrl()
    {
        $this->visit('/change-password')->seePageIs('/login');
    }

    public function testLoggedUser()
    {
        $user = new App\User(['name' => 'John']);
        $this->be($user);

        Session::start();
        Session::set('currentRole', Role::first());
        Session::set('currentBranch', MasterBranch::first());

        $this->visit('/change-password')->seePageIs('/change-password');
    }
}
