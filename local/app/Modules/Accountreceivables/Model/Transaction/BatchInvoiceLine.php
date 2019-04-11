<?php

namespace App\Modules\Accountreceivables\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Master\MasterCustomer;

class BatchInvoiceLine extends Model
{
    protected $connection = 'ar';
    protected $table      = 'batch_invoice_line';
    protected $primaryKey = 'batch_invoice_line_id';

    public $timestamps = false;

    public function header()
    {
        return $this->belongsTo(BatchInvoiceHeader::class, 'batch_invoice_header_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
