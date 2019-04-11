<?php

namespace App\Modules\Marketing\Model\Transaction;

use Illuminate\Database\Eloquent\Model;

class OperatorBook extends Model
{
    protected $connection = 'marketing';
    protected $table      = 'trans_obook';
    protected $primaryKey = 'obook_id';

    public $timestamps = false;
}
