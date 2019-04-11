<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Master\MasterRoute;
use App\Modules\Operational\Model\Master\MasterCustomer;
use App\Modules\Operational\Model\Master\MasterDeliveryArea;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Model\Transaction\DeliveryOrderLine;
use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader;
use App\Modules\Marketing\Model\Transaction\PickupRequest;
use App\Modules\Accountreceivables\Model\Transaction\Invoice;
use App\Modules\Accountreceivables\Model\Transaction\Receipt;
use App\User;

class TransactionResiHeader extends Model
{
    protected $connection = 'operational';
    protected $table      = 'trans_resi_header';
    protected $primaryKey = 'resi_header_id';

    public $timestamps = false;

    const INCOMPLETE = 'Incomplete';
    const INPROCESS  = 'In Process';
    const APPROVED   = 'Approved';
    const CANCELED   = 'Canceled';
    const DELETED    = 'Deleted';

    const CASH             = 'Cash';
    const BILL_TO_SENDER   = 'Bill to Sender';
    const BILL_TO_RECIEVER = 'Bill to Reciever';

    const SINGKATAN_CASH             = 'C';
    const SINGKATAN_BILL_TO_SENDER   = 'BTS';
    const SINGKATAN_BILL_TO_RECIEVER = 'BTR';

    const REGULER = 'Reguler';
    const CARTER = 'Carter';

    const PEMBULATAN = 100;

    public function line()
    {
        return $this->hasMany(TransactionResiLine::class, 'resi_header_id');
    }

    public function stock()
    {
        return $this->hasMany(ResiStock::class, 'resi_header_id');
    }

    public function customerTaking()
    {
        return $this->hasMany(CustomerTaking::class, 'resi_header_id');
    }

    public function deliveryOrder()
    {
        return $this->hasMany(DeliveryOrderLine::class, 'resi_header_id');
    }

    public function pickupRequest()
    {
        return $this->belongsTo(PickupRequest::class, 'pickup_request_id');
    }

    public function lineDetail()
    {
        return $this->line()->whereNull('unit_id');
    }

    public function lineUnit()
    {
        return $this->line()->whereNotNull('unit_id');
    }

    public function nego()
    {
        return $this->hasMany(TransactionResiNego::class, 'resi_header_id');
    }

    public function route()
    {
        return $this->belongsTo(MasterRoute::class, 'route_id');
    }

