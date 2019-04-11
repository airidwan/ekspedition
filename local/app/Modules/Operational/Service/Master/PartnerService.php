<?php 

namespace App\Modules\Operational\Service\Master;

class PartnerService
{
    
    public static function getActivePartner()
    {
        return \DB::table('op.mst_partner')
                ->select('mst_partner.*', 'mst_city')
                ->join('op.dt_partner_branch', 'mst_partner.partner_id', '=', 'dt_partner_branch.partner_id')
                ->join('op.mst_city', 'mst_partner.city_id', '=', 'mst_city.city_id')
                ->where('mst_partner.active', '=', 'Y')
                ->where('dt_partner_branch.active', '=', 'Y')
                ->where('dt_partner_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                ->orderBy('mst_partner.partner_code')
                ->distinct()
                ->get();
    }
}