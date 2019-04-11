<?php

namespace App\Modules\Inventory\Model\Transaction;

use Illuminate\Database\Eloquent\Model;

class ReceiptBranchTransferHeader extends Model
{
    protected $connection = 'inventory';
    protected $table      = 'trans_receipt_bt_header';
    protected $primaryKey = 'receipt_bt_header_id';

    public $timestamps = false;

    public function lines()
    {
        return $this->hasMany(ReceiptBranchTransferLine::class, 'receipt_bt_header_id');
    }

    public function branchTransferHeader()
    {
        return $this->belongsTo(BranchTransferHeader::class, 'bt_header_id');
    }
}
