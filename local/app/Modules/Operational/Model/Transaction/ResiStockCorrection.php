<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;

class ResiStockCorrection extends Model
{
    protected $connection = 'operational';
    protected $table      = 'trans_resi_stock_correction';
    protected $primaryKey = 'resi_stock_correction_id';

    public $timestamps = false;

    const CORRECTION_PLUS  = 'Correction +';
    const CORRECTION_MINUS = 'Correction -';
    const DELIVERY_ORDER   = 'Correction Delivery Order';
    const CUSTOMER_TAKING  = 'Correction Letter of Goods Expenditure';

    public function officialReport()
    {
        return $this->belongsTo(OfficialReport::class, 'official_report_id');
    }

    public function doLine()
    {
        return $this->belongsTo(DeliveryOrderLine::class, 'delivery_order_line_id');
    }

    public function customerTakingTransact()
    {
        return $this->belongsTo(CustomerTakingTransact::class, 'customer_taking_transact_id');
    }

    public function resi()
    {
        return $this->belongsTo(TransactionResiHeader::class, 'resi_header_id');
    }
}
