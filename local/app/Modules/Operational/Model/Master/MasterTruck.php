<?php

namespace App\Modules\Operational\Model\Master;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Asset\Model\Transaction\AdditionAsset;

class MasterTruck extends Model
{
    protected $connection = 'operational';
    protected $table      = 'mst_truck';
    public $timestamps     = false;

    protected $primaryKey = 'truck_id';

    const BRAND    = 'MERK_KENDARAAN';
    const TYPE     = 'TIPE_KENDARAAN';
    const CATEGORY = 'KATEGORI_KENDARAAN';

    const ASSET        = 'ASSET';
    const SEWA_BULANAN = 'SEWA_BULANAN';
    const SEWA_TRIP    = 'SEWA_TRIP';

    public function truckBranch()
    {
        return $this->hasMany(DetailTruckBranch::class, 'truck_id');
    }

    public function truckRent()
    {
        return $this->belongsTo(DetailTruckRent::class, 'truck_id');
    }

    public function asset()
    {
        return $this->belongsTo(AdditionAsset::class, 'asset_id');
    }

    public function getCategory()
    {
        if ($this->isAsset()) {
            return 'Asset';
        } elseif ($this->isSewaBulanan()) {
            return 'Sewa Per Bulan';
        } else {
            return 'Sewa Per Trip';
        }
    }

    public function getBrand()
    {
        $brand = \DB::table('adm.mst_lookup_values')->where('lookup_code', '=', $this->brand)->first();
        return $brand->meaning;
    }

    public function getType()
    {
        $brand = \DB::table('adm.mst_lookup_values')->where('lookup_code', '=', $this->type)->first();
        return $brand->meaning;
    }

    public function isAsset()
    {
        return $this->category == self::ASSET;
    }

    public function isSewaBulanan()
    {
        return $this->category == self::SEWA_BULANAN;
    }

    public function isSewaTrip()
    {
        return $this->category == self::SEWA_TRIP;
    }
}
