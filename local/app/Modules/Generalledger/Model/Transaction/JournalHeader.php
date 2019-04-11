<?php

namespace App\Modules\Generalledger\Model\Transaction;

use Illuminate\Database\Eloquent\Model;

class JournalHeader extends Model
{
    const MANUAL                = 'Manual';
    const SALARY                = 'Salary';
    const INVOICE_RESI          = 'Invoice Resi';
    const CANCEL_INVOICE_RESI   = 'Cancel Invoice Resi';
    const INVOICE_PICKUP        = 'Invoice Pickup';
    const INVOICE_DO            = 'Invoice DO';
    const INVOICE_EXTRA_COST    = 'Invoice Extra Cost';
    const DISCOUNT_INVOICE      = 'Discount Invoice';
    const RECEIPT_RESI          = 'Receipt Resi';
    const RECEIPT_PICKUP        = 'Receipt Pickup';
    const RECEIPT_DO            = 'Receipt DO';
    const RECEIPT_EXTRA_COST    = 'Receipt Extra Cost';
    const RECEIPT_KASBON        = 'Receipt Kasbon';
    const RECEIPT_ASSET_SELLING = 'Receipt Asset Selling';
    const RECEIPT_OTHER         = 'Receipt Other';
    const CEK_GIRO              = 'Cek / Giro';

    const RECEIPT_PO                   = 'Receipt Purchase Order';
    const RETURN_PO                    = 'Return Purchase Order';
    const INVOICE_PO                   = 'Invoice Purchase Order';
    const CANCEL_INVOICE_PO            = 'Cancel Invoice Purchase Order';
    const INVOICE_DP                   = 'Invoice Down Payment';
    const CANCEL_INVOICE_DP            = 'Cancel Invoice Down Payment';
    const INVOICE_DRIVER_SALARY        = 'Invoice Driver Salary';
    const CANCEL_INVOICE_DRIVER_SALARY = 'Cancel Invoice Driver Salary';
    const INVOICE_DO_PARTNER           = 'Invoice DO Partner';
    const CANCEL_INVOICE_DO_PARTNER    = 'Cancel Invoice DO Partner';
    const INVOICE_MANIFEST_MONEY_TRIP  = 'Invoice Manifest Money Trip';
    const CANCEL_INVOICE_MANIFEST_MONEY_TRIP  = 'Cancel Invoice Manifest Money Trip';
    const INVOICE_DO_PICKUP_MONEY_TRIP = 'Invoice DO/Pickup Money Trip';
    const CANCEL_INVOICE_DO_PICKUP_MONEY_TRIP = 'Cancel Invoice DO/Pickup Money Trip';
    const INVOICE_SERVICE              = 'Invoice Service';
    const CANCEL_INVOICE_SERVICE       = 'Cancel Invoice Service';
    const INVOICE_KAS_BON              = 'Invoice Kas Bon';
    const CANCEL_INVOICE_KAS_BON       = 'Cancel Invoice Kas Bon';
    const INVOICE_OTHER                = 'Invoice Other';
    const CANCEL_INVOICE_OTHER         = 'Cancel Invoice Other';
    const PAYMENT                      = 'Payment';

    const MOVE_ORDER                   = 'Move Order';
    const ADJUSTMENT                   = 'Adjustment';
    const ADDITION_ASSET               = 'Addition Asset';
    const DEPRECIATION                 = 'Depreciation';
    const BRANCH_TRANSFER_TRANSACT     = 'Branch Transfer Transact';
    const BRANCH_TRANSFER_RECEIPT      = 'Branch Transfer Receipt';

    const OPEN                  = 'Open';
    const POST                  = 'Post';
    const RESERVED              = 'Reserved';

    protected $connection = 'gl';
    protected $table      = 'trans_journal_header';
    protected $primaryKey = 'journal_header_id';

    public $timestamps    = false;

    public function lines()
    {
        return $this->hasMany(JournalLine::class, 'journal_header_id');
    }

    public function totalDebet()
    {
        $totalDebet = 0;
        foreach ($this->lines as $line) {
            $totalDebet += $line->debet;
        }

        return $totalDebet;
    }

    public function totalCredit()
    {
        $totalCredit = 0;
        foreach ($this->lines as $line) {
            $totalCredit += $line->credit;
        }

        return $totalCredit;
    }

    public function isOpen()
    {
        return $this->status == self::OPEN;
    }

    public function isPost()
    {
        return $this->status == self::POST;
    }

    public function isReserved()
    {
        return $this->status == self::RESERVED;
    }
}
