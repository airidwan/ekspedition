<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;

class TransactionResiLineVolume extends Model
{
    protected $connection = 'operational';
    protected $table      = 'trans_resi_line_volume';
    public $timestamps    = false;

    protected $primaryKey = 'resi_line_volume_id';
}
