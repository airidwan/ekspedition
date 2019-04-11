<?php 

namespace App\Modules\Operational\Service\Master;

class CustomerService
{
    public static function getActiveCustomer()
    {
        return \DB::table('op.v_mst_customer')
                ->select('v_mst_customer.*')
                ->join('op.dt_customer_branch', 'v_mst_customer.customer_id', '=', 'dt_customer_branch.customer_id')
                ->where('v_mst_customer.active', '=', 'Y')
                ->where('dt_customer_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                ->orderBy('v_mst_customer.customer_code')
                ->distinct()
                ->get();
    }

    public static function getQueryActiveCustomer()
    {
        return \DB::table('op.mst_customer')
                    ->join('op.dt_customer_branch', 'mst_customer.customer_id', '=', 'dt_customer_branch.customer_id')
                    ->where('mst_customer.active', '=', 'Y')
                    ->where('dt_customer_branch.branch_id', '=', \Session::get('currentBranch')->branch_id);
    }
}