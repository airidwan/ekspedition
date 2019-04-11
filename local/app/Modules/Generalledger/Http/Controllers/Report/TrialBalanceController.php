<?php

namespace App\Modules\Generalledger\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Service\Penomoran;

class TrialBalanceController extends Controller
{
    const RESOURCE = 'Generalledger\Report\BalanceSheet';
    const URL      = 'general-ledger/report/trial-balance';

    protected $now;

    public function __construct()
    {
        $this->middleware('auth');
        $this->now = new \DateTime();
    }

    public function index(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        if ($request->isMethod('post')) {
            $request->session()->put('filters', $request->all());
        } elseif (empty($request->get('page'))) {
            $request->session()->forget('filters');
        }

        $filters = $request->session()->get('filters');

        $queryAssetKas         = null;
        $queryAssetBank        = null;
        $queryAssetPersediaan  = null;
        $queryAssetSewa        = null;
        $queryAssetPiutang     = null;
        $queryAsset            = null;
        $querySusut            = null;
        $queryLiability        = null;
        $queryLiabilityEquitas = null;
        $queryProfitLoss       = 0;

        if(!empty($filters['accountCode'] || $filters['accountDescription'] || ($filters['periodMonth'] && $filters['periodYear']) || $filters['dateFrom']|| $filters['dateTo'])){
            $queryAssetKas          = $this->getQueryAssetKas($request, $filters);
            $queryAssetBank         = $this->getQueryAssetBank($request, $filters);
            $queryAssetPersediaan   = $this->getQueryAssetPersediaan($request, $filters);
            $queryAssetSewa         = $this->getQueryAssetSewa($request, $filters);
            $queryAssetPiutang      = $this->getQueryAssetPiutang($request, $filters);
            $queryAsset             = $this->getQueryAsset($request, $filters);
            $querySusut             = $this->getQuerySusut($request, $filters);
            $queryLiability         = $this->getQueryLiability($request, $filters);
            $queryLiabilityEquitas  = $this->getQueryLiabilityEquitas($request, $filters);
            $queryProfitLoss        = $this->getQueryProfitLoss($request, $filters);
        }

        return view('generalledger::report.trial-balance.index', [
            'assetKas'          => empty($queryAssetKas) ? [] : $queryAssetKas->get(),
            'assetBank'         => empty($queryAssetBank) ? [] : $queryAssetBank->get(),
            'assetPersediaan'   => empty($queryAssetPersediaan) ? [] : $queryAssetPersediaan->get(),
            'assetSewa'         => empty($queryAssetSewa) ? [] : $queryAssetSewa->get(),
            'assetPiutang'      => empty($queryAssetPiutang) ? [] : $queryAssetPiutang->get(),
            'asset'             => empty($queryAsset) ? [] : $queryAsset->get(),
            'liability'         => empty($queryLiability) ? [] : $queryLiability->get(),
            'liabilityEquitas'  => empty($queryLiabilityEquitas) ? [] : $queryLiabilityEquitas->get(),
            'assetPenyusutan'   => empty($querySusut) ? [] : $querySusut->get(),
            'profitLoss'        => $queryProfitLoss,
            'filters'           => $filters,
            'optionBranch'      => $this->getAllBranch(),
            'optionPeriodMonth' => $this->getOptionMonth(),
            'optionPeriodYear'  => $this->getOptionYear(),
            'resource'          => self::RESOURCE,
            'url'               => self::URL,
            ]);
    }

