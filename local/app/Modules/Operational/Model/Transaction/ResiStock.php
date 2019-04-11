<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Master\MasterBranch;

class ResiStock extends Model
{
    protected $connection = 'operational';
    protected $table      = 'mst_stock_resi';
    protected $primaryKey = 'stock_resi_id';

    public $timestamps = false;

    public function resi()
    {
        return $this->belongsTo(TransactionResiHeader::class, 'resi_header_id');
    }

    public function branch()
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id');
    }
}
