<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Transaction\ManifestLine;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;

class ReceiptManifestLine extends Model
{
    protected $connection = 'operational';
    protected $table      = 'trans_manifest_receipt_line';
    protected $primaryKey = 'manifest_receipt_line_id';

    public $timestamps = false;

    public function header()
    {
        return $this->belongsTo(ReceiptManifestHeader::class, 'trans_manifest_receipt_header');
    }

    public function item()
    {
        return $this->belongsTo(TransactionResiHeader::class, 'resi_header_id');
    }

    public function manifestLine()
    {
        return $this->belongsTo(ManifestLine::class, 'manifest_line_id');
    }
}
