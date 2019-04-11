<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader;

class ReceiptOrReturnDeliveryHeader extends Model
{
    protected $connection = 'operational';
    protected $table      = 'trans_receipt_or_return_delivery_header';
    protected $primaryKey = 'receipt_or_return_delivery_header_id';

    public $timestamps = false;

    public function lines()
    {
        return $this->hasMany(ReceiptOrReturnDeliveryLine::class, 'receipt_or_return_delivery_header_id');
    }

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrderHeader::class, 'delivery_order_header_id');
    }
}
