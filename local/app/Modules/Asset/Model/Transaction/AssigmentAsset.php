<?php

namespace App\Modules\Asset\Model\Transaction;

use Illuminate\Database\Eloquent\Model;

class AssigmentAsset extends Model
{
    protected $connection = 'asset';
    protected $table      = 'assigment_asset';
    protected $primaryKey = 'assigment_asset_id';

    public $timestamps = false;
}
