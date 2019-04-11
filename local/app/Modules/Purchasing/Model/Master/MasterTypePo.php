<?php

namespace App\Modules\Purchasing\Model\Master;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Generalledger\Model\Master\MasterCoa;

class MasterTypePo extends Model
{
    protected $connection = 'purchasing';
    protected $table      = 'mst_po_type';
    protected $primaryKey = 'type_id';

    const TRUCK_RENT              = 3; // asalnya 7 terus 10
    const TICKET                  = 5; // asalnya 6 terus 12
    const TRUCK_RENT_PER_TRIP     = 13; 
    const TRUCK_RENT_PER_TRIP_DO  = 15; 
    const TRUCK_RENT_PER_TRIP_PICKUP  = 16; 

    public $timestamps = false;

    public function coa()
    {
        return $this->belongsTo(MasterCoa::class, 'coa_id');
    }
}
