<?php

namespace App\Modules\Inventory\Model\Transaction;

use Illuminate\Database\Eloquent\Model;

class ReturnHeader extends Model
{
    protected $connection = 'inventory';
    protected $table      = 'trans_return_header';
    protected $primaryKey = 'return_id';

    public $timestamps = false;

    public function lines()
    {
        return $this->hasMany(ReturnLine::class, 'return_id');
    }

    public function receipt(){
        return $this->belongsTo(ReceiptHeader::class, 'receipt_id');
    }
}
