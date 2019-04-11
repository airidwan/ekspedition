<?php

namespace App\Modules\Payable\Service\Master;

use App\Modules\Payable\Model\Master\MasterVendor;

class VendorService
{
    public static function getQueryVendorEmployee()
    {
        return \DB::table('ap.mst_vendor')
                    ->select('mst_vendor.*', 'mst_lookup_values.meaning as category_meaning')
                    ->join('ap.dt_vendor_branch', 'dt_vendor_branch.vendor_id', '=', 'mst_vendor.vendor_id')
                    ->leftJoin('adm.mst_lookup_values', 'mst_lookup_values.lookup_code', '=', 'mst_vendor.category')
                    ->where('dt_vendor_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where('category', '=', MasterVendor::EMPLOYEE)
                    ->where('mst_vendor.active', '=', 'Y')
                    ->distinct();
    }

    public static function getQueryAllVendorEmployee()
    {
        return \DB::table('ap.mst_vendor')
                    ->select('mst_vendor.*', 'mst_lookup_values.meaning as category_meaning')
                    ->leftJoin('adm.mst_lookup_values', 'mst_lookup_values.lookup_code', '=', 'mst_vendor.category')
                    ->where('category', '=', MasterVendor::EMPLOYEE)
                    ->where('mst_vendor.active', '=', 'Y')
                    ->distinct();
    }

    public static function getQueryVendorSupplier()
    {
        return \DB::table('ap.mst_vendor')
                    ->select('mst_vendor.*', 'mst_lookup_values.meaning as category_meaning')
                    ->join('ap.dt_vendor_branch', 'dt_vendor_branch.vendor_id', '=', 'mst_vendor.vendor_id')
                    ->leftJoin('adm.mst_lookup_values', 'mst_lookup_values.lookup_code', '=', 'mst_vendor.category')
                    ->where('dt_vendor_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where('category', '=', MasterVendor::VENDOR)
                    ->where('mst_vendor.active', '=', 'Y')
                    ->distinct();
    }

    public static function getQueryVendorMitra()
    {
        return \DB::table('ap.mst_vendor')
                    ->select('mst_vendor.*', 'mst_lookup_values.meaning as category_meaning')
                    ->join('ap.dt_vendor_branch', 'dt_vendor_branch.vendor_id', '=', 'mst_vendor.vendor_id')
                    ->leftJoin('adm.mst_lookup_values', 'mst_lookup_values.lookup_code', '=', 'mst_vendor.category')
                    ->where('dt_vendor_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where('category', '=', MasterVendor::MITRA)
                    ->where('mst_vendor.active', '=', 'Y')
                    ->distinct();
    }

    public static function getQueryVendorAll()
    {
        return \DB::table('ap.mst_vendor')
                    ->select('mst_vendor.*', 'mst_lookup_values.meaning as category_meaning')
                    ->join('ap.dt_vendor_branch', 'dt_vendor_branch.vendor_id', '=', 'mst_vendor.vendor_id')
                    ->leftJoin('adm.mst_lookup_values', 'mst_lookup_values.lookup_code', '=', 'mst_vendor.category')
                    ->where('dt_vendor_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where('mst_vendor.active', '=', 'Y')
                    ->distinct();
                    
    }
}
