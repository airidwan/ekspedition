<?php

namespace App\Modules\Operational\Service\Transaction;

use App\Modules\Operational\Model\Transaction\ManifestHeader;
use App\Modules\Operational\Model\Master\MasterDriver;
use App\Modules\Payable\Model\Transaction\InvoiceHeader;

class ManifestService
{
    public static function getClosedManifest($driver, $type)
    {
        $query =  \DB::table('op.trans_manifest_header')
                    ->select('trans_manifest_header.*', 'v_mst_route.route_code', 'v_mst_route.city_start_name', 'v_mst_route.city_end_name','trans_manifest_header.driver_salary as salary');
        if ($type == MasterDriver::ASSISTANT) {
             $query->select('trans_manifest_header.*', 'v_mst_route.route_code', 'v_mst_route.city_start_name', 'v_mst_route.city_end_name','trans_manifest_header.driver_assistant_salary as salary');
        }

        $query->join('op.v_mst_route', 'v_mst_route.route_id', '=', 'trans_manifest_header.route_id')
                    ->where('trans_manifest_header.status', '=', ManifestHeader::CLOSED)
                    ->distinct();
        if ($type == MasterDriver::DRIVER) {
            $query->where('trans_manifest_header.driver_id', '=', $driver);
        }
        if ($type == MasterDriver::ASSISTANT) {
            $query->where('trans_manifest_header.driver_assistant_id', '=', $driver);
        }
        $arrManifest = [];
        $listManifest = $query->get();

        foreach($listManifest as $manifest) {
            $modelManifest = ManifestHeader::find($manifest->manifest_header_id);
            if ($type == MasterDriver::DRIVER) {
                $modelDriver  = $modelManifest->driver;
            }else{
                $modelDriver  = $modelManifest->driverAssistant;
            }
            
            $manifest->total_remain = $modelManifest->getTotalRemain($modelDriver->position);
            $manifest->position     = $modelDriver->position;

            if ($modelDriver->driver_id != $driver || $manifest->total_remain <= 0 || ($modelManifest->status != ManifestHeader::CLOSED) ) { 
                continue;
            }
          
            $arrManifest[] = $manifest;
        }

        return $arrManifest;
    }

    public static function getManifestPo()
    {
        return \DB::table('op.trans_manifest_header')
                    ->leftJoin('op.mst_driver as driver', 'driver.driver_id', '=', 'trans_manifest_header.driver_id')
                    ->leftJoin('op.mst_driver as assistant', 'assistant.driver_id', '=', 'trans_manifest_header.driver_assistant_id')
                    ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_manifest_header.truck_id')
                    ->leftJoin('op.mst_route', 'mst_route.route_id', '=', 'trans_manifest_header.route_id')
                    ->where('trans_manifest_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where(function ($query) {
                        $query->where('trans_manifest_header.status', '=', ManifestHeader::OTR)                
                              ->orWhere('trans_manifest_header.status', '=', ManifestHeader::ARRIVED)                  
                              ->orWhere('trans_manifest_header.status', '=', ManifestHeader::CLOSED_WARNING)                  
                              ->orWhere('trans_manifest_header.status', '=', ManifestHeader::CLOSED);                  
                    });
    }

    public static function getManifestPoTruckRent()
    {
        return \DB::table('op.trans_manifest_header')
                    ->leftJoin('op.mst_driver as driver', 'driver.driver_id', '=', 'trans_manifest_header.driver_id')
                    ->leftJoin('op.mst_driver as assistant', 'assistant.driver_id', '=', 'trans_manifest_header.driver_assistant_id')
                    ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_manifest_header.truck_id')
                    ->leftJoin('op.mst_route', 'mst_route.route_id', '=', 'trans_manifest_header.route_id')
                    ->where('trans_manifest_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where(function ($query) {
                        $query->where('trans_manifest_header.status', '=', ManifestHeader::OTR)                
                              ->orWhere('trans_manifest_header.status', '=', ManifestHeader::APPROVED)                  
                              ->orWhere('trans_manifest_header.status', '=', ManifestHeader::ARRIVED)                  
                              ->orWhere('trans_manifest_header.status', '=', ManifestHeader::CLOSED_WARNING)                  
                              ->orWhere('trans_manifest_header.status', '=', ManifestHeader::CLOSED);                  
                    });
    }

    public static function getQueryManifestMoneyTrip()
    {
        $query =  \DB::table('op.trans_manifest_header')
                    ->select('trans_manifest_header.*')
                    ->leftJoin('op.mst_driver as driver', 'driver.driver_id', '=', 'trans_manifest_header.driver_id')
                    ->leftJoin('op.mst_driver as assistant', 'assistant.driver_id', '=', 'trans_manifest_header.driver_assistant_id')
                    ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_manifest_header.truck_id')
                    ->where('trans_manifest_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where(function ($query) {
                        $query->where('trans_manifest_header.status', '=', ManifestHeader::APPROVED)
                              ->orWhere('trans_manifest_header.status', '=', ManifestHeader::OTR)                
                              ->orWhere('trans_manifest_header.status', '=', ManifestHeader::ARRIVED)                  
                              ->orWhere('trans_manifest_header.status', '=', ManifestHeader::CLOSED_WARNING)                  
                              ->orWhere('trans_manifest_header.status', '=', ManifestHeader::CLOSED);                  
                    })
                    ->where('trans_manifest_header.money_trip', '>', 0);
        return $query;
    }
}
