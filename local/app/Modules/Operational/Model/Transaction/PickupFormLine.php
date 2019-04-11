<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Marketing\Model\Transaction\PickupRequest;

class PickupFormLine extends Model
{
    protected $connection = 'operational';
    protected $table      = 'trans_pickup_form_line';
    protected $primaryKey = 'pickup_form_line_id';

    public $timestamps = false;

    public function header()
    {
        return $this->belongsTo(PickupFormHeader::class, 'pickup_form_header_id');
    }

    public function resi()
    {
        return $this->belongsTo(TransactionResiHeader::class, 'resi_id');
    }

    public function pickupRequest()
    {
        return $this->belongsTo(PickupRequest::class, 'pickup_request_id');
    }
}
