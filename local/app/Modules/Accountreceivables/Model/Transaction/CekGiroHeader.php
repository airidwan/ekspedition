<?php

namespace App\Modules\Accountreceivables\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Master\MasterCustomer;

class CekGiroHeader extends Model
{
    const CEK  = 'Cek';
    const GIRO = 'Giro';

    const OPEN     = 'Open';
    const CLOSED   = 'Closed';
    const CANCELED = 'Canceled';

    protected $connection = 'ar';
    protected $table      = 'cek_giro_header';
    protected $primaryKey = 'cek_giro_header_id';

    public $timestamps = false;

    public function lines()
    {
        return $this->hasMany(CekGiroLine::class, 'cek_giro_header_id');
    }

    public function customer()
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
    }

    public function totalAmount()
    {
        $total = 0;
        foreach ($this->lines as $line) {
            $total += $line->amount;
        }

        return $total;
    }

    public function isCek()
    {
        return $this->type == self::CEK;
    }

    public function isGiro()
    {
        return $this->type == self::GIRO;
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
}
