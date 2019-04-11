<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;

class MasterOrganization extends Model
{
    protected $connection = 'operational';
    protected $table      = 'mst_organization';
    public $timestamps     = false;

    protected $primaryKey  = 'org_id';

    public function city()
    {
        return $this->belongsTo(MasterCity::class, 'city_id');
    }
}
