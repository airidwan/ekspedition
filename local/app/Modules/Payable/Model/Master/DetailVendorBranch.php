<?php

namespace App\Modules\Payable\Model\Master;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Master\MasterBranch;

class DetailVendorBranch extends Model
{
    protected $connection = 'payable';
    protected $table      = 'dt_vendor_branch';
    public $timestamps    = false;

    protected $primaryKey = 'dt_vendor_branch_id';

    public function branch()
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id');
    }
}
