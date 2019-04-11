<?php

namespace App\Modules\Accountreceivables\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Master\MasterCustomer;

class BatchInvoiceHeader extends Model
{
    const OPEN      = 'Open';
    const INPROCESS = 'Inprocess';
    const CLOSED    = 'Closed';
    const CANCELED  = 'Canceled';

    protected $connection = 'ar';
    protected $table      = 'batch_invoice_header';
    protected $primaryKey = 'batch_invoice_header_id';

    public $timestamps = false;

    public function lines()
    {
        return $this->hasMany(BatchInvoiceLine::class, 'batch_invoice_header_id');
    }

    public function customer()
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
    }

    public function totalAmount()
    {
        $totalAmount = 0;
        foreach ($this->lines as $line) {
            if (!empty($line->invoice)) {
                $totalAmount += $line->invoice->amount;
            }
        }

        return $totalAmount;
    }

    public function totalDiscount()
    {
        $totalDiscount = 0;
        foreach ($this->lines as $line) {
            if (!empty($line->invoice)) {
                $totalDiscount += $line->invoice->totalDiscount();
            }
        }

        return $totalDiscount;
    }

    public function total()
    {
        $total = 0;
        foreach ($this->lines as $line) {
            if (!empty($line->invoice)) {
                $total += $line->invoice->totalInvoice();
            }
        }

        return $total;
    }

    public function totalReceipt()
    {
        $totalReceipt = 0;
        foreach ($this->lines as $line) {
            if (!empty($line->invoice)) {
                $totalReceipt += $line->invoice->totalReceipt();
            }
        }

        return $totalReceipt;
    }

    public function remaining()
    {
        $remaining = 0;
        foreach ($this->lines as $line) {
            if (!empty($line->invoice)) {
                $remaining += $line->invoice->remaining();
            }
        }

        return $remaining;
    }

    public function getTotalDiscountInprocess()
    {
        $totalDiscountInprocess = 0;
        foreach($this->lines as $line) {
            if (!empty($line->invoice)) {
                $totalDiscountInprocess += $line->invoice->getDiscountInprocess();
            }
        }

        return $totalDiscountInprocess;
    }

    public function isOpen()
    {
        return $this->status == self::OPEN;
    }

    public function isInprocess()
    {
        return $this->status == self::INPROCESS;
    }

    public function isClosed()
    {
        return $this->status == self::CLOSED;
    }

    public function isCanceled()
    {
        return $this->status == self::CANCELED;
    }
}
