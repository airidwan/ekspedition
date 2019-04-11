<?php

namespace App\Modules\Inventory\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Purchasing\Model\Transaction\PurchaseOrderLine;
use App\Modules\Inventory\Model\Transaction\ReceiptHeader;
use App\Modules\Inventory\Model\Master\MasterItem;
use App\Modules\Inventory\Model\Master\MasterWarehouse;

class ReceiptLine extends Model
{
    protected $connection = 'inventory';
    protected $table      = 'trans_receipt_line';
    protected $primaryKey = 'receipt_line_id';

    public $timestamps = false;

    public function header()
    {
        return $this->belongsTo(ReceiptHeader::class, 'receipt_id');
    }

    public function item()
    {
        return $this->belongsTo(MasterItem::class, 'item_id');
    }

    public function wh()
    {
        return $this->belongsTo(MasterWarehouse::class, 'wh_id');
    }

    public function poLine()
    {
        return $this->belongsTo(PurchaseOrderLine::class, 'po_line_id');
    }
}
