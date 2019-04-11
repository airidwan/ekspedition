<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Accountreceivables\Model\Transaction\Invoice;
use App\Modules\Payable\Model\Transaction\InvoiceLine;

class DeliveryOrderLine extends Model
{
    protected $connection = 'operational';
    protected $table      = 'trans_delivery_order_line';
    protected $primaryKey = 'delivery_order_line_id';

    public $timestamps = false;

    public function resi()
    {
        return $this->belongsTo(TransactionResiHeader::class, 'resi_header_id');
    }

    public function header()
    {
        return $this->belongsTo(DeliveryOrderHeader::class, 'delivery_order_header_id');
    }

    public function receiptReturn()
    {
        return $this->hasOne(ReceiptOrReturnDeliveryLine::class, 'delivery_order_line_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'delivery_order_line_id');
    }

    public function invoiceLine()
    {
        return $this->hasOne(InvoiceLine::class, 'do_line_id');
    }
}
