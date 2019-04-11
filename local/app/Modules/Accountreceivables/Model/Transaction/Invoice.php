<?php

namespace App\Modules\Accountreceivables\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Master\MasterCustomer;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Transaction\DeliveryOrderLine;
use App\Modules\Marketing\Model\Transaction\PickupRequest;
use App\Modules\Accountreceivables\Model\Transaction\BatchInvoiceLine;
use App\Modules\Generalledger\Model\Master\MasterCoa;

class Invoice extends Model
{
    const OPEN            = 'Open';
    const INPROCESS       = 'Inprocess';
    const INPROCESS_BATCH = 'Inprocess Batch';
    const APPROVED        = 'Approved';
    const CLOSED          = 'Closed';
    const CANCELED        = 'Canceled';

    const INV_RESI       = 'Invoice Resi';
    const INV_PICKUP     = 'Invoice Pickup';
    const INV_DO         = 'Invoice DO';
    const INV_EXTRA_COST = 'Invoice Extra Cost';

    protected $connection = 'ar';
    protected $table      = 'invoice';
    protected $primaryKey = 'invoice_id';

    public $timestamps = false;

    public function customer()
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
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

    public function coa()
    {
        return $this->belongsTo(MasterCoa::class, 'coa_id');
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class, 'invoice_id');
    }

    public function batchInvoiceLine()
    {
        return $this->hasMany(BatchInvoiceLine::class, 'invoice_id');
    }

    public function canAddDiscount()
    {
        return $this->current_discount <= 3;
    }

    public function getDiscountInprocess()
    {
        if (!$this->isInprocess() && !$this->isInprocessBatch()) {
            return 0;
        }

        if ($this->current_discount == 1) {
            return $this->discount_1;
        } elseif ($this->current_discount == 2) {
            return $this->discount_2;
        } elseif ($this->current_discount == 3) {
            return $this->discount_3;
        } else {
            return 0;
        }
    }

    public function clearDiscountInprocess()
    {
        if ($this->current_discount == 1) {
            $this->discount_1 = null;
        } elseif ($this->current_discount == 2) {
            $this->discount_2 = null;
        } elseif ($this->current_discount == 3) {
            $this->discount_3 = null;
        }
    }

    public function totalDiscount($approved = true)
    {
        if (!$approved) {
            return $this->discount_1 + $this->discount_2 + $this->discount_3;
        }

        if ($this->current_discount == 2) {
            return $this->discount_1;
        } elseif ($this->current_discount == 3) {
            return $this->discount_1 + $this->discount_2;
        } elseif ($this->current_discount > 3) {
            return $this->discount_1 + $this->discount_2 + $this->discount_3;
        }
    }

    public function totalInvoice($approved = true)
    {
        return intval($this->amount - $this->totalDiscount($approved) + $this->extra_price);
    }

    public function totalReceipt($exceptReceiptId = 0)
    {
        $totalReceipt = 0;
        foreach ($this->receipts as $receipt) {
            if ($receipt->receipt_id == $exceptReceiptId) {
                continue;
            }

            $totalReceipt += $receipt->amount;
        }

        return $totalReceipt;
    }

    public function remaining($exceptReceiptId = 0)
    {
        return $this->totalInvoice() - $this->totalReceipt($exceptReceiptId);
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

    public function isInvoiceExtraCost()
    {
        return $this->type == self::INV_EXTRA_COST;
    }

    public function isOpen()
    {
        return $this->status == $this::OPEN;
    }

    public function isInprocess()
    {
        return $this->status == $this::INPROCESS;
    }

    public function isInprocessBatch()
    {
        return $this->status == $this::INPROCESS_BATCH;
    }

    public function isApproved()
    {
        return $this->status == $this::APPROVED;
    }

    public function isClosed()
    {
        return $this->status == $this::CLOSED;
    }

    public function isCanceled()
    {
        return $this->status == $this::CANCELED;
    }
}
