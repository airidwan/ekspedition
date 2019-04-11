<?php

namespace App\Modules\Inventory\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Purchasing\Model\Transaction\PurchaseOrderHeader;
use App\Modules\Inventory\Model\Transaction\ReceiptHeader;

class ReturnLine extends Model
{
    protected $connection = 'inventory';
    protected $table      = 'trans_return_line';
    protected $primaryKey = 'return_line_id';

    public $timestamps = false;

    public function po()
    {
        return $this->belongsTo(PurchaseOrderHeader::class, 'po_header_id');
    }

    public function receipt()
    {
        return $this->belongsTo(ReceiptHeader::class, 'receipt_id');
    }

    
}
