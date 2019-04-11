<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;

class MasterCustomer extends Model
{
    protected $connection  = 'operational';
    protected $table       = 'mst_customer';
    public $timestamps     = false;

    protected $primaryKey  = 'customer_id';

    public function customerBranch()
    {
        return $this->hasMany(DetailCustomerBranch::class, 'customer_id');
    }
}
