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

class IncomeController extends Controller
{
    const RESOURCE = 'Generalledger\Report\IncomeStatement';
    const URL      = 'general-ledger/report/income';

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

        $queryRevenue  = null;
        $queryDeduction = null;
        $queryBebanOperasional = null;
        $queryBebanAdministrasi = null;
        $queryBebanLain = null;
        $queryBebanPajak = null;
        $queryPendapatanLain = null;

        if(!empty($filters['accountCode'] || $filters['accountDescription'] || ($filters['periodMonth'] && $filters['periodYear']) || $filters['dateFrom']|| $filters['dateTo'])){
            $queryRevenue           = $this->getQueryRevenue($request, $filters);
            $queryDeduction         = $this->getQueryDeduction($request, $filters);
            $queryBebanOperasional  = $this->getQueryBebanOperasional($request, $filters);
            $queryBebanAdministrasi = $this->getQueryBebanAdministrasi($request, $filters);
            $queryBebanLain         = $this->getQueryBebanLain($request, $filters);
            $queryBebanPajak        = $this->getQueryBebanPajak($request, $filters);
            $queryPendapatanLain    = $this->getQueryPendapatanLain($request, $filters);
        }

        return view('generalledger::report.income.index', [
            'revenue'           => empty($queryRevenue) ? [] : $queryRevenue->get(),
            'deduction'         => empty($queryDeduction) ? [] : $queryDeduction->get(),
            'bebanOperasional'  => empty($queryBebanOperasional) ? [] : $queryBebanOperasional->get(),
            'bebanAdministrasi' => empty($queryBebanAdministrasi) ? [] : $queryBebanAdministrasi->get(),
            'bebanLain'         => empty($queryBebanLain) ? [] : $queryBebanLain->get(),
            'bebanPajak'        => empty($queryBebanPajak) ? [] : $queryBebanPajak->get(),
            'pendapatanLain'    => empty($queryPendapatanLain) ? [] : $queryPendapatanLain->get(),
            'filters'           => $filters,
            'optionBranch'      => $this->getAllBranch(),
            'optionPeriodMonth' => $this->getOptionMonth(),
            'optionPeriodYear'  => $this->getOptionYear(),
            'resource'          => self::RESOURCE,
            'url'               => self::URL,
            ]);
    }

    protected function getQueryRevenue(Request $request, $filters){
        $queryRevenue   = \DB::table('gl.mst_coa')
                                ->select('mst_coa.coa_code', 'mst_coa.description as coa_description', \DB::raw('SUM(trans_journal_line.debet) as debet'), \DB::raw('SUM(trans_journal_line.credit) as credit'))
                                ->leftjoin('gl.mst_account_combination', 'mst_account_combination.segment_3', '=', 'mst_coa.coa_id')
                                ->leftjoin('gl.trans_journal_line', 'trans_journal_line.account_combination_id', '=', 'mst_account_combination.account_combination_id')
                                ->leftjoin('gl.trans_journal_header', 'trans_journal_header.journal_header_id', '=', 'trans_journal_line.journal_header_id')
                                // ->where('trans_journal_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                                ->whereIn('mst_coa.coa_code', MasterCoa::PENDAPATAN_UTAMA)
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

    protected function getQueryPendapatanLain(Request $request, $filters){
        $queryPendapatanLain   = \DB::table('gl.mst_coa')
                                ->select('mst_coa.coa_code', 'mst_coa.description as coa_description', \DB::raw('SUM(trans_journal_line.debet) as debet'), \DB::raw('SUM(trans_journal_line.credit) as credit'))
                                ->leftjoin('gl.mst_account_combination', 'mst_account_combination.segment_3', '=', 'mst_coa.coa_id')
                                ->leftjoin('gl.trans_journal_line', 'trans_journal_line.account_combination_id', '=', 'mst_account_combination.account_combination_id')
                                ->leftjoin('gl.trans_journal_header', 'trans_journal_header.journal_header_id', '=', 'trans_journal_line.journal_header_id')
                                // ->where('trans_journal_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                                ->whereIn('mst_coa.coa_code', MasterCoa::PENDAPATAN_LAIN)
                                ->groupBy('mst_coa.coa_code', 'mst_coa.description')
                                ->orderBy('credit', 'desc');

        if (!empty($filters['accountCode'])) {
            $queryPendapatanLain->where('mst_coa.coa_code', 'ilike', '%'.$filters['accountCode'].'%');
        }

        if (!empty($filters['branchId'])) {
            $queryPendapatanLain->where('trans_journal_header.branch_id', '=', $filters['branchId']);
        }

        if (!empty($filters['accountDescription'])) {
            $queryPendapatanLain->where('mst_coa.description', 'ilike', '%'.$filters['accountDescription'].'%');
        }

        if (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) {
            $period = new \DateTime($filters['periodYear'].'-'.$filters['periodMonth'].'-1');
            $queryPendapatanLain->where('trans_journal_header.period', '>=', $period->format('Y-m-1 00:00:00'))
            ->where('trans_journal_header.period', '<=', $period->format('Y-m-t 23:59:59'));
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $queryPendapatanLain->where('trans_journal_header.journal_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $queryPendapatanLain->where('trans_journal_header.journal_date', '<=', $date->format('Y-m-d 23:59:59'));
        }
        return $queryPendapatanLain;
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

    protected function getQueryBebanOperasional(Request $request, $filters){
        $queryBebanOperasional   = \DB::table('gl.mst_coa')
                            ->select('mst_coa.coa_code', 'mst_coa.description as coa_description', \DB::raw('SUM(trans_journal_line.debet) as debet'), \DB::raw('SUM(trans_journal_line.credit) as credit'))
                            ->join('gl.mst_account_combination', 'mst_account_combination.segment_3', '=', 'mst_coa.coa_id')
                            ->join('gl.trans_journal_line', 'trans_journal_line.account_combination_id', '=', 'mst_account_combination.account_combination_id')
                            ->join('gl.trans_journal_header', 'trans_journal_header.journal_header_id', '=', 'trans_journal_line.journal_header_id')
                            // ->where('trans_journal_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->whereIn('mst_coa.coa_code', MasterCoa::BEBAN_OPERASIONAL)
                            // ->where('mst_coa.identifier', '=', MasterCoa::EXPENSE)
                            ->groupBy('mst_coa.coa_code', 'mst_coa.description')
                            ->orderBy('debet', 'desc');
        if (!empty($filters['accountCode'])) {
            $queryBebanOperasional->where('mst_coa.coa_code', 'ilike', '%'.$filters['accountCode'].'%');
        }

        if (!empty($filters['branchId'])) {
            $queryBebanOperasional->where('trans_journal_header.branch_id', '=', $filters['branchId']);
        }

        if (!empty($filters['accountDescription'])) {
            $queryBebanOperasional->where('mst_coa.description', 'ilike', '%'.$filters['accountDescription'].'%');
        }

        if (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) {
            $period = new \DateTime($filters['periodYear'].'-'.$filters['periodMonth'].'-1');
            $queryBebanOperasional->where('trans_journal_header.period', '>=', $period->format('Y-m-1 00:00:00'))
            ->where('trans_journal_header.period', '<=', $period->format('Y-m-t 23:59:59'));
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $queryBebanOperasional->where('trans_journal_header.journal_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $queryBebanOperasional->where('trans_journal_header.journal_date', '<=', $date->format('Y-m-d 23:59:59'));
        }
        return $queryBebanOperasional;
    }

    protected function getQueryBebanAdministrasi(Request $request, $filters){
        $queryBebanAdministrasi   = \DB::table('gl.mst_coa')
                            ->select('mst_coa.coa_code', 'mst_coa.description as coa_description', \DB::raw('SUM(trans_journal_line.debet) as debet'), \DB::raw('SUM(trans_journal_line.credit) as credit'))
                            ->join('gl.mst_account_combination', 'mst_account_combination.segment_3', '=', 'mst_coa.coa_id')
                            ->join('gl.trans_journal_line', 'trans_journal_line.account_combination_id', '=', 'mst_account_combination.account_combination_id')
                            ->join('gl.trans_journal_header', 'trans_journal_header.journal_header_id', '=', 'trans_journal_line.journal_header_id')
                            // ->where('trans_journal_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->whereIn('mst_coa.coa_code', MasterCoa::BEBAN_ADMINISTRASI)
                            // ->where('mst_coa.identifier', '=', MasterCoa::EXPENSE)
                            ->groupBy('mst_coa.coa_code', 'mst_coa.description')
                            ->orderBy('debet', 'desc');
        if (!empty($filters['accountCode'])) {
            $queryBebanAdministrasi->where('mst_coa.coa_code', 'ilike', '%'.$filters['accountCode'].'%');
        }

        if (!empty($filters['branchId'])) {
            $queryBebanAdministrasi->where('trans_journal_header.branch_id', '=', $filters['branchId']);
        }

        if (!empty($filters['accountDescription'])) {
            $queryBebanAdministrasi->where('mst_coa.description', 'ilike', '%'.$filters['accountDescription'].'%');
        }

        if (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) {
            $period = new \DateTime($filters['periodYear'].'-'.$filters['periodMonth'].'-1');
            $queryBebanAdministrasi->where('trans_journal_header.period', '>=', $period->format('Y-m-1 00:00:00'))
            ->where('trans_journal_header.period', '<=', $period->format('Y-m-t 23:59:59'));
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $queryBebanAdministrasi->where('trans_journal_header.journal_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $queryBebanAdministrasi->where('trans_journal_header.journal_date', '<=', $date->format('Y-m-d 23:59:59'));
        }
        return $queryBebanAdministrasi;
    }

    protected function getQueryBebanLain(Request $request, $filters){
        $queryBebanLain   = \DB::table('gl.mst_coa')
                            ->select('mst_coa.coa_code', 'mst_coa.description as coa_description', \DB::raw('SUM(trans_journal_line.debet) as debet'), \DB::raw('SUM(trans_journal_line.credit) as credit'))
                            ->join('gl.mst_account_combination', 'mst_account_combination.segment_3', '=', 'mst_coa.coa_id')
                            ->join('gl.trans_journal_line', 'trans_journal_line.account_combination_id', '=', 'mst_account_combination.account_combination_id')
                            ->join('gl.trans_journal_header', 'trans_journal_header.journal_header_id', '=', 'trans_journal_line.journal_header_id')
                            // ->where('trans_journal_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->whereIn('mst_coa.coa_code', MasterCoa::BEBAN_LAIN)
                            // ->where('mst_coa.identifier', '=', MasterCoa::EXPENSE)
                            ->groupBy('mst_coa.coa_code', 'mst_coa.description')
                            ->orderBy('debet', 'desc');
        if (!empty($filters['accountCode'])) {
            $queryBebanLain->where('mst_coa.coa_code', 'ilike', '%'.$filters['accountCode'].'%');
        }

        if (!empty($filters['branchId'])) {
            $queryBebanLain->where('trans_journal_header.branch_id', '=', $filters['branchId']);
        }

        if (!empty($filters['accountDescription'])) {
            $queryBebanLain->where('mst_coa.description', 'ilike', '%'.$filters['accountDescription'].'%');
        }

        if (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) {
            $period = new \DateTime($filters['periodYear'].'-'.$filters['periodMonth'].'-1');
            $queryBebanLain->where('trans_journal_header.period', '>=', $period->format('Y-m-1 00:00:00'))
            ->where('trans_journal_header.period', '<=', $period->format('Y-m-t 23:59:59'));
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $queryBebanLain->where('trans_journal_header.journal_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $queryBebanLain->where('trans_journal_header.journal_date', '<=', $date->format('Y-m-d 23:59:59'));
        }
        return $queryBebanLain;
    }

    protected function getQueryBebanPajak(Request $request, $filters){
        $queryBebanPajak   = \DB::table('gl.mst_coa')
                            ->select('mst_coa.coa_code', 'mst_coa.description as coa_description', \DB::raw('SUM(trans_journal_line.debet) as debet'), \DB::raw('SUM(trans_journal_line.credit) as credit'))
                            ->join('gl.mst_account_combination', 'mst_account_combination.segment_3', '=', 'mst_coa.coa_id')
                            ->join('gl.trans_journal_line', 'trans_journal_line.account_combination_id', '=', 'mst_account_combination.account_combination_id')
                            ->join('gl.trans_journal_header', 'trans_journal_header.journal_header_id', '=', 'trans_journal_line.journal_header_id')
                            // ->where('trans_journal_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->whereIn('mst_coa.coa_code', MasterCoa::BEBAN_PAJAK)
                            // ->where('mst_coa.identifier', '=', MasterCoa::EXPENSE)
                            ->groupBy('mst_coa.coa_code', 'mst_coa.description')
                            ->orderBy('debet', 'desc');

        if (!empty($filters['accountCode'])) {
            $queryBebanPajak->where('mst_coa.coa_code', 'ilike', '%'.$filters['accountCode'].'%');
        }

        if (!empty($filters['branchId'])) {
            $queryBebanPajak->where('trans_journal_header.branch_id', '=', $filters['branchId']);
        }

        if (!empty($filters['accountDescription'])) {
            $queryBebanPajak->where('mst_coa.description', 'ilike', '%'.$filters['accountDescription'].'%');
        }

        if (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) {
            $period = new \DateTime($filters['periodYear'].'-'.$filters['periodMonth'].'-1');
            $queryBebanPajak->where('trans_journal_header.period', '>=', $period->format('Y-m-1 00:00:00'))
            ->where('trans_journal_header.period', '<=', $period->format('Y-m-t 23:59:59'));
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $queryBebanPajak->where('trans_journal_header.journal_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $queryBebanPajak->where('trans_journal_header.journal_date', '<=', $date->format('Y-m-d 23:59:59'));
        }
        return $queryBebanPajak;
    }


    public function printPdf(Request $request)
    {
        $filters = \Session::get('filters');
        $queryRevenue  = [];
        $queryDeduction = [];
        $queryBebanOperasional = [];
        $queryBebanAdministrasi = [];
        $queryBebanLain = [];
        $queryBebanPajak = [];
        $queryPendapatanLain = [];

        if(!empty($filters['accountCode'] || $filters['accountDescription'] || ($filters['periodMonth'] && $filters['periodYear']) || $filters['dateFrom']|| $filters['dateTo'])){
            $queryRevenue           = $this->getQueryRevenue($request, $filters);
            $queryDeduction         = $this->getQueryDeduction($request, $filters);
            $queryBebanOperasional  = $this->getQueryBebanOperasional($request, $filters);
            $queryBebanAdministrasi = $this->getQueryBebanAdministrasi($request, $filters);
            $queryBebanLain         = $this->getQueryBebanLain($request, $filters);
            $queryBebanPajak        = $this->getQueryBebanPajak($request, $filters);
            $queryPendapatanLain    = $this->getQueryPendapatanLain($request, $filters);
            $queryBebanOperasional   = $this->getQueryBebanOperasional($request, $filters);
        }

        $header = view('print.header-pdf', ['title' => trans('general-ledger/menu.income')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('generalledger::report.income.print-pdf', [
            'revenue'           => empty($queryRevenue) ? [] : $queryRevenue->get(),
            'deduction'         => empty($queryDeduction) ? [] : $queryDeduction->get(),
            'bebanOperasional'  => empty($queryBebanOperasional) ? [] : $queryBebanOperasional->get(),
            'bebanAdministrasi' => empty($queryBebanAdministrasi) ? [] : $queryBebanAdministrasi->get(),
            'bebanLain'         => empty($queryBebanLain) ? [] : $queryBebanLain->get(),
            'bebanPajak'        => empty($queryBebanPajak) ? [] : $queryBebanPajak->get(),
            'pendapatanLain'    => empty($queryPendapatanLain) ? [] : $queryPendapatanLain->get(),
            'filters'           => $filters,
        ])->render();

        \PDF::SetTitle(trans('general-ledger/menu.income').' - '.\Session::get('currentBranch')->branch_code);
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('P', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output(trans('general-ledger/menu.income').'.pdf');
        \PDF::reset();
    }

    public function printExcel(Request $request)
    {
        $filters = \Session::get('filters');
        $queryRevenue  = [];
        $queryDeduction = [];
        $queryBebanOperasional = [];
        $queryBebanAdministrasi = [];
        $queryBebanLain = [];
        $queryBebanPajak = [];
        $queryPendapatanLain = [];

        if(!empty($filters['accountCode'] || $filters['accountDescription'] || ($filters['periodMonth'] || $filters['periodYear']) || $filters['dateFrom']|| $filters['dateTo'])){
            $queryRevenue           = $this->getQueryRevenue($request, $filters)->get();
            $queryDeduction         = $this->getQueryDeduction($request, $filters)->get();
            $queryBebanOperasional  = $this->getQueryBebanOperasional($request, $filters)->get();
            $queryBebanAdministrasi = $this->getQueryBebanAdministrasi($request, $filters)->get();
            $queryBebanLain         = $this->getQueryBebanLain($request, $filters)->get();
            $queryBebanPajak        = $this->getQueryBebanPajak($request, $filters)->get();
            $queryPendapatanLain    = $this->getQueryPendapatanLain($request, $filters)->get();
            $queryBebanOperasional  = $this->getQueryBebanOperasional($request, $filters)->get();
        }

        \Excel::create(trans('general-ledger/menu.income'), function($excel) use ($queryRevenue, $queryBebanOperasional, $queryDeduction, $queryBebanAdministrasi, $queryBebanLain, $queryBebanPajak, $queryPendapatanLain, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($queryRevenue, $queryBebanOperasional, $queryDeduction, $queryBebanAdministrasi, $queryBebanLain, $queryBebanPajak, $queryPendapatanLain, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('general-ledger/menu.income'));
                });
                $sheet->cell('A3', function($cell) {
                    $cell->setFont(['size' => '12', 'bold' => true]);
                    $cell->setValue('Main Revenue');
                });

                $sheet->cells('A4:D4', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });


                $sheet->row(4, [
                    trans('shared/common.num'),
                    trans('general-ledger/fields.account-description'),
                    trans('general-ledger/fields.account-code'),
                    trans('general-ledger/fields.amount'),
                ]);

                $totalDebetRevenue   = 0;
                $totalCreditRevenue  = 0;
                $totalBalanceRevenue = 0;
                foreach($queryRevenue as $index => $model) {
                    $date = !empty($model->period) ? new \DateTime($model->period) : null;
                    $balanceRevenue       = $model->debet - $model->credit;
                    $totalBalanceRevenue += $balanceRevenue;
                    $totalDebetRevenue   += $model->debet;
                    $totalCreditRevenue  += $model->credit;
                    $data = [
                        $index + 1,
                        $model->coa_description,
                        $model->coa_code,
                        $model->credit,
                    ];
                    $sheet->row($index + 5, $data);
                }

                $currentRow = count($queryRevenue) + 5;

                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'C', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalCreditRevenue, 'D', $currentRow);

                $currentRow += 2;

                $sheet->cells('A'.$currentRow, function($cells) {
                    $cells->setFont(['size' => '12', 'bold' => true]);
                    $cells->setValue('Deduction');
                });

                $currentRow += 1;

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

                $totalDebetDeduction   = 0;
                $totalCreditDeduction  = 0;
                $totalBalanceDeduction = 0;
                foreach($queryDeduction as $index => $model) {
                    $date = !empty($model->period) ? new \DateTime($model->period) : null;
                    $balanceDeduction       = $model->debet - $model->credit;
                    $totalBalanceDeduction += $balanceDeduction;
                    $totalDebetDeduction   += $model->debet;
                    $totalCreditDeduction  += $model->credit;
                    $data = [
                        $index + 1,
                        $model->coa_description,
                        $model->coa_code,
                        $model->debet,
                    ];
                    $sheet->row($index + $currentRow, $data);
                }

                $currentRow += count($queryDeduction);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'C', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalDebetDeduction, 'D', $currentRow);

                $this->addLabelDescriptionCell($sheet,  'Total Gross Income Rp. '.number_format($totalCreditRevenue - $totalDebetDeduction) , 'B', ++$currentRow);

                $currentRow += 2;

                $sheet->cells('A'.$currentRow++, function($cells) {
                    $cells->setFont(['size' => '12', 'bold' => true]);
                    $cells->setValue('Expense');
                });

                $sheet->cells('A'.$currentRow++, function($cells) {
                    $cells->setFont(['size' => '12', 'bold' => true]);
                    $cells->setValue('Operational Expense');
                });

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

                $totalDebetBebanOperasional   = 0;
                $totalCreditBebanOperasional  = 0;
                $totalBalanceBebanOperasional = 0;
                foreach($queryBebanOperasional as $index => $model) {
                    $date = !empty($model->period) ? new \DateTime($model->period) : null;
                    $balanceBebanOperasional       = $model->debet - $model->credit;
                    $totalBalanceBebanOperasional += $balanceBebanOperasional;
                    $totalDebetBebanOperasional   += $model->debet;
                    $totalCreditBebanOperasional  += $model->credit;
                    $data = [
                        $index + 1,
                        $model->coa_description,
                        $model->coa_code,
                        $model->debet,
                    ];
                    $sheet->row($index + $currentRow, $data);
                }

                $currentRow += count($queryBebanOperasional);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'C', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalDebetBebanOperasional, 'D', $currentRow);

                // ==============================

                $currentRow += 2;

                $sheet->cells('A'.$currentRow++, function($cells) {
                    $cells->setFont(['size' => '12', 'bold' => true]);
                    $cells->setValue('Administration Expense');
                });

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

                $totalDebetBebanAdministrasi   = 0;
                $totalCreditBebanAdministrasi  = 0;
                $totalBalanceBebanAdministrasi = 0;
                foreach($queryBebanAdministrasi as $index => $model) {
                    $date = !empty($model->period) ? new \DateTime($model->period) : null;
                    $balanceBebanAdministrasi       = $model->debet - $model->credit;
                    $totalBalanceBebanAdministrasi += $balanceBebanAdministrasi;
                    $totalDebetBebanAdministrasi   += $model->debet;
                    $totalCreditBebanAdministrasi  += $model->credit;
                    $data = [
                        $index + 1,
                        $model->coa_description,
                        $model->coa_code,
                        $model->debet,
                    ];
                    $sheet->row($index + $currentRow, $data);
                }

                $currentRow += count($queryBebanAdministrasi);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'C', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalDebetBebanAdministrasi, 'D', $currentRow);

                // ==============================

                $currentRow += 2;

                $sheet->cells('A'.$currentRow++, function($cells) {
                    $cells->setFont(['size' => '12', 'bold' => true]);
                    $cells->setValue('Other Expense');
                });

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

                $totalDebetBebanLain   = 0;
                $totalCreditBebanLain  = 0;
                $totalBalanceBebanLain = 0;
                foreach($queryBebanLain as $index => $model) {
                    $date = !empty($model->period) ? new \DateTime($model->period) : null;
                    $balanceBebanLain       = $model->debet - $model->credit;
                    $totalBalanceBebanLain += $balanceBebanLain;
                    $totalDebetBebanLain   += $model->debet;
                    $totalCreditBebanLain  += $model->credit;
                    $data = [
                        $index + 1,
                        $model->coa_description,
                        $model->coa_code,
                        $model->debet,
                    ];
                    $sheet->row($index + $currentRow, $data);
                }

                $currentRow += count($queryBebanLain);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'C', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalDebetBebanLain, 'D', $currentRow);
                
                // ==============================
                $currentRow += 2;

                $sheet->cells('A'.$currentRow++, function($cells) {
                    $cells->setFont(['size' => '12', 'bold' => true]);
                    $cells->setValue('Tax Expense');
                });

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

                $totalDebetBebanPajak   = 0;
                $totalCreditBebanPajak  = 0;
                $totalBalanceBebanPajak = 0;
                foreach($queryBebanPajak as $index => $model) {
                    $date = !empty($model->period) ? new \DateTime($model->period) : null;
                    $balanceBebanPajak       = $model->debet - $model->credit;
                    $totalBalanceBebanPajak += $balanceBebanPajak;
                    $totalDebetBebanPajak   += $model->debet;
                    $totalCreditBebanPajak  += $model->credit;
                    $data = [
                        $index + 1,
                        $model->coa_description,
                        $model->coa_code,
                        $model->debet,
                    ];
                    $sheet->row($index + $currentRow, $data);
                }
                $totalDebetExpense = $totalDebetBebanOperasional + $totalDebetBebanAdministrasi + $totalDebetBebanLain + $totalDebetBebanPajak;

                $currentRow += count($queryBebanPajak);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'C', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalDebetBebanPajak, 'D', $currentRow);

                // ==============================

                $currentRow += 2;

                $sheet->cells('A'.$currentRow++, function($cells) {
                    $cells->setFont(['size' => '12', 'bold' => true]);
                    $cells->setValue('Other Revenue');
                });

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

                $totalDebetPendapatanLain   = 0;
                $totalCreditPendapatanLain  = 0;
                $totalBalancePendapatanLain = 0;
                foreach($queryPendapatanLain as $index => $model) {
                    $date = !empty($model->period) ? new \DateTime($model->period) : null;
                    $balancePendapatanLain       = $model->debet - $model->credit;
                    $totalBalancePendapatanLain += $balancePendapatanLain;
                    $totalDebetPendapatanLain   += $model->debet;
                    $totalCreditPendapatanLain  += $model->credit;
                    $data = [
                        $index + 1,
                        $model->coa_description,
                        $model->coa_code,
                        $model->credit,
                    ];
                    $sheet->row($index + $currentRow, $data);
                }

                $currentRow += count($queryPendapatanLain);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'C', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalCreditPendapatanLain, 'D', $currentRow);
                // ==============================
                
                $profitLoss = $totalCreditRevenue- $totalDebetDeduction - $totalDebetExpense + $totalCreditPendapatanLain;

                $currentRow += 2;
                $this->addLabelDescriptionCell($sheet,  $profitLoss >= 0 ? 'Profit Rp. '.number_format($profitLoss) : 'Loss Rp. '.number_format($profitLoss), 'B', $currentRow);
                
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
