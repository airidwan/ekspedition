<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;

class MasterDriver extends Model
{
    const DRIVER    = 'DRIVER';
    const ASSISTANT = 'DRIVER ASSISTANT';

    const MONTHLY_EMPLOYEE = 'Monthly Employee';
    const TRIP_EMPLOYEE    = 'Trip Employee';
    const NON_EMPLOYEE     = 'Non Employee';

    protected $connection = 'operational';
    protected $table      = 'mst_driver';
    public $timestamps    = false;

    protected $primaryKey = 'driver_id';

    public function driverBranch()
    {
        return $this->hasMany(DetailDriverBranch::class, 'driver_id');
    }

    public function isMonthlyEmployee()
    {
        return $this->type == self::MONTHLY_EMPLOYEE;
    }

    public function isTripEmployee()
    {
        return $this->type == self::TRIP_EMPLOYEE;
    }

    public function isNonEmployee()
    {
        return $this->type == self::NON_EMPLOYEE;
    }
}
