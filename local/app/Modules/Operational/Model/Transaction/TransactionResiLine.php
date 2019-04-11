<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Master\MasterShippingPrice;

class TransactionResiLine extends Model
{
    protected $connection = 'operational';
    protected $table      = 'trans_resi_line';
    public $timestamps    = false;

    protected $primaryKey = 'resi_line_id';

    public function isMenangWeight()
    {
        return $this->price_weight >= $this->total_price && $this->totalPriceVolume() < $this->total_price;
    }

    public function isMenangVolume()
    {
        return $this->price_weight < $this->total_price && $this->totalPriceVolume() >= $this->total_price;
    }

    public function lineVolume()
    {
        return $this->hasMany(TransactionResiLineVolume::class, 'resi_line_id');
    }

    public function totalVolume()
    {
        $totalVolume = 0;
        foreach ($this->lineVolume as $lineVolume) {
            $totalVolume += $lineVolume->total_volume;
        }

        return $totalVolume;
    }

    public function totalPriceVolume()
    {
        $totalPriceVolume = 0;
        foreach ($this->lineVolume as $lineVolume) {
            $totalPriceVolume += $lineVolume->price_volume;
        }

        return $totalPriceVolume;
    }

    public function shippingPrice()
    {
        return $this->belongsTo(MasterShippingPrice::class, 'unit_id');
    }
}
