<?php

namespace App\Modules\Generalledger\Model\Master;

use Illuminate\Database\Eloquent\Model;

class SettingJournal extends Model
{
    const COMPANY     = 'Company';
    const COST_CENTER = 'Cost Center';
    const ACCOUNT     = 'Account';
    const SUB_ACCOUNT = 'Sub Account';
    const FUTURE      = 'Future 1';

    const DEFAULT_SUB_ACCOUNT  = 'Default Sub Account';
    const DEFAULT_FUTURE_1     = 'Default Future 1';
    const PPN_MASUKAN          = 'PPN Masukan';
    const INTEREST             = 'Interest';
    const INTRANSIT_INVENTORY  = 'Intransit Inventory';
    const PIUTANG_USAHA        = 'Piutang Usaha';
    const PENDAPATAN_UTAMA     = 'Pendapatan Utama';
    const PENDAPATAN_LAIN_LAIN = 'Pendapatan Lain Lain';
    const DISKON               = 'Diskon';
    const CEK_GIRO             = 'Cek / Giro';
    const INVENTORY_ADJUSTMENT = 'Inventory Adjustment';
    const KELEBIHAN_PEMBAYARAN = 'Kelebihan Pembayaran';
    const PEMBULATAN           = 'Pembulatan';
    const LABA_PENJUALAN_ASSET = 'Laba Penjualan Asset';
    const RUGI_PENJUALAN_ASSET = 'Rugi Penjualan Asset';
    const HUTANG_USAHA         = 'Hutang Usaha';

    protected $connection = 'gl';
    protected $table      = 'mst_setting_journal';
    protected $primaryKey = 'setting_journal_id';

    public $timestamps     = false;

    public function coa(){
        return $this->belongsTo(MasterCoa::class, 'coa_id');
    }
}
