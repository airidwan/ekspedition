<?php

namespace App\Modules\Accountreceivables\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Http\Controllers\Transaction\TransactionResiController;
use App\Modules\Generalledger\Model\Master\MasterBank;
use App\Modules\Accountreceivables\Model\Transaction\Receipt;
use App\Modules\Accountreceivables\Model\Transaction\BatchInvoiceHeader;
use App\Modules\Accountreceivables\Model\Transaction\BatchInvoiceLine;
use App\Modules\Accountreceivables\Model\Transaction\Invoice;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Accountreceivables\Model\Transaction\CekGiroHeader;
use App\Modules\Operational\Service\Master\CustomerService;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Generalledger\Service\Master\AccountCombinationService;
use App\Modules\Generalledger\Service\Master\JournalService;
use App\Modules\Generalledger\Model\Master\SettingJournal;
use App\Role;
use App\Service\Penomoran;
use App\Service\NotificationService;
use App\Modules\Operational\Service\Transaction\HistoryResiService;

class CashInController extends Controller
{
    const RESOURCE = 'Accountreceivables\Report\CashIn';
    const URL      = 'accountreceivables/report/cash-in';
    const BATCH    = 'Batch';
    const CEK_GIRO = 'Cek/Giro';

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

        if(!empty($filters['branchId']) || !empty($filters['description']) || !empty($filters['createdBy']) || !empty($filters['dateFrom']) || !empty($filters['dateTo'])){
            $query     = $this->getQuery($request, $filters);
            $queryGl   = $this->getQueryGl($request, $filters);

        }


