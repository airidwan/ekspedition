<?php

namespace App\Modules\Inventory\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Inventory\Model\Transaction\AdjustmentStockHeader;
use App\Modules\Inventory\Model\Master\MasterItem;
use App\Modules\Inventory\Model\Master\MasterWarehouse;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Generalledger\Model\Master\MasterAccountCombination;

class AdjustmentStockLine extends Model
{
    protected $connection = 'inventory';
    protected $table      = 'trans_adjustment_line';
    public $timestamps    = false;

    protected $primaryKey = 'adjustment_line_id';

    public function header()
    {
        return $this->belongsTo(AdjustmentStockHeader::class, 'adjustment_header_id');
    }

    public function item()
    {
        return $this->belongsTo(MasterItem::class, 'item_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(MasterWarehouse::class, 'wh_id');
    }

    public function coaCombination()
    {
        return $this->belongsTo(MasterAccountCombination::class, 'account_comb_id');
    }
}
