<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;

class TransactionResiNego extends Model
{
    protected $connection = 'operational';
    protected $table      = 'trans_resi_nego';
    public $timestamps    = false;

    protected $primaryKey = 'resi_nego_id';

    public function header()
    {
        return $this->belongsTo(TransactionResiHeader::class, 'resi_header_id');
    }
}
