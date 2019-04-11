<?php

namespace App\Modules\Payable\Model\Master;

use Illuminate\Database\Eloquent\Model;

class MasterVendor extends Model
{
    protected $connection = 'payable';
    protected $table      = 'mst_vendor';
    public $timestamps    = false;

    protected $primaryKey = 'vendor_id';

    const EMPLOYEE = 'KARY_NON_SUPIR';

    const VENDOR         = 'VENDOR'; 
    const TOKO           = 'TOKO'; 
    const MITRA          = 'MITRA'; 
    const KARY_NON_SUPIR = 'KARY_NON_SUPIR'; 

    public function vendorBranch()
    {
        return $this->hasMany(DetailVendorBranch::class, 'vendor_id');
    }
}
