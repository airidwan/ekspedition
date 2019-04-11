<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;
use App\Role;

class DetailOfficialReportToRole extends Model
{
    protected $connection = 'operational';
    protected $table      = 'dt_official_report_to_role';
    protected $primaryKey = 'official_report_to_role_id';

    public $timestamps = false;

    public function officialReport()
    {
        return $this->belongsTo(OfficialReport::class, 'official_report_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
