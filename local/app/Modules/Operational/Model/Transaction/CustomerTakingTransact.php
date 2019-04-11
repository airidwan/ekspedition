<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;

class CustomerTakingTransact extends Model
{
    protected $connection = 'operational';
    protected $table      = 'trans_customer_taking_transact';
    protected $primaryKey = 'customer_taking_transact_id';

    public $timestamps = false;

    public function customerTaking()
    {
        return $this->belongsTo(CustomerTaking::class, 'customer_taking_id');
    }
}
