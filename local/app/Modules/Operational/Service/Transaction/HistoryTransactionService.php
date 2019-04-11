<?php

namespace App\Modules\Operational\Service\Transaction;

use App\Modules\Operational\Model\Transaction\HistoryTransaction;

class HistoryTransactionService
{
    public static function saveHistory($type, $transactionId, $transactionNumber, $transactionName, $description, $data)
    {
        $history = new HistoryTransaction();
        $history->type = $type;
        $history->transaction_id = $transactionId;
        $history->transaction_number = $transactionNumber;
        $history->transaction_name = $transactionName;
        $history->description = $description;
        $history->data = json_encode($data);
        $history->transaction_date = new \DateTime();
        $history->user_id = \Auth::user()->id;
        $history->branch_id = \Session::get('currentBranch')->branch_id;
        $history->role_id = \Session::get('currentRole')->id;

        $history->save();
    }
}
