<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;

class DetailOfficialReportToBranch extends Model
{
    protected $connection = 'operational';
    protected $table      = 'dt_official_report_to_branch';
    protected $primaryKey = 'official_report_to_branch_id';

    public $timestamps = false;

    public function officialReport()
    {
        return $this->belongsTo(OfficialReport::class, 'official_report_id');
    }

    public function branch()
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id');
    }
}
