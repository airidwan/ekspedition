<?php

namespace App\Modules\Operational\Service\Master;

use App\Modules\Operational\Model\Master\MasterDriver;

class DriverService
{
    public static function getActiveDriverAsistant()
    {
        return \DB::table('op.v_mst_driver')
            ->select('v_mst_driver.*')
            ->leftJoin('op.dt_driver_branch', 'v_mst_driver.driver_id', '=', 'dt_driver_branch.driver_id')
            ->where('dt_driver_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
            ->where('v_mst_driver.active', '=', 'Y')
            ->where('dt_driver_branch.active', '=', 'Y')
            ->distinct()
            ->get();
    }

    public static function getActiveDriverAsistantDistinct()
    {
        $query = \DB::table('op.mst_driver')
            ->select('mst_driver.*', 'position.meaning as position')
            ->leftJoin('op.dt_driver_branch', 'mst_driver.driver_id', '=', 'dt_driver_branch.driver_id')
            ->leftJoin('adm.mst_lookup_values as position', 'position.lookup_code', '=', 'mst_driver.position')
            ->where('dt_driver_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
            ->where('mst_driver.active', '=', 'Y')
            ->distinct()
            ->get();

        // $arrDriver = [];
        // foreach ($query as $driver) {
        //     if (isset($arrDriver[$driver->identity_number])) {
        //         // continue;
        //     }

        //     $arrDriver [$driver->identity_number] = [
        //         'driver_id'       => $driver->driver_id,
        //         'driver_code'     => $driver->driver_code,
        //         'identity_number' => $driver->identity_number,
        //         'driver_name'     => $driver->driver_name,
        //         'address'         => $driver->address,
        //         'phone_number'    => $driver->phone_number,
        //         'description'     => $driver->description,
        //         'position'        => $driver->position
        //     ];
        // }
        return $query;
    }

    public static function getActiveAllDriverAsistantDistinct()
    {
        $query = \DB::table('op.v_mst_driver')
            ->select('v_mst_driver.*')
            ->leftJoin('op.dt_driver_branch', 'v_mst_driver.driver_id', '=', 'dt_driver_branch.driver_id')
            ->where('v_mst_driver.active', '=', 'Y')
            ->distinct()
            ->get();

        $arrDriver = [];
        foreach ($query as $driver) {
            if (isset($arrDriver[$driver->identity_number])) {
                // continue;
            }

            $arrDriver [$driver->identity_number] = [
                'driver_id'       => $driver->driver_id,
                'driver_code'     => $driver->driver_code,
                'identity_number' => $driver->identity_number,
                'driver_name'     => $driver->driver_name,
                'address'         => $driver->address,
                'phone_number'    => $driver->phone_number,
                'description'     => $driver->description
            ];
        }
        return $arrDriver;
    }

    public static function getActiveDriver()
    {
        return \DB::table('op.mst_driver')
            ->select('mst_driver.*')
            ->leftJoin('op.dt_driver_branch', 'mst_driver.driver_id', '=', 'dt_driver_branch.driver_id')
            ->where('dt_driver_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
            ->where('mst_driver.position', '=', MasterDriver::DRIVER)
            ->where('mst_driver.active', '=', 'Y')
            ->where('dt_driver_branch.active', '=', 'Y')
            ->distinct()
            ->get();
    }

    public static function getActiveAssistant()
    {
    return \DB::table('op.mst_driver')
            ->select('mst_driver.*')
            ->leftJoin('op.dt_driver_branch', 'mst_driver.driver_id', '=', 'dt_driver_branch.driver_id')
            ->where('dt_driver_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
            ->where('mst_driver.position', '=', MasterDriver::ASSISTANT)
            ->where('mst_driver.active', '=', 'Y')
            ->where('dt_driver_branch.active', '=', 'Y')
            ->distinct()
            ->get();
    }

    public static function getQueryDriver()
    {
        return \DB::table('op.mst_driver')
                    ->select('mst_driver.*')
                    ->join('op.dt_driver_branch', 'dt_driver_branch.driver_id', '=', 'mst_driver.driver_id')
                    ->leftJoin('adm.mst_lookup_values', 'mst_lookup_values.lookup_code', '=', 'mst_driver.position')
                    ->where('dt_driver_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where('mst_driver.active', '=', 'Y')
                    ->distinct();
    }
}
