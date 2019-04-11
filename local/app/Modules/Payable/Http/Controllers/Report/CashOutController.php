<?php

namespace App\Modules\Payable\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Payable\Model\Transaction\Payment;
use App\Modules\Payable\Model\Transaction\InvoiceHeader;
use App\Modules\Payable\Model\Transaction\InvoiceLine;
use App\Modules\Payable\Service\Master\VendorService;
use App\Modules\Operational\Service\Master\DriverService;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Generalledger\Model\Master\MasterBank;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Modules\Generalledger\Model\Master\SettingJournal;
use App\Modules\Generalledger\Model\Master\MasterAccountCombination;
use App\Modules\Generalledger\Service\Master\AccountCombinationService;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Payable\Model\Master\MasterApType;
use App\Service\Penomoran;

class CashOutController extends Controller
{
    const RESOURCE = 'Payable\Report\CashOut';
    const URL      = 'payable/report/cash-out';
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

        $queryPayment = null;
        $queryGl      = null;

        if(!empty($filters['branchId']) || !empty($filters['description']) || !empty($filters['createdBy']) || !empty($filters['dateFrom']) || !empty($filters['dateTo'])){
            $queryPayment   = $this->getQueryPayment($request, $filters);
            $queryGl        = $this->getQueryGl($request, $filters);
        }


