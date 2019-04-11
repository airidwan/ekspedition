<?php

namespace App\Modules\Generalledger\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Service\Penomoran;

class AccountPostController extends Controller
{
    const RESOURCE = 'Generalledger\Report\AccountPost';
    const URL      = 'general-ledger/report/account-post';

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

        $query = null;

        if(!empty($filters['accountCode'] || $filters['accountDescription'] || ($filters['periodMonth'] && $filters['periodYear']) || $filters['dateFrom']|| $filters['dateTo'])){
            $query   = $this->getQuery($request, $filters);
        }

        return view('generalledger::report.account-post.index', [
            'models'            => empty($query) ? [] : $query->get(),
            'filters'           => $filters,
            'optionPeriodMonth' => $this->getOptionMonth(),
            'optionPeriodYear'  => $this->getOptionYear(),
            'resource'          => self::RESOURCE,
            'url'               => self::URL,
            ]);
    }

    protected function getQuery(Request $request, $filters){
        $query   = \DB::table('gl.mst_coa')
                        ->select('mst_coa.coa_code', 'mst_coa.description as coa_description', \DB::raw('sum(trans_journal_line.debet) as debet'), \DB::raw('sum(trans_journal_line.credit) as credit'))
                        ->join('gl.mst_account_combination', 'mst_account_combination.segment_3', '=', 'mst_coa.coa_id')
                        ->join('gl.trans_journal_line', 'trans_journal_line.account_combination_id', '=', 'mst_account_combination.account_combination_id')
                        ->join('gl.trans_journal_header', 'trans_journal_header.journal_header_id', '=', 'trans_journal_line.journal_header_id')
                        ->where('trans_journal_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->whereNotNull('mst_account_combination.account_combination_id')
                        ->whereNotNull('trans_journal_line.journal_line_id')
                        ->groupBy('mst_coa.coa_code', 'mst_coa.description')
                        ->orderBy('mst_coa.coa_code', 'desc');
          
            if (!empty($filters['accountCode'])) {
                $query->where('mst_coa.coa_code', 'ilike', '%'.$filters['accountCode'].'%');
            }

            if (!empty($filters['accountDescription'])) {
                $query->where('mst_coa.description', 'ilike', '%'.$filters['accountDescription'].'%');
            }

            if (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) {
                $period = new \DateTime($filters['periodYear'].'-'.$filters['periodMonth'].'-1');
                $query->where('trans_journal_header.period', '>=', $period->format('Y-m-1 00:00:00'))
                ->where('trans_journal_header.period', '<=', $period->format('Y-m-t 23:59:59'));
            }

            if (!empty($filters['dateFrom'])) {
                $date = new \DateTime($filters['dateFrom']);
                $query->where('trans_journal_header.journal_date', '>=', $date->format('Y-m-d 00:00:00'));
            }

            if (!empty($filters['dateTo'])) {
                $date = new \DateTime($filters['dateTo']);
                $query->where('trans_journal_header.journal_date', '<=', $date->format('Y-m-d 23:59:59'));
            }

            return $query;
    }

    public function printPdf(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters);

        $header = view('print.header-pdf', ['title' => trans('general-ledger/menu.account-post')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('generalledger::report.account-post.print-pdf', [
            'models'  => $query->get(),
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('general-ledger/menu.account-post').' - '.\Session::get('currentBranch')->branch_code);
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('P', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output(trans('general-ledger/menu.account-post').' '.\Session::get('currentBranch')->branch_code.'.pdf');
        \PDF::reset();
    }

    public function printExcel(Request $request)
    {
        $filters = \Session::get('filters');
        $query = [];
        if(!empty($filters['accountCode'] || $filters['accountDescription'] || ($filters['periodMonth'] && $filters['periodYear']) || $filters['dateFrom'] || $filters['dateTo'])){
            $query   = $this->getQuery($request, $filters)->get();
        }

        \Excel::create(trans('general-ledger/menu.account-post'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('general-ledger/menu.account-post'));
                });

                $sheet->cells('A3:F3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('shared/common.date'),
                    trans('general-ledger/fields.account-description'),
                    trans('general-ledger/fields.account-code'),
                    trans('general-ledger/fields.debet'),
                    trans('general-ledger/fields.credit'),
                ]);
                $totalDebet  = 0;
                $totalCredit = 0;
                foreach($query as $index => $model) {
                    $date = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                    $data = [
                        $index + 1,
                        !empty($date) ? $date->format('d-m-Y') : '',
                        $model->coa_description,
                        $model->coa_code,
                        $model->debet,
                        $model->credit,
                    ];
                    $sheet->row($index + 4, $data);
                    $totalDebet  += $model->debet;
                    $totalCredit += $model->credit;
                }

                $currentRow = count($query) + 4;
                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'D', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalDebet, 'E', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalCredit, 'F', $currentRow);
                
                $currentRow = count($query) + 6;
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

                $currentRow = count($query) + 6;
                $this->addLabelDescriptionCell($sheet, trans('shared/common.date'), 'E', $currentRow);
                $this->addValueDescriptionCell($sheet, $this->now->format('d-m-Y'), 'F', $currentRow);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.user'), 'E', $currentRow + 1);
                $this->addValueDescriptionCell($sheet, \Auth::user()->full_name, 'F', $currentRow + 1);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.branch'), 'E', $currentRow + 2);
                $this->addValueDescriptionCell($sheet, \Session::get('currentBranch')->branch_name, 'F', $currentRow + 2);
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
