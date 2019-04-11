<?php

namespace App\Modules\Generalledger\Model\Master;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Master\MasterBranch;

class MasterBank extends Model
{
    const CASH_IN  = 'Cash In';
    const CASH_OUT = 'Cash Out';
    const BANK     = 'Bank';

    protected $connection = 'gl';
    protected $table      = 'mst_bank';
    protected $primaryKey = 'bank_id';

    public $timestamps    = false;

    public function coaClearing()
    {
        return $this->belongsTo(MasterCoa::class, 'coa_clearing_id');
    }

    public function coaBank()
    {
        return $this->belongsTo(MasterCoa::class, 'coa_bank_id');
    }

    public function branch()
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id_insert');
    }

    public function isCashIn()
    {
        return $this->type == self::CASH_IN;
    }

    public function isCashOut()
    {
        return $this->type == self::CASH_OUT;
    }

    public function isBank()
    {
        return $this->type == self::BANK;
    }

    public function bankBranch()
    {
        return $this->hasMany(DetailBankBranch::class, 'bank_id');
    }
}
