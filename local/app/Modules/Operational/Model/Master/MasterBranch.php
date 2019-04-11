<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;

class MasterBranch extends Model
{
    const KODE_NUMERIC_HO = '01';

    protected $connection = 'operational';
    protected $table      = 'mst_branch';
    protected $primaryKey = 'branch_id';

    public $timestamps = false;

    public function city()
    {
        return $this->belongsTo(MasterCity::class, 'city_id');
    }
}
