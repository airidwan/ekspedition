<?php

namespace App\Modules\Payable\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Payable\Model\Master\MasterApType;
use App\Modules\Operational\Model\Master\MasterDriver;
use App\Modules\Generalledger\Model\Master\MasterAccountCombination;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader;
use App\Modules\Operational\Model\Transaction\DeliveryOrderLine;
use App\Modules\Operational\Model\Transaction\PickupFormHeader;
use App\Modules\Payable\Model\Transaction\InvoiceHeader;
use App\Modules\Asset\Model\Transaction\ServiceAsset;
use App\Modules\Purchasing\Model\Transaction\PurchaseOrderHeader;

class InvoiceLine extends Model
{
    protected $connection = 'payable';
    protected $table      = 'invoice_line';
    public $timestamps    = false;

    protected $primaryKey = 'line_id';

    const MANIFEST_SALARY       = 'Manifest Salary';
    const PICKUP_SALARY         = 'Pickup Salary';
    const DELIVERY_ORDER_SALARY = 'Delivery Order Salary';

    public function po()
    {
        return $this->belongsTo(PurchaseOrderHeader::class, 'po_header_id');
    }

    public function header()
    {
        return $this->belongsTo(InvoiceHeader::class, 'header_id');
    }

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrderHeader::class, 'do_header_id');
    }

    public function deliveryOrderLine()
    {
        return $this->belongsTo(DeliveryOrderLine::class, 'do_line_id');
    }

    public function manifest()
    {
        return $this->belongsTo(ManifestHeader::class, 'manifest_id');
    }

    public function accountCombination()
    {
        return $this->belongsTo(MasterAccountCombination::class, 'account_comb_id');
    }

    public function service()
    {
        return $this->belongsTo(ServiceAsset::class, 'service_id');
    }

    public function pickup()
    {
        return $this->belongsTo(PickupFormHeader::class, 'pickup_form_header_id');
    }

    public function amountPlusTax()
    {
        return $this->amount + ($this->tax / 100 * $this->amount);
    }

    public function totalTax()
    {
        return $this->tax / 100 * $this->amount;
    }

    public function totalAmount()
    {
        return $this->amountPlusTax() + $this->interest_bank;
    }

    public function totalAmountWithoutTax()
    {
        return $this->amount+ $this->interest_bank;
    }

    public function getPosition(){
        $header = $this->header;
        $driver = !empty($header) ? $header->driver : null;
        return $driver->position;
    }

    public function getPositionMeaning(){
        $position = '';
        if ($this->position == MasterDriver::DRIVER) {
            $position = 'Driver';
        }elseif($this->position == MasterDriver::ASSISTANT){
            $position = 'Assistant';
        }
        return $position;
    }
}
