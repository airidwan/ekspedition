<?php

namespace App\Modules\Purchasing\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Inventory\Model\Master\MasterItem;
use App\Modules\Inventory\Model\Master\MasterWarehouse;
use App\Modules\Inventory\Model\Master\MasterUom;
use App\Modules\Asset\Model\Transaction\ServiceAsset;


class PurchaseOrderLine extends Model
{
    protected $connection = 'purchasing';
    protected $table      = 'po_lines';
    protected $primaryKey = 'line_id';

    public $timestamps = false;

    public function purchaseOrderHeader()
    {
        return $this->belongsTo(PurchaseOrderHeader::class, 'header_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(MasterWarehouse::class, 'wh_id');
    }

    public function uom()
    {
        return $this->belongsTo(MasterUom::class, 'uom_id');
    }

    public function item()
    {
        return $this->belongsTo(MasterItem::class, 'item_id');
    }

    public function service()
    {
        return $this->belongsTo(ServiceAsset::class, 'service_asset_id');
    }

}
