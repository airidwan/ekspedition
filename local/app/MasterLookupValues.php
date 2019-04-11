<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterLookupValues extends Model
{
    protected $connection = 'adm';
    protected $table      = 'mst_lookup_values';
    protected $primaryKey = 'id';

    public $timestamps = false;

    const MERK_KENDARAAN      = 'MERK_KENDARAAN';
    const TIPE_KENDARAAN      = 'TIPE_KENDARAAN';
    const KATEGORI_KENDARAAN = 'KATEGORI_KENDARAAN';

}
