<?php

namespace App\Modules\Operational\Service\Transaction;

use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader;

class DeliveryOrderService
{
    public static function getQueryDeliveryOrderClosed()
    {
        return \DB::table('op.trans_delivery_order_header')
                    ->select('trans_delivery_order_header.*')
                    ->leftJoin('op.mst_driver as driver', 'driver.driver_id', '=', 'trans_delivery_order_header.driver_id')
                    ->leftJoin('op.mst_driver as assistant', 'assistant.driver_id', '=', 'trans_delivery_order_header.assistant_id')
                    ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_delivery_order_header.truck_id')
                    ->where('trans_delivery_order_header.status', '=', DeliveryOrderHeader::CLOSED)
                    ->where('trans_delivery_order_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->distinct();
    }

    public static function getQueryDeliveryOrderMinimumApproved()
    {
        return \DB::table(
                        'op.trans_delivery_order_header')
                    ->select('trans_delivery_order_header.*',
                            'driver.driver_name',
                            'assistant.driver_name as assistant_name',
                            'mst_truck.police_number',
                            'mst_vendor.vendor_name as partner_name'
                        )
                    ->leftJoin('op.mst_driver as driver', 'driver.driver_id', '=', 'trans_delivery_order_header.driver_id')
                    ->leftJoin('op.mst_driver as assistant', 'assistant.driver_id', '=', 'trans_delivery_order_header.assistant_id')
                    ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_delivery_order_header.truck_id')
                    ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'trans_delivery_order_header.partner_id')
                    ->where(function($query){
                        $query->where('trans_delivery_order_header.status', '=', DeliveryOrderHeader::APPROVED)
                              ->orWhere('trans_delivery_order_header.status', '=', DeliveryOrderHeader::CONFIRMED)
                              ->orWhere('trans_delivery_order_header.status', '=', DeliveryOrderHeader::ON_THE_ROAD)
                              ->orWhere('trans_delivery_order_header.status', '=', DeliveryOrderHeader::CLOSED);
                    })
                    ->where('trans_delivery_order_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->distinct();
    }

    public static function getQueryDeliveryOrderMinimumApprovedAllBranch()
    {
        return \DB::table(
                        'op.trans_delivery_order_header')
                    ->select('trans_delivery_order_header.*',
                            'driver.driver_name',
                            'assistant.driver_name as assistant_name',
                            'mst_truck.police_number',
                            'mst_vendor.vendor_name as partner_name'
                        )
                    ->leftJoin('op.mst_driver as driver', 'driver.driver_id', '=', 'trans_delivery_order_header.driver_id')
                    ->leftJoin('op.mst_driver as assistant', 'assistant.driver_id', '=', 'trans_delivery_order_header.assistant_id')
                    ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_delivery_order_header.truck_id')
                    ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'trans_delivery_order_header.partner_id')
                    ->where(function($query){
                        $query->where('trans_delivery_order_header.status', '=', DeliveryOrderHeader::APPROVED)
                              ->orWhere('trans_delivery_order_header.status', '=', DeliveryOrderHeader::CONFIRMED)
                              ->orWhere('trans_delivery_order_header.status', '=', DeliveryOrderHeader::ON_THE_ROAD)
                              ->orWhere('trans_delivery_order_header.status', '=', DeliveryOrderHeader::CLOSED);
                    })
                    ->distinct();
    }

    public static function getQueryDeliveryOrderTransitionClosed()
    {
        return \DB::table('op.trans_delivery_order_header')
                    ->select('trans_delivery_order_header.*')
                    ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'trans_delivery_order_header.partner_id')
                    ->leftJoin('op.mst_driver as driver', 'driver.driver_id', '=', 'trans_delivery_order_header.driver_id')
                    ->leftJoin('op.mst_driver as assistant', 'assistant.driver_id', '=', 'trans_delivery_order_header.assistant_id')
                    ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_delivery_order_header.truck_id')
                    ->where('trans_delivery_order_header.status', '=', DeliveryOrderHeader::CLOSED)
                    ->where('trans_delivery_order_header.type', '=', DeliveryOrderHeader::TRANSITION)
                    ->distinct();
    }
}
