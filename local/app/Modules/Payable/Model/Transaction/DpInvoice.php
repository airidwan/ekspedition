<?php

namespace App\Modules\Payable\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Generalledger\Model\Master\MasterBank;
use App\Modules\Purchasing\Model\Transaction\PurchaseOrderHeader;
use App\Modules\Operational\Model\Master\MasterBranch;

class DpInvoice extends Model
{
    protected $connection = 'payable';
    protected $table      = 'dp_invoice';
    public $timestamps    = false;

    protected $primaryKey = 'dp_invoice_id';
  
    public function bank()
    {
        return $this->belongsTo(MasterBank::class, 'bank_id');
    }

    public function po()
    {
        return $this->belongsTo(PurchaseOrderHeader::class, 'po_header_id');
    }

    public function branch()
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id');
    }
}
