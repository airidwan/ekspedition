<?php

namespace App\Modules\Inventory\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Inventory\Model\Transaction\AdjustmentStockLine;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Model\Transaction\OfficialReport;

class AdjustmentStockHeader extends Model
{
    protected $connection = 'inventory';
    protected $table      = 'trans_adjustment_header';
    public $timestamps    = false;

    protected $primaryKey = 'adjustment_header_id';

    const INCOMPLETE = 'Incomplete';
    const COMPLETE   = 'Complete';
    const CANCELED   = 'Canceled';

    const ADJUSTMENT_PLUS = 'Adjustment +';
    const ADJUSTMENT_MIN  = 'Adjustment -';

    public function lines()
    {
        return $this->hasMany(AdjustmentStockLine::class, 'adjustment_header_id');
    }

    public function officialReport()
    {
        return $this->belongsTo(OfficialReport::class, 'official_report_id');
    }

    public function branch()
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id');
    }
}
