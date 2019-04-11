<?php

namespace App\Modules\Inventory\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Inventory\Model\Transaction\ReceiptBranchTransferHeader;

class ReceiptBranchTransferLine extends Model
{
    protected $connection = 'inventory';
    protected $table      = 'trans_receipt_bt_line';
    protected $primaryKey = 'receipt_bt_line_id';

    public $timestamps = false;

    public function header()
    {
        return $this->belongsTo(ReceiptBranchTransferHeader::class, 'receipt_bt_header_id');
    }

    public function item()
    {
        return $this->belongsTo(MasterItem::class, 'item_id');
    }

    public function wh()
    {
        return $this->belongsTo(MasterWarehouse::class, 'wh_id');
    }

    public function branchTransferLine()
    {
        return $this->belongsTo(BranchTransferLine::class, 'bt_line_id');
    }
}
