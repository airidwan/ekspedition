<?php

namespace App\Modules\Asset\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Master\MasterTruck;

class ServiceAsset extends Model
{
    protected $connection = 'asset';
    protected $table      = 'service_asset';
    protected $primaryKey = 'service_asset_id';

    public $timestamps = false;

    const ASSET = 'ASSET';
    const TRUCK_MONTHLY = 'TRUCK MONTHLY';

    public function addAsset()
    {
        return $this->belongsTo(AdditionAsset::class, 'asset_id');
    }

    public function truck()
    {
        return $this->belongsTo(MasterTruck::class, 'truck_id');
    }
}
