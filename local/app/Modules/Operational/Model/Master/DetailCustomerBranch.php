<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Master\MasterBranch;

class DetailCustomerBranch extends Model
{
    protected $connection  = 'operational';
    protected $table       = 'dt_customer_branch';
    public $timestamps     = false;

    protected $primaryKey  = 'customer_branch_id';

    public function branch()
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id');
    }
}
