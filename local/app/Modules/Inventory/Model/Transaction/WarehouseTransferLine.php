<?php

namespace App\Modules\Inventory\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Inventory\Model\Transaction\WarehouseTransferHeader;
use App\Modules\Inventory\Model\Master\MasterItem;
use App\Modules\Inventory\Model\Master\MasterWarehouse;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Generalledger\Model\Master\MasterAccountCombination;

class WarehouseTransferLine extends Model
{
    protected $connection = 'inventory';
    protected $table      = 'trans_wht_line';
    public $timestamps    = false;

    protected $primaryKey = 'wht_line_id';

    public function header()
    {
        return $this->belongsTo(WarehouseTransferHeader::class, 'wht_header_id');
    }

    public function item()
    {
        return $this->belongsTo(MasterItem::class, 'item_id');
    }

    public function fromWarehouse()
    {
        return $this->belongsTo(MasterWarehouse::class, 'from_wh_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(MasterWarehouse::class, 'to_wh_id');
    }

    public function coaCombination()
    {
        return $this->belongsTo(MasterAccountCombination::class, 'account_comb_id');
    }
}
