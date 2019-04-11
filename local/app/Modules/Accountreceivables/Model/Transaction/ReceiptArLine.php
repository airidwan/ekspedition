<?php

namespace App\Modules\Accountreceivables\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Accountreceivables\Model\Master\MasterCekGiro;

class ReceiptArLine extends Model
{
    protected $connection = 'ar';
    protected $table      = 'receipt_ar_line';
    protected $primaryKey = 'receipt_ar_line_id';

    public $timestamps     = false;

    public function header()
    {
        return $this->belongsTo(ReceiptArHeader::class, 'receipt_ar_header_id');
    }

    public function invoiceArLine()
    {
        return $this->belongsTo(InvoiceArLine::class, 'inv_ar_line_id');
    }

    public function cekGiro()
    {
        return $this->belongsTo(MasterCekGiro::class, 'cek_giro_id');
    }

    public function totalAmount()
    {
        return $this->amount - $this->discount;
    }
}
