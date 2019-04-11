<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Master\MasterRoute;
use App\Modules\Operational\Model\Master\MasterDriver;
use App\Modules\Operational\Model\Master\MasterTruck;
use App\Modules\Payable\Model\Transaction\InvoiceHeader;
use App\Modules\Payable\Model\Transaction\InvoiceLine;
use App\Modules\Purchasing\Model\Transaction\PurchaseOrderHeader;

class ManifestHeader extends Model
{
    protected $connection = 'operational';
    protected $table      = 'trans_manifest_header';
    protected $primaryKey = 'manifest_header_id';

    public $timestamps = false;

    const OPEN            = 'Open';
    const REQUEST_APPROVE = 'Request Approve';
    const APPROVED        = 'Approved';
    const OTR             = 'Shipped';
    const ARRIVED         = 'Arrived';
    const CLOSED          = 'Closed';
    const CLOSED_WARNING  = 'Closed Warning';
    const DELETED         = 'Deleted';
    const RETURNED        = 'Returned';
    const RETURNED_CLOSED = 'Returned Closed';
    const RETURNED_CLOSED_WARNING = 'Returned Closed Warning';

    public static function canPrint(){
        return [
            self::APPROVED,
            self::OTR,
            self::ARRIVED,
            self::CLOSED,
            self::CLOSED_WARNING,
        ];
    }
    public function line()
    {
        return $this->hasMany(ManifestLine::class, 'manifest_header_id');
    }

    public function route()
    {
        return $this->belongsTo(MasterRoute::class, 'route_id');
    }

    public function driver()
    {
        return $this->belongsTo(MasterDriver::class, 'driver_id');
    }

    public function driverAssistant()
    {
        return $this->belongsTo(MasterDriver::class, 'driver_assistant_id');
    }

    public function truck()
    {
        return $this->belongsTo(MasterTruck::class, 'truck_id');
    }

    public function po()
    {
        return $this->belongsTo(PurchaseOrderHeader::class, 'po_header_id');
    }

    public function totalWeight()
    {
        $totalWeight = 0;
        foreach ($this->line as $line) {
            $totalWeight += $line->approximate_weight;
        }

        return $totalWeight;
    }

    public function totalColy()
    {
        $totalColy = 0;
        foreach ($this->line as $line) {
            $totalColy += $line->coly_sent;
        }

        return $totalColy;
    }

    public function isOpen()
    {
        return $this->status == self::OPEN;
    }

    public function isRequestApprove()
    {
        return $this->status == self::REQUEST_APPROVE;
    }

    public function isApproved()
    {
        return $this->status == self::APPROVED;
    }

    public function isOtr()
    {
        return $this->status == self::OTR;
    }

    public function isClosed()
    {
        return $this->status == self::CLOSED;
    }

    public function isClosedWarning()
    {
        return $this->status == self::CLOSED_WARNING;
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
            if($position == $invoice->position && $invoiceHeader->notCanceled()){
                $totalInvoice += $invoice->totalAmountWithoutTax();
            }
        }
        return $totalInvoice;
    }

    public function getTotalInvoice($position){
        $totalInvoice  = 0;
        foreach ($this->invoiceLine()->get() as $invoice) {
            $invoiceHeader = $invoice->header;
             if($position == $invoice->position && $invoiceHeader->notCanceled()){
                $totalInvoice += $invoice->totalAmount();
            }
        }
        return $totalInvoice;
    }

    public function invoiceLine()
    {
        return $this->hasMany(InvoiceLine::class, 'manifest_id');
    }

    public function invoiceHeader()
    {
        return $this->hasMany(InvoiceHeader::class, 'manifest_header_id');
    }

    public function getPosition(){
        $driver = $this->driver;
        return $driver->position;
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

    public function getTotalTonasa(){
        $totalTonasa = 0;
        foreach ($this->line as $line) {
            if ($line->coly_sent == $line->resi->totalColy()) {
                $totalTonasa += $line->resi->totalWeightAll();
            }
        }
        return $totalTonasa;
    }

    public function getTotalVolume(){
        $totalVolume = 0;
        foreach ($this->line as $line) {
            if ($line->coly_sent == $line->resi->totalColy()) {
                $totalVolume += $line->resi->totalVolumeAll();
            }
        }
        return $totalVolume;
    }

    public function getTotalUnit(){
        $totalUnit = 0;
        foreach ($this->line as $line) {
            if ($line->coly_sent == $line->resi->totalColy()) {
                $totalUnit += $line->resi->totalUnit();
            }
        }
        return $totalUnit;
    }

    public function getTotalCash(){
        $totalCash = 0;
        foreach ($this->line as $line) {
            if ($line->resi->payment == TransactionResiHeader::CASH) {
                $totalCash += $line->resi->total();
            }
        }
        return $totalCash;
    }

    public function getTotalBillSender(){
        $totalBillSender = 0;
        foreach ($this->line as $line) {
            if ($line->resi->payment == TransactionResiHeader::BILL_TO_SENDER) {
                $totalBillSender += $line->resi->total();
            }
        }
        return $totalBillSender;
    }

    public function getTotalBillReceiver(){
        $totalBillReceiver = 0;
        foreach ($this->line as $line) {
            if ($line->resi->payment == TransactionResiHeader::BILL_TO_RECIEVER) {
                $totalBillReceiver += $line->resi->total();
            }
        }
        return $totalBillReceiver;
    }

    public function isArrived(){
        return $this->status == self::ARRIVED;
    }
}