    public function customer()
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
    }

    public function customerReceiver()
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_receiver_id');
    }

    public function deliveryArea()
    {
        return $this->belongsTo(MasterDeliveryArea::class, 'delivery_area_id');
    }

    public function manifestLine()
    {
        return $this->hasMany(ManifestLine::class, 'resi_header_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'resi_header_id');
    }

    public function invoiceExtraCosts()
    {
        return $this->invoices()->where('type', '=', Invoice::INV_EXTRA_COST);
    }

    public function extraCosts()
    {
        return $this->hasMany(Receipt::class, 'resi_header_id');
    }

    public function branch()
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function totalColy()
    {
        $totalColy = 0;
        foreach ($this->line()->get() as $line) {
            $totalColy += $line->coly;
        }

        return $totalColy;
    }

    public function getCustomerName()
    {
        if ($this->customer !== null) {
            return $this->customer->customer_name;
        }

        return $this->sender_name;
    }

    public function getCustomerReceiverName()
    {
        if ($this->customerReceiver !== null) {
            return $this->customerReceiver->customer_name;
        }

        return $this->receiver_name;
    }

    public function totalColySent($excludeManifestId = 0)
    {
        $totalColySent = 0;
        foreach ($this->manifestLine as $manifestLine) {
            if ($manifestLine->manifest_header_id != $excludeManifestId) {
                $totalColySent += $manifestLine->coly_sent;
            }
        }

        return $totalColySent;
    }

    public function colyRemaining($excludeManifestId = 0)
    {
        return $this->totalColy() - $this->totalColySent($excludeManifestId);
    }

    public function totalWeight()
    {
        $totalWeight = 0;
        foreach ($this->lineDetail()->get() as $line) {
            if ($line->isMenangWeight()) {
                $totalWeight += $line->weight;
            }
        }

        return $totalWeight;
    }

    public function totalWeightAll()
    {
        $totalWeight = 0;
        foreach ($this->lineDetail()->get() as $line) {
            $totalWeight += $line->weight;
        }

        return $totalWeight;
    }

    public function totalWeightPrice()
    {
        $totalWeightPrice = 0;
        foreach ($this->lineDetail()->get() as $line) {
            if ($line->isMenangWeight()) {
                $totalWeightPrice += $line->price_weight;
            }
        }

        return $totalWeightPrice;
    }

    public function totalVolume()
    {
        $totalVolume = 0;
        foreach ($this->lineDetail()->get() as $line) {
            if (!$line->isMenangWeight()) {
                $totalVolume += $line->totalVolume();
            }
        }

        return $totalVolume;
    }

    public function totalVolumeAll()
    {
        $totalVolume = 0;
        foreach ($this->lineDetail()->get() as $line) {
            $totalVolume += $line->totalVolume();
        }

        return $totalVolume;
    }

    public function totalVolumePrice()
    {
        $totalVolumePrice = 0;
        foreach ($this->lineDetail()->get() as $line) {
            if ($line->isMenangVolume()) {
                $totalVolumePrice += $line->totalPriceVolume();
            }
        }

        return $totalVolumePrice;
    }

    public function totalUnit()
    {
        $totalUnit = 0;
        foreach ($this->lineUnit()->get() as $line) {
            $totalUnit += $line->total_unit;
        }

        return $totalUnit;
    }

    public function totalUnitPrice()
    {
        $totalUnitPrice = 0;
        foreach ($this->lineUnit()->get() as $line) {
            $totalUnitPrice += $line->total_price;
        }

        return $totalUnitPrice;
    }

    public function totalAmountAsli()
    {
        $totalAmount = 0;
        foreach ($this->line()->get() as $line) {
            $totalAmount += $line->total_price;
        }

        return $totalAmount > $this->minimum_rates ? $totalAmount : $this->minimum_rates;
    }

    public function totalAmount()
    {
        $totalAmountAsli = $this->totalAmountAsli();

        return floor($totalAmountAsli / self::PEMBULATAN) * self::PEMBULATAN;
    }

    public function total()
    {
        return $this->totalAmount() - $this->discount;
    }

    public function itemName()
    {
        if (!empty($this->item_name)) {
            $itemName = $this->item_name;
        } else {
            $itemNames = [];
            foreach ($this->lineDetail()->get() as $line) {
                if (!empty($line->item_name)) {
                    $itemNames[] = $line->item_name;
                }
            }

            $itemName = implode(', ', $itemNames);
        }

        return $itemName;
    }

    public function itemUnit()
    {
        $itemUnits = [];
        foreach ($this->lineUnit()->get() as $line) {
            $itemUnits[] = $line->item_name;
        }

        return implode(', ', $itemUnits);
    }

    public function getItemAndUnitNames()
    {
        return !empty($this->itemName()) && !empty($this->itemUnit()) ? $this->itemName().', '.$this->itemUnit() : $this->itemName().''.$this->itemUnit();
    }

    public function totalReceipt()
    {
        $totalReceipt = 0;
        foreach ($this->stock()->get() as $stock) {
            if ($stock->branch_id == \Session::get('currentBranch')->branch_id) {
                    $totalReceipt += $stock->coly;
            }
        }

        return $totalReceipt;
    }

    public function totalAvailable()
    {
        $totalAvailable = $this->totalReceipt();
        foreach ($this->deliveryOrder()->get() as $do) {
            $header = $do->header;
            if ($header->branch_id == \Session::get('currentBranch')->branch_id && $header->status != DeliveryOrderHeader::ON_THE_ROAD && $header->status != DeliveryOrderHeader::CLOSED && $header->status != DeliveryOrderHeader::CANCELED) {
                    $totalAvailable -= $do->total_coly;
            }
        }
        return $totalAvailable;
    }

    public function totalAvailableExcept($doHeaderId){
        $totalAvailable = $this->totalReceipt();
        foreach ($this->deliveryOrder()->get() as $do) {
            $header = $do->header;
            if ($header->branch_id == \Session::get('currentBranch')->branch_id && $header->status != DeliveryOrderHeader::ON_THE_ROAD && $header->status != DeliveryOrderHeader::CLOSED && $header->status != DeliveryOrderHeader::CANCELED && $header->delivery_order_header_id != $doHeaderId) {
                    $totalAvailable -= $do->total_coly;
            }
        }
        return $totalAvailable;
    }

    public function totalInvoice()
    {
        $totalInvoice = 0;
        foreach ($this->invoices as $invoice) {
            $totalInvoice += $invoice->totalInvoice();
        }

        return $totalInvoice;
    }

    public function totalRemainingInvoice()
    {
        $totalRemainingInvoice = 0;
        foreach ($this->invoices as $invoice) {
            $totalRemainingInvoice += $invoice->remaining();
        }

        return $totalRemainingInvoice;
    }

    public function getInvoicePickup()
    {
        return $this->invoices()->where('type', '=', Invoice::INV_PICKUP)->first();
    }

    public function getInvoiceResi()
    {
        return $this->invoices()->where('type', '=', Invoice::INV_RESI)->first();
    }

    public function getDownPayment()
    {
        if ($this->getInvoiceResi() === null) {
            return 0;
        }

        foreach ($this->getInvoiceResi()->receipts as $receipt) {
            if ($receipt->type == Receipt::DP) {
                return $receipt->amount;
            }
        }

        return 0;
    }

    public function getShortNumber()
    {
        $explode = explode('.', $this->resi_number);
        return !empty($explode[3]) ? $explode[3] : '';
    }

    public function isTagihan()
    {
        foreach ($this->invoices as $invoice) {
            if ($invoice->is_tagihan) {
                return true;
            }
        }

        return false;
    }

    public function activeNego()
    {
        return $this->nego()->whereNull('approved_date')->first();
    }

    public function isReadyDelivery()
    {
        $stock = ResiStock::where('resi_header_id', '=', $this->resi_header_id)->first();
        return $stock->is_ready_delivery;
    }

    public function isIncomplete()
    {
        return $this->status == self::INCOMPLETE;
    }

    public function isInprocess()
    {
        return $this->status == self::INPROCESS;
    }

    public function isApproved()
    {
        return $this->status == self::APPROVED;
    }

    public function isCash()
    {
        return $this->payment == self::CASH;
    }

    public function isBillToSender()
    {
        return $this->payment == self::BILL_TO_SENDER;
    }

    public function isBillToReceiver()
    {
        return $this->payment == self::BILL_TO_RECIEVER;
    }

    public function getSingkatanPayment()
    {
        if ($this->isCash()) {
            return self::SINGKATAN_CASH;
        } elseif ($this->isBillToSender()) {
            return self::SINGKATAN_BILL_TO_SENDER;
        } elseif ($this->isBillToReceiver()) {
            return self::SINGKATAN_BILL_TO_RECIEVER;
        }
    }

    public function isReguler()
    {
        return $this->type == self::REGULER;
    }

    public function isCarter()
    {
        return $this->type == self::CARTER;
    }
}
