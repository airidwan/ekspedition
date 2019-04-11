<?php

namespace App\Modules\Marketing\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Master\MasterCustomer;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Transaction\PickupFormLine;
use App\Modules\Operational\Model\Transaction\PickupFormHeader;

class PickupRequest extends Model
{
    protected $connection = 'marketing';
    protected $table      = 'trans_pickup_request';
    protected $primaryKey = 'pickup_request_id';

    public $timestamps = false;

    const OPEN     = 'Open';
    const APPROVED = 'Approved';
    const CLOSED   = 'Closed';
    const CANCELED = 'Canceled';

    public function customer()
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
    }

    public function resi()
    {
        return $this->hasOne(TransactionResiHeader::class, 'pickup_request_id');
    }

    public function pickupForm()
    {
        return $this->hasMany(PickupFormLine::class, 'pickup_request_id');
    }

    public function isOpen(){
        return $this->status == self::OPEN;
    }

    public function isApproved(){
        return $this->status == self::APPROVED;
    }

    public function isClosed(){
        return $this->status == self::CLOSED;
    }
    public function getDriver(){
        $CustomerName = '';
        foreach ($this->pickupForm()->get() as $pickupFormLine) {
            if ($pickupFormLine->header->status != PickupFormHeader::CANCELED) {
                $CustomerName = !empty($pickupFormLine->header->driver) ? $pickupFormLine->header->driver->driver_name : '';
            }
        }
        return $CustomerName;
    }
}
