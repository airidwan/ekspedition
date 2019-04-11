<?php

namespace App\Modules\Generalledger\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Generalledger\Model\Master\MasterBank;

class BeginningBalance extends Model
{

    protected $connection = 'gl';
    protected $table      = 'trans_beginning_balance';
    protected $primaryKey = 'beginning_balance_id';

    public $timestamps    = false;

    public function bank(){
        return $this->belongsTo(MasterBank::class, 'bank_id');
    }
}
