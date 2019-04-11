<?php

namespace App\Modules\Accountreceivables\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Master\MasterCustomer;

class InvoiceArHeader extends Model
{
    const OPEN      = 'Open';
    const INPROCESS = 'Inprocess';
    const APPROVED  = 'Approved';

    const INV_RESI   = 'Invoice Resi';
    const INV_PICKUP = 'Invoice Pickup';
    const INV_DO     = 'Invoice DO';

    protected $connection = 'ar';
    protected $table      = 'inv_ar_header';
    protected $primaryKey = 'inv_ar_header_id';

    public $timestamps = false;

    public function customer()
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
    }

    public function lines()
    {
        return $this->hasMany(InvoiceArLine::class, 'inv_ar_header_id');
    }

    public function line()
    {
        return $this->lines()->first();
    }

    public function receipts()
    {
        return $this->hasMany(ReceiptArHeader::class, 'inv_ar_header_id');
    }

    public function totalInvoice()
    {
        $totalInvoice = 0;
        foreach ($this->lines as $line) {
            $totalInvoice += $line->amount;
        }

        return $totalInvoice;
    }

    public function totalDiscount()
    {
        $totalDiscount = 0;
        foreach ($this->lines as $line) {
            $totalDiscount += $line->discount;
        }

        return $totalDiscount;
    }

    public function totalExtraPrice()
    {
        $totalExtraPrice = 0;
        foreach ($this->lines as $line) {
            $totalExtraPrice += $line->extra_price;
        }

        return $totalExtraPrice;
    }

    public function total()
    {
        return $this->totalInvoice() - $this->totalDiscount() + $this->totalExtraPrice();
    }

    public function totalReceipt($exceptReceiptHeaderId = 0)
    {
        $totalReceipt = 0;
        foreach ($this->receipts as $receipt) {
            if ($receipt->receipt_ar_header_id == $exceptReceiptHeaderId) {
                continue;
            }

            $totalReceipt += $receipt->totalReceipt();
        }

        return $totalReceipt;
    }

    public function remaining($exceptReceiptHeaderId = 0)
    {
        return $this->total() - $this->totalReceipt($exceptReceiptHeaderId);
    }

    public function totalAmountReceiptLine($invoiceArLineId, $exceptReceiptHeaderId)
    {
        $totalAmountReceiptLine = 0;

        foreach ($this->receipts as $receipt) {
            if ($receipt->receipt_ar_header_id == $exceptReceiptHeaderId) {
                continue;
            }

            foreach ($receipt->lines as $receiptLine) {
                if ($receiptLine->inv_ar_line_id == $invoiceArLineId) {
                    $totalAmountReceiptLine += $receiptLine->amount;
                }
            }
        }

        return $totalAmountReceiptLine;
    }

    public function totalDiscountReceiptLine($invoiceArLineId, $exceptReceiptHeaderId)
    {
        $totalDiscountReceiptLine = 0;

        foreach ($this->receipts as $receipt) {
            if ($receipt->receipt_ar_header_id == $exceptReceiptHeaderId) {
                continue;
            }

            foreach ($receipt->lines as $receiptLine) {
                if ($receiptLine->inv_ar_line_id == $invoiceArLineId) {
                    $totalDiscountReceiptLine += $receiptLine->discount;
                }
            }
        }

        return $totalDiscountReceiptLine;
    }

    public function isInvoiceResi()
    {
        return $this->type == self::INV_RESI;
    }

    public function isInvoiceDO()
    {
        return $this->type == self::INV_DO;
    }

    public function isInvoicePickup()
    {
        return $this->type == self::INV_PICKUP;
    }

    public function isOpen()
    {
        return $this->status == $this::OPEN;
    }

    public function isInprocess()
    {
        return $this->status == $this::INPROCESS;
    }

    public function isApproved()
    {
        return $this->status == $this::APPROVED;
    }
}
