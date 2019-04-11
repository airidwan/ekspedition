<?php

namespace App\Modules\Generalledger\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Service\Penomoran;

class JournalEntryController extends Controller
{
    const RESOURCE = 'Generalledger\Transaction\JournalEntry';
    const URL      = 'general-ledger/transaction/journal-entry';

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

        if (empty($filters['jenis']) || $filters['jenis'] == 'headers') {
            $query   = \DB::table('gl.trans_journal_header')
                        ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->orderBy('trans_journal_header.created_date', 'desc')
                        ->orderBy('journal_number', 'desc');
        }else{
            $query   = \DB::table('gl.trans_journal_header')
                        ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->join('gl.trans_journal_line', 'trans_journal_line.journal_header_id', '=', 'trans_journal_header.journal_header_id')
                        ->orderBy('trans_journal_header.created_date', 'desc')
                        ->orderBy('journal_number', 'desc')
                        ->orderBy('debet', 'desc');
        }

        if ($request->user()->cannot('access', [self::RESOURCE, 'viewSalary'])) {
            $query->where('trans_journal_header.category', '!=', JournalHeader::SALARY);
        }
        
        if (!empty($filters['journalNumber'])) {
            $query->where('trans_journal_header.journal_number', 'ilike', '%'.$filters['journalNumber'].'%');
        }

        if (!empty($filters['category'])) {
            $query->where('trans_journal_header.category', '=', $filters['category']);
        }

        if (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) {
            $period = new \DateTime($filters['periodYear'].'-'.$filters['periodMonth'].'-1');
            $query->where('trans_journal_header.journal_date', '>=', $period->format('Y-m-1 00:00:00'))
            ->where('trans_journal_header.journal_date', '<=', $period->format('Y-m-t 23:59:59'));
        }

