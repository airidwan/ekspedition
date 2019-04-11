<?php

namespace App\Modules\Generalledger\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Generalledger\Model\Master\MasterAccountCombination;

class JournalLine extends Model
{
    protected $connection = 'gl';
    protected $table      = 'trans_journal_line';
    protected $primaryKey = 'journal_line_id';

    public $timestamps    = false;

    public function header()
    {
        return $this->belongsTo(JournalHeader::class, 'journal_header_id');
    }

    public function accountCombination()
    {
        return $this->belongsTo(MasterAccountCombination::class, 'account_combination_id');
    }
}
