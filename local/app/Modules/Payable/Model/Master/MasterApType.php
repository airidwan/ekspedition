<?php

namespace App\Modules\Payable\Model\Master;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Generalledger\Model\Master\MasterCoa;

class MasterApType extends Model
{
    protected $connection = 'payable';
    protected $table      = 'mst_ap_type';
    public $timestamps    = false;

    protected $primaryKey = 'type_id';

    public function coaC()
    {
        return $this->belongsTo(MasterCoa::class, 'coa_id_c');
    }

    public function coaD()
    {
        return $this->belongsTo(MasterCoa::class, 'coa_id_d');
    }

    public function getCoaC(){
        $coa = $this->coaC()->first();
        return $coa !== null ? $coa->coa_code : '';
    }

    public function getCoaD(){
        $coa = $this->coaD()->first();
        return $coa !== null ? $coa->coa_code : '';
    }

    public function getCoaDescriptionC(){
        $coa = $this->coaC()->first();
        return $coa !== null ? $coa->description : '';
    }

    public function getCoaDescriptionD(){
        $coa = $this->coaD()->first();
        return $coa !== null ? $coa->description : '';
    }

}
