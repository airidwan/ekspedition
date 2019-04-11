<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;

class ManifestLine extends Model
{
    protected $connection = 'operational';
    protected $table      = 'trans_manifest_line';
    protected $primaryKey = 'manifest_line_id';

    public $timestamps = false;

    public function header()
    {
        return $this->belongsTo(ManifestHeader::class, 'manifest_header_id');
    }

    public function resi()
    {
        return $this->belongsTo(TransactionResiHeader::class, 'resi_header_id');
    }

    public function receiptManifestLines()
    {
        return $this->hasMany(ReceiptManifestLine::class, 'manifest_line_id');
    }

    public function totalColyReceived()
    {
        $totalColyReceived = 0;
        foreach($this->receiptManifestLines as $receiptManifestLine) {
            $totalColyReceived += $receiptManifestLine->coly_receipt;
        }

        return $totalColyReceived;
    }

    public function remainingColyReceipt()
    {
        return $this->coly_sent - $this->totalColyReceived();
    }
}
