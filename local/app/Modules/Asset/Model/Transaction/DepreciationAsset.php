<?php

namespace App\Modules\Asset\Model\Transaction;

use Illuminate\Database\Eloquent\Model;

class DepreciationAsset extends Model
{
    protected $connection = 'asset';
    protected $table      = 'depreciation_asset';
    protected $primaryKey = 'depreciation_asset_id';

    public $timestamps = false;
}
