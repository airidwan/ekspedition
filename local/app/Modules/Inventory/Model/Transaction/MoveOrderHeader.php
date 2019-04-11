<?php

namespace App\Modules\Inventory\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Inventory\Model\Transaction\MoveOrderLine;
use App\Modules\Payable\Model\Master\MasterVendor;
use App\Modules\Operational\Model\Master\MasterDriver;
use App\Modules\Operational\Model\Master\MasterTruck;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Asset\Model\Transaction\ServiceAsset;

class MoveOrderHeader extends Model
{
    protected $connection = 'inventory';
    protected $table      = 'trans_mo_header';
    public $timestamps    = false;

    protected $primaryKey = 'mo_header_id';

    const INCOMPLETE = 'Incomplete';
    const CANCELED   = 'Canceled';
    const COMPLETE   = 'Complete';

    const STANDART   = 'Standart';
    const SERVICE    = 'Service';

    public function lines()
    {
        return $this->hasMany(MoveOrderLine::class, 'mo_header_id');
    }

    public function service()
    {
        return $this->belongsTo(ServiceAsset::class, 'service_asset_id');
    }

    public function vendor()
    {
        return $this->belongsTo(MasterVendor::class, 'vendor_id');
    }

    public function driver()
    {
        return $this->belongsTo(MasterDriver::class, 'driver_id');
    }

    public function truck()
    {
        return $this->belongsTo(MasterTruck::class, 'truck_id');
    }

    public function branch()
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id');
    }
}
