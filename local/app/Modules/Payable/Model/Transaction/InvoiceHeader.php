<?php

namespace App\Modules\Payable\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Payable\Model\Master\MasterApType;
use App\Modules\Payable\Model\Transaction\InvoiceLine;
use App\Modules\Payable\Model\Transaction\Payment;
use App\Modules\Payable\Model\Master\MasterVendor;
use App\Modules\Operational\Model\Master\MasterDriver;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
use App\Modules\Accountreceivables\Model\Transaction\Receipt;

class InvoiceHeader extends Model
{
    protected $connection = 'payable';
    protected $table      = 'invoice_header';
    public $timestamps    = false;

    protected $primaryKey = 'header_id';

    const KAS_BON_EMPLOYEE      = '1';
    const KAS_BON_DRIVER        = '9';
    const PURCHASE_ORDER        = '2';
    const DRIVER_SALARY         = '3';
    const OTHER_VENDOR          = '4';
    const PURCHASE_ORDER_CREDIT = '5';
    const DO_MONEY_TRIP         = '6';
    const SERVICE               = '7';
    const DOWN_PAYMENT          = '8';
    const PICKUP_MONEY_TRIP     = '10';
    const DO_PARTNER            = '11';
    const OTHER_DRIVER          = '12';
    const MANIFEST_MONEY_TRIP   = '13';

    const VENDOR_TYPE = [self::KAS_BON_EMPLOYEE, self::PURCHASE_ORDER, self::OTHER_VENDOR, self::PURCHASE_ORDER_CREDIT, self::DOWN_PAYMENT, self::DO_PARTNER, self::SERVICE];
    const DRIVER_TYPE = [self::KAS_BON_DRIVER, self::OTHER_DRIVER, self::DRIVER_SALARY, self::DO_MONEY_TRIP, self::PICKUP_MONEY_TRIP, self::MANIFEST_MONEY_TRIP];

    const INCOMPLETE            = 'Incomplete';
    const INPROCESS             = 'Inprocess';
    const APPROVED              = 'Approved';
    const CLOSED                = 'Closed';
    const CANCELED              = 'Canceled';

    //Baru

    public function isIncomplete(){
        return $this->status == $this::INCOMPLETE;
    }

    public function isInprocess(){
        return $this->status == $this::INPROCESS;
    }

    public function isClosed(){
        return $this->status == $this::CLOSED;
    }

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrderHeader::class, 'do_header_id');
    }

    public function manifest()
    {
        return $this->belongsTo(ManifestHeader::class, 'manifest_header_id');
    }

    public function payment()
    {
        return $this->hasMany(Payment::class, 'invoice_header_id');
    }

    public function getTotalPayment()
    {
        $totalPayment = 0;
        foreach ($this->payment()->get() as $payment) {
            if ($payment->isApproved()) {
                $totalPayment += $payment->total_amount;
                $totalPayment += $payment->total_interest;
            }
        }
        return $totalPayment;
    }

    public function getTotalPaymentAmount()
    {
        $totalPayment = 0;
        foreach ($this->payment()->get() as $payment) {
            if ($payment->isApproved()) {
                $totalPayment += $payment->total_amount;
            }
        }
        return $totalPayment;
    }

    public function getTotalPaymentInterest()
    {
        $totalPayment = 0;
        foreach ($this->payment()->get() as $payment) {
            if ($payment->isApproved()) {
                $totalPayment += $payment->total_interest;
            }
        }
        return $totalPayment;
    }

    public function getTotalRemain()
    {
        return $this->getTotalInvoice() - $this->getTotalPayment();
    }

    public function getTotalRemainAmount()
    {
        return $this->getTotalAmount() + $this->getTotalTax() - $this->getTotalPaymentAmount();
    }

    public function getTotalRemainInterest()
    {
        return $this->getTotalInterest() - $this->getTotalPaymentInterest();
    }

    // Lama
    public function lines()
    {
        return $this->hasMany(InvoiceLine::class, 'header_id');
    }

    public function lineOne()
    {
        return $this->hasOne(InvoiceLine::class, 'header_id');
    }

    public function type()
    {
        return $this->belongsTo(MasterApType::class, 'type_id');
    }

    public function vendor()
    {
        return $this->belongsTo(MasterVendor::class, 'vendor_id');
    }

    public function driver()
    {
        return $this->belongsTo(MasterDriver::class, 'vendor_id');
    }

    public function branch()
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id');
    }

    public function getPoNumber()
    {
        $poNumber = '';
        foreach ($this->lines()->get() as $line) {
            $poNumber .= !empty($line->po) ? $line->po->po_number.' ' : ''; 
        }
        return $poNumber;
    }

    public function getTotalAmount()
    {
        $totalAmount = 0;
        foreach ($this->lines()->get() as $line) {
            $totalAmount += $line->amount;
        }
        return $totalAmount;
    }

    public function getTotalTax()
    {
        $totalTax = 0;
        foreach ($this->lines()->get() as $line) {
            $totalTax += ($line->amount * $line->tax / 100);
        }
        return $totalTax;
    }

    public function getTotalInterest()
    {
        $totalInterest = 0;
        foreach ($this->lines()->get() as $line) {
            $totalInterest += $line->interest_bank;
        }
        return $totalInterest;
    }

    public function getTotalInvoice()
    {
        return $this->getTotalAmount() + $this->getTotalTax() + $this->getTotalInterest();
    }

    public function getTradingKasbonCode()
    {
        if ($this->type_id == self::KAS_BON_EMPLOYEE) {
            return !empty($this->vendor) ? $this->vendor->vendor_code : '';
        } elseif ($this->type_id == self::KAS_BON_DRIVER) {
            return !empty($this->driver) ? $this->driver->driver_code : '';
        }
    }

    public function getTradingKasbonName()
    {
        if ($this->type_id == self::KAS_BON_EMPLOYEE) {
            return !empty($this->vendor) ? $this->vendor->vendor_name : '';
        } elseif ($this->type_id == self::KAS_BON_DRIVER) {
            return !empty($this->driver) ? $this->driver->driver_name : '';
        }
    }

    public function isDp()
    {
        return $this->type_id == self::DOWN_PAYMENT;
    }

    public function isApproved()
    {
        return $this->status == self::APPROVED;
    }

    public function notCanceled()
    {
        return $this->status != self::CANCELED;
    }

    public function receiptAr(){
        return $this->hasMany(Receipt::class, 'invoice_ap_header_id');
    }

    public function getTotalPaymentAr(){
        $totalPaymentAr = 0;
        foreach ($this->receiptAr as $receipt) {
            $totalPaymentAr += $receipt->amount;
        }
        return $totalPaymentAr;
    }

    public function getTotalRemainAr()
    {
        return $this->getTotalPayment() - $this->getTotalPaymentAr();
    }
}
