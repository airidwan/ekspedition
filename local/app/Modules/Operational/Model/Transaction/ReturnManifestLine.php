<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Transaction\ManifestLine;
use App\Modules\Operational\Model\Master\TransactionResiHeader;

class ReturnManifestLine extends Model
{
    protected $connection = 'operational';
    protected $table      = 'trans_manifest_return_line';
    protected $primaryKey = 'manifest_return_line_id';

    public $timestamps = false;

    public function header()
    {
        return $this->belongsTo(ReturnManifestHeader::class, 'trans_manifest_return_header');
    }

    public function manifestLine()
    {
        return $this->belongsTo(ManifestLine::class, 'manifest_line_id');
    }
}