    protected function getQueryAssetKas(Request $request, $filters){
        $queryAssetKas   = \DB::table('gl.mst_coa')
                            ->select('mst_coa.coa_code', 'mst_coa.description as coa_description', \DB::raw('SUM(trans_journal_line.debet) - SUM(trans_journal_line.credit)  as debet'), \DB::raw('SUM(trans_journal_line.credit) as credit'))
                            ->leftjoin('gl.mst_account_combination', 'mst_account_combination.segment_3', '=', 'mst_coa.coa_id')
                            ->leftjoin('gl.trans_journal_line', 'trans_journal_line.account_combination_id', '=', 'mst_account_combination.account_combination_id')
                            ->leftjoin('gl.trans_journal_header', 'trans_journal_header.journal_header_id', '=', 'trans_journal_line.journal_header_id')
                            // ->where('trans_journal_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->whereIn('mst_coa.coa_code', MasterCoa::ACTIVA_KAS)
                            ->groupBy('mst_coa.coa_code', 'mst_coa.description')
                            ->orderBy('debet', 'desc');

        if (!empty($filters['accountCode'])) {
            $queryAssetKas->where('mst_coa.coa_code', 'ilike', '%'.$filters['accountCode'].'%');
        }

        if (!empty($filters['branchId'])) {
            $queryAssetKas->where('trans_journal_header.branch_id', '=', $filters['branchId']);
        }

        if (!empty($filters['accountDescription'])) {
            $queryAssetKas->where('mst_coa.description', 'ilike', '%'.$filters['accountDescription'].'%');
        }

        if (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) {
            $period = new \DateTime($filters['periodYear'].'-'.$filters['periodMonth'].'-1');
            $queryAssetKas->where('trans_journal_header.period', '>=', $period->format('Y-m-1 00:00:00'))
            ->where('trans_journal_header.period', '<=', $period->format('Y-m-t 23:59:59'));
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $queryAssetKas->where('trans_journal_header.journal_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $queryAssetKas->where('trans_journal_header.journal_date', '<=', $date->format('Y-m-d 23:59:59'));
        }
        return $queryAssetKas;
    }

    protected function getQueryAssetBank(Request $request, $filters){
        $queryAssetBank   = \DB::table('gl.mst_coa')
                            ->select('mst_coa.coa_code', 'mst_coa.description as coa_description', \DB::raw('SUM(trans_journal_line.debet) - SUM(trans_journal_line.credit)  as debet'), \DB::raw('SUM(trans_journal_line.credit) as credit'))
                            ->leftjoin('gl.mst_account_combination', 'mst_account_combination.segment_3', '=', 'mst_coa.coa_id')
                            ->leftjoin('gl.trans_journal_line', 'trans_journal_line.account_combination_id', '=', 'mst_account_combination.account_combination_id')
                            ->leftjoin('gl.trans_journal_header', 'trans_journal_header.journal_header_id', '=', 'trans_journal_line.journal_header_id')
                            // ->where('trans_journal_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->whereIn('mst_coa.coa_code', MasterCoa::ACTIVA_BANK)
                            ->groupBy('mst_coa.coa_code', 'mst_coa.description')
                            ->orderBy('debet', 'desc');

        if (!empty($filters['accountCode'])) {
            $queryAssetBank->where('mst_coa.coa_code', 'ilike', '%'.$filters['accountCode'].'%');
        }

        if (!empty($filters['branchId'])) {
            $queryAssetBank->where('trans_journal_header.branch_id', '=', $filters['branchId']);
        }

        if (!empty($filters['accountDescription'])) {
            $queryAssetBank->where('mst_coa.description', 'ilike', '%'.$filters['accountDescription'].'%');
        }

        if (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) {
            $period = new \DateTime($filters['periodYear'].'-'.$filters['periodMonth'].'-1');
            $queryAssetBank->where('trans_journal_header.period', '>=', $period->format('Y-m-1 00:00:00'))
            ->where('trans_journal_header.period', '<=', $period->format('Y-m-t 23:59:59'));
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $queryAssetBank->where('trans_journal_header.journal_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $queryAssetBank->where('trans_journal_header.journal_date', '<=', $date->format('Y-m-d 23:59:59'));
        }
        return $queryAssetBank;
    }

    protected function getQueryAssetPersediaan(Request $request, $filters){
        $queryAssetPersediaan   = \DB::table('gl.mst_coa')
                            ->select('mst_coa.coa_code', 'mst_coa.description as coa_description', \DB::raw('SUM(trans_journal_line.debet) - SUM(trans_journal_line.credit)  as debet'), \DB::raw('SUM(trans_journal_line.credit) as credit'))
                            ->leftjoin('gl.mst_account_combination', 'mst_account_combination.segment_3', '=', 'mst_coa.coa_id')
                            ->leftjoin('gl.trans_journal_line', 'trans_journal_line.account_combination_id', '=', 'mst_account_combination.account_combination_id')
                            ->leftjoin('gl.trans_journal_header', 'trans_journal_header.journal_header_id', '=', 'trans_journal_line.journal_header_id')
                            // ->where('trans_journal_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->whereIn('mst_coa.coa_code', MasterCoa::ACTIVA_PERSEDIAAN)
                            ->groupBy('mst_coa.coa_code', 'mst_coa.description')
                            ->orderBy('debet', 'desc');

        if (!empty($filters['accountCode'])) {
            $queryAssetPersediaan->where('mst_coa.coa_code', 'ilike', '%'.$filters['accountCode'].'%');
        }

        if (!empty($filters['branchId'])) {
            $queryAssetPersediaan->where('trans_journal_header.branch_id', '=', $filters['branchId']);
        }

        if (!empty($filters['accountDescription'])) {
            $queryAssetPersediaan->where('mst_coa.description', 'ilike', '%'.$filters['accountDescription'].'%');
        }

        if (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) {
            $period = new \DateTime($filters['periodYear'].'-'.$filters['periodMonth'].'-1');
            $queryAssetPersediaan->where('trans_journal_header.period', '>=', $period->format('Y-m-1 00:00:00'))
            ->where('trans_journal_header.period', '<=', $period->format('Y-m-t 23:59:59'));
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $queryAssetPersediaan->where('trans_journal_header.journal_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $queryAssetPersediaan->where('trans_journal_header.journal_date', '<=', $date->format('Y-m-d 23:59:59'));
        }
        return $queryAssetPersediaan;
    }

    protected function getQueryAssetSewa(Request $request, $filters){
        $queryAssetSewa   = \DB::table('gl.mst_coa')
                            ->select('mst_coa.coa_code', 'mst_coa.description as coa_description', \DB::raw('SUM(trans_journal_line.debet) - SUM(trans_journal_line.credit)  as debet'), \DB::raw('SUM(trans_journal_line.credit) as credit'))
                            ->leftjoin('gl.mst_account_combination', 'mst_account_combination.segment_3', '=', 'mst_coa.coa_id')
                            ->leftjoin('gl.trans_journal_line', 'trans_journal_line.account_combination_id', '=', 'mst_account_combination.account_combination_id')
                            ->leftjoin('gl.trans_journal_header', 'trans_journal_header.journal_header_id', '=', 'trans_journal_line.journal_header_id')
                            // ->where('trans_journal_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->whereIn('mst_coa.coa_code', MasterCoa::ACTIVA_SEWA)
                            ->groupBy('mst_coa.coa_code', 'mst_coa.description')
                            ->orderBy('debet', 'desc');

        if (!empty($filters['accountCode'])) {
            $queryAssetSewa->where('mst_coa.coa_code', 'ilike', '%'.$filters['accountCode'].'%');
        }

        if (!empty($filters['branchId'])) {
            $queryAssetSewa->where('trans_journal_header.branch_id', '=', $filters['branchId']);
        }

        if (!empty($filters['accountDescription'])) {
            $queryAssetSewa->where('mst_coa.description', 'ilike', '%'.$filters['accountDescription'].'%');
        }

        if (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) {
            $period = new \DateTime($filters['periodYear'].'-'.$filters['periodMonth'].'-1');
            $queryAssetSewa->where('trans_journal_header.period', '>=', $period->format('Y-m-1 00:00:00'))
            ->where('trans_journal_header.period', '<=', $period->format('Y-m-t 23:59:59'));
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $queryAssetSewa->where('trans_journal_header.journal_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $queryAssetSewa->where('trans_journal_header.journal_date', '<=', $date->format('Y-m-d 23:59:59'));
        }
        return $queryAssetSewa;
    }

    protected function getQueryAssetPiutang(Request $request, $filters){
        $queryAssetPiutang   = \DB::table('gl.mst_coa')
                            ->select('mst_coa.coa_code', 'mst_coa.description as coa_description', \DB::raw('SUM(trans_journal_line.debet) - SUM(trans_journal_line.credit)  as debet'), \DB::raw('SUM(trans_journal_line.credit) as credit'))
                            ->leftjoin('gl.mst_account_combination', 'mst_account_combination.segment_3', '=', 'mst_coa.coa_id')
                            ->leftjoin('gl.trans_journal_line', 'trans_journal_line.account_combination_id', '=', 'mst_account_combination.account_combination_id')
                            ->leftjoin('gl.trans_journal_header', 'trans_journal_header.journal_header_id', '=', 'trans_journal_line.journal_header_id')
                            // ->where('trans_journal_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->whereIn('mst_coa.coa_code', MasterCoa::ACTIVA_PIUTANG)
                            ->groupBy('mst_coa.coa_code', 'mst_coa.description')
                            ->orderBy('debet', 'desc');

        if (!empty($filters['accountCode'])) {
            $queryAssetPiutang->where('mst_coa.coa_code', 'ilike', '%'.$filters['accountCode'].'%');
        }

        if (!empty($filters['branchId'])) {
            $queryAssetPiutang->where('trans_journal_header.branch_id', '=', $filters['branchId']);
        }

        if (!empty($filters['accountDescription'])) {
            $queryAssetPiutang->where('mst_coa.description', 'ilike', '%'.$filters['accountDescription'].'%');
        }

        if (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) {
            $period = new \DateTime($filters['periodYear'].'-'.$filters['periodMonth'].'-1');
            $queryAssetPiutang->where('trans_journal_header.period', '>=', $period->format('Y-m-1 00:00:00'))
            ->where('trans_journal_header.period', '<=', $period->format('Y-m-t 23:59:59'));
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $queryAssetPiutang->where('trans_journal_header.journal_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $queryAssetPiutang->where('trans_journal_header.journal_date', '<=', $date->format('Y-m-d 23:59:59'));
        }
        return $queryAssetPiutang;
    }

    protected function getQueryAsset(Request $request, $filters){
        $queryAsset   = \DB::table('gl.mst_coa')
                            ->select('mst_coa.coa_code', 'mst_coa.description as coa_description', \DB::raw('SUM(trans_journal_line.debet) - SUM(trans_journal_line.credit)  as debet'), \DB::raw('SUM(trans_journal_line.credit) as credit'))
                            ->leftjoin('gl.mst_account_combination', 'mst_account_combination.segment_3', '=', 'mst_coa.coa_id')
                            ->leftjoin('gl.trans_journal_line', 'trans_journal_line.account_combination_id', '=', 'mst_account_combination.account_combination_id')
                            ->leftjoin('gl.trans_journal_header', 'trans_journal_header.journal_header_id', '=', 'trans_journal_line.journal_header_id')
                            // ->where('trans_journal_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->whereIn('mst_coa.coa_code', MasterCoa::ACTIVA_ASSET)
                            ->groupBy('mst_coa.coa_code', 'mst_coa.description')
                            ->orderBy('debet', 'desc');

        if (!empty($filters['accountCode'])) {
            $queryAsset->where('mst_coa.coa_code', 'ilike', '%'.$filters['accountCode'].'%');
        }

        if (!empty($filters['branchId'])) {
            $queryAsset->where('trans_journal_header.branch_id', '=', $filters['branchId']);
        }

        if (!empty($filters['accountDescription'])) {
            $queryAsset->where('mst_coa.description', 'ilike', '%'.$filters['accountDescription'].'%');
        }

        if (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) {
            $period = new \DateTime($filters['periodYear'].'-'.$filters['periodMonth'].'-1');
            $queryAsset->where('trans_journal_header.period', '>=', $period->format('Y-m-1 00:00:00'))
            ->where('trans_journal_header.period', '<=', $period->format('Y-m-t 23:59:59'));
        }

        // if (!empty($filters['dateFrom'])) {
        //     $date = new \DateTime($filters['dateFrom']);
        //     $queryAsset->where('trans_journal_header.journal_date', '>=', $date->format('Y-m-d 00:00:00'));
        // }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $queryAsset->where('trans_journal_header.journal_date', '<=', $date->format('Y-m-d 23:59:59'));
        }
        return $queryAsset;
    }

    protected function getQuerySusut(Request $request, $filters){
        $queryAssetKas   = \DB::table('gl.mst_coa')
                            ->select('mst_coa.coa_code', 'mst_coa.description as coa_description', \DB::raw('SUM(trans_journal_line.debet) - SUM(trans_journal_line.credit) as debet'), \DB::raw('SUM(trans_journal_line.credit) as credit'))
                            ->leftjoin('gl.mst_account_combination', 'mst_account_combination.segment_3', '=', 'mst_coa.coa_id')
                            ->leftjoin('gl.trans_journal_line', 'trans_journal_line.account_combination_id', '=', 'mst_account_combination.account_combination_id')
                            ->leftjoin('gl.trans_journal_header', 'trans_journal_header.journal_header_id', '=', 'trans_journal_line.journal_header_id')
                            // ->where('trans_journal_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->whereIn('mst_coa.coa_code', MasterCoa::SUSUT)
                            ->groupBy('mst_coa.coa_code', 'mst_coa.description')
                            ->orderBy('debet', 'desc');

        if (!empty($filters['accountCode'])) {
            $queryAssetKas->where('mst_coa.coa_code', 'ilike', '%'.$filters['accountCode'].'%');
        }

        if (!empty($filters['branchId'])) {
            $queryAssetKas->where('trans_journal_header.branch_id', '=', $filters['branchId']);
        }

        if (!empty($filters['accountDescription'])) {
            $queryAssetKas->where('mst_coa.description', 'ilike', '%'.$filters['accountDescription'].'%');
        }

        if (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) {
            $period = new \DateTime($filters['periodYear'].'-'.$filters['periodMonth'].'-1');
            $queryAssetKas->where('trans_journal_header.period', '>=', $period->format('Y-m-1 00:00:00'))
            ->where('trans_journal_header.period', '<=', $period->format('Y-m-t 23:59:59'));
        }

        // if (!empty($filters['dateFrom'])) {
        //     $date = new \DateTime($filters['dateFrom']);
        //     $queryAssetKas->where('trans_journal_header.journal_date', '>=', $date->format('Y-m-d 00:00:00'));
        // }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $queryAssetKas->where('trans_journal_header.journal_date', '<=', $date->format('Y-m-d 23:59:59'));
        }
        return $queryAssetKas;
    }

    protected function getQueryLiability(Request $request, $filters){
        $queryLiability   = \DB::table('gl.mst_coa')
                            ->select('mst_coa.coa_code', 'mst_coa.description as coa_description', \DB::raw('SUM(trans_journal_line.debet) as debet'), \DB::raw('SUM(trans_journal_line.debet) - SUM(trans_journal_line.credit) as credit'))
                            ->leftjoin('gl.mst_account_combination', 'mst_account_combination.segment_3', '=', 'mst_coa.coa_id')
                            ->leftjoin('gl.trans_journal_line', 'trans_journal_line.account_combination_id', '=', 'mst_account_combination.account_combination_id')
                            ->leftjoin('gl.trans_journal_header', 'trans_journal_header.journal_header_id', '=', 'trans_journal_line.journal_header_id')
                            // ->where('trans_journal_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->whereIn('mst_coa.coa_code', MasterCoa::PASIVA_HUTANG)
                            ->groupBy('mst_coa.coa_code', 'mst_coa.description')
                            ->orderBy('credit', 'desc');

        if (!empty($filters['accountCode'])) {
            $queryLiability->where('mst_coa.coa_code', 'ilike', '%'.$filters['accountCode'].'%');
        }

        if (!empty($filters['branchId'])) {
            $queryLiability->where('trans_journal_header.branch_id', '=', $filters['branchId']);
        }

        if (!empty($filters['accountDescription'])) {
            $queryLiability->where('mst_coa.description', 'ilike', '%'.$filters['accountDescription'].'%');
        }

        if (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) {
            $period = new \DateTime($filters['periodYear'].'-'.$filters['periodMonth'].'-1');
            $queryLiability->where('trans_journal_header.period', '>=', $period->format('Y-m-1 00:00:00'))
            ->where('trans_journal_header.period', '<=', $period->format('Y-m-t 23:59:59'));
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $queryLiability->where('trans_journal_header.journal_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $queryLiability->where('trans_journal_header.journal_date', '<=', $date->format('Y-m-d 23:59:59'));
        }
        return $queryLiability;
    }

    protected function getQueryLiabilityEquitas(Request $request, $filters){
        $queryLiabilityEquitas   = \DB::table('gl.mst_coa')
                            ->select('mst_coa.coa_code', 'mst_coa.description as coa_description', \DB::raw('SUM(trans_journal_line.debet) as debet'), \DB::raw('SUM(trans_journal_line.debet) - SUM(trans_journal_line.credit) as credit'))
                            ->leftjoin('gl.mst_account_combination', 'mst_account_combination.segment_3', '=', 'mst_coa.coa_id')
                            ->leftjoin('gl.trans_journal_line', 'trans_journal_line.account_combination_id', '=', 'mst_account_combination.account_combination_id')
                            ->leftjoin('gl.trans_journal_header', 'trans_journal_header.journal_header_id', '=', 'trans_journal_line.journal_header_id')
                            // ->where('trans_journal_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->whereIn('mst_coa.coa_code', MasterCoa::PASIVA_EQUITAS)
                            ->groupBy('mst_coa.coa_code', 'mst_coa.description')
                            ->orderBy('credit', 'desc');

        if (!empty($filters['accountCode'])) {
            $queryLiabilityEquitas->where('mst_coa.coa_code', 'ilike', '%'.$filters['accountCode'].'%');
        }

        if (!empty($filters['branchId'])) {
            $queryLiabilityEquitas->where('trans_journal_header.branch_id', '=', $filters['branchId']);
        }

        if (!empty($filters['accountDescription'])) {
            $queryLiabilityEquitas->where('mst_coa.description', 'ilike', '%'.$filters['accountDescription'].'%');
        }

        if (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) {
            $period = new \DateTime($filters['periodYear'].'-'.$filters['periodMonth'].'-1');
            $queryLiabilityEquitas->where('trans_journal_header.period', '>=', $period->format('Y-m-1 00:00:00'))
            ->where('trans_journal_header.period', '<=', $period->format('Y-m-t 23:59:59'));
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $queryLiabilityEquitas->where('trans_journal_header.journal_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $queryLiabilityEquitas->where('trans_journal_header.journal_date', '<=', $date->format('Y-m-d 23:59:59'));
        }
        return $queryLiabilityEquitas;
    }

    protected function getQueryProfitLoss(Request $request, $filters){
        $totalRevenue   = 0;
        $totalDeduction = 0;
        $totalExpense   = 0;

        $queryRevenue   = $this->getQueryRevenue($request, $filters);
        foreach ($queryRevenue->get() as $model) {
            $totalRevenue += $model->credit;
        }

        $queryDeduction = $this->getQueryDeduction($request, $filters);
        foreach ($queryDeduction->get() as $model) {
            $totalDeduction += $model->debet;
        }

        $queryExpense   = $this->getQueryExpense($request, $filters);
        foreach ($queryExpense->get() as $model) {
            $totalExpense += $model->debet;
        }

        return $totalRevenue - $totalDeduction - $totalExpense;
    }

    protected function getQueryRevenue(Request $request, $filters){
        $queryRevenue   = \DB::table('gl.mst_coa')
                                ->select('mst_coa.coa_code', 'mst_coa.description as coa_description', \DB::raw('SUM(trans_journal_line.debet) as debet'), \DB::raw('SUM(trans_journal_line.credit) as credit'))
                                ->leftjoin('gl.mst_account_combination', 'mst_account_combination.segment_3', '=', 'mst_coa.coa_id')
                                ->leftjoin('gl.trans_journal_line', 'trans_journal_line.account_combination_id', '=', 'mst_account_combination.account_combination_id')
                                ->leftjoin('gl.trans_journal_header', 'trans_journal_header.journal_header_id', '=', 'trans_journal_line.journal_header_id')
                                // ->where('trans_journal_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                                ->whereIn('mst_coa.coa_code', MasterCoa::PENDAPATAN)
                                ->groupBy('mst_coa.coa_code', 'mst_coa.description')
                                ->orderBy('credit', 'desc');

        if (!empty($filters['accountCode'])) {
            $queryRevenue->where('mst_coa.coa_code', 'ilike', '%'.$filters['accountCode'].'%');
        }

        if (!empty($filters['branchId'])) {
            $queryRevenue->where('trans_journal_header.branch_id', '=', $filters['branchId']);
        }

        if (!empty($filters['accountDescription'])) {
            $queryRevenue->where('mst_coa.description', 'ilike', '%'.$filters['accountDescription'].'%');
        }

        if (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) {
            $period = new \DateTime($filters['periodYear'].'-'.$filters['periodMonth'].'-1');
            $queryRevenue->where('trans_journal_header.period', '>=', $period->format('Y-m-1 00:00:00'))
            ->where('trans_journal_header.period', '<=', $period->format('Y-m-t 23:59:59'));
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $queryRevenue->where('trans_journal_header.journal_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $queryRevenue->where('trans_journal_header.journal_date', '<=', $date->format('Y-m-d 23:59:59'));
        }
        return $queryRevenue;
    }

     protected function getQueryDeduction(Request $request, $filters){
        $queryDeduction   = \DB::table('gl.mst_coa')
                                ->select('mst_coa.coa_code', 'mst_coa.description as coa_description', \DB::raw('SUM(trans_journal_line.debet) as debet'), \DB::raw('SUM(trans_journal_line.credit) as credit'))
                                ->leftjoin('gl.mst_account_combination', 'mst_account_combination.segment_3', '=', 'mst_coa.coa_id')
                                ->leftjoin('gl.trans_journal_line', 'trans_journal_line.account_combination_id', '=', 'mst_account_combination.account_combination_id')
                                ->leftjoin('gl.trans_journal_header', 'trans_journal_header.journal_header_id', '=', 'trans_journal_line.journal_header_id')
                                // ->where('trans_journal_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                                ->whereIn('mst_coa.coa_code', MasterCoa::POTONGAN)
                                ->groupBy('mst_coa.coa_code', 'mst_coa.description')
                                ->orderBy('debet', 'desc');

        if (!empty($filters['accountCode'])) {
            $queryDeduction->where('mst_coa.coa_code', 'ilike', '%'.$filters['accountCode'].'%');
        }

        if (!empty($filters['branchId'])) {
            $queryDeduction->where('trans_journal_header.branch_id', '=', $filters['branchId']);
        }

        if (!empty($filters['accountDescription'])) {
            $queryDeduction->where('mst_coa.description', 'ilike', '%'.$filters['accountDescription'].'%');
        }

        if (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) {
            $period = new \DateTime($filters['periodYear'].'-'.$filters['periodMonth'].'-1');
            $queryDeduction->where('trans_journal_header.period', '>=', $period->format('Y-m-1 00:00:00'))
            ->where('trans_journal_header.period', '<=', $period->format('Y-m-t 23:59:59'));
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $queryDeduction->where('trans_journal_header.journal_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $queryDeduction->where('trans_journal_header.journal_date', '<=', $date->format('Y-m-d 23:59:59'));
        }
        return $queryDeduction;
    }

    protected function getQueryExpense(Request $request, $filters){
        $queryExpense   = \DB::table('gl.mst_coa')
                            ->select('mst_coa.coa_code', 'mst_coa.description as coa_description', \DB::raw('SUM(trans_journal_line.debet) as debet'), \DB::raw('SUM(trans_journal_line.credit) as credit'))
                            ->join('gl.mst_account_combination', 'mst_account_combination.segment_3', '=', 'mst_coa.coa_id')
                            ->join('gl.trans_journal_line', 'trans_journal_line.account_combination_id', '=', 'mst_account_combination.account_combination_id')
                            ->join('gl.trans_journal_header', 'trans_journal_header.journal_header_id', '=', 'trans_journal_line.journal_header_id')
                            // ->where('trans_journal_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->whereIn('mst_coa.coa_code', MasterCoa::BIAYA)
                            // ->where('mst_coa.identifier', '=', MasterCoa::EXPENSE)
                            ->groupBy('mst_coa.coa_code', 'mst_coa.description')
                            ->orderBy('debet', 'desc');
        if (!empty($filters['accountCode'])) {
            $queryExpense->where('mst_coa.coa_code', 'ilike', '%'.$filters['accountCode'].'%');
        }

        if (!empty($filters['branchId'])) {
            $queryExpense->where('trans_journal_header.branch_id', '=', $filters['branchId']);
        }

        if (!empty($filters['accountDescription'])) {
            $queryExpense->where('mst_coa.description', 'ilike', '%'.$filters['accountDescription'].'%');
        }

        if (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) {
            $period = new \DateTime($filters['periodYear'].'-'.$filters['periodMonth'].'-1');
            $queryExpense->where('trans_journal_header.period', '>=', $period->format('Y-m-1 00:00:00'))
            ->where('trans_journal_header.period', '<=', $period->format('Y-m-t 23:59:59'));
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $queryExpense->where('trans_journal_header.journal_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $queryExpense->where('trans_journal_header.journal_date', '<=', $date->format('Y-m-d 23:59:59'));
        }
        return $queryExpense;
    }

    public function printPdf(Request $request)
    {
        $filters         = \Session::get('filters');

        $queryAssetKas          = $this->getQueryAssetKas($request, $filters);
        $queryAssetBank         = $this->getQueryAssetBank($request, $filters);
        $queryAssetPersediaan   = $this->getQueryAssetPersediaan($request, $filters);
        $queryAssetSewa         = $this->getQueryAssetSewa($request, $filters);
        $queryAssetPiutang      = $this->getQueryAssetPiutang($request, $filters);
        $queryAsset             = $this->getQueryAsset($request, $filters);
        $querySusut             = $this->getQuerySusut($request, $filters);
        $queryLiability         = $this->getQueryLiability($request, $filters);
        $queryLiabilityEquitas  = $this->getQueryLiabilityEquitas($request, $filters);
        $queryProfitLoss        = $this->getQueryProfitLoss($request, $filters);


        $header = view('print.header-pdf', ['title' => trans('general-ledger/menu.trial-balance')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });
        $html = view('generalledger::report.trial-balance.print-pdf', [
            'assetKas'          => empty($queryAssetKas) ? [] : $queryAssetKas->get(),
            'assetBank'         => empty($queryAssetBank) ? [] : $queryAssetBank->get(),
            'assetPersediaan'   => empty($queryAssetPersediaan) ? [] : $queryAssetPersediaan->get(),
            'assetSewa'         => empty($queryAssetSewa) ? [] : $queryAssetSewa->get(),
            'assetPiutang'      => empty($queryAssetPiutang) ? [] : $queryAssetPiutang->get(),
            'asset'             => empty($queryAsset) ? [] : $queryAsset->get(),
            'liability'         => empty($queryLiability) ? [] : $queryLiability->get(),
            'liabilityEquitas'  => empty($queryLiabilityEquitas) ? [] : $queryLiabilityEquitas->get(),
            'assetPenyusutan'   => empty($querySusut) ? [] : $querySusut->get(),
            'profitLoss'        => $queryProfitLoss,
            'filters'           => $filters,
        ])->render();

        \PDF::SetTitle(trans('general-ledger/menu.trial-balance'));
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('P', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output(trans('general-ledger/menu.trial-balance').'.pdf');
        \PDF::reset();
    }

    public function printExcel(Request $request)
    {
        $filters = \Session::get('filters');
        $queryAssetKas   = [];
        $queryLiability = [];

        if(!empty($filters['accountCode'] || $filters['accountDescription'] || ($filters['periodMonth'] && $filters['periodYear']) || $filters['dateFrom']|| $filters['dateTo'])){
            $queryAssetKas          = $this->getQueryAssetKas($request, $filters)->get();
            $queryAssetBank         = $this->getQueryAssetBank($request, $filters)->get();
            $queryAssetPersediaan   = $this->getQueryAssetPersediaan($request, $filters)->get();
            $queryAssetSewa         = $this->getQueryAssetSewa($request, $filters)->get();
            $queryAssetPiutang      = $this->getQueryAssetPiutang($request, $filters)->get();
            $queryAsset             = $this->getQueryAsset($request, $filters)->get();
            $querySusut             = $this->getQuerySusut($request, $filters)->get();
            $queryLiability         = $this->getQueryLiability($request, $filters)->get();
            $queryLiabilityEquitas  = $this->getQueryLiabilityEquitas($request, $filters)->get();
            $queryProfitLoss        = $this->getQueryProfitLoss($request, $filters);
        }

        \Excel::create(trans('general-ledger/menu.trial-balance'), function($excel) use ($queryAssetKas, $queryAssetBank, $queryAssetPersediaan, $queryAssetSewa, $queryAssetPiutang, $queryAsset,$querySusut, $queryLiability, $queryLiabilityEquitas, $queryProfitLoss, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($queryAssetKas, $queryAssetBank, $queryAssetPersediaan, $queryAssetSewa, $queryAssetPiutang, $queryAsset,$querySusut, $queryLiability, $queryLiabilityEquitas, $queryProfitLoss, $filters) {
                $currentRow = 1;

                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('general-ledger/menu.trial-balance'));
                });

                $currentRow += 2;

                $sheet->cells('A'.$currentRow, function($cells) {
                    $cells->setFont(['size' => '12', 'bold' => true]);
                    $cells->setValue('Deduction');
                });

                $sheet->cells('A'.$currentRow.':D'.$currentRow, function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('general-ledger/fields.account-description'),
                    trans('general-ledger/fields.account-code'),
                    trans('general-ledger/fields.amount'),
                ]);
                $totalDebetAsset   = 0;
                $totalCreditAsset  = 0;
                $totalBalanceAsset = 0;


                foreach($queryAssetKas as $index => $model) {
                    $date = !empty($model->period) ? new \DateTime($model->period) : null;
                    $balanceAsset       = $model->debet - $model->credit;
                    $totalBalanceAsset += $balanceAsset;
                    $totalDebetAsset   += $model->debet;
                    $totalCreditAsset  += $model->credit;
                    $data = [
                        $index + 1,
                        $model->coa_description,
                        $model->coa_code,
                        $model->debet,
                    ];
                    $sheet->row($index + $currentRow, $data);
                }

                $currentRow = count($queryAssetKas) + 4;

                foreach($querySusut as $index => $model) {
                    $date = !empty($model->period) ? new \DateTime($model->period) : null;
                    $balanceAsset       = $model->debet - $model->credit;
                    $totalBalanceAsset += $balanceAsset;
                    $totalDebetAsset   += $model->debet;
                    $totalCreditAsset  += $model->credit;
                    $data = [
                        $currentRow - 4 + $index + 1,
                        $model->coa_description,
                        $model->coa_code,
                        $model->debet,
                    ];
                    $sheet->row($index + $currentRow, $data);
                }
                $currentRow += count($querySusut);

                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'C', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalDebetAsset, 'D', $currentRow);

                $currentRow += 2;

                $sheet->cells('A'.$currentRow.':D'.$currentRow, function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });
                $sheet->row($currentRow++, [
                    trans('shared/common.num'),
                    trans('general-ledger/fields.account-description'),
                    trans('general-ledger/fields.account-code'),
                    trans('general-ledger/fields.amount'),
                ]);

                $totalDebetLiability   = 0;
                $totalCreditLiability  = 0;
                $totalBalanceLiability = 0;
                $sem = 0;
                foreach($queryLiability as $index => $model) {
                    $date = !empty($model->period) ? new \DateTime($model->period) : null;
                    $balanceLiability       = $model->debet - $model->credit;
                    $totalBalanceLiability += $balanceLiability;
                    $totalDebetLiability   += $model->debet;
                    $totalCreditLiability  += $model->credit;
                    $data = [
                        $index + 1,
                        $model->coa_description,
                        $model->coa_code,
                        $model->credit,
                    ];
                    $sheet->row($index + $currentRow, $data);
                    $sem = $index +1;
                }
                $currentRow += count($queryLiability);

                $dataSem = [
                            ++$sem,
                            'RUGI/LABA',
                            '',
                            $queryProfitLoss,
                        ];
                $sheet->row($currentRow, $dataSem);

                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'C', ++$currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalCreditLiability + $queryProfitLoss, 'D', $currentRow);

                $currentRow += 2;
                $tempRow     = $currentRow;
                if (!empty($filters['accountCode'])) {
                    $this->addLabelDescriptionCell($sheet, trans('general-ledger/fields.account-code'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['accountCode'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['accountDescription'])) {
                    $this->addLabelDescriptionCell($sheet, trans('general-ledger/fields.account-description'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['accountDescription'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.period'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['periodMonth'].'-'.$filters['periodYear'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['branchId'])) {
                    $branch = MasterBranch::find($filters['branchId']);
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.branch'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $branch->branch_name, 'C', $currentRow);
                    $currentRow++;
                }else{
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.branch'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  'All Branch', 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['dateFrom'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.date-from'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['dateFrom'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['dateTo'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.date-to'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['dateTo'], 'C', $currentRow);
                    $currentRow++;
                }

                $this->addLabelDescriptionCell($sheet, trans('shared/common.date'), 'E', $tempRow);
                $this->addValueDescriptionCell($sheet, $this->now->format('d-m-Y'), 'F', $tempRow);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.user'), 'E', $tempRow + 1);
                $this->addValueDescriptionCell($sheet, \Auth::user()->full_name, 'F', $tempRow + 1);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.branch'), 'E', $tempRow + 2);
                $this->addValueDescriptionCell($sheet, \Session::get('currentBranch')->branch_name, 'F', $tempRow + 2);
            });

        })->export('xlsx');
    }

    protected function addLabelDescriptionCell($sheet, $value, $column, $row)
    {
        $sheet->cell($column.$row, function($cell) use($value) {
            $cell->setFont(['bold' => true]);
            $cell->setValue($value);
        });
    }

    protected function addValueDescriptionCell($sheet, $value, $column, $row)
    {
        $sheet->cell($column.$row, function($cell) use($value) {
            $cell->setValue($value);
        });
    }

    protected function getAllBranch(){
        return \DB::table('op.mst_branch')->where('active', 'Y')->orderBy('branch_code')->get();
    }

    protected function getOptionMonth(){
        return [
        1  => 'Jan',
        2  => 'Feb',
        3  => 'Mar',
        4  => 'Apr',
        5  => 'May',
        6  => 'Jun',
        7  => 'Jul',
        8  => 'Aug',
        9  => 'Sep',
        10 => 'Oct',
        11 => 'Nov',
        12 => 'Des',
        ];
    }

    protected function getOptionLimit(){
        return [
            50,
            100,
            500,
            1000,
        ];
    }

    protected function getOptionYear()
    {
        $optionYear = [];
        for ($i = $this->now->format('Y'); $i > $this->now->format('Y') - 10; $i--) {
            $optionYear[] = $i;
        }

        return $optionYear;
    }
}
