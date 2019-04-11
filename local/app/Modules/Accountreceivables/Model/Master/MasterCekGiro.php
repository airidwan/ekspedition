<?php

namespace App\Modules\Accountreceivables\Model\Master;

use Illuminate\Database\Eloquent\Model;

class MasterCekGiro extends Model
{
    CONST CEK = 'C';

    protected $connection = 'ar';
    protected $table      = 'mst_cek_giro';
    protected $primaryKey = 'cek_giro_id';

    public $timestamps     = false;

    public function getType()
    {
        return $this->isCek() ? 'Cek' : 'Giro';
    }

    public function isCek()
    {
        return $this->cg_type == self::CEK;
    }

    public function isGiro()
    {
        return $this->cg_type != self::CEK;
    }
}
