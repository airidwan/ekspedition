<?php

namespace App\Modules\Payable\Http\Controllers\Transaction;

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

class PaymentController extends Controller
{
    const RESOURCE = 'Payable\Transaction\Payment';
    const URL      = 'payable/transaction/payment';
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

        return view('payable::transaction.payment.index', [
            'models'       => $query->paginate(10),
            'filters'      => $filters,
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
            'optionStatus' => $this->getStatus(),
            'optionType'   => $this->optionType(),
            'optionPaymentMethod' => $this->getOptionPaymentMethod(),
        ]);
    }

    protected function getQuery(Request $request, $filters){
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
                    ->where('payment.branch_id', '=', $request->session()->get('currentBranch')->branch_id)
                    ->distinct();

        if (!empty($filters['paymentNumber'])) {
            $query->where('payment_number', 'ilike', '%'.$filters['paymentNumber'].'%');
        }

        if (!empty($filters['paymentMethod'])) {
            $query->where('payment_method', '=', $filters['paymentMethod']);
        }

        if (!empty($filters['invoiceNumber'])) {
            $query->where('invoice_header.invoice_number', 'ilike', '%'.$filters['invoiceNumber'].'%');
        }

        if (!empty($filters['status'])) {
            $query->where('payment.status', '=', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('invoice_header.type_id', '=', $filters['type']);
        }

        if (!empty($filters['description'])) {
            $query->where('payment.note', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['createdBy'])) {
            $query->where('users.full_name', 'ilike', '%'.$filters['createdBy'].'%');
        }

        if (!empty($filters['vendor'])) {
            $query->where(function ($query) use ($filters) {
                            $query->where('mst_vendor.vendor_name', 'ilike', '%'.$filters['vendor'].'%')
                                  ->orWhere('mst_driver.driver_name', 'ilike', '%'.$filters['vendor'].'%');
                        });
        }

        if (!empty($filters['vendorCode'])) {
            $query->where(function ($query) use ($filters) {
                            $query->where('mst_vendor.vendor_code', 'ilike', '%'.$filters['vendorCode'].'%')
                                  ->orWhere('mst_driver.driver_code', 'ilike', '%'.$filters['vendorCode'].'%');
                        });
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

    public function add(Request $request, $id=null)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new Payment();
        $model->status = Payment::INCOMPLETE;

        if ($id !== null) {
            $invoice = InvoiceHeader::find($id);
        }else{
            $invoice = $model->invoice;
        }

        return view('payable::transaction.payment.add', [
            'title'             => trans('shared/common.add'),
            'model'             => $model,
            'invoice'           => $invoice,
            'url'               => self::URL,
            'resource'          => self::RESOURCE,
            'optionPaymentMethod' => $this->getOptionPaymentMethod(),

        ]);
    }

    public function edit(Request $request, $id)
    {
        $model = Payment::find($id);
        if ($model === null) {
            abort(404);
        }

        $invoice = $model->invoice;

        $data = [
            'title'             => trans('shared/common.edit'),
            'model'             => $model,
            'invoice'           => $invoice,
            'url'               => self::URL,
            'resource'          => self::RESOURCE,
            'optionPaymentMethod' => $this->getOptionPaymentMethod(),
        ];

        if ($request->user()->can('access', [self::RESOURCE, 'update'])) {
            return view('payable::transaction.payment.add', $data);
        } else {
            return view('payable::transaction.payment.detail', $data);
        }
    }

    public function save(Request $request)
    {
        // var_dump($request->all());exit();
        $id = intval($request->get('id'));
        $model = !empty($id) ? Payment::find($id) : new Payment();

        $this->validate($request, [
            'invoiceId'    => 'required',
            'totalAmount'  => 'required',
            'totalPayment' => 'required',
        ]);
        $invoice = InvoiceHeader::find($request->get('invoiceId'));

        if($request->get('totalAmount') <= 0){
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Total amount can\'t be zero']);
        }

        if ($invoice->status != InvoiceHeader::APPROVED) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Invoice is canceled']);
        }

        if ($request->get('paymentMethod') == Payment::TRANSFER) {
            $this->validate($request, [
                'bankId'   => 'required',
            ]);
        }

        if($request->get('paymentMethod') == Payment::CASH && empty($this->getBankCash())){
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Kas of this branch not exist!']);
        }


        $message = [];
        $paymentForm     = intval(str_replace(',', '', $request->get('totalPayment')));
        $amountInvoice  = $invoice->getTotalInvoice();
        $paymentExist   = $this->getPaymentExist($id, $request->get('invoiceId'));
        $max            = intval($amountInvoice - $paymentExist->total_payment);
        if ($paymentForm > $max)  {
            $message [] = $request->get('invoiceNumber'). ' total payment remain is '. number_format($max) .'.';
        }

        $amountForm     = intval(str_replace(',', '', $request->get('totalAmount')));
        $amountInvoice  = $invoice->getTotalAmount() + $invoice->getTotalTax();
        $paymentExist   = $this->getPaymentAmountExist($id, $request->get('invoiceId'));
        $max            = intval($amountInvoice - $paymentExist->total_amount);
        if ($amountForm > $max)  {
            $message [] = 'amount remain is '. number_format($max) .'.';
        }

        $amountForm     = intval(str_replace(',', '', $request->get('totalInterest')));
        $amountInvoice  = $invoice->getTotalInterest();
        $paymentExist   = $this->getPaymentInterestExist($id, $request->get('invoiceId'));
        $max            = intval($amountInvoice - $paymentExist->total_interest);
        if ($amountForm > $max)  {
            $message [] = 'interest remain is '. number_format($max) .'.';
        }

        if(!empty($message)){
            $string = '';
            foreach ($message as $mess) {
                $string = $string.' '.$mess;
            }
            $stringMessage = 'Payment exceed!'. $string;
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $stringMessage]);
        }

        if (empty($model->branch_id)) {
            $model->branch_id = $request->session()->get('currentBranch')->branch_id;
        }

        $now = new \DateTime();

        if (empty($model->payment_number)) {
            $model->payment_number = $this->getPaymentNumber($model);
        }

        $model->invoice_header_id = $request->get('invoiceId');
        $model->total_amount      = intval(str_replace(',', '', $request->get('totalAmount')));
        $model->total_interest    = intval(str_replace(',', '', $request->get('totalInterest')));
        $model->note              = $request->get('note');
        $model->payment_method    = $request->get('paymentMethod');

        if ($model->payment_method == Payment::TRANSFER) {
            $model->bank_id = $request->get('bankId');
        }else{
            $kas = $this->getBankCash();
            $model->bank_id = $kas->bank_id;
        }

        if (empty($id)) {
            $model->status = Payment::INCOMPLETE;
            $model->created_date = $now;
            $model->created_by = \Auth::user()->id;
        } else {
            $model->last_updated_date = $now;
            $model->last_updated_by = \Auth::user()->id;
        }

        try {
            $model->save();  
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        if ($request->get('btn-approve') !== null) {
            $model->status = Payment::APPROVED;
            $model->save();

            $invoice        = InvoiceHeader::find($model->invoice_header_id);
            // insert journal
            $journalHeader           = new JournalHeader();
            $journalHeader->category = JournalHeader::PAYMENT;
            $journalHeader->status   = JournalHeader::OPEN;

            $now                    = new \DateTime();
            $period                 = new \DateTime($now->format('Y-m-1'));
            $journalHeader->period  = $period;

            $journalHeader->description = $model->payment_number.' - '.$invoice->invoice_number.'. '.$model->note;
            $journalHeader->branch_id   = \Session::get('currentBranch')->branch_id;

            $journalHeader->journal_date = $now;
            $journalHeader->created_date = $now;
            $journalHeader->created_by = \Auth::user()->id;
            $journalHeader->journal_number = $this->getJournalNumber($journalHeader);

            try {
                $journalHeader->save();
            } catch (\Exception $e) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            if (in_array($invoice->type_id, InvoiceHeader::VENDOR_TYPE)) {
                $subAccount   = $invoice->vendor;
            }else{
                $subAccount   = $invoice->driver;
            }
            $subAccountCode   = $subAccount->subaccount_code;

            // insert journal line debit
            $typeInvoice    = $invoice->type;

            if ($typeInvoice->type_id == InvoiceHeader::KAS_BON_DRIVER || $typeInvoice->type_id == InvoiceHeader::KAS_BON_EMPLOYEE) {
                $journalLine      = new JournalLine();
                $modelInvoice     = $model->invoice;        
                $modelApType      = $modelInvoice->type;        
                $account          = $modelApType->coaC;
                $accountCode      = $account->coa_code;

                $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

                $journalLine->journal_header_id      = $journalHeader->journal_header_id;
                $journalLine->account_combination_id = $accountCombination->account_combination_id;
                $journalLine->debet                  = $model->total_amount + $model->total_interest;
                $journalLine->credit                 = 0;
                $journalLine->description            = $modelApType->type_name;

                $journalLine->created_date = $now;
                $journalLine->created_by = \Auth::user()->id;

                try {
                    $journalLine->save();
                } catch (\Exception $e) {
                    return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }
            }else{
                $settingCoa  = SettingJournal::where('setting_name', SettingJournal::HUTANG_USAHA)->first();
                $accountCombination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

                $journalLine      = new JournalLine();
                $journalLine->journal_header_id      = $journalHeader->journal_header_id;
                $journalLine->account_combination_id = $accountCombination->account_combination_id;
                $journalLine->debet                  = $model->total_amount + $model->total_interest;
                $journalLine->credit                 = 0;
                $journalLine->description            = 'Setting GL Hutang Usaha';

                $journalLine->created_date = $now;
                $journalLine->created_by = \Auth::user()->id;

                try {
                    $journalLine->save();
                } catch (\Exception $e) {
                    return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }
            }

            // insert journal line credit
            $journalLine      = new JournalLine();
            $modelBank        = $model->bank;
            $account          = $modelBank->coaBank;
            $accountCode      = $account->coa_code;

            $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

            $journalLine->journal_header_id      = $journalHeader->journal_header_id;
            $journalLine->account_combination_id = $accountCombination->account_combination_id;
            $journalLine->debet                  = 0;
            $journalLine->credit                 = $model->total_amount + $model->total_interest;
            $journalLine->description            = $modelBank->bank_name;

            $journalLine->created_date = $now;
            $journalLine->created_by = \Auth::user()->id;

            try {
                $journalLine->save();
            } catch (\Exception $e) {
                return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }
        }

        if($model->invoice->getTotalRemain() <= 0){
            $model->invoice->status = InvoiceHeader::CLOSED;
            $model->invoice->save();
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('payable/menu.payment').' '.$model->payment_number])
        );

        return redirect(self::URL);
    }

    public function cancel(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'cancel'])) {
            abort(403);
        }

        $model = Payment::find($request->get('id'));
        if ($model === null) {
            abort(404);
        }
        $model->note = $model->note.'. Canceled reason is "'.$request->get('reason').'"'; 
        $model->status = Payment::CANCELED;
        $model->last_updated_by   = \Auth::user()->id;
        $model->last_updated_date = new \DateTime;
        $model->save();

        $request->session()->flash(
            'successMessage',
            trans('purchasing/fields.canceled-message', ['variable' => trans('payable/menu.payment').' '.$model->payment_number])
        );

        return redirect(self::URL);
    }

    public function printPdf(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $model = Payment::where('payment_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        if ($request->user()->cannot('accessBranch', $model->branch_id)) {
            abort(403);
        }

        $header = view('print.header-pdf', ['title' => trans('payable/menu.payment')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('payable::transaction.payment.print-pdf-detail', ['model' => $model])->render();

        \PDF::SetTitle(trans('payable/menu.payment').' '.$model->payment_number);
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A5');
        \PDF::writeHTML($html);
        \PDF::Output($model->payment_number.'.pdf');
        \PDF::reset();
    }

    public function printPdfIndex(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters);

        $header = view('print.header-pdf', ['title' => trans('payable/menu.payment')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('payable::transaction.payment.print-pdf-index', [
            'models'  => $query->get(),
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('payable/menu.payment'));
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output(trans('payable/menu.payment').'.pdf');
        \PDF::reset();
    }

    public function printExcelIndex(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters);

        \Excel::create(trans('payable/menu.payment'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('payable/menu.payment'));
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
                foreach($query->get() as $model) {
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

                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'M', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalAmount, 'N', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalInterest, 'O', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalPayment, 'P', $currentRow++);

                $lastDataRow = $currentRow;
                $currentRow = $lastDataRow + 1;
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

    protected function addValueDescriptionCell($sheet, $value, $column, $row)
    {
        $sheet->cell($column.$row, function($cell) use($value) {
            $cell->setValue($value);
        });
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

    protected function getBankCash(){
        return \DB::table('gl.mst_bank')
                    ->join('gl.dt_bank_branch', 'dt_bank_branch.bank_id', '=', 'mst_bank.bank_id')
                    ->where('dt_bank_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where('dt_bank_branch.active', '=', 'Y')
                    ->where('mst_bank.active', '=', 'Y')
                    ->where('type', '=', MasterBank::CASH_OUT)
                    ->first();
    }

    protected function getPaymentExist($id, $invoiceId){

        return \DB::table('ap.payment')
                    ->selectRaw('sum(total_amount) + sum(total_interest) as total_payment')
                    ->where('payment.payment_id', '!=', $id)
                    ->where('invoice_header_id', '=', $invoiceId)
                    ->where(function ($query) {
                          $query->where('payment.status', '=', Payment::APPROVED)
                                ->orWhere('payment.status', '=', Payment::INCOMPLETE);
                      })
                    ->where('payment.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->first();
    }

    protected function getPaymentAmountExist($id, $invoiceId){

        return \DB::table('ap.payment')
                    ->selectRaw('sum(total_amount) as total_amount')
                    ->where('payment.payment_id', '!=', $id)
                    ->where('invoice_header_id', '=', $invoiceId)
                    ->where(function ($query) {
                          $query->where('payment.status', '=', Payment::APPROVED)
                                ->orWhere('payment.status', '=', Payment::INCOMPLETE);
                      })
                    ->where('payment.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->first();
    }

    protected function getPaymentInterestExist($id, $invoiceId){

        return \DB::table('ap.payment')
                    ->selectRaw('sum(total_interest) as total_interest')
                    ->where('payment.payment_id', '!=', $id)
                    ->where('invoice_header_id', '=', $invoiceId)
                    ->where(function ($query) {
                          $query->where('payment.status', '=', Payment::APPROVED)
                                ->orWhere('payment.status', '=', Payment::INCOMPLETE);
                      })
                    ->where('payment.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->first();
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

    protected function getPaymentNumber(Payment $model)
    {
        $date = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $branch = MasterBranch::find($model->branch_id);
        $count = \DB::table('ap.payment')
                        ->where('branch_id', '=', $model->branch_id)
                        ->where('created_date', '>=', $date->format('Y-1-1 00:00:00'))
                        ->where('created_date', '<=', $date->format('Y-12-31 23:59:59'))
                        ->count();

        return 'PAP.'.$branch->branch_code.'.'.$date->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }
}
