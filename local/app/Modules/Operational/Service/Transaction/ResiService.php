<?php

namespace App\Modules\Operational\Service\Transaction;

use App\Modules\Operational\Model\Transaction\TransactionResiHeader;

class ResiService
{
    public static function getApprovedResiAllBranch()
    {
        return \DB::table('op.trans_resi_header')
                    ->select('trans_resi_header.*')
                    ->where('trans_resi_header.status', '=', TransactionResiHeader::APPROVED)
                    ->distinct()
                    ->get();
    }

    public static function getResiAllBranchLastYears()
    {
        $now = strtotime(date("Y-m-d"));
        $date = new \DateTime(date('Y-m-j', strtotime('-1 year', $now)));
        return \DB::table('op.trans_resi_header')
                    ->select('trans_resi_header.*', 'mst_customer.customer_name', 'mst_customer.address as customer_address', 'mst_customer.phone_number as customer_phone_number')
                    ->leftJoin('op.mst_customer', 'mst_customer.customer_id', '=', 'trans_resi_header.customer_id')
                    ->where('trans_resi_header.created_date', '>=', $date->format('Y-m-d 00:00:00'))
                    ->distinct()
                    ->get();
    }

    public static function getQueryResiAllBranch()
    {
        $now = strtotime(date("Y-m-d"));
        return \DB::table('op.trans_resi_header')
                    ->select('trans_resi_header.*', 'mst_customer.customer_name', 'mst_customer.address as customer_address', 'mst_customer.phone_number as customer_phone_number')
                    ->leftJoin('op.mst_customer', 'mst_customer.customer_id', '=', 'trans_resi_header.customer_id')
                    ->distinct();
    }
}
