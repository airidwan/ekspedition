<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Transaction\ManifestHeader;

class ReturnManifestHeader extends Model
{
    protected $connection = 'operational';
    protected $table      = 'trans_manifest_return_header';
    protected $primaryKey = 'manifest_return_header_id';

    public $timestamps = false;

    public function lines()
    {
        return $this->hasMany(ReturnManifestLine::class, 'manifest_return_header_id');
    }

    public function manifest()
    {
        return $this->belongsTo(ManifestHeader::class, 'manifest_header_id');
    }
}
