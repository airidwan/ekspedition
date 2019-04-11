<?php

namespace App\Modules\Accountreceivables\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Master\MasterCustomer;

class CekGiroLine extends Model
{
    protected $connection = 'ar';
    protected $table      = 'cek_giro_line';
    protected $primaryKey = 'cek_giro_line_id';

    public $timestamps = false;

    public function header()
    {
        return $this->belongsTo(CekGiroHeader::class, 'cek_giro_header_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
