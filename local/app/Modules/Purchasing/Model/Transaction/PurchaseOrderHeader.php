<?php

namespace App\Modules\Purchasing\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Purchasing\Model\Master\MasterTypePo;
use App\Modules\Payable\Model\Master\MasterVendor;
use App\Modules\Payable\Model\Transaction\DpInvoice;
use App\Modules\Payable\Model\Transaction\InvoiceLine;

class PurchaseOrderHeader extends Model
{
    const INCOMPLETE = 'Incomplete';
    const INPROCESS  = 'In Process';
    const APPROVED   = 'Approved';
    const CANCELED   = 'Canceled';
    const CLOSED     = 'Closed';

    const GOODS     = 'Goods';
    const SERVICE   = 'Service';

    protected $connection = 'purchasing';
    protected $table      = 'po_headers';
    protected $primaryKey = 'header_id';

    public $timestamps = false;

    public function purchaseOrderLines()
    {
        return $this->hasMany(PurchaseOrderLine::class, 'header_id');
    }

    public function branch()
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id');
    }

    public function vendor()
    {
        return $this->belongsTo(MasterVendor::class, 'supplier_id');
    }

    public function type()
    {
        return $this->belongsTo(MasterTypePo::class, 'type_id');
    }

    public function isApproved()
    {
        return $this->status == self::APPROVED;
    }

    public function getAmountDp()
    {
        foreach ($this->invoiceLine as $invoiceLine) {
            $invoiceHeader = $invoiceLine->header;
            if ($invoiceHeader !== null && $invoiceHeader->isDp() && $invoiceHeader->notCanceled() && ($invoiceHeader->isApproved() || $invoiceHeader->isClosed())) {
                return $invoiceLine->amount;
            }
        }
        return 0;
    }

    public function getAmountDpIncomplete()
    {
        foreach ($this->invoiceLine as $invoiceLine) {
            $invoiceHeader = $invoiceLine->header;
            if ($invoiceHeader !== null && $invoiceHeader->isDp() && $invoiceHeader->notCanceled() && $invoiceHeader->isIncomplete()) {
                return $invoiceLine->amount;
            }
        }
        return 0;
    }

    public function invoiceLine()
    {
        return $this->hasMany(InvoiceLine::class, 'po_header_id');
    }

    public function getTotalPrice()
    {
        $totalPrice = 0;
        foreach ($this->purchaseOrderLines()->get() as $line) {
            if ($line->active == 'Y') {
                $totalPrice += $line->total_price;
            }
        }
        return $totalPrice;
    }

    public function getTotalRemain()
    {
        return $this->total - $this->getTotalInvoiceWithoutTax();
    }

    public function getTotalRemainWithoutDp()
    {
        return $this->total - $this->getTotalInvoiceWithoutTax() + $this->getAmountDp() ;
    }

    public function getTotalInvoice(){
        $totalInvoice  = 0;
        foreach ($this->invoiceLine()->get() as $invoice) {
             $invoiceHeader = $invoice->header;
            if ($invoiceHeader !== null && $invoiceHeader->notCanceled()) {
                $totalInvoice += $invoice->totalAmount();
            }
        }
        return $totalInvoice;
    }

    public function getTotalInvoiceWithoutTax(){
        $totalInvoice  = 0;
        foreach ($this->invoiceLine()->get() as $invoice) {
             $invoiceHeader = $invoice->header;
            if ($invoiceHeader !== null && $invoiceHeader->notCanceled()) {
                $totalInvoice += $invoice->totalAmountWithoutTax();
            }
        }
        return $totalInvoice;
    }
}