        return view('accountreceivables::report.cash-in.index', [
            'models'        => empty($query) ? [] : $query->get(),
            'modelsGl'      => empty($queryGl) ? [] : $queryGl->get(),
            'filters'       => $filters,
            'resource'      => self::RESOURCE,
            'url'           => self::URL,
            'optionBranch'  => $this->getAllBranch(),
        ]);
    }

    protected function getQuery(Request $request, $filters){
        $query   = \DB::table('ar.receipt')
                        ->select('receipt.*', 'mst_bank.bank_name', 'users.full_name')
                        ->join('ar.invoice', 'receipt.invoice_id', '=', 'invoice.invoice_id')
                        ->leftJoin('op.trans_resi_header', 'invoice.resi_header_id', '=', 'trans_resi_header.resi_header_id')
                        ->leftJoin('op.mst_customer', 'trans_resi_header.customer_id', '=', 'mst_customer.customer_id')
                        ->leftJoin('op.mst_customer as customer_receiver', 'trans_resi_header.customer_receiver_id', '=', 'customer_receiver.customer_id')
                        ->leftJoin('gl.mst_bank', 'mst_bank.bank_id', '=', 'receipt.bank_id')
                        ->join('adm.users', 'users.id', '=', 'receipt.created_by')
                        // ->where('receipt.branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->orderBy('receipt.created_date', 'desc')
                        ->distinct();

        if (!empty($filters['branchId'])) {
            $query->where('receipt.branch_id', '=', $filters['branchId']);
        }

        if (!empty($filters['description'])) {
            $query->where('receipt.description', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['createdBy'])) {
            $query->where('users.full_name', 'ilike', '%'.$filters['createdBy'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $query->where('receipt.created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $query->where('receipt.created_date', '<=', $date->format('Y-m-d 23:59:59'));
        }

        return $query;
    }

    protected function getQueryGl(Request $request, $filters){
        $query = \DB::table('gl.trans_journal_line')
                    ->select(
                        'trans_journal_line.*', 
                        'trans_journal_header.journal_number',
                        'users.full_name',
                        'coa_journal.coa_code',
                        'coa_journal.description as coa_description',
                        'trans_journal_header.journal_date'
                        )
                    ->join('gl.trans_journal_header', 'trans_journal_header.journal_header_id', '=', 'trans_journal_line.journal_header_id')
                    ->join('gl.mst_account_combination', 'mst_account_combination.account_combination_id', '=', 'trans_journal_line.account_combination_id')
                    ->join('gl.mst_coa as coa_journal', 'coa_journal.coa_id', '=', 'mst_account_combination.segment_3')
                    ->join('gl.mst_coa as coa_branch', 'coa_branch.coa_id', '=', 'mst_account_combination.segment_2')
                    ->join('adm.users', 'users.id', '=', 'trans_journal_header.created_by')
                    ->join('gl.mst_bank', 'mst_bank.coa_bank_id', '=', 'coa_journal.coa_id')
                    ->where('trans_journal_line.debet', '<>' , 0)
                    ->where('mst_bank.type', '=' , MasterBank::CASH_IN)
                    // ->where('coa_branch.coa_code', '=', $request->session()->get('currentBranch')->cost_center_code)
                    ->where('trans_journal_header.category', '=', JournalHeader::MANUAL);
                    // ->where(function ($query) {
                    //         $query->whereIn('coa_journal.coa_code', MasterCoa::ACTIVA_KAS)
                    //               ->orWhereIn('coa_journal.coa_code', MasterCoa::ACTIVA_BANK);
                    //     });

        if (!empty($filters['branchId'])) {
            $branch = MasterBranch::find($filters['branchId']);
            $query->where('coa_branch.coa_code', '=', $branch->cost_center_code);
        }
                
        if (!empty($filters['description'])) {
            $query->where('trans_journal_header.description', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['createdBy'])) {
            $query->where('users.full_name', 'ilike', '%'.$filters['createdBy'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $query->where('trans_journal_header.journal_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = new \DateTime($filters['dateTo']);
            $query->where('trans_journal_header.journal_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        $query->orderBy('journal_number', 'desc');

        return $query->distinct();
    }

    protected function getAllBranch(){
        return \DB::table('op.mst_branch')->where('active', 'Y')->orderBy('branch_code')->get();
    }

    public function printPdf(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $model = Receipt::where('receipt_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        if ($request->user()->cannot('accessBranch', $model->branch_id)) {
            abort(403);
        }

        $header = view('print.header-pdf', ['title' => trans('accountreceivables/menu.cash-in')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('accountreceivables::report.cash-in.print-pdf', ['model' => $model])->render();

        \PDF::SetTitle(trans('accountreceivables/menu.cash-in').' '.$model->receipt_number);
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A5');
        \PDF::writeHTML($html);
        \PDF::Output('resi-stock-list-'.\Session::get('currentBranch')->branch_code.'.pdf');
        \PDF::reset();
    }

    public function printPdfIndex(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters);
        $queryGl = $this->getQueryGl($request, $filters);


        $header = view('print.header-pdf', ['title' => trans('accountreceivables/menu.cash-in')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('accountreceivables::report.cash-in.print-pdf-index', [
            'models'   => $query->get(),
            'modelsGl' => $queryGl->get(),
            'filters'  => $filters,
        ])->render();

        \PDF::SetTitle(trans('accountreceivables/menu.cash-in'));
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output(trans('accountreceivables/menu.cash-in').'.pdf');
        \PDF::reset();
    }

    public function printExcelIndex(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters);
        $queryGl   = $this->getQueryGl($request, $filters);

        \Excel::create(trans('accountreceivables/menu.cash-in'), function($excel) use ($query, $queryGl, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $queryGl, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('accountreceivables/menu.cash-in'));
                });

                $sheet->cells('A3:P3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('accountreceivables/fields.receipt-number'),
                    trans('shared/common.date'),
                    trans('shared/common.created-by'),
                    trans('shared/common.type'),
                    trans('accountreceivables/fields.invoice-number'),
                    trans('accountreceivables/fields.invoice-type'),
                    trans('operational/fields.resi-number'),
                    trans('operational/fields.route'),
                    trans('operational/fields.customer'),
                    trans('operational/fields.sender'),
                    trans('operational/fields.customer'),
                    trans('operational/fields.receiver'),
                    trans('accountreceivables/fields.receipt-method'),
                    trans('accountreceivables/fields.cash-or-bank'),
                    trans('accountreceivables/fields.amount'),
                ]);

                $currentRow = 4;
                $num = 1;
                $totalAmount = 0;
                foreach($query->get() as $model) {
                    $model = Receipt::find($model->receipt_id);
                    $date = !empty($model->created_date) ? new \DateTime($model->created_date) : null;

                    $data = [
                        $num++,
                        $model->receipt_number,
                        $date !== null ? $date->format('d-m-Y') : '',
                        $model->type,
                        !empty($model->createdBy) ? $model->createdBy->full_name : '',
                        !empty($model->invoice) ? $model->invoice->invoice_number : '',
                        !empty($model->invoice) ? $model->invoice->type : '',
                        !empty($model->invoice->resi) ? $model->invoice->resi->resi_number : '',
                        !empty($model->invoice->resi->route) ? $model->invoice->resi->route->route_code : '',
                        !empty($model->invoice->resi->customer) ? $model->invoice->resi->customer->customer_name : '',
                        !empty($model->invoice->resi) ? $model->invoice->resi->sender_name : '',
                        !empty($model->invoice->resi->customerReceiver) ? $model->invoice->resi->customerReceiver->customer_name : '',
                        !empty($model->invoice->resi) ? $model->invoice->resi->receiver_name : '',
                        $model->receipt_method,
                        !empty($model->bank) ? $model->bank->bank_name : '',
                        $model->amount,
                    ];
                    $totalAmount += $model->amount;

                    $sheet->row($currentRow++, $data);
                }

                foreach($queryGl->get() as $model) {
                    $date = !empty($model->journal_date) ? new \DateTime($model->journal_date) : null;
                    $data = [
                        $num++,
                        $model->journal_number,
                        $date !== null ? $date->format('d-m-Y') : '',
                        '',
                        $model->full_name,
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        $model->description,
                        $model->coa_code,
                        $model->coa_description,
                        $model->debet,
                    ];

                    $totalAmount += $model->debet;

                    $sheet->row($currentRow++, $data);
                }

                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'O', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalAmount, 'P', $currentRow++);

                $lastDataRow = $currentRow;
                $currentRow = $lastDataRow + 1;
                if (!empty($filters['branchId'])) {
                    $branch = \DB::table('op.mst_branch')->where('branch_id', '=', $filters['branchId'])->first();
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.branch'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $branch->branch_name, 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['receiptNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('accountreceivables/fields.receipt-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['receiptNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['invoiceNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('accountreceivables/fields.invoice-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['invoiceNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['resiNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.resi-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['resiNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['customer'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.customer'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['customer'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['createdBy'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.created-by'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['createdBy'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['description'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.description'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['description'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['type'])) { 
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.type'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['type'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['receiptMethod'])) { 
                    $this->addLabelDescriptionCell($sheet, trans('accountreceivables/fields.receipt-method'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['receiptMethod'], 'C', $currentRow);
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

    protected function getOptionReceiptMethod()
    {
        return [Receipt::CASH, Receipt::TRANSFER];
    }
}
