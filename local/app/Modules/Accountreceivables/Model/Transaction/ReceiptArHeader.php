<?php

namespace App\Modules\Accountreceivables\Model\Transaction;

use Illuminate\Database\Eloquent\Model;

class ReceiptArHeader extends Model
{
    const OPEN      = 'Open';
    const INPROCESS = 'Inprocess';
    const APPROVED  = 'Approved';

    const CASH     = 'Cash';
    const TRANSFER = 'Transfer';
    const CEK_GIRO = 'Cek / Giro';

    protected $connection = 'ar';
    protected $table      = 'receipt_ar_header';
    protected $primaryKey = 'receipt_ar_header_id';

    public $timestamps = false;

    public function lines()
    {
        return $this->hasMany(ReceiptArLine::class, 'receipt_ar_header_id');
    }

    public function invoiceArHeader()
    {
        return $this->belongsTo(InvoiceArHeader::class, 'inv_ar_header_id');
    }

    public function totalReceipt()
    {
        $totalReceipt = 0;
        foreach ($this->lines as $line) {
            $totalReceipt += $line->amount;
        }

        return $totalReceipt;
    }

    public function totalDiscount()
    {
        $totalDiscount = 0;
        foreach ($this->lines as $line) {
            $totalDiscount += $line->discount;
        }

        return $totalDiscount;
    }

    public function total()
    {
        return $this->totalReceipt() + $this->totalDiscount();
    }

    public function isLineExist($invoiceArLineId)
    {
        foreach ($this->lines as $line) {
            if ($line->inv_ar_line_id == $invoiceArLineId) {
                return true;
            }
        }

        return false;
    }

    public function getAmountLine($invoiceArLineId)
    {
        foreach ($this->lines as $line) {
            if ($line->inv_ar_line_id == $invoiceArLineId) {
                return $line->amount;
            }
        }

        return 0;
    }

    public function getDiscountLine($invoiceArLineId)
    {
        foreach ($this->lines as $line) {
            if ($line->inv_ar_line_id == $invoiceArLineId) {
                return $line->discount;
            }
        }

        return 0;
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

    public function isCash()
    {
        return $this->receipt_method == $this::CASH;
    }

    public function isTransfer()
    {
        return $this->receipt_method == $this::TRANSFER;
    }

    public function isCekGiro()
    {
        return $this->receipt_method == $this::CEK_GIRO;
    }
}
