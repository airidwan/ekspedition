<?php

namespace App\Modules\Asset\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Inventory\Model\Master\MasterItem;
use App\Modules\Asset\Model\Master\AssetCategory;
use App\Modules\Inventory\Model\Transaction\ReceiptHeader;
use App\Modules\Inventory\Model\Transaction\ReceiptLine;
use App\Modules\Operational\Model\Master\MasterTruck;

class AdditionAsset extends Model
{
    protected $connection = 'asset';
    protected $table      = 'addition_asset';
    protected $primaryKey = 'asset_id';

    public $timestamps = false;

    const ACTIVE      = '1';
    const RETIREMENT  = '2';
    const NONACTIVE   = '3';
    const ONSERVICE   = '4';
    const SOLD        = '5';

    const EXIST = 'Exist';
    const PO    = 'Purchase Order';
    
    const DEFAULT_STATUS     = 'Non Active';

    public function item()
    {
        return $this->belongsTo(MasterItem::class, 'item_id');
    }

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function receipt()
    {
        return $this->belongsTo(ReceiptHeader::class, 'receipt_id');
    }

    public function receiptLine()
    {
        return $this->belongsTo(ReceiptLine::class, 'receipt_line_id');
    }

    public function assigment()
    {
        return $this->hasOne(AssigmentAsset::class, 'asset_id');
    }

    public function depreciation()
    {
        return $this->hasOne(DepreciationAsset::class, 'asset_id');
    }

    public function retirement()
    {
        return $this->hasOne(RetirementAsset::class, 'asset_id');
    }

    public function truck()
    {
        return $this->hasOne(MasterTruck::class, 'asset_id');
    }

    public function isRetirement(){
        return $this->status_id == self::RETIREMENT;
    }

    public function isSold(){
        return $this->status_id == self::SOLD;
    }

    public function isService(){
        return $this->status_id == self::ONSERVICE;
    }
}
