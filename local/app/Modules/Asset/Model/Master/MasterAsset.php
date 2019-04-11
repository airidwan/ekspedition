<?php

namespace App\Modules\Asset\Model\Master;

use Illuminate\Database\Eloquent\Model;

class MasterAsset extends Model
{
    protected $connection = 'asset';
    protected $table      = 'mask_add_asset';
    protected $primaryKey = 'mask_add_asset_id';

    public $timestamps = false;
}
