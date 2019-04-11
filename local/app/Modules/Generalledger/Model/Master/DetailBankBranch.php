<?php

namespace App\Modules\Generalledger\Model\Master;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Master\MasterBranch;

class DetailBankBranch extends Model
{
    protected $connection  = 'gl';
    protected $table       = 'dt_bank_branch';
    public $timestamps     = false;

    protected $primaryKey  = 'bank_branch_id';

    public function branch()
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id');
    }
}
