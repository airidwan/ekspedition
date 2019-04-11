<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Master\MasterDeliveryArea;
use App\Modules\Operational\Model\Master\MasterDriver;
use App\Modules\Operational\Model\Master\MasterTruck;
use App\Modules\Operational\Model\Transaction\DeliveryOrderLine;
use App\Modules\Payable\Model\Master\MasterVendor;
use App\Modules\Payable\Model\Transaction\InvoiceHeader;
use App\Modules\Payable\Model\Transaction\InvoiceLine;

class DeliveryOrderHeader extends Model
{
    protected $connection = 'operational';
    protected $table      = 'trans_delivery_order_header';
    protected $primaryKey = 'delivery_order_header_id';

    public $timestamps = false;

    const OPEN               = 'Open';
    const REQUEST_APPROVAL   = 'Request Approval';
    const APPROVED           = 'Approved';
    const CONFIRMED          = 'Confirmed';
    const ON_THE_ROAD        = 'Shipped';
    const CLOSED             = 'Closed';
    const CANCELED           = 'Canceled';

    const REGULAR    = 'Regular';
    const TRANSITION = 'Transition';
 
    public function driver()
    {
        return $this->belongsTo(MasterDriver::class, 'driver_id');
    }

    public function assistant()
    {
        return $this->belongsTo(MasterDriver::class, 'assistant_id');
    }

    public function partner()
    {
        return $this->belongsTo(MasterVendor::class, 'partner_id');
    }

    public function truck()
    {
        return $this->belongsTo(MasterTruck::class, 'truck_id');
    }

    public function draftDo()
    {
        return $this->belongsTo(DraftDeliveryOrderHeader::class, 'draft_delivery_order_header_id');
    }

    public function lines()
    {
        return $this->hasMany(DeliveryOrderLine::class, 'delivery_order_header_id');
    }

    public function invoiceMoneyTrip()
    {
        return $this->hasMany(InvoiceLine::class, 'do_line_id');
    }

    public function invoicePartner()
    {
        return $this->hasMany(InvoiceHeader::class, 'do_header_id');
    }

    public function getTotalInvoiceMoneyTrip(){
        $totalInvoice  = 0;
        foreach ($this->invoiceMoneyTrip()->get() as $invoice) {
             $invoiceHeader = $invoice->header;
            if ($invoiceHeader !== null && $invoiceHeader->notCanceled()) {
                $totalInvoice += $invoice->totalAmount();
            }
        }
        return $totalInvoice;
    }

    public function getTotalInvoicePartner(){
        $totalInvoice  = 0;
        foreach ($this->invoicePartner() as $invoiceHeader) {
            foreach ($invoiceHeader->lines->get() as $invoice) {
                if ($invoiceHeader->notCanceled()) {
                    $totalInvoice += $invoice->totalAmount();
                }
            }
        }
        return $totalInvoice;
    }

    public function totalCost()
    {
        $totalCost = 0;
        foreach ($this->lines as $line) {
            $totalCost += $line->delivery_cost;
        }
        return $totalCost;
    }

    public function isOpen()
    {
        return $this->status == self::OPEN;
    }

    public function isRequestApproval()
    {
        return $this->status == self::REQUEST_APPROVAL;
    }

    public function isApproved()
    {
        return $this->status == self::APPROVED;
    }

    public function isConfirmed()
    {
        return $this->status == self::CONFIRMED;
    }

    public function isOnTheRoad()
    {
        return $this->status == self::ON_THE_ROAD;
    }

    public function isClosed()
    {
        return $this->status == self::CLOSED;
    }

    public function isCanceled()
    {
        return $this->status == self::CANCELED;
    }

    public static function canPrint(){
        return [
            self::APPROVED,
            self::CONFIRMED,
            self::ON_THE_ROAD,
            self::CLOSED,
        ];
    }

    public function deliveryArea()
    {
        return $this->belongsTo(MasterDeliveryArea::class, 'delivery_area_id');
    }

    public function getTotalRemain($position)
    {
        if($position == MasterDriver::DRIVER){
            return $this->driver_salary - $this->getTotalInvoiceWithoutTax($position);
        }else{
            return $this->driver_assistant_salary - $this->getTotalInvoiceWithoutTax($position);
        }
    }

    public function getTotalInvoiceWithoutTax($position){
        $totalInvoice  = 0;
        foreach ($this->invoiceLine()->get() as $invoice) {
            $invoiceHeader = $invoice->header;
            if($position == $invoice->position && $invoiceHeader->notCanceled() && $invoiceHeader->type == InvoiceHeader::DRIVER_SALARY){
                $totalInvoice += $invoice->totalAmountWithoutTax();
            }
        }
        return $totalInvoice;
    }

    public function invoiceLine()
    {
        return $this->hasMany(InvoiceLine::class, 'manifest_id');
    }

    public function getRemainingSalaryDriver($driverId){
        return $this->driver_salary - $this->getTotalPaymentDriverAssistant($driverId);
    }

    public function getRemainingSalaryAssistant($driverId){
        return $this->driver_assistant_salary - $this->getTotalPaymentDriverAssistant($driverId);
    }

    public function getTotalPaymentDriverAssistant($driverId){
        $totalPayment = 0;
        foreach ($this->invoiceLine as $invoiceLine) {
            if ($invoiceLine->header->getTotalRemain() <= 0 && $invoiceLine->header->vendor_id == $driverId) {
                $totalPayment += $invoiceLine->totalAmount();
            }
        }
        return $totalPayment;
    }
}
