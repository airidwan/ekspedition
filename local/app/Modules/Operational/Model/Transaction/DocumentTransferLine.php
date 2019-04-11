<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Transaction\DocumentTransferHeader;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Master\MasterBranch;

class DocumentTransferLine extends Model
{
    protected $connection = 'operational';
    protected $table      = 'trans_document_transfer_line';
    public $timestamps    = false;

    protected $primaryKey = 'document_transfer_line_id';

    public function header()
    {
        return $this->belongsTo(DocumentTransferHeader::class, 'document_transfer_header_id');
    }

    public function resi()
    {
        return $this->belongsTo(TransactionResiHeader::class, 'resi_header_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(MasterBranch::class, 'to_branch_id');
    }
}
