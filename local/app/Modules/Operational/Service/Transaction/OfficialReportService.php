<?php

namespace App\Modules\Operational\Service\Transaction;

use App\Modules\Operational\Model\Transaction\OfficialReport;

class OfficialReportService
{
    public static function getAllOfficialReport()
    {
        return \DB::table('op.trans_official_report')
                    ->get();
    }

    public static function getAdjustmentOfficialReport()
    {
        return \DB::table('op.trans_official_report')
                    ->where('category', '=', OfficialReport::ADJUSTMENT)
                    ->where('status', '=', OfficialReport::APPROVED)
                    ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('created_date', 'desc')
                    ->get();
    }

    public static function getResiCorrectionOfficialReport()
    {
        return \DB::table('op.trans_official_report')
                    ->select('trans_official_report.*', 'trans_resi_header.resi_number', 'trans_resi_header.item_name')
                    ->leftJoin('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_official_report.resi_header_id')
                    ->where('category', '=', OfficialReport::RESI_CORRECTION)
                    ->where('trans_official_report.status', '=', OfficialReport::APPROVED)
                    // ->where('trans_official_report.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('trans_official_report.created_date', 'desc')
                    ->get();
    }

    public static function getGeneralOfficialReport()
    {
        return \DB::table('op.trans_official_report')
                    ->where('category', '=', OfficialReport::UMUM)
                    ->where('status', '=', OfficialReport::OPEN)
                    ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('created_date', 'desc')
                    ->get();
    }
}
