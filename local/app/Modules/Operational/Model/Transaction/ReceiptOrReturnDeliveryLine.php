<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Transaction\DeliveryOrderLine;

class ReceiptOrReturnDeliveryLine extends Model
{
    const RECEIVED = 'Received';
    const RETURNED = 'Returned';

    protected $connection = 'operational';
    protected $table      = 'trans_receipt_or_return_delivery_line';
    protected $primaryKey = 'receipt_or_return_delivery_line_id';

    public $timestamps = false;

    public function header()
    {
        return $this->belongsTo(ReceiptOrReturnDeliveryHeader::class, 'receipt_or_return_delivery_header_id');
    }

    public function deliveryOrderLine()
    {
        return $this->belongsTo(DeliveryOrderLine::class, 'delivery_order_line_id');
    }

    public function isReceived()
    {
        return $this->status == self::RECEIVED;
    }

    public function isReturned()
    {
        return $this->status == self::RETURNED;
    }
}
