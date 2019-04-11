<?php

namespace App\Modules\Inventory\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Inventory\Model\Transaction\WarehouseTransferLine;
use App\Modules\Operational\Model\Master\MasterBranch;

class WarehouseTransferHeader extends Model
{
    protected $connection = 'inventory';
    protected $table      = 'trans_wht_header';
    public $timestamps    = false;

    protected $primaryKey = 'wht_header_id';

    const INCOMPLETE = 'Incomplete';
    const COMPLETE   = 'Complete';
    const CANCELED   = 'Canceled';

    public function lines()
    {
        return $this->hasMany(WarehouseTransferLine::class, 'wht_header_id');
    }

    public function branch()
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id');
    }
}
