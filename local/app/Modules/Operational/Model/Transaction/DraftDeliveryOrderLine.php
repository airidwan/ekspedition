<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;

class DraftDeliveryOrderLine extends Model
{
    protected $connection = 'operational';
    protected $table      = 'trans_draft_delivery_order_line';
    protected $primaryKey = 'draft_delivery_order_line_id';

    public $timestamps = false;

    public function resi()
    {
        return $this->belongsTo(TransactionResiHeader::class, 'resi_header_id');
    }

    public function header()
    {
        return $this->belongsTo(DraftDeliveryOrderHeader::class, 'draft_delivery_order_header_id');
    }
    }
