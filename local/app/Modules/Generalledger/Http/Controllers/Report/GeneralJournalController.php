<?php

namespace App\Modules\Generalledger\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Service\Penomoran;

class GeneralJournalController extends Controller
{
    const RESOURCE = 'Generalledger\Report\JournalEntries';
    const URL      = 'general-ledger/report/general-journal';

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
            return redirect(self::URL.'?page=1');
        } elseif (empty($request->get('page'))) {
            $request->session()->forget('filters');
        }

        $filters = $request->session()->get('filters');

        $query   = $this->getQuery($request, $filters);

        return view('generalledger::report.general-journal.index', [
            'models'            => $query->paginate(50),
            'filters'           => $filters,
            'optionPeriodMonth' => $this->getOptionMonth(),
            'optionPeriodYear'  => $this->getOptionYear(),
            'optionCategory'    => $this->getOptionCategory($request),
            'resource'          => self::RESOURCE,
            'url'               => self::URL,
            ]);
    }

    protected function getQuery(Request $request, $filters){
        $query   = \DB::table('gl.trans_journal_line')
                        ->select(
                            'account.coa_code', 
                            'account.description as coa_description', 
                            'trans_journal_line.debet', 
                            'trans_journal_line.credit', 
                            'trans_journal_header.journal_date', 
                            'trans_journal_header.journal_number', 
                            'trans_journal_header.description as header_description', 
                            'trans_journal_line.description as line_description', 
                            \DB::raw("CONCAT(company.coa_code, '-', cost_center.coa_code, '-', account.coa_code, '-' ,subaccount.coa_code, '-', future.coa_code) AS account_combination_code")
                        )
                        ->join('gl.trans_journal_header', 'trans_journal_header.journal_header_id', '=', 'trans_journal_line.journal_header_id')
                        ->join('gl.mst_account_combination',  'mst_account_combination.account_combination_id', '=', 'trans_journal_line.account_combination_id')
                        ->join('gl.mst_coa as company', 'company.coa_id', '=', 'mst_account_combination.segment_1')
                        ->join('gl.mst_coa as cost_center', 'cost_center.coa_id', '=', 'mst_account_combination.segment_2')
                        ->join('gl.mst_coa as account', 'account.coa_id', '=', 'mst_account_combination.segment_3')
                        ->join('gl.mst_coa as subaccount', 'subaccount.coa_id', '=', 'mst_account_combination.segment_4')
                        ->join('gl.mst_coa as future', 'future.coa_id', '=', 'mst_account_combination.segment_4')
                        ->where('trans_journal_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->orderBy('trans_journal_line.journal_line_id', 'desc');
          
            if (!empty($filters['accountFrom'])) {
                $query->where('account.coa_code', '>=', $filters['accountFrom']);
            }

            if (!empty($filters['accountTo'])) {
                $query->where('account.coa_code', '<=', $filters['accountTo']);
            }

            if (!empty($filters['category'])) {
                $query->where('trans_journal_header.category', '=', $filters['category']);
            }

            if (!empty($filters['subaccountFrom'])) {
                $query->where('subaccount.coa_code', '>=', $filters['subaccountFrom']);
            }

            if (!empty($filters['subaccountTo'])) {
                $query->where('subaccount.coa_code', '<=', $filters['subaccountTo']);
            }

            if (!empty($filters['futureFrom'])) {
                $query->where('future.coa_code', '>=', $filters['futureFrom']);
            }

            if (!empty($filters['futureTo'])) {
                $query->where('future.coa_code', '<=', $filters['futureTo']);
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

    protected function getOptionCategory(Request $request){
        $category = [
            JournalHeader::MANUAL, 

            JournalHeader::ADJUSTMENT,
            JournalHeader::ADDITION_ASSET,

            JournalHeader::BRANCH_TRANSFER_TRANSACT,
            JournalHeader::BRANCH_TRANSFER_RECEIPT,
            
            JournalHeader::CANCEL_INVOICE_DP,
            JournalHeader::CANCEL_INVOICE_DO_PARTNER,
            JournalHeader::CANCEL_INVOICE_DO_PICKUP_MONEY_TRIP,
            JournalHeader::CANCEL_INVOICE_DRIVER_SALARY,
            JournalHeader::CANCEL_INVOICE_KAS_BON,
            JournalHeader::CANCEL_INVOICE_MANIFEST_MONEY_TRIP,
            JournalHeader::CANCEL_INVOICE_OTHER,
            JournalHeader::CANCEL_INVOICE_PO,
            JournalHeader::CANCEL_INVOICE_RESI,
            JournalHeader::CANCEL_INVOICE_SERVICE,

            JournalHeader::CEK_GIRO, 

            JournalHeader::DEPRECIATION,
            JournalHeader::DISCOUNT_INVOICE,

            JournalHeader::INVOICE_RESI, 
            JournalHeader::INVOICE_PICKUP, 

            JournalHeader::INVOICE_DP,
            JournalHeader::INVOICE_DO,
            JournalHeader::INVOICE_DO_PARTNER,
            JournalHeader::INVOICE_DO_PICKUP_MONEY_TRIP,
            JournalHeader::INVOICE_DRIVER_SALARY,
            JournalHeader::INVOICE_KAS_BON,
            JournalHeader::INVOICE_MANIFEST_MONEY_TRIP,
            JournalHeader::INVOICE_OTHER,
            JournalHeader::INVOICE_PO,
            JournalHeader::INVOICE_SERVICE,
            
            JournalHeader::MOVE_ORDER,
            
            JournalHeader::PAYMENT,
            
            JournalHeader::RECEIPT_ASSET_SELLING, 
            JournalHeader::RECEIPT_EXTRA_COST, 
            JournalHeader::RECEIPT_DO, 
            JournalHeader::RECEIPT_KASBON, 
            JournalHeader::RECEIPT_PO,
            JournalHeader::RECEIPT_OTHER, 
            JournalHeader::RECEIPT_PICKUP, 
            JournalHeader::RECEIPT_RESI, 

            JournalHeader::RETURN_PO,

        ];
        if (!$request->user()->cannot('access', [self::RESOURCE, 'viewSalary'])) {
            array_unshift($category, JournalHeader::SALARY);
        }

        return $category;
    }

    public function printPdf(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters)->get();

        $header = view('print.header-pdf', ['title' => trans('general-ledger/menu.general-journal')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('generalledger::report.general-journal.print-pdf', [
            'models'  => $query,
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('general-ledger/menu.general-journal').' - '.\Session::get('currentBranch')->branch_code);
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('P', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output(trans('general-ledger/menu.general-journal').' '.\Session::get('currentBranch')->branch_code.'.pdf');
        \PDF::reset();
    }

    public function printExcel(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters)->get();

        \Excel::create(trans('general-ledger/menu.general-journal'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('general-ledger/menu.general-journal'));
                });

                $sheet->cells('A3:I3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('general-ledger/fields.journal-number'),
                    trans('shared/common.date'),
                    trans('general-ledger/fields.account-combination'),
                    trans('general-ledger/fields.account-description'),
                    trans('shared/common.transaction-description'),
                    trans('shared/common.description'),
                    trans('general-ledger/fields.debet'),
                    trans('general-ledger/fields.credit'),
                ]);
                $totalDebet  = 0;
                $totalCredit = 0;
                foreach($query as $index => $model) {
                    $date = !empty($model->journal_date) ? new \DateTime($model->journal_date) : null;
                    $data = [
                        $index + 1,
                        $model->journal_number,
                        !empty($date) ? $date->format('d-m-Y') : '',
                        $model->account_combination_code,
                        $model->coa_description,
                        $model->header_description,
                        $model->line_description,
                        $model->debet,
                        $model->credit,
                    ];
                    $sheet->row($index + 4, $data);
                    $totalDebet  += $model->debet;
                    $totalCredit += $model->credit;
                }

                $currentRow = count($query) + 4;
                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'G', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalDebet, 'H', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalCredit, 'I', $currentRow);
                
                $currentRow = count($query) + 6;
                if (!empty($filters['accountFrom'])) {
                    $this->addLabelDescriptionCell($sheet, trans('general-ledger/fields.account-from'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['accountFrom'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['subaccountFrom'])) {
                    $this->addLabelDescriptionCell($sheet, trans('general-ledger/fields.subaccount-from'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['subaccountFrom'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['futureFrom'])) {
                    $this->addLabelDescriptionCell($sheet, trans('general-ledger/fields.future-from'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['futureFrom'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['accountTo'])) {
                    $this->addLabelDescriptionCell($sheet, trans('general-ledger/fields.account-to'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['accountTo'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['subaccountTo'])) {
                    $this->addLabelDescriptionCell($sheet, trans('general-ledger/fields.subaccount-to'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['subaccountTo'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['futureTo'])) {
                    $this->addLabelDescriptionCell($sheet, trans('general-ledger/fields.future-to'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['futureTo'], 'C', $currentRow);
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
                if (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.period'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['periodMonth'].'-'.$filters['periodYear'], 'C', $currentRow);
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
