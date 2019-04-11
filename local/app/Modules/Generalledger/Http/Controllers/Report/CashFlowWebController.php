<?php

namespace App\Modules\Generalledger\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Accountreceivables\Model\Transaction\Receipt;
use App\Modules\Payable\Model\Transaction\Payment;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Modules\Generalledger\Model\Master\MasterBank;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Service\Penomoran;

class CashFlowWebController extends Controller
{
    const RESOURCE = 'Generalledger\Report\DailyCash';
    const URL      = 'general-ledger/report/daily-cash';
    const CASH_IN  = 'Kas Masuk';
    const CASH_OUT = 'Kas Keluar';

    protected $now;

    public function __construct()
    {
        $this->now = new \DateTime();
    }

    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->session()->put('filters', $request->all());
        } elseif (empty($request->get('page'))) {
            $request->session()->forget('filters');
        }

        $filters = $request->session()->get('filters');

        $queryIncome = null;
        $queryExpense = null;

        if($filters['dateFrom'] || $filters['dateTo']){
            $queryIncome   = $this->getQueryIncome($request, $filters);
            $queryExpense   = $this->getQueryExpense($request, $filters);
        }

        return view('generalledger::report.cash-flow.index', [
            'income'            => empty($queryIncome) ? [] : $queryIncome->get(),
            'expense'           => empty($queryExpense) ? [] : $queryExpense->get(),
            'filters'           => $filters,
            'optionBranch'      => $this->getAllBranch(),
            'optionType'        => $this->getOptionType(),
            'resource'          => self::RESOURCE,
            'url'               => self::URL,
            ]);
    }

    protected function getQueryIncome(Request $request, $filters){
        $now              = new \DateTime();
   
        $queryIncome     = \DB::table('ar.receipt')
                            ->select(
                                'receipt.receipt_number', 
                                'receipt.amount', 
                                'receipt.description', 
                                'receipt.resi_header_id', 
                                'receipt.created_date', 
                                'trans_resi_header.resi_number', 
                                'trans_resi_header.sender_name', 
                                'trans_resi_header.receiver_name', 
                                'mst_route.route_code' 
                                )
                            ->leftJoin('ar.invoice', 'invoice.invoice_id', '=', 'receipt.invoice_id')
                            ->leftJoin('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'invoice.resi_header_id')
                            ->leftJoin('op.mst_route', 'mst_route.route_id', '=', 'trans_resi_header.route_id')
                            ->orderBy('receipt.created_date', 'desc');

        if (!empty($filters['branchId'])) {
            $queryIncome->where('receipt.branch_id', '=', $filters['branchId']);
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $queryIncome->where('receipt.created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $queryIncome->where('receipt.created_date', '<=', $date->format('Y-m-d 23:59:59'));
        }
        return $queryIncome;
    }

    protected function getQueryExpense(Request $request, $filters){
        $now = new \DateTime();
        $queryExpense   = \DB::table('ap.payment')
                                ->select(
                                    'payment.payment_number',
                                    'payment.created_date',
                                    'payment.note',
                                    'payment.total_amount',
                                    'payment.total_interest'
                                    )
                                ->where('payment.status', Payment::APPROVED)
                                ->orderBy('payment.created_date', 'desc');

        if (!empty($filters['branchId'])) {
            $queryExpense->where('payment.branch_id', '=', $filters['branchId']);
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $queryExpense->where('payment.created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $queryExpense->where('payment.created_date', '<=', $date->format('Y-m-d 23:59:59'));
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
    
    protected function getAllBranch(){
        return \DB::table('op.mst_branch')->where('active', 'Y')->orderBy('branch_code')->get();
    }

    protected function getOptionType(){
        return [
            self::CASH_IN,
            self::CASH_OUT,
        ];

    }

    public function printPdf(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $filters = \Session::get('filters');

        $queryIncome = null;
        $queryExpense = null;

        if($filters['description'] || $filters['date']){
            $queryIncome   = $this->getQueryIncome($request, $filters);
            $queryExpense   = $this->getQueryExpense($request, $filters);
        }

        $header = view('print.header-pdf', ['title' => trans('general-ledger/menu.daily-cash')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('generalledger::report.daily-cash.print-pdf', [
            'income'  => empty($queryIncome) ? [] : $queryIncome->get(),
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
        $queryIncome = [];
        $queryExpense = [];

        if(!empty($filters['description'] || $filters['date'])){
            $queryIncome   = $this->getQueryIncome($request, $filters)->get();
            $queryExpense   = $this->getQueryExpense($request, $filters)->get();
        }

        \Excel::create(trans('general-ledger/menu.daily-cash'), function($excel) use ($queryIncome, $queryExpense, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($queryIncome, $queryExpense, $filters) {
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
                    trans('general-ledger/fields.income'),
                ]);
                $no                  = 1;
                $totalDebetExpense   = 0;
                $totalCreditExpense  = 0;
                $totalBalanceExpense = 0;
                foreach($queryIncome as $index => $model) {
                    $incomeBalance       = $model->debet - $model->credit;
                    $totalDebetExpense   += $model->debet;
                    $totalCreditExpense  += $model->credit;
                    $totalBalanceExpense += $model->credit;
                    $data = [
                        $no++,
                        '',
                        trans('general-ledger/fields.beginning-income'),
                        $model->debet,
                        $model->credit,
                        $incomeBalance,
                    ];
                    $sheet->row($index + 4, $data);
                }
                $add = $no-1;
                foreach($queryExpense as $index => $model) {
                    $incomeExpense       = $model->debet - $model->credit;
                    $totalBalanceExpense += $incomeExpense;
                    $totalDebetExpense   += $model->debet;
                    $totalCreditExpense  += $model->credit;
                    $data = [
                        $no++,
                        $model->journal_number,
                        $model->gl_header_description,
                        $model->debet,
                        $model->credit,
                        $incomeExpense,
                    ];
                    $sheet->row($index + $add + 4, $data);
                }

                $currentRow = count($queryIncome) + count($queryExpense) + 4;
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
