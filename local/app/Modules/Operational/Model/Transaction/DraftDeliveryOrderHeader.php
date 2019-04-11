<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Master\MasterDriver;
use App\Modules\Operational\Model\Master\MasterTruck;
use App\Modules\Operational\Model\Transaction\DraftDeliveryOrderLine;

class DraftDeliveryOrderHeader extends Model
{
    protected $connection = 'operational';
    protected $table      = 'trans_draft_delivery_order_header';
    protected $primaryKey = 'draft_delivery_order_header_id';

    public $timestamps = false;

    const OPEN       = 'Open';
    const CLOSED     = 'Closed';
    const CANCELED   = 'Canceled';

    public function driver()
    {
        return $this->belongsTo(MasterDriver::class, 'driver_id');
    }

    public function assistant()
    {
        return $this->belongsTo(MasterDriver::class, 'assistant_id');
    }

    public function truck()
    {
        return $this->belongsTo(MasterTruck::class, 'truck_id');
    }

    public function lines()
    {
        return $this->hasMany(DraftDeliveryOrderLine::class, 'draft_delivery_order_header_id');
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

    public static function canPrint(){
        return [
            self::OPEN,
            self::CLOSED,
        ];
    }
}
