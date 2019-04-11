<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;

class CustomerTaking extends Model
{
    protected $connection = 'operational';
    protected $table      = 'trans_customer_taking';
    protected $primaryKey = 'customer_taking_id';

    public $timestamps = false;

    public function transact()
    {
        return $this->hasMany(CustomerTakingTransact::class, 'customer_taking_id');
    }

    public function resi()
    {
        return $this->belongsTo(TransactionResiHeader::class, 'resi_header_id');
    }

    public function haveTransact(){
        return $this->transact()->count() > 0;
    }
}
