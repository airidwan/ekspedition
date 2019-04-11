<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Dashboard;

class Dashboard extends Model
{
    protected $connection  = 'adm';
    protected $table      = 'dashboard';
    protected $primaryKey = 'dashboard_id';

    public $timestamps = false;
}
