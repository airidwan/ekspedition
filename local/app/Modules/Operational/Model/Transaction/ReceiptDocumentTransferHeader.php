<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;

class ReceiptDocumentTransferHeader extends Model
{
    protected $connection = 'operational';
    protected $table      = 'trans_receipt_document_transfer_header';
    protected $primaryKey = 'receipt_document_transfer_header_id';

    public $timestamps = false;

    public function lines()
    {
        return $this->hasMany(ReceiptDocumentTransferLine::class, 'receipt_document_transfer_header_id');
    }

    public function documentTransferHeader()
    {
        return $this->belongsTo(DocumentTransferHeader::class, 'document_transfer_header_id');
    }
}
