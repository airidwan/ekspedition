<?php

namespace App\Modules\Asset\Model\Transaction;

use Illuminate\Database\Eloquent\Model;

class RetirementAsset extends Model
{
    protected $connection = 'asset';
    protected $table      = 'retirement_asset';
    protected $primaryKey = 'retirement_asset_id';

    public $timestamps = false;

    const BROKEN = '1';
    const SALE   = '2';
}
