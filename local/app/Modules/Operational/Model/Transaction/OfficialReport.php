<?php

namespace App\Modules\Operational\Model\Transaction;
use App\Modules\Operational\Model\Master\DetailOfficialReportToBranch;
use App\Modules\Operational\Model\Master\DetailOfficialReportToRole;

use Illuminate\Database\Eloquent\Model;

class OfficialReport extends Model
{
    const UMUM            = 'Umum';
    const ADJUSTMENT      = 'Adjustment Stock';
    const RESI_CORRECTION = 'Resi Correction';
    const INVOICE_REQUEST = 'Invoice Request';

    protected $connection = 'operational';
    protected $table      = 'trans_official_report';
    protected $primaryKey = 'official_report_id';

    const OPEN     = 'Open';
    const APPROVED = 'Approved';
    const CLOSED   = 'Closed';

    public $timestamps = false;

    public function resi()
    {
        return $this->belongsTo(TransactionResiHeader::class, 'resi_header_id');
    }

    public function toBranch()
    {
        return $this->hasMany(DetailOfficialReportToBranch::class, 'official_report_id');
    }

    public function toRole()
    {
        return $this->hasMany(DetailOfficialReportToRole::class, 'official_report_id');
    }
}
