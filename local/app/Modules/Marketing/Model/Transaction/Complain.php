<?php

namespace App\Modules\Marketing\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;

class Complain extends Model
{
    protected $connection = 'marketing';
    protected $table      = 'trans_complain';
    protected $primaryKey = 'complain_id';

    public $timestamps = false;

    const OPEN   = 'Open';
    const CLOSED = 'Closed';
    
    public function resi()
    {
        return $this->belongsTo(TransactionResiHeader::class, 'resi_id');
    }

    public function isOpen()
    {
        return $this->status == self::OPEN;
    }

    public function isClosed()
    {
        return $this->status == self::CLOSED;
    }
}