        if (!empty($filters['description'])) {
            $query->where('trans_journal_header.description', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $query->where('trans_journal_header.journal_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $query->where('trans_journal_header.journal_date', '<=', $date->format('Y-m-d 23:59:59'));
        }

        if (!empty($filters['status'])) {
            $query->where('trans_journal_header.status', '=', $filters['status']);
        }

        return view('generalledger::transaction.journal-entry.index', [
            'models'            => $query->paginate(10),
            'filters'           => $filters,
            'optionCategory'    => $this->getOptionCategory($request),
            'optionPeriodMonth' => $this->getOptionMonth(),
            'optionPeriodYear'  => $this->getOptionYear(),
            'optionStatus'      => $this->getOptionStatus(),
            'resource'          => self::RESOURCE,
            'url'               => self::URL,
            ]);
    }

    public function printExcel(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $filters = \Session::get('filters');
        $query   = \DB::table('gl.trans_journal_header')
                    ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('trans_journal_header.created_date', 'desc')
                    ->orderBy('journal_number', 'desc');

        if ($request->user()->cannot('access', [self::RESOURCE, 'viewSalary'])) {
            $query->where('trans_journal_header.category', '!=', JournalHeader::SALARY);
        }

        if (!empty($filters['journalNumber'])) {
            $query->where('trans_journal_header.journal_number', 'ilike', '%'.$filters['journalNumber'].'%');
        }

        if (!empty($filters['category'])) {
            $query->where('trans_journal_header.category', '=', $filters['category']);
        }

        if (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) {
            $period = new \DateTime($filters['periodYear'].'-'.$filters['periodMonth'].'-1');
            $query->where('trans_journal_header.journal_date', '>=', $period->format('Y-m-1 00:00:00'))
            ->where('trans_journal_header.journal_date', '<=', $period->format('Y-m-t 23:59:59'));
        }

        if (!empty($filters['description'])) {
            $query->where('trans_journal_header.description', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $query->where('trans_journal_header.journal_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $query->where('trans_journal_header.journal_date', '<=', $date->format('Y-m-d 23:59:59'));
        }

        if (!empty($filters['status'])) {
            $query->where('trans_journal_header.status', '=', $filters['status']);
        }

        \Excel::create(trans('general-ledger/menu.journal-entry'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('general-ledger/menu.journal-entry'));
                });

                $sheet->cells('A3:F3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('general-ledger/fields.journal-number'),
                    trans('shared/common.category'),
                    trans('shared/common.date'),
                    trans('shared/common.period'),
                    trans('shared/common.description'),
                    trans('shared/common.status'),
                ]);

                $currentRow = 4;
                foreach($query->get() as $model) {
                    $journalDate = !empty($model->journal_date) ? new \DateTime($model->journal_date) : null;
                    $period      = !empty($model->period) ? new \DateTime($model->period) : null;

                    $data = [
                        $model->journal_number,
                        $model->category,
                        $journalDate !== null ? $journalDate->format('d-m-Y') : '',
                        $period !== null ? $period->format('M-Y') : '',
                        $model->description,
                        $model->status,
                    ];

                    $sheet->row($currentRow++, $data);
                }

                $lastDataRow = $currentRow;
                $currentRow = $lastDataRow + 1;
                if (!empty($filters['journalNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('general-ledger/fields.journal-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['journalNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['category'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.category'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['category'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.period'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['periodMonth'].' - '.$filters['periodYear'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['dateFrom'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.date-from'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['dateFrom'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['dateTo'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.date-to'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['dateTo'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['description'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.description'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['description'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['status'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.status'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['status'], 'C', $currentRow);
                    $currentRow++;
                }

                $currentRow = $lastDataRow + 1;
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

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model         = new JournalHeader();
        $model->status = JournalHeader::OPEN;

        return view('generalledger::transaction.journal-entry.add', [
            'title'             => trans('shared/common.add'),
            'model'             => $model,
            'resource'          => self::RESOURCE,
            'url'               => self::URL,
            'optionCategory'    => $this->getOptionCategory($request),
            'optionPeriodMonth' => $this->getOptionMonth(),
            'optionPeriodYear'  => $this->getOptionYear(),
            ]);
    }

    public function edit(Request $request, $id)
    {
        $model = JournalHeader::where('journal_header_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        if ($request->user()->cannot('accessBranch', $model->branch_id)) {
            abort(403);
        }

        $data = [
            'title'             => trans('shared/common.edit'),
            'model'             => $model,
            'resource'          => self::RESOURCE,
            'url'               => self::URL,
            'optionCategory'    => $this->getOptionCategory($request),
            'optionPeriodMonth' => $this->getOptionMonth(),
            'optionPeriodYear'  => $this->getOptionYear(),
        ];

        if ($request->user()->can('access', [self::RESOURCE, 'update'])) {
            return view('generalledger::transaction.journal-entry.add', $data);
        } else {
            return view('generalledger::transaction.journal-entry.detail', $data);
        }
    }

    public function save(Request $request)
    {
        $id    = intval($request->get('id'));
        $model = !empty($id) ? JournalHeader::find($id) : new JournalHeader();

        if (empty($model->status)) {
            $model->status = JournalHeader::OPEN;
        }

        if ($model->isOpen()) {
            $this->validate($request, [
                'journalDate' => 'required',
                'category'    => 'required',
                'description' => 'required',
            ]);

            $journalDate = new \DateTime($request->get('journalDate'));
            $period = new \DateTime($request->get('periodYear').'-'.$request->get('periodMonth').'-1');

            if ($journalDate->format('mY') != $period->format('mY')) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Journal date and period is not match']);
            }

            if (empty($request->get('lineId'))) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'You must insert minimal 1 line']);
            }

            $totalDebet  = 0;
            $totalCredit = 0;
            for ($i=0; $i < count($request->get('lineId')); $i++) { 
                $debet  = intval($request->get('debet')[$i]);
                $credit = intval($request->get('credit')[$i]);

                if ((!empty($debet) && !empty($credit)) || (empty($debet) && empty($credit))) {
                    return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Line ' . $i .' is not valid']);
                }

                $totalDebet  += $debet;
                $totalCredit += $credit;
            }


            if ($totalDebet != $totalCredit) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Journal is not balance']);
            }

            $model->category = $request->get('category');
            $model->journal_date = $journalDate;
            $model->period = $period;

            $model->description = $request->get('description');
            $model->branch_id = \Session::get('currentBranch')->branch_id;

            if (empty($id)) {
                $model->created_date = $this->now;
                $model->created_by = \Auth::user()->id;
            } else {
                $model->last_updated_date = $this->now;
                $model->last_updated_by = \Auth::user()->id;
            }

            if (empty($model->journal_number)) {
                $model->journal_number = $this->getJournalNumber($model);
            }

            try {
                $model->save();
            } catch (\Exception $e) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            $model->lines()->delete();
            for ($i=0; $i < count($request->get('lineId')); $i++) { 
                $line =  new JournalLine();
                $line->journal_header_id = $model->journal_header_id;
                $line->account_combination_id = $request->get('accountCombinationId')[$i];
                $line->debet = intval($request->get('debet')[$i]);
                $line->credit = intval($request->get('credit')[$i]);
                $line->description = $request->get('descriptionLine')[$i];

                if (empty($id)) {
                    $line->created_date = $this->now;
                    $line->created_by = \Auth::user()->id;
                }else{
                    $line->last_updated_date = $this->now;
                    $line->last_updated_by = \Auth::user()->id;
                }

                try {
                    $line->save();
                } catch (\Exception $e) {
                    return redirect(self::URL . '/edit/' . $model->journal_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }
            }
        }

        if ($request->get('btn-post') !== null && $model->isOpen()) {
            $model->status = JournalHeader::POST;
            $model->last_updated_date = $this->now;
            $model->last_updated_by = \Auth::user()->id;

            try {
                $model->save();
            } catch (\Exception $e) {
                return redirect(self::URL . '/edit/' . $model->journal_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }
        }

        if ($request->get('btn-reserve') !== null && $model->isPost()) {
            $model->status = JournalHeader::RESERVED;
            $model->last_updated_date = $this->now;
            $model->last_updated_by = \Auth::user()->id;

            try {
                $model->save();
            } catch (\Exception $e) {
                return redirect(self::URL . '/edit/' . $model->journal_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            $reserveModel = new JournalHeader();
            $reserveModel->category = $model->category;
            $reserveModel->status = JournalHeader::RESERVED;
            $reserveModel->period = new \DateTime($this->now->format('Y-m-1'));
            $reserveModel->description = 'Reserve Journal ' . $model->journal_number;
            $reserveModel->branch_id = $model->branch_id;
            $reserveModel->created_date = $this->now;
            $reserveModel->created_by = \Auth::user()->id;
            $reserveModel->journal_number = $this->getJournalNumber($reserveModel);

            try {
                $reserveModel->save();
            } catch (\Exception $e) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            foreach ($model->lines as $line) {
                $reserveLine =  new JournalLine();
                $reserveLine->journal_header_id = $reserveModel->journal_header_id;
                $reserveLine->account_combination_id = $line->account_combination_id;
                $reserveLine->debet = $line->credit;
                $reserveLine->credit = $line->debet;
                $reserveLine->description = $line->description;
                $reserveLine->created_date = $this->now;
                $reserveLine->created_by = \Auth::user()->id;

                try {
                    $reserveLine->save();
                } catch (\Exception $e) {
                    return redirect(self::URL . '/edit/' . $model->journal_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }
            }
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('general-ledger/menu.journal-entry').' '.$model->journal_number])
            );

        return redirect(self::URL);
    }

    public function postAll(Request $request)
    {
        // var_dump($request->all());exit();
        if ($request->user()->cannot('access', [self::RESOURCE, 'postAll'])) {
            abort(403);
        }

        if ($request->isMethod('post') && !empty($request->all())) {
            $request->session()->put('filters', $request->all());
        }

        $filters = $request->session()->get('filters');

        if (!empty($filters['journalNumber']) || !empty($filters['category']) || (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) || !empty($filters['dateForm']) || !empty($filters['dateTo']) || !empty($filters['limit'])) {
            $query   = \DB::table('gl.trans_journal_header')
                        ->select('trans_journal_header.*')
                        ->join('gl.trans_journal_line', 'trans_journal_line.journal_header_id', '=', 'trans_journal_header.journal_header_id')
                        ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->where('trans_journal_header.status', '=', JournalHeader::OPEN)
                        ->orderBy('journal_number', 'desc')
                        ->distinct();

            if (!empty($filters['journalNumber'])) {
                $query->where('trans_journal_header.journal_number', 'ilike', '%'.$filters['journalNumber'].'%');
            }

            if (!empty($filters['category'])) {
                $query->where('category', '=', $filters['category']);
            }

            if (!empty($filters['periodMonth']) && !empty($filters['periodYear'])) {
                $period = new \DateTime($filters['periodYear'].'-'.$filters['periodMonth'].'-1');
                $query->where('period', '>=', $period->format('Y-m-1 00:00:00'))
                ->where('period', '<=', $period->format('Y-m-t 23:59:59'));
            }

            if (!empty($filters['description'])) {
                $query->where('description', 'ilike', '%'.$filters['description'].'%');
            }

            if (!empty($filters['dateFrom'])) {
                $date = new \DateTime($filters['dateFrom']);
                $query->where('trans_journal_header.created_date', '>=', $date->format('Y-m-d 00:00:00'));
            }

            if (!empty($filters['dateTo'])) {
                $date = new \DateTime($filters['dateTo']);
                $query->where('trans_journal_header.created_date', '<=', $date->format('Y-m-d 23:59:59'));
            }

            if (!empty($filters['limit'])) {
                $query->take($filters['limit']);
            }
            $query = $query->get();
        }

        return view('generalledger::transaction.journal-entry.post-all', [
            'models'            => !empty($query) ? $query : [],
            'filters'           => $filters,
            'resource'          => self::RESOURCE,
            'optionCategory'    => $this->getOptionCategory($request),
            'optionPeriodMonth' => $this->getOptionMonth(),
            'optionPeriodYear'  => $this->getOptionYear(),
            'optionLimit'       => $this->getOptionLimit(),
            'url'               => self::URL,
            ]);
    }

    public function savePostAll(Request $request)
    {
        if (empty($request->get('journalHeaderId'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'You must checked minimal of one journal']);
        }

        for ($i=0; $i < count($request->get('journalHeaderId')); $i++) { 
            $model = JournalHeader::find($request->get('journalHeaderId')[$i]);
            $model->status = JournalHeader::POST;
            $model->save();            
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('general-ledger/fields.post-all')])
            );

        return redirect(self::URL);

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

    protected function getOptionStatus(){
        return [JournalHeader::OPEN, JournalHeader::POST, JournalHeader::RESERVED];
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

    public function getJsonAccountCombination(Request $request)
    {
        $search = $request->get('search');
        $query  = \DB::table('gl.mst_account_combination')
        ->select(
            'mst_account_combination.*',
            \DB::raw('CONCAT(segment1.coa_code, \'.\', segment2.coa_code, \'.\', segment3.coa_code, \'.\', segment4.coa_code, \'.\', segment5.coa_code) AS account_combination_code'),
            \DB::raw('CONCAT(segment1.description, \'.\', segment2.description, \'.\', segment3.description, \'.\', segment4.description, \'.\', segment5.description) AS account_combination_description')
            )
        ->orderBy('account_combination_code')
        ->join('gl.mst_coa as segment1', 'mst_account_combination.segment_1', '=', 'segment1.coa_id')
        ->join('gl.mst_coa as segment2', 'mst_account_combination.segment_2', '=', 'segment2.coa_id')
        ->join('gl.mst_coa as segment3', 'mst_account_combination.segment_3', '=', 'segment3.coa_id')
        ->join('gl.mst_coa as segment4', 'mst_account_combination.segment_4', '=', 'segment4.coa_id')
        ->join('gl.mst_coa as segment5', 'mst_account_combination.segment_5', '=', 'segment5.coa_id')
        // ->join('op.mst_branch', 'segment2.coa_code', '=', 'mst_branch.cost_center_code')
        ->where(function($query) use ($search) {
            $query->where(\DB::raw('CONCAT(segment1.coa_code, \'.\', segment2.coa_code, \'.\', segment3.coa_code, \'.\', segment4.coa_code)'), 'ilike', '%'.$search.'%')
            ->orWhere(\DB::raw('CONCAT(segment1.description, \'.\', segment2.description, \'.\', segment3.description, \'.\', segment4.description)'), 'ilike', '%'.$search.'%');
        })
        // ->where('mst_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
        ->take(10);

        return response()->json($query->get());
    }

    protected function getJournalNumber(JournalHeader $model)
    {
        $branch      = MasterBranch::find($model->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('gl.trans_journal_header')
        ->where('branch_id', '=', $model->branch_id)
        ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
        ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
        ->count();

        return 'J.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 5);
    }
}
