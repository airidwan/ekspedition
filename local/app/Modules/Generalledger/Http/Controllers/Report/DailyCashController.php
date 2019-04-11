<?php

namespace App\Modules\Generalledger\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Modules\Generalledger\Model\Master\MasterBank;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Service\Penomoran;

class DailyCashController extends Controller
{
    const RESOURCE = 'Generalledger\Report\DailyCash';
    const URL      = 'general-ledger/report/daily-cash';

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

        $queryBalance = null;
        $queryExpense = null;

        if($filters['description'] || $filters['date']){
            $queryBalance   = $this->getQueryBalance($request, $filters);
            $queryExpense   = $this->getQueryExpense($request, $filters);
        }

        return view('generalledger::report.daily-cash.index', [
            'balance'           => empty($queryBalance) ? [] : $queryBalance->get(),
            'expense'           => empty($queryExpense) ? [] : $queryExpense->get(),
            'filters'           => $filters,
            'optionPeriodMonth' => $this->getOptionMonth(),
            'optionPeriodYear'  => $this->getOptionYear(),
            'resource'          => self::RESOURCE,
            'url'               => self::URL,
            ]);
    }

    protected function getQueryBalance(Request $request, $filters){
        $now              = new \DateTime();
   
        $queryBalance     = \DB::table('gl.mst_coa')
                            ->select('mst_coa.coa_code', 'mst_coa.description as coa_description', \DB::raw('SUM(trans_journal_line.debet) as debet'), \DB::raw('SUM(trans_journal_line.credit) as credit'))
                            ->join('gl.mst_account_combination', 'mst_account_combination.segment_3', '=', 'mst_coa.coa_id')
                            ->join('gl.trans_journal_line', 'trans_journal_line.account_combination_id', '=', 'mst_account_combination.account_combination_id')
                            ->join('gl.trans_journal_header', 'trans_journal_header.journal_header_id', '=', 'trans_journal_line.journal_header_id')
                            ->whereIn('mst_coa.coa_id', $this->getCashOut())
                            ->groupBy('mst_coa.coa_code', 'mst_coa.description')
                            ->orderBy('mst_coa.coa_code', 'desc');

        if (\Session::get('currentBranch')->branch_code_numeric != MasterBranch::KODE_NUMERIC_HO) {
            $queryBalance->where('trans_journal_header.branch_id', '=', \Session::get('currentBranch')->branch_id);
        }

        if (!empty($filters['coaCode'])) {
            $queryBalance->where('mst_coa.coa_code', 'ilike', '%'.$filters['coaCode'].'%');
        }

        if (!empty($filters['coaDescription'])) {
            $queryBalance->where('mst_coa.description', 'ilike', '%'.$filters['coaDescription'].'%');
        }

        if (!empty($filters['date'])) {
            $date = new \DateTime($filters['date']);
            $queryBalance->where('trans_journal_header.journal_date', '<', $date->format('Y-m-d 00:00:00'));
        }else{
            $queryBalance->where('trans_journal_header.journal_date', '<', $now->format('Y-m-d 00:00:00'));
        }
        return $queryBalance;
    }

    protected function getQueryExpense(Request $request, $filters){
        $now = new \DateTime();
        $queryExpense   = \DB::table('gl.trans_journal_line')
                                ->select('trans_journal_line.description as gl_description', 'trans_journal_header.description as gl_header_description', 'trans_journal_header.journal_number',  'mst_coa.coa_code', 'mst_coa.description as coa_description', 'trans_journal_line.debet', 'trans_journal_line.credit')
                                ->join('gl.trans_journal_header', 'trans_journal_header.journal_header_id', '=', 'trans_journal_line.journal_header_id')
                                ->join('gl.mst_account_combination', 'mst_account_combination.account_combination_id', '=', 'trans_journal_line.account_combination_id')
                                ->join('gl.mst_coa', 'mst_coa.coa_id', '=', 'mst_account_combination.segment_3')
                                ->whereIn('mst_coa.coa_id', $this->getCashOut())
                                ->orderBy('trans_journal_header.journal_date', 'asc');

        if (\Session::get('currentBranch')->branch_code_numeric != MasterBranch::KODE_NUMERIC_HO) {
            $queryExpense->where('trans_journal_header.branch_id', '=', \Session::get('currentBranch')->branch_id);
        }

        if (!empty($filters['description'])) {
            $queryExpense->where('trans_journal_header.description', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['coaCode'])) {
            $queryExpense->where('mst_coa.coa_code', 'ilike', '%'.$filters['coaCode'].'%');
        }

        if (!empty($filters['coaDescription'])) {
            $queryExpense->where('mst_coa.description', 'ilike', '%'.$filters['coaDescription'].'%');
        }

        if (!empty($filters['journalNumber'])) {
            $queryExpense->where('trans_journal_header.journal_number', 'ilike', '%'.$filters['journalNumber'].'%');
        }

        if (!empty($filters['date'])) {
            $date = new \DateTime($filters['date']);
            $queryExpense->where('trans_journal_header.journal_date', '>=', $date->format('Y-m-d 00:00:00'))
                         ->where('trans_journal_header.journal_date', '<=', $date->format('Y-m-d 23:59:59'));
        }else{
            $queryExpense->where('trans_journal_header.journal_date', '=', $now->format('Y-m-d 23:59:59'));
        }

        return $queryExpense;
    }

    protected function getCashOut(){
        $query = \DB::table('gl.mst_bank')
                ->select('mst_bank.coa_bank_id')
                ->join('gl.dt_bank_branch', 'dt_bank_branch.bank_id', '=', 'mst_bank.bank_id')
                ->where('mst_bank.type', '=', MasterBank::CASH_OUT);

        if (\Session::get('currentBranch')->branch_code_numeric != MasterBranch::KODE_NUMERIC_HO) {
            $query->where('dt_bank_branch.branch_id', '=', \Session::get('currentBranch')->branch_id);
        }

        $ids = [];
        foreach ($query->get() as $bank) {
            $ids[] = $bank->coa_bank_id;
        }

        return $ids;
    }

    public function printPdf(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $filters = \Session::get('filters');

        $queryBalance = null;
        $queryExpense = null;

        if($filters['description'] || $filters['date']){
            $queryBalance   = $this->getQueryBalance($request, $filters);
            $queryExpense   = $this->getQueryExpense($request, $filters);
        }

        $header = view('print.header-pdf', ['title' => trans('general-ledger/menu.daily-cash')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('generalledger::report.daily-cash.print-pdf', [
            'balance'  => empty($queryBalance) ? [] : $queryBalance->get(),
            'expense'  => empty($queryExpense) ? [] : $queryExpense->get(),
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('general-ledger/menu.daily-cash').' - '.\Session::get('currentBranch')->branch_code);
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('P', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output(trans('general-ledger/menu.daily-cash').' '.\Session::get('currentBranch')->branch_code.'.pdf');
        \PDF::reset();
    }

    public function printExcel(Request $request)
    {
        $filters = \Session::get('filters');
        $queryBalance = [];
        $queryExpense = [];

        if(!empty($filters['description'] || $filters['date'])){
            $queryBalance   = $this->getQueryBalance($request, $filters)->get();
            $queryExpense   = $this->getQueryExpense($request, $filters)->get();
        }

        \Excel::create(trans('general-ledger/menu.daily-cash'), function($excel) use ($queryBalance, $queryExpense, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($queryBalance, $queryExpense, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('general-ledger/menu.daily-cash'));
                });

                $sheet->cells('A3:F3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('general-ledger/fields.journal-number'),
                    trans('shared/common.description'),
                    trans('general-ledger/fields.debet'),
                    trans('general-ledger/fields.credit'),
                    trans('general-ledger/fields.balance'),
                ]);
                $no                  = 1;
                $totalDebetExpense   = 0;
                $totalCreditExpense  = 0;
                $totalBalanceExpense = 0;
                foreach($queryBalance as $index => $model) {
                    $balanceBalance       = $model->debet - $model->credit;
                    $totalDebetExpense   += $model->debet;
                    $totalCreditExpense  += $model->credit;
                    $totalBalanceExpense += $model->credit;
                    $data = [
                        $no++,
                        '',
                        trans('general-ledger/fields.beginning-balance'),
                        $model->debet,
                        $model->credit,
                        $balanceBalance,
                    ];
                    $sheet->row($index + 4, $data);
                }
                $add = $no-1;
                foreach($queryExpense as $index => $model) {
                    $balanceExpense       = $model->debet - $model->credit;
                    $totalBalanceExpense += $balanceExpense;
                    $totalDebetExpense   += $model->debet;
                    $totalCreditExpense  += $model->credit;
                    $data = [
                        $no++,
                        $model->journal_number,
                        $model->gl_header_description,
                        $model->debet,
                        $model->credit,
                        $balanceExpense,
                    ];
                    $sheet->row($index + $add + 4, $data);
                }

                $currentRow = count($queryBalance) + count($queryExpense) + 4;
                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'C', $currentRow);
                $this->addLabelDescriptionCell($sheet, $totalDebetExpense, 'D', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalCreditExpense, 'E', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalDebetExpense-$totalCreditExpense, 'F', $currentRow);
                $currentRow += 2;
                $tempRow     = $currentRow;

                if (!empty($filters['date'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.date'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['date'], 'C', $currentRow);
                    $currentRow++;
                }

                if (!empty($filters['journalNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('general-ledger/fields.journal-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['journalNumber'], 'C', $currentRow);
                    $currentRow++;
                }

                if (!empty($filters['description'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.description'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['description'], 'C', $currentRow);
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
