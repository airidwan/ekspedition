<?php

namespace App\Modules\Inventory\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Purchasing\Model\Transaction\PurchaseOrderHeader;

class ReceiptHeader extends Model
{
    protected $connection = 'inventory';
    protected $table      = 'trans_receipt_header';
    protected $primaryKey = 'receipt_id';

    public $timestamps = false;

    public function lines()
    {
        return $this->hasMany(ReceiptLine::class, 'receipt_id');
    }

    public function po()
    {
        return $this->belongsTo(PurchaseOrderHeader::class, 'po_header_id');
    }
}
