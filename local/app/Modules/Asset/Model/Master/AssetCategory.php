<?php

namespace App\Modules\Asset\Model\Master;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Generalledger\Model\Master\MasterCoa;

class AssetCategory extends Model
{
    protected $connection = 'asset';
    protected $table      = 'asset_category';
    protected $primaryKey = 'asset_category_id';

    public $timestamps = false;

    const KENDARAAN    = 1; 
    const RUMAH_TANGGA = 2; 

    public function clearing(){
        return $this->belongsTo(MasterCoa::class, 'clearing_coa_id');
    }

    public function depreciation(){
        return $this->belongsTo(MasterCoa::class, 'depreciation_coa_id');
    }

    public function acumulated(){
        return $this->belongsTo(MasterCoa::class, 'acumulated_coa_id');
    }
}
