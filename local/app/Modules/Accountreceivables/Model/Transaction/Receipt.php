<?php

namespace App\Modules\Accountreceivables\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Generalledger\Model\Master\MasterBank;
use App\Modules\Accountreceivables\Model\Master\MasterCekGiro;
use App\Modules\Accountreceivables\Model\Transaction\CekGiroLine;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Payable\Model\Transaction\InvoiceHeader as InvoiceApHeader;
use App\Modules\Asset\Model\Transaction\AdditionAsset;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\User;

class Receipt extends Model
{
    const DP            = 'Down Payment';
    const REGULER       = 'Reguler';
    const BATCH         = 'Batch';
    const CEK_GIRO      = 'Cek/Giro';
    const EXTRA_COST    = 'Extra Cost';
    const KASBON        = 'Kasbon';
    const ASSET_SELLING = 'Asset Selling';
    const OTHER         = 'Other';

    const CASH     = 'Cash';
    const TRANSFER = 'Transfer';

    protected $connection = 'ar';
    protected $table      = 'receipt';
    protected $primaryKey = 'receipt_id';

    public $timestamps = false;

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function resi()
    {
        return $this->belongsTo(TransactionResiHeader::class, 'resi_header_id');
    }

    public function invoiceApHeader()
    {
        return $this->belongsTo(InvoiceApHeader::class, 'invoice_ap_header_id');
    }

    public function additionAsset()
    {
        return $this->belongsTo(AdditionAsset::class, 'asset_id');
    }

    public function bank()
    {
        return $this->belongsTo(MasterBank::class, 'bank_id');
    }

    public function cekGiroLine()
    {
        return $this->belongsTo(CekGiroLine::class, 'cek_giro_line_id');
    }

    public function batchInvoiceLine()
    {
        return $this->belongsTo(BatchInvoiceLine::class, 'batch_invoice_line_id');
    }

    public function coa()
    {
        return $this->belongsTo(MasterCoa::class, 'coa_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDp()
    {
        return $this->type == self::DP;
    }

    public function isReguler()
    {
        return $this->type == $this::REGULER;
    }

    public function isCekGiro()
    {
        return $this->type == $this::CEK_GIRO;
    }

    public function isBatch()
    {
        return $this->type == $this::BATCH;
    }

    public function isExtraCost()
    {
        return $this->type == $this::EXTRA_COST;
    }

    public function isKasbon()
    {
        return $this->type == $this::KASBON;
    }

    public function isAssetSelling()
    {
        return $this->type == $this::ASSET_SELLING;
    }

    public function isOther()
    {
        return $this->type == $this::OTHER;
    }

    public function isCash()
    {
        return $this->receipt_method == $this::CASH;
    }

    public function isTransfer()
    {
        return $this->receipt_method == $this::TRANSFER;
    }
}
