<?php

namespace App\Modules\Generalledger\Service\Master;

use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Modules\Generalledger\Model\Master\MasterAccountCombination;
use App\Modules\Generalledger\Model\Master\SettingJournal;

class AccountCombinationService
{
    public static function getCombination($accountCode, $subAccountCode = null, $branchId = null, $futureCode = null)
    {
        $account = self::getAccountFromCode($accountCode);
        $subAccount = $subAccountCode !== null ? self::getSubAccountFromCode($subAccountCode) : self::getDefaultSubAccount();
        $costCenter = $branchId !== null ? self::getCostCenterBranchId($branchId) : self::getCostCenterBranchId(\Session('currentBranch')->branch_id);
        $future = $futureCode !== null ? self::getFutureFromCode($futureCode) : self::getDefaultFuture();

        $combination = self::getAccountCombination($costCenter->coa_id, $account->coa_id, $subAccount->coa_id, $future->coa_id);
        if ($combination === null) {
            $combination = self::createAccountCombination($costCenter->coa_id, $account->coa_id, $subAccount->coa_id, $future->coa_id);
        }

        return $combination;
    }

    public static function getCombinationDefault()
    {
        $account = self::getAccountFromCode($accountCode);
        $subAccount = $subAccountCode !== null ? self::getSubAccountFromCode($subAccountCode) : self::getDefaultSubAccount();
        $costCenter = $branchId !== null ? self::getCostCenterBranchId($branchId) : self::getCostCenterBranchId(\Session('currentBranch')->branch_id);
        $future = $futureCode !== null ? self::getFutureFromCode($futureCode) : self::getDefaultFuture();

        $combination = self::getAccountCombination($costCenter->coa_id, $account->coa_id, $subAccount->coa_id, $future->coa_id);
        if ($combination === null) {
            $combination = self::createAccountCombination($costCenter->coa_id, $account->coa_id, $subAccount->coa_id, $future->coa_id);
        }

        return $combination;
    }

    protected static function getAccountCombination($costCenterId, $accountId, $subAccountId, $futureId)
    {
        return MasterAccountCombination::where('segment_2', '=', $costCenterId)
                                        ->where('segment_3', '=', $accountId)
                                        ->where('segment_4', '=', $subAccountId)
                                        ->where('segment_5', '=', $futureId)
                                        ->first();
    }

    protected static function createAccountCombination($costCenterId, $accountId, $subAccountId, $futureId)
    {
        $combination  = new MasterAccountCombination();
        $organization = \DB::table('gl.mst_coa')->where('segment_name', '=', MasterCoa::COMPANY)->where('coa_code', '=', MasterCoa::COMPANY_CODE)->first();

        $combination->segment_1 = $organization->coa_id;
        $combination->segment_2 = $costCenterId;
        $combination->segment_3 = $accountId;
        $combination->segment_4 = $subAccountId;
        $combination->segment_5 = $futureId;
        $combination->active    = 'Y';
        $combination->created_date = new \DateTime();

        if (\Auth::user() !== null) {
            $combination->created_by   = \Auth::user()->id;
        }

        $combination->save();

        return $combination;
    }

    protected static function getDefaultAccount()
    {
        $settingDefaultSubAccount = \DB::table('gl.mst_setting_journal')->where('setting_name', '=', SettingJournal::DEFAULT_SUB_ACCOUNT)->first();

        return \DB::table('gl.mst_coa')->where('coa_id', '=', $settingDefaultSubAccount->coa_id)->first();
    }

    protected static function getAccountFromCode($accountCode)
    {
        return \DB::table('gl.mst_coa')->where('segment_name', '=', MasterCoa::ACCOUNT)->where('coa_code', '=', $accountCode)->first();
    }

    protected static function getSubAccountFromCode($subAccountCode)
    {
        return \DB::table('gl.mst_coa')->where('segment_name', '=', MasterCoa::SUB_ACCOUNT)->where('coa_code', '=', $subAccountCode)->first();
    }

    protected static function getDefaultSubAccount()
    {
        $settingDefaultSubAccount = \DB::table('gl.mst_setting_journal')->where('setting_name', '=', SettingJournal::DEFAULT_SUB_ACCOUNT)->first();

        return \DB::table('gl.mst_coa')->where('coa_id', '=', $settingDefaultSubAccount->coa_id)->first();
    }

    protected static function getFutureFromCode($futureCode)
    {
        return \DB::table('gl.mst_coa')->where('segment_name', '=', MasterCoa::FUTURE)->where('coa_code', '=', $futureCode)->first();
    }

    protected static function getDefaultFuture()
    {
        $settingDefaultFuture = \DB::table('gl.mst_setting_journal')->where('setting_name', '=', SettingJournal::DEFAULT_FUTURE_1)->first();

        return \DB::table('gl.mst_coa')->where('coa_id', '=', $settingDefaultFuture->coa_id)->first();
    }

    protected static function getCostCenterBranchId($branchId)
    {
        $branch = MasterBranch::find($branchId);

        return \DB::table('gl.mst_coa')->where('segment_name', '=', MasterCoa::COST_CENTER)->where('coa_code', '=', $branch->cost_center_code)->first();
    }
}