<?php

namespace App\Modules\Inventory\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Inventory\Model\Transaction\BranchTransferLine;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Model\Master\MasterDriver;
use App\Modules\Operational\Model\Master\MasterTruck;

class BranchTransferHeader extends Model
{
    protected $connection = 'inventory';
    protected $table      = 'trans_bt_header';
    public $timestamps    = false;

    protected $primaryKey = 'bt_header_id';

    const INCOMPLETE     = 'Incomplete';
    const INPROCESS      = 'Inprocess';
    const COMPLETE       = 'Complete';
    const CANCELED       = 'Canceled';
    const CLOSED_WARNING = 'Closed Warning';

    public function lines()
    {
        return $this->hasMany(BranchTransferLine::class, 'bt_header_id');
    }

    public function branch()
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id');
    }

     public function driver()
    {
        return $this->belongsTo(MasterDriver::class, 'driver_id');
    }

    public function truck()
    {
        return $this->belongsTo(MasterTruck::class, 'truck_id');
    }

    public function isInprocess(){
        return $this->status == self::INPROCESS;
    }
}
