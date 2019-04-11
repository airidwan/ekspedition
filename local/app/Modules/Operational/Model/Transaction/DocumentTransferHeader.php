<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Transaction\DocumentTransferLine;
use App\Modules\Operational\Model\Master\MasterCity;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Model\Master\MasterDriver;
use App\Modules\Operational\Model\Master\MasterTruck;

class DocumentTransferHeader extends Model
{
    protected $connection = 'operational';
    protected $table      = 'trans_document_transfer_header';
    public $timestamps    = false;

    protected $primaryKey = 'document_transfer_header_id';

    const INCOMPLETE = 'Incomplete';
    const INPROCESS  = 'Inprocess';
    const COMPLETE   = 'Complete';
    const CANCELED   = 'Canceled';
    const CLOSED_WARNING = 'Closed Warning';

    public function lines()
    {
        return $this->hasMany(DocumentTransferLine::class, 'document_transfer_header_id');
    }

    public function branch()
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id');
    }

    public function toCity()
    {
        return $this->belongsTo(MasterCity::class, 'to_city_id');
    }

     public function driver()
    {
        return $this->belongsTo(MasterDriver::class, 'driver_id');
    }

    public function truck()
    {
        return $this->belongsTo(MasterTruck::class, 'truck_id');
    }

    public function isInprocess()
    {
        return $this->status == self::INPROCESS;
    }
}
