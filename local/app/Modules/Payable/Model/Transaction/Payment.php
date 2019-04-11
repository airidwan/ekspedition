<?php

namespace App\Modules\Payable\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Payable\Model\Master\MasterApType;
use App\Modules\Payable\Model\Transaction\InvoiceHeader;
use App\Modules\Payable\Model\Master\MasterVendor;
use App\Modules\Generalledger\Model\Master\MasterBank;
use App\Modules\Operational\Model\Master\MasterDriver;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader;

class Payment extends Model
{
    protected $connection = 'payable';
    protected $table      = 'payment';
    public $timestamps    = false;

    protected $primaryKey = 'payment_id';

    const INCOMPLETE      = 'Incomplete';
    const APPROVED        = 'Approved';
    const CLOSED          = 'Closed';
    const CANCELED        = 'Canceled';

    const CASH     = 'Cash';
    const TRANSFER = 'Transfer';


    public function invoice()
    {
        return $this->belongsTo(InvoiceHeader::class, 'invoice_header_id');
    }

    public function bank()
    {
        return $this->belongsTo(MasterBank::class, 'bank_id');
    }

    public function branch()
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id');
    }

    public function isApproved()
    {
        return $this->status == self::APPROVED;
    }

    public function isIncomplete(){
        return $this->status == $this::INCOMPLETE;
    }

    public function notCanceled()
    {
        return $this->status != self::CANCELED;
    }

    public function getTotalPayment()
    {
        return $this->total_amount + $this->total_interest;
    }
}