        return view('payable::report.cash-out.index', [
            'modelsPayment' => empty($queryPayment) ? [] : $queryPayment->get(),
            'modelsGl'      => empty($queryGl) ? [] : $queryGl->get(),
            'filters'       => $filters,
            'resource'      => self::RESOURCE,
            'url'           => self::URL,
            'optionType'    => $this->optionType(),
            'optionBranch'      => $this->getAllBranch(),
        ]);
    }

    protected function getQueryPayment(Request $request, $filters){
        $query   = \DB::table('ap.payment')
                    ->select(
                        'payment.payment_id',
                        'payment.payment_number',
                        'payment.payment_method',
                        'payment.total_amount',
                        'payment.total_interest',
                        'payment.note',
                        'payment.status',
                        'payment.created_date',
                        'users.full_name',
                        'mst_bank.bank_name',
                        'invoice_header.header_id',
                        'invoice_header.type_id',
                        'invoice_header.invoice_number',
                        'mst_ap_type.type_name',
                        'mst_vendor.vendor_code',
                        'mst_vendor.vendor_name',
                        'mst_vendor.address as vendor_address',
                        'mst_vendor.phone_number as vendor_phone_number',
                        'mst_driver.driver_code',
                        'mst_driver.driver_name',
                        'mst_driver.address as driver_address',
                        'mst_driver.phone_number as driver_phone_number'
                      )
                    ->leftJoin('ap.invoice_header', 'invoice_header.header_id', '=', 'payment.invoice_header_id')
                    ->leftJoin('gl.mst_bank', 'mst_bank.bank_id', '=', 'payment.bank_id')
                    ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'invoice_header.vendor_id')
                    ->leftJoin('op.mst_driver', 'mst_driver.driver_id', '=', 'invoice_header.vendor_id')
                    ->join('ap.mst_ap_type', 'mst_ap_type.type_id', '=', 'invoice_header.type_id')
                    ->join('adm.users', 'users.id', '=', 'payment.created_by')
                    // ->where('payment.branch_id', '=', $request->session()->get('currentBranch')->branch_id)
                    ->distinct();

        if (!empty($filters['branchId'])) {
            $query->where('payment.branch_id', '=', $filters['branchId']);
        }

        if (!empty($filters['description'])) {
            $query->where('payment.note', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['createdBy'])) {
            $query->where('users.full_name', 'ilike', '%'.$filters['createdBy'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = new \DateTime($filters['dateFrom']);
            $query->where('payment.created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = new \DateTime($filters['dateTo']);
            $query->where('payment.created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        $query->orderBy('payment_number', 'desc');
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
                    ->where('mst_bank.type', '=' , MasterBank::CASH_OUT)
                    ->where('trans_journal_line.credit', '<>' , 0)
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


     protected function getQueryJournal(Request $request, $filters){
        $query   = \DB::table('gl.trans_journal_header')
                    ->select(
                        'trans_journal_header.journal_number',
                        'trans_journal_header.description',
                        'trans_journal_header.journal_date',
                        'trans_journal_header.created_by'
                      )
                    ->leftJoin('gl.trans_journal_line', 'trans_journal_line.journal_header_id', '=', 'journal_header_id.journal_header_id')
                    ->leftJoin('gl.mst_bank', 'mst_bank.bank_id', '=', 'payment.bank_id')
                    ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'invoice_header.vendor_id')
                    ->leftJoin('op.mst_driver', 'mst_driver.driver_id', '=', 'invoice_header.vendor_id')
                    ->join('ap.mst_ap_type', 'mst_ap_type.type_id', '=', 'invoice_header.type_id')
                    ->join('adm.users', 'users.id', '=', 'payment.created_by')
                    ->where('payment.branch_id', '=', $request->session()->get('currentBranch')->branch_id)
                    ->distinct();

        if (!empty($filters['description'])) {
            $query->where('payment.note', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['createdBy'])) {
            $query->where('users.full_name', 'ilike', '%'.$filters['createdBy'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = new \DateTime($filters['dateFrom']);
            $query->where('payment.created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = new \DateTime($filters['dateTo']);
            $query->where('payment.created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        $query->orderBy('payment_number', 'desc');
        return $query;
    }

    public function printPdfIndex(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $filters = \Session::get('filters');
        $queryPayment   = $this->getQueryPayment($request, $filters);
        $queryGl        = $this->getQueryGl($request, $filters);

        $header = view('print.header-pdf', ['title' => trans('payable/menu.cash-out')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('payable::report.cash-out.print-pdf-index', [
            'modelsPayment'  => $queryPayment->get(),
            'modelsGl'       => $queryGl->get(),
            'filters'        => $filters,
        ])->render();

        \PDF::SetTitle(trans('payable/menu.cash-out'));
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output(trans('payable/menu.cash-out').'.pdf');
        \PDF::reset();
    }

    public function printExcelIndex(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $filters = \Session::get('filters');
        $queryPayment   = $this->getQueryPayment($request, $filters);
        $queryGl        = $this->getQueryGl($request, $filters);

        \Excel::create(trans('payable/menu.cash-out'), function($excel) use ($queryPayment, $queryGl, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($queryPayment, $queryGl, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('payable/menu.cash-out'));
                });

                $sheet->cells('A3:P3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('payable/fields.payment-number'),
                    trans('payable/fields.invoice-number'),
                    trans('shared/common.type'),
                    trans('shared/common.date'),
                    trans('payable/fields.trading-code'),
                    trans('payable/fields.trading-name'),
                    trans('shared/common.address'),
                    trans('shared/common.description'),
                    trans('shared/common.status'),
                    trans('shared/common.created-by'),
                    trans('payable/fields.payment-method'),
                    trans('accountreceivables/fields.cash-or-bank'),
                    trans('payable/fields.total-amount'),
                    trans('payable/fields.total-interest'),
                    trans('payable/fields.total-payment'),
                ]);

                $currentRow = 4;
                $num = 1;
                $totalAmount = 0;
                $totalInterest = 0;
                $totalPayment = 0;
                foreach($queryPayment->get() as $model) {
                    $date = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                    if(in_array($model->type_id, InvoiceHeader::VENDOR_TYPE)){
                        $tradingCode    = $model->vendor_code;
                        $tradingName    = $model->vendor_name;
                        $tradingAddress = $model->vendor_address;
                    }else{
                        $tradingCode    = $model->driver_code;
                        $tradingName    = $model->driver_name;
                        $tradingAddress = $model->driver_address;

                    }
                    $data = [
                        $num++,
                        $model->payment_number,
                        $model->invoice_number,
                        $model->type_name,
                        $date !== null ? $date->format('d-m-Y') : '',
                        $tradingCode,
                        $tradingName,
                        $tradingAddress,
                        $model->note,
                        $model->status,
                        $model->full_name,
                        $model->payment_method,
                        $model->bank_name,
                        $model->total_amount,
                        $model->total_interest,
                        $model->total_amount + $model->total_interest,
                    ];

                    $totalAmount += $model->total_amount;
                    $totalInterest += $model->total_interest;
                    $totalPayment += $model->total_amount + $model->total_interest;

                    $sheet->row($currentRow++, $data);
                }

                foreach($queryGl->get() as $model) {
                    $date = !empty($model->journal_date) ? new \DateTime($model->journal_date) : null;
                    $data = [
                        $num++,
                        $model->journal_number,
                        '',
                        '',
                        $date !== null ? $date->format('d-m-Y') : '',
                        '',
                        '',
                        '',
                        $model->description,
                        '',
                        $model->full_name,
                        $model->coa_code,
                        $model->coa_description,
                        0,
                        0,
                        $model->credit,
                    ];

                    $totalPayment += $model->credit;

                    $sheet->row($currentRow++, $data);
                }

                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'M', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalAmount, 'N', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalInterest, 'O', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalPayment, 'P', $currentRow++);

                $lastDataRow = $currentRow;
                $currentRow = $lastDataRow + 1;
                if (!empty($filters['branchId'])) {
                    $branch = \DB::table('op.mst_branch')->where('branch_id', '=', $filters['branchId'])->first();
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.branch'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $branch->branch_name, 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['paymentNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('payable/fields.payment-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['paymentNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['invoiceNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('payable/fields.invoice-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['invoiceNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['vendorCode'])) {
                    $this->addLabelDescriptionCell($sheet, trans('payable/fields.trading-code'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['vendorCode'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['vendor'])) {
                    $this->addLabelDescriptionCell($sheet, trans('payable/fields.trading-name'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['vendor'], 'C', $currentRow);
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
                    $type = \DB::table('ap.mst_ap_type')->where('type_id', '=', $filters['type'])->first();
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.type'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $type->type_name, 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['paymentMethod'])) { 
                    $this->addLabelDescriptionCell($sheet, trans('payable/fields.payment-method'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['paymentMethod'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['status'])) { 
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.status'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['status'], 'C', $currentRow);
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

    protected function getAllBranch(){
        return \DB::table('op.mst_branch')->where('active', 'Y')->orderBy('branch_code')->get();
    }

    protected function addValueDescriptionCell($sheet, $value, $column, $row)
    {
        $sheet->cell($column.$row, function($cell) use($value) {
            $cell->setValue($value);
        });
    }

    protected function getOptionPaymentMethod()
    {
        return [Payment::CASH, Payment::TRANSFER];
    }

    public function getJsonBank(Request $request)
    {
        $query = \DB::table('gl.mst_bank')
                    ->select('mst_bank.*')
                    ->where('active', '=', 'Y')
                    ->where('type', '=', MasterBank::BANK)
                    ->where('bank_name', 'ilike', '%'.$request->get('search').'%')
                    ->orderBy('bank_name', 'asc')
                    ->take(10);

        return response()->json($query->get());
    }

    public function getJsonInvoice(Request $request)
    {
        $search   = $request->get('search');
        $query = \DB::table('ap.invoice_header')
                        ->select(
                            'invoice_header.*',
                            'mst_ap_type.type_name' 
                            )
                        ->leftJoin('ap.mst_ap_type', 'mst_ap_type.type_id', '=', 'invoice_header.type_id')
                        ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'invoice_header.vendor_id')
                        ->leftJoin('op.mst_driver', 'mst_driver.driver_id', '=', 'invoice_header.vendor_id')
                        ->leftJoin('op.trans_manifest_header', 'trans_manifest_header.manifest_header_id', '=', 'invoice_header.manifest_header_id')
                        ->leftJoin('op.mst_route', 'mst_route.route_id', '=', 'trans_manifest_header.route_id')
                        ->where(function($query){
                            $query->where('invoice_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                                  ->orWhere(function($query){
                                        $query->where('mst_route.city_end_id', '=', \Session::get('currentBranch')->city_id)
                                              ->where('invoice_header.type_id', '=', InvoiceHeader::MANIFEST_MONEY_TRIP);
                                  });
                        })
                        ->where('invoice_header.status', '=', InvoiceHeader::APPROVED)
                        ->where(function ($query) use ($search) {
                            $query->where('invoice_header.invoice_number', 'ilike', '%'.$search.'%')
                              ->orWhere('mst_ap_type.type_name', 'ilike', '%'.$search.'%')
                              ->orWhere('mst_vendor.vendor_code', 'ilike', '%'.$search.'%')
                              ->orWhere('mst_vendor.vendor_name', 'ilike', '%'.$search.'%')
                              ->orWhere('mst_driver.driver_code', 'ilike', '%'.$search.'%')
                              ->orWhere('mst_driver.driver_name', 'ilike', '%'.$search.'%')
                              ->orWhere('invoice_header.description', 'ilike', '%'.$search.'%');
                        })
                        ->orderBy('invoice_header.created_date', 'asc')
                        ->take(10);

        $arrInvoice = [];
        foreach($query->get() as $invoice) {
            $modelInvoice = InvoiceHeader::find($invoice->header_id);
            $invoice->total_amount   = $modelInvoice->getTotalAmount();
            $invoice->total_tax      = $modelInvoice->getTotalTax();
            $invoice->total_interest = $modelInvoice->getTotalInterest();
            $invoice->total_invoice  = $modelInvoice->getTotalInvoice();
            $invoice->total_payment  = $modelInvoice->getTotalPayment();
            $invoice->total_remain   = $modelInvoice->getTotalRemain();
            $invoice->total_remain_amount   = $modelInvoice->getTotalRemainAmount();
            $invoice->total_remain_interest = $modelInvoice->getTotalRemainInterest();

            if (in_array($invoice->type_id, InvoiceHeader::VENDOR_TYPE)) {
                $vendor     = $modelInvoice->vendor;
                $invoice->vendor_id   = !empty($vendor) ? $vendor->vendor_id : '';
                $invoice->vendor_code = !empty($vendor) ? $vendor->vendor_code : '';
                $invoice->vendor_name = !empty($vendor) ? $vendor->vendor_name : '';
                $invoice->address     = !empty($vendor) ? $vendor->address : '';
            }elseif(in_array($invoice->type_id, InvoiceHeader::DRIVER_TYPE)) {
                $driver     = $modelInvoice->driver;
                $invoice->vendor_id   = !empty($driver) ? $driver->driver : '';
                $invoice->vendor_code = !empty($driver) ? $driver->driver_code : '';
                $invoice->vendor_name = !empty($driver) ? $driver->driver_name : '';
                $invoice->address     = !empty($driver) ? $driver->address : '';
            }else{
                $invoice->vendor_id   = '';
                $invoice->vendor_code = '';
                $invoice->vendor_name = '';
            }

            if ($invoice->total_remain <= 0) {
                continue;
            }
            $arrInvoice[] = $invoice;
        }
        return response()->json($arrInvoice);
    }

    protected function getStatus(){
        return [
            Payment::INCOMPLETE,
            Payment::APPROVED,
            Payment::CLOSED,
            Payment::CANCELED,
        ];
    }

    public function optionType()
    {
        return \DB::table('ap.mst_ap_type')->get();
    }
}
