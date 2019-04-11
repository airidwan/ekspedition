<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Master\MasterDriver;
use App\Modules\Operational\Model\Master\MasterTruck;
use App\Modules\Operational\Model\Master\MasterDeliveryArea;
use App\Modules\Payable\Model\Transaction\InvoiceHeader;
use App\Modules\Payable\Model\Transaction\InvoiceLine;

class PickupFormHeader extends Model
{
    protected $connection = 'operational';
    protected $table      = 'trans_pickup_form_header';
    protected $primaryKey = 'pickup_form_header_id';

    public $timestamps = false;

    const OPEN     = 'Open';
    const CLOSED   = 'Closed';
    const CANCELED = 'Canceled';
 
    public function driver()
    {
        return $this->belongsTo(MasterDriver::class, 'driver_id');
    }

    public function truck()
    {
        return $this->belongsTo(MasterTruck::class, 'truck_id');
    }

    public function lines()
    {
        return $this->hasMany(PickupFormLine::class, 'pickup_form_header_id');
    }

    public function isOpen()
    {
        return $this->status == self::OPEN;
    }

    public function isClosed()
    {
        return $this->status == self::CLOSED;
    }

    public function isCanceled()
    {
        return $this->status == self::CANCELED;
    }

    public function deliveryArea()
    {
        return $this->belongsTo(MasterDeliveryArea::class, 'delivery_area_id');
    }

    public function getTotalRemain()
    {
        return $this->driver_salary - $this->getTotalInvoiceWithoutTax();
    }

    public function getTotalInvoiceWithoutTax(){
        $totalInvoice  = 0;
        foreach ($this->invoiceLine()->get() as $invoice) {
            $invoiceHeader = $invoice->header;
            if($invoiceHeader->notCanceled() && $invoiceHeader->type == InvoiceHeader::DRIVER_SALARY){
                $totalInvoice += $invoice->totalAmountWithoutTax();
            }
        }
        return $totalInvoice;
    }

    public function invoiceLine()
    {
        return $this->hasMany(InvoiceLine::class, 'pickup_form_header_id');
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
