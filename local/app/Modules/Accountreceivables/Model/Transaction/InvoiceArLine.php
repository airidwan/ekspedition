<?php

namespace App\Modules\Accountreceivables\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Transaction\DeliveryOrderLine;
use App\Modules\Marketing\Model\Transaction\PickupRequest;

class InvoiceArLine extends Model
{
    protected $connection = 'ar';
    protected $table      = 'inv_ar_line';
    protected $primaryKey = 'inv_ar_line_id';

    public $timestamps     = false;

    public function header()
    {
        return $this->belongsTo(InvoiceArHeader::class, 'inv_ar_header_id');
    }

    public function resi()
    {
        return $this->belongsTo(TransactionResiHeader::class, 'resi_header_id');
    }

    public function deliveryOrderLine()
    {
        return $this->belongsTo(DeliveryOrderLine::class, 'delivery_order_line_id');
    }

    public function pickupRequest()
    {
        return $this->belongsTo(PickupRequest::class, 'pickup_request_id');
    }

    public function totalAmount()
    {
        return $this->amount - $this->discount + $this->extra_price;
    }
}
