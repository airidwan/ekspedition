<?php

namespace App\Modules\Operational\Model\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\User;
use App\Role;

class HistoryTransaction extends Model
{
    protected $connection = 'operational';
    protected $table      = 'history_transaction';
    protected $primaryKey = 'history_transaction_id';

    public $timestamps = false;

    const RESI     = 'Resi';
    const MANIFEST = 'Manifest';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function branch()
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id');
    }
}
