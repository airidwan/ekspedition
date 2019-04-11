<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Transaction\ManifestHeader;

class ReceiptManifestHeader extends Model
{
    protected $connection = 'operational';
    protected $table      = 'trans_manifest_receipt_header';
    protected $primaryKey = 'manifest_receipt_header_id';

    public $timestamps = false;

    public function lines()
    {
        return $this->hasMany(ReceiptManifestLine::class, 'manifest_receipt_header_id');
    }

    public function manifest()
    {
        return $this->belongsTo(ManifestHeader::class, 'manifest_header_id');
    }
}
