<?php

namespace App\Modules\Operational\Service\Transaction;

use App\Modules\Marketing\Model\Transaction\PickupRequest;
use App\Modules\Operational\Model\Transaction\PickupFormHeader;

class PickupService
{
    public static function getPickupRequestOpen()
    {
        return \DB::table('mrk.trans_pickup_request')
                    ->where('status', '=', PickupRequest::OPEN)
                    ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->get();
    }

    public static function getPickupRequestApproved()
    {
        return \DB::table('mrk.trans_pickup_request')
                    ->where('status', '=', PickupRequest::APPROVED)
                    ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->get();
    }

    public static function getQueryPickupClosed()
    {
        return \DB::table('op.trans_pickup_form_header')
                    ->select('trans_pickup_form_header.*')
                    ->leftJoin('op.mst_driver', 'mst_driver.driver_id', '=', 'trans_pickup_form_header.driver_id')
                    ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_pickup_form_header.truck_id')
                    ->where('trans_pickup_form_header.status', '=', PickupFormHeader::CLOSED)
                    ->where('trans_pickup_form_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->distinct();
    }
}
