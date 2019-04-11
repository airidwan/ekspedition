<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;

class MasterPartner extends Model
{
    protected $connection  = 'operational';
    protected $table       = 'mst_partner';
    public $timestamps     = false;

    protected $primaryKey  = 'partner_id';

    public function partnerBranch()
    {
        return $this->hasMany(DetailPartnerBranch::class, 'partner_id');
    }
}
