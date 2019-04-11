<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;

class MasterAlertResiStock extends Model
{
    protected $connection = 'operational';
    protected $table      = 'mst_alert_resi_stock';
    public $timestamps    = false;

    protected $primaryKey = 'alert_resi_stock_id';

    public function cityEnd()
    {
        return $this->hasOne(MasterCity::class, 'city_end_id');
    }
}
