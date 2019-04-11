<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;

class ReceiptDocumentTransferLine extends Model
{
    protected $connection = 'operational';
    protected $table      = 'trans_receipt_document_transfer_line';
    protected $primaryKey = 'receipt_document_transfer_line_id';

    public $timestamps = false;

    public function header()
    {
        return $this->belongsTo(ReceiptDocumentTransferHeader::class, 'receipt_document_transfer_header_id');
    }

    public function documentTransferLine()
    {
        return $this->belongsTo(DocumentTransferLine::class, 'document_transfer_line_id');
    }
}
