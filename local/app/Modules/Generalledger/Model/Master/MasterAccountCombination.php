<?php

namespace App\Modules\Generalledger\Model\Master;

use Illuminate\Database\Eloquent\Model;

class MasterAccountCombination extends Model
{
    protected $connection = 'gl';
    protected $table      = 'mst_account_combination';
    protected $primaryKey = 'account_combination_id';

    public $timestamps     = false;

    public function company(){
        return $this->belongsTo(MasterCoa::class, 'segment_1');
    }
    public function costCenter(){
        return $this->belongsTo(MasterCoa::class, 'segment_2');
    }
    public function account(){
        return $this->belongsTo(MasterCoa::class, 'segment_3');
    }
    public function subAccount(){
        return $this->belongsTo(MasterCoa::class, 'segment_4');
    }
    public function future(){
        return $this->belongsTo(MasterCoa::class, 'segment_5');
    }
    public function getCombinationCode(){
        $company = $this->company()->first();
        $costCenter = $this->costCenter()->first();
        $account = $this->account()->first();
        $subAccount = $this->subAccount()->first();
        $future = $this->future()->first();
        return $company->coa_code.'.'.$costCenter->coa_code.'.'.$account->coa_code.'.'.$subAccount->coa_code.'.'.$future->coa_code;
    }

    public function getCombinationDescription(){
        $company = $this->company()->first();
        $costCenter = $this->costCenter()->first();
        $account = $this->account()->first();
        $subAccount = $this->subAccount()->first();
        $future = $this->future()->first();

        $companyDesc    = !empty($company) ? $company->description : '';
        $costCenterDesc = !empty($costCenter) ? $costCenter->description : '';
        $accountDesc    = !empty($account) ? $account->description : '';
        $subAccountDesc = !empty($subAccount) ? $subAccount->description : '';
        $futureDesc     = !empty($future) ? $future->description : '';

        return $companyDesc.'.'.$costCenterDesc.'.'.$accountDesc.'.'.$subAccountDesc.'.'.$futureDesc;
    }
}
