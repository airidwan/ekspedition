<?php

namespace App\Modules\Accountreceivables\Http\Controllers\Transaction;

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
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Generalledger\Service\Master\AccountCombinationService;
use App\Modules\Generalledger\Service\Master\JournalService;
use App\Modules\Generalledger\Model\Master\SettingJournal;
use App\Role;
use App\Service\Penomoran;
use App\Service\NotificationService;
use App\Modules\Operational\Service\Transaction\HistoryResiService;

class ReceiptController extends Controller
{
    const RESOURCE = 'Accountreceivables\Transaction\Receipt';
    const URL      = 'accountreceivables/transaction/receipt';
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

        $query   = $this->getQuery($request, $filters);

        return view('accountreceivables::transaction.receipt.index', [
            'models' => $query->paginate(10),
            'filters' => $filters,
            'resource' => self::RESOURCE,
            'url' => self::URL,
            'optionType' => [Receipt::REGULER, Receipt::DP, Receipt::BATCH, Receipt::CEK_GIRO],
            'optionReceiptMethod' => $this->getOptionReceiptMethod(),
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
                        ->where('receipt.branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->orderBy('receipt.created_date', 'desc')
                        ->distinct();

        if (!empty($filters['receiptNumber'])) {
            $query->where('receipt.receipt_number', 'ilike', '%'.$filters['receiptNumber'].'%');
        }

        if (!empty($filters['invoiceNumber'])) {
            $query->where('invoice.invoice_number', 'ilike', '%'.$filters['invoiceNumber'].'%');
        }

        if (!empty($filters['resiNumber'])) {
            $query->where('trans_resi_header.resi_number', 'ilike', '%'.$filters['resiNumber'].'%');
        }

        if (!empty($filters['createdBy'])) {
            $query->where('users.full_name', 'ilike', '%'.$filters['createdBy'].'%');
        }

        if (!empty($filters['customer'])) {
            $query->where(function($query) use ($filters) {
                $query->where('mst_customer.customer_name', 'ilike', '%'.$filters['customer'].'%')
                        ->orWhere('trans_resi_header.sender_name', 'ilike', '%'.$filters['customer'].'%')
                        ->orWhere('customer_receiver.customer_name', 'ilike', '%'.$filters['customer'].'%')
                        ->orWhere('trans_resi_header.receiver_name', 'ilike', '%'.$filters['customer'].'%');
            });
        }

        if (!empty($filters['receiptMethod'])) {
            $query->where('receipt.receipt_method', '=', $filters['receiptMethod']);
        }

        if (!empty($filters['type'])) {
            $query->where('receipt.type', '=', $filters['type']);
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

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        return view('accountreceivables::transaction.receipt.add', [
            'title' => trans('shared/common.add'),
            'url' => self::URL,
            'resource' => self::RESOURCE,
            'optionType' => [Receipt::REGULER, Receipt::DP, Receipt::BATCH, Receipt::CEK_GIRO],
            'optionReceiptMethod' => $this->getOptionReceiptMethod(),
        ]);
    }

    public function edit(Request $request, $id)
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

        return view('accountreceivables::transaction.receipt.edit', [
            'model' => $model,
            'url' => self::URL,
            'urlResi' => TransactionResiController::URL,
            'resource' => self::RESOURCE,
            'optionReceiptMethod' => $this->getOptionReceiptMethod(),
        ]);
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

        $header = view('print.header-pdf', ['title' => trans('accountreceivables/menu.receipt')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('accountreceivables::transaction.receipt.print-pdf', ['model' => $model])->render();

        \PDF::SetTitle(trans('accountreceivables/menu.receipt').' '.$model->receipt_number);
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

        $header = view('print.header-pdf', ['title' => trans('accountreceivables/menu.receipt')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('accountreceivables::transaction.receipt.print-pdf-index', [
            'models'  => $query->get(),
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('accountreceivables/menu.receipt'));
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output(trans('accountreceivables/menu.receipt').'.pdf');
        \PDF::reset();
    }

    public function printExcelIndex(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters);

        \Excel::create(trans('accountreceivables/menu.receipt'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('accountreceivables/menu.receipt'));
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

                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'O', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalAmount, 'P', $currentRow++);

                $lastDataRow = $currentRow;
                $currentRow = $lastDataRow + 1;
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

    public function save(Request $request)
    {
        $this->validate($request, [
            'personName' => 'required',
            'batchInvoiceId' => 'required_if:type,'.Receipt::BATCH,
            'bankId' => 'required_if:receiptMethod,'.Receipt::TRANSFER,
            'cekGiroId' => 'required_if:receiptMethod,'.Receipt::CEK_GIRO,
        ], [
            'batchInvoiceId.required_if' => 'This field is required',
            'bankId.required_unless' => 'This field is required',
            'cekGiroId.required_if' => 'This field is required',
        ]);

        if ($request->get('type') !== Receipt::BATCH && $request->get('type') !== Receipt::CEK_GIRO && empty($request->get('invoiceId'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'You must insert minimal 1 line']);
        }

        if ($request->get('receiptMethod') === Receipt::CASH) {
            $kasBranch = $this->getCurrentBranchKas();
            if ($kasBranch === null) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(
                    ['errorMessage' => 'Kas '.\Session::get('currentBranch')->branch_name.' is not exist']
                );
            }
        }

        if ($request->get('type') == Receipt::BATCH) {
            $error = $this->createReceiptBatchInvoice($request);
            if (!empty($error)) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $error]);
            }
        } elseif ($request->get('type') == Receipt::CEK_GIRO) {
            $error = $this->createReceiptCekGiro($request);
            if (!empty($error)) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $error]);
            }
        } else {
            $error = $this->validateKelebihanBayar($request);
            if (!empty($error)) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $error]);
            }

            $error = $this->createReceiptInvoice($request);
            if (!empty($error)) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $error]);
            }
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('accountreceivables/menu.receipt')])
        );

        return redirect(self::URL);
    }

    protected function validateKelebihanBayar(Request $request)
    {
        foreach ($request->get('invoiceId') as $index => $invoiceId) {
            $amount = intval($request->get('receipt')[$index]);
            $invoice = Invoice::find($invoiceId);
            $remaining = $invoice->remaining();
            if ($invoice !== null && $amount > $remaining) {
                return 'Receipt amount for invoice '.$invoice->invoice_number.' exceed remaining invoice Rp. '.number_format($remaining);
            }
        }
    }

    protected function createReceiptInvoice(Request $request)
    {
        $i = 0;
        foreach ($request->get('invoiceId') as $invoiceId) {
            $model = new Receipt();
            $model->invoice_id = $invoiceId;
            $model->person_name = $request->get('personName');
            $model->type = $request->get('type');
            $model->receipt_method = $request->get('receiptMethod');
            $model->description = $request->get('description');
            $model->branch_id = \Session::get('currentBranch')->branch_id;

            if ($model->isCash()) {
                $kasBranch = $this->getCurrentBranchKas();
                $model->bank_id = $kasBranch !== null ? $kasBranch->bank_id : null;
            } elseif ($model->isTransfer()) {
                $model->bank_id = $request->get('bankId');
            } else {
                $model->bank_id = null;
            }

            $model->amount = $request->get('receipt')[$i];
            $model->created_date = $this->now;
            $model->created_by = \Auth::user()->id;
            $model->receipt_number = $this->getReceiptNumber($model);

            try {
                $model->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }

            if ($model->isDp()) {
                $invoice = Invoice::find($invoiceId);
                $resi    = $invoice->resi;
                if ($resi !== null) {
                    $resi->payment = $request->get('payment')[$i];

                    try {
                        $resi->save();
                    } catch (\Exception $e) {
                        return $e->getMessage();
                    }
                }
            }

            $error = $this->closeInvoiceJikaLunas($model);
            if (!empty($error)) {
                return $error;
            }

            $error = $this->createJournalReceiptAr($model);
            if (!empty($error)) {
                return $error;
            }

            $this->saveHistoryResi($model);

            $i++;
        }
    }

    protected function closeInvoiceJikaLunas(Receipt $model)
    {
        $invoice = Invoice::find($model->invoice_id);
        if ($invoice->remaining() <= 0) {
            $invoice->status = Invoice::CLOSED;

            try {
                $invoice->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }

        foreach($invoice->batchInvoiceLine as $batchInvoiceLine) {
            $batchInvoiceHeader = $batchInvoiceLine->header;
            if ($batchInvoiceHeader === null) {
                continue;
            }

            if ($batchInvoiceHeader->isOpen() && $batchInvoiceHeader->remaining() <= 0) {
                $batchInvoiceHeader->status = BatchInvoiceHeader::CLOSED;
                try {
                    $batchInvoiceHeader->save();
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            }
        }
    }

    protected function saveHistoryResi(Receipt $model)
    {
        $receipt = Receipt::find($model->receipt_id);
        HistoryResiService::saveHistory(
            $receipt->invoice->resi_header_id,
            'Receipt Invoice',
            'Receipt Number: '.$receipt->receipt_number.', Amount: '.number_format($receipt->amount)
        );
    }

    protected function createJournalReceiptAr(Receipt $model, $cekGiroNumber = null)
    {
        $receipt       = Receipt::find($model->receipt_id);
        $journalHeader = new JournalHeader();

        if ($receipt->invoice->isInvoicePickup()) {
            $category = JournalHeader::RECEIPT_PICKUP;
        } elseif ($receipt->invoice->isInvoiceDO()) {
            $category = JournalHeader::RECEIPT_DO;
        } else {
            $category = JournalHeader::RECEIPT_RESI;
        }

        $journalHeader->category       = $category;
        $journalHeader->period         = new \DateTime($this->now->format('Y-m-1'));
        $journalHeader->status         = JournalHeader::OPEN;
        $journalHeader->description    = 'Receipt Number: '.$receipt->receipt_number.'. Invoice Number: '.$receipt->invoice->invoice_number.
                                            '. Resi Number: '.$receipt->invoice->resi->resi_number;
        $journalHeader->branch_id      = $receipt->branch_id;
        $journalHeader->journal_date   = $this->now;
        $journalHeader->created_date   = $this->now;
        $journalHeader->created_by     = \Auth::user()->id;
        $journalHeader->journal_number = JournalService::getJournalNumber($journalHeader);

        try {
            $journalHeader->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        /** KAS / BANK **/
        $coaBank     = $receipt->bank->coaBank;
        $combination = AccountCombinationService::getCombination($coaBank->coa_code);

        $line = new JournalLine();
        $line->journal_header_id      = $journalHeader->journal_header_id;
        $line->account_combination_id = $combination->account_combination_id;
        $line->debet                  = $receipt->amount;
        $line->credit                 = 0;
        $line->created_date           = $this->now;
        $line->created_by             = \Auth::user()->id;

        try {
            $line->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        /** PIUTANG USAHA - CEK / GIRO **/
        if (!empty($cekGiroNumber)) {
            $settingCoa  = SettingJournal::where('setting_name', SettingJournal::CEK_GIRO)->first();
            $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

            $line = new JournalLine();
            $line->journal_header_id      = $journalHeader->journal_header_id;
            $line->account_combination_id = $combination->account_combination_id;
            $line->debet                  = 0;
            $line->credit                 = $receipt->amount;
            $line->created_date           = $this->now;
            $line->created_by             = \Auth::user()->id;

            if (!empty($cekGiroNumber)) {
                $line->description = 'Cek Giro Number: '.$cekGiroNumber;
            }

            try {
                $line->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }

        } else {
            $kelebihanBayar    = $receipt->invoice->remaining() < 0 ? abs($receipt->invoice->remaining()) : 0;
            $kelebihanBayarNow = $receipt->amount - $kelebihanBayar > 0 ? $kelebihanBayar : $receipt->amount;
            $receiptPiutang    = $receipt->amount - $kelebihanBayarNow;

            if ($receiptPiutang > 0) {
                $settingCoa  = SettingJournal::where('setting_name', SettingJournal::PIUTANG_USAHA)->first();
                $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

                $line = new JournalLine();
                $line->journal_header_id      = $journalHeader->journal_header_id;
                $line->account_combination_id = $combination->account_combination_id;
                $line->debet                  = 0;
                $line->credit                 = $receiptPiutang;
                $line->created_date           = $this->now;
                $line->created_by             = \Auth::user()->id;

                try {
                    $line->save();
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            }

            if ($kelebihanBayarNow > 0) {
                $settingCoa  = SettingJournal::where('setting_name', SettingJournal::KELEBIHAN_PEMBAYARAN)->first();
                $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

                $line = new JournalLine();
                $line->journal_header_id      = $journalHeader->journal_header_id;
                $line->account_combination_id = $combination->account_combination_id;
                $line->debet                  = 0;
                $line->credit                 = $kelebihanBayarNow;
                $line->created_date           = $this->now;
                $line->created_by             = \Auth::user()->id;

                try {
                    $line->save();
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            }
        }
    }

    protected function getCurrentBranchKas()
    {
        $bank = \DB::table('gl.mst_bank')
                    ->select('mst_bank.*')
                    ->leftJoin('gl.dt_bank_branch', 'mst_bank.bank_id', '=', 'dt_bank_branch.bank_id')
                    ->where('dt_bank_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where('mst_bank.type', '=', MasterBank::CASH_IN)
                    ->where('mst_bank.active', '=', 'Y')
                    ->first();

        return $bank;
    }

    protected function createReceiptBatchInvoice(Request $request)
    {
        $batchInvoice = BatchInvoiceHeader::find(intval($request->get('batchInvoiceId')));
        if ($batchInvoice === null) {
            return;
        }

        foreach ($batchInvoice->lines as $line) {
            $invoice = $line->invoice;
            if ($invoice === null) {
                continue;
            }

            if ($invoice->remaining() <= 0) {
                continue;
            }

            $model = new Receipt();
            $model->invoice_id = $invoice->invoice_id;
            $model->person_name = $request->get('personName');
            $model->type = Receipt::BATCH;
            $model->receipt_method = $request->get('receiptMethod');
            $model->description = $request->get('description');
            $model->branch_id = \Session::get('currentBranch')->branch_id;
            $model->batch_invoice_line_id = $line->batch_invoice_line_id;

            if ($model->isCash()) {
                $kasBranch = $this->getCurrentBranchKas();
                $model->bank_id = $kasBranch !== null ? $kasBranch->bank_id : null;
            } elseif ($model->isTransfer()) {
                $model->bank_id = $request->get('bankId');
            }

            $model->amount = $invoice->remaining();
            $model->created_date = $this->now;
            $model->created_by = \Auth::user()->id;
            $model->receipt_number = $this->getReceiptNumber($model);

            try {
                $model->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }

            $error = $this->closeInvoiceJikaLunas($model);
            if (!empty($error)) {
                return $error;
            }

            $error = $this->createJournalReceiptAr($model);
            if (!empty($error)) {
                return $error;
            }

            $this->saveHistoryResi($model);
        }
    }

    protected function createReceiptCekGiro(Request $request)
    {
        $cekGiro = CekGiroHeader::find(intval($request->get('cekGiroHeaderId')));
        if ($cekGiro === null) {
            return;
        }

        $cekGiro->payment_date = new \DateTime();
        try {
            $cekGiro->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        foreach ($cekGiro->lines as $line) {
            $invoice = $line->invoice;
            if ($invoice === null) {
                continue;
            }

            if ($line->amount <= 0) {
                continue;
            }

            $model = new Receipt();
            $model->invoice_id = $invoice->invoice_id;
            $model->person_name = $request->get('personName');
            $model->type = Receipt::CEK_GIRO;
            $model->receipt_method = $request->get('receiptMethod');
            $model->description = $request->get('description');
            $model->branch_id = \Session::get('currentBranch')->branch_id;

            if ($model->isCash()) {
                $kasBranch = $this->getCurrentBranchKas();
                $model->bank_id = $kasBranch !== null ? $kasBranch->bank_id : null;
            } elseif ($model->isTransfer()) {
                $model->bank_id = $request->get('bankId');
            }

            $model->cek_giro_line_id = $line->cek_giro_line_id;
            $model->amount = $line->amount;
            $model->created_date = $this->now;
            $model->created_by = \Auth::user()->id;
            $model->receipt_number = $this->getReceiptNumber($model);

            try {
                $model->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }

            $error = $this->closeInvoiceJikaLunas($model);
            if (!empty($error)) {
                return $error;
            }

            $error = $this->createJournalReceiptAr($model, $cekGiro->cek_giro_number);
            if (!empty($error)) {
                return $error;
            }

            $this->saveHistoryResi($model);
        }
    }

    public function getJsonInvoice(Request $request)
    {
        $maxData   = 10;
        $iteration = 1;
        $isFull    = false;
        $data      = [];

        while(!$isFull) {
            $dataQuery = $this->getDataQueryInvoice($request, $maxData, $iteration);
            if (empty($dataQuery)) {
                $isFull = true;
            }

            foreach ($dataQuery as $invoice) {
                if ($invoice->remaining > 0) {
                    $data[] = $invoice;
                }

                if (count($data) >= $maxData) {
                    $isFull = true;
                    break;
                }
            }

            $iteration++;
        }

        return response()->json($data);
    }

    protected function getDataQueryInvoice(Request $request, $maxData, $iteration)
    {
        $search = $request->get('search');
        $type   = $request->get('type');
        $query  = \DB::table('ar.invoice')
                    ->select(
                        'invoice.*', 
                        'trans_resi_header.resi_number', 
                        'trans_resi_header.sender_name', 
                        'trans_resi_header.receiver_name',
                        'trans_resi_header.payment', 
                        'mst_route.route_code', 
                        'customer_sender.customer_name as customer_sender_name',
                        'customer_receiver.customer_name as customer_receiver_name'
                    )
                    ->leftJoin('ar.receipt', 'invoice.invoice_id', '=', 'receipt.invoice_id')
                    ->leftJoin('op.trans_resi_header', 'invoice.resi_header_id', '=', 'trans_resi_header.resi_header_id')
                    ->leftJoin('op.mst_route', 'trans_resi_header.route_id', '=', 'mst_route.route_id')
                    ->leftJoin('op.mst_customer as customer_sender', 'trans_resi_header.customer_id', '=', 'customer_sender.customer_id')
                    ->leftJoin('op.mst_customer as customer_receiver', 'trans_resi_header.customer_receiver_id', '=', 'customer_receiver.customer_id')
                    ->leftJoin('ar.cek_giro_line', 'invoice.invoice_id', '=', 'cek_giro_line.invoice_id')
                    ->leftJoin('ar.cek_giro_header', 'cek_giro_line.cek_giro_header_id', '=', 'cek_giro_header.cek_giro_header_id')
                    ->where('invoice.status', '<>', Invoice::CANCELED)
                    ->where(function($query) {
                        $query->whereNull('cek_giro_line.cek_giro_line_id')
                                ->orWhere('cek_giro_header.status', '<>', CekGiroHeader::OPEN);
                    })
                    ->orderBy('invoice.created_date', 'desc')
                    ->distinct();

        if (!empty($search)) {
            $query->where(function($query) use ($search) {
                $query->where('invoice.invoice_number', 'ilike', '%'.$search.'%')
                        ->orWhere('trans_resi_header.resi_number', 'ilike', '%'.$search.'%')
                        ->orWhere('mst_route.route_code', 'ilike', '%'.$search.'%')
                        ->orWhere('invoice.type', 'ilike', '%'.$search.'%')
                        ->orWhere('trans_resi_header.sender_name', 'ilike', '%'.$search.'%')
                        ->orWhere('trans_resi_header.receiver_name', 'ilike', '%'.$search.'%')
                        ->orWhere('customer_sender.customer_name', 'ilike', '%'.$search.'%')
                        ->orWhere('trans_resi_header.sender_name', 'ilike', '%'.$search.'%')
                        ->orWhere('customer_receiver.customer_name', 'ilike', '%'.$search.'%')
                        ->orWhere('trans_resi_header.receiver_name', 'ilike', '%'.$search.'%');
            });
        }

        if ($type == Receipt::DP) {
            $query->whereNull('receipt.receipt_id')
                    ->where('invoice.type', '=', Invoice::INV_RESI);
        }

        $invoices = [];
        $skip     = ($iteration - 1) * $maxData;
        foreach ($query->take($maxData)->skip($skip)->get() as $invoice) {
            $modelInvoice       = Invoice::find($invoice->invoice_id);
            $createdDate        = !empty($modelInvoice->created_date) ? new \DateTime($modelInvoice->created_date) : null;
            $invoice->date      = $createdDate !== null ? $createdDate->format('d-m-Y') : '';
            $invoice->remaining = $modelInvoice->remaining();

            $invoices[] = $invoice;
        }

        return $invoices;
    }

    public function getJsonBatchInvoice(Request $request)
    {
        $maxData   = 10;
        $iteration = 1;
        $isFull    = false;
        $data      = [];

        while(!$isFull) {
            $dataQuery = $this->getDataQueryBatchInvoice($request, $maxData, $iteration);
            if (empty($dataQuery)) {
                $isFull = true;
            }

            foreach ($dataQuery as $batch) {
                $data[] = $batch;
                if (count($data) >= $maxData) {
                    $isFull = true;
                    break;
                }
            }

            $iteration++;
        }

        return response()->json($data);
    }

    protected function getDataQueryBatchInvoice(Request $request, $maxData, $iteration)
    {
        $search = $request->get('search');
        $type   = $request->get('type');
        $query  = \DB::table('ar.invoice')
                    ->select(
                        'invoice.*', 'batch_invoice_header.batch_invoice_header_id', 'batch_invoice_header.batch_invoice_number', 'trans_resi_header.resi_number',
                        'trans_resi_header.sender_name', 'trans_resi_header.receiver_name', 'trans_resi_header.payment', 'mst_route.route_code',
                        'customer_sender.customer_name as customer_sender_name', 'customer_receiver.customer_name as customer_receiver_name'
                    )
                    ->join('ar.batch_invoice_line', 'invoice.invoice_id', '=', 'batch_invoice_line.invoice_id')
                    ->join('ar.batch_invoice_header', 'batch_invoice_line.batch_invoice_header_id', '=', 'batch_invoice_header.batch_invoice_header_id')
                    ->leftJoin('ar.receipt', 'invoice.invoice_id', '=', 'receipt.invoice_id')
                    ->leftJoin('op.trans_resi_header', 'invoice.resi_header_id', '=', 'trans_resi_header.resi_header_id')
                    ->leftJoin('op.mst_route', 'trans_resi_header.route_id', '=', 'mst_route.route_id')
                    ->leftJoin('op.mst_customer as customer_sender', 'trans_resi_header.customer_id', '=', 'customer_sender.customer_id')
                    ->leftJoin('op.mst_customer as customer_receiver', 'trans_resi_header.customer_receiver_id', '=', 'customer_receiver.customer_id')
                    ->where('batch_invoice_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where('batch_invoice_header.status', '=', BatchInvoiceHeader::OPEN)
                    ->orderBy('batch_invoice_header.batch_invoice_header_id', 'desc')
                    ->distinct();

        if (!empty($search)) {
            $query->where(function($query) use ($search) {
                $query->where('batch_invoice_header.batch_invoice_number', 'ilike', '%'.$search.'%')
                        ->orwhere('invoice.invoice_number', 'ilike', '%'.$search.'%')
                        ->orWhere('trans_resi_header.resi_number', 'ilike', '%'.$search.'%')
                        ->orWhere('mst_route.route_code', 'ilike', '%'.$search.'%')
                        ->orWhere('invoice.type', 'ilike', '%'.$search.'%')
                        ->orWhere('trans_resi_header.sender_name', 'ilike', '%'.$search.'%')
                        ->orWhere('trans_resi_header.receiver_name', 'ilike', '%'.$search.'%')
                        ->orWhere('customer_sender.customer_name', 'ilike', '%'.$search.'%')
                        ->orWhere('trans_resi_header.sender_name', 'ilike', '%'.$search.'%')
                        ->orWhere('customer_receiver.customer_name', 'ilike', '%'.$search.'%')
                        ->orWhere('trans_resi_header.receiver_name', 'ilike', '%'.$search.'%');
            });
        }

        $invoices = [];
        $skip     = ($iteration - 1) * $maxData;
        foreach ($query->take($maxData)->skip($skip)->get() as $invoice) {
            $batchInvoice = BatchInvoiceHeader::find($invoice->batch_invoice_header_id);
            if ($batchInvoice === null) {
                continue;
            }

            $modelInvoice = Invoice::find($invoice->invoice_id);
            if ($modelInvoice->remaining() <= 0) {
                continue;
            }

            $createdDate              = !empty($modelInvoice->created_date) ? new \DateTime($modelInvoice->created_date) : null;
            $invoice->date            = $createdDate !== null ? $createdDate->format('d-m-Y') : '';
            $invoice->remaining       = $modelInvoice->remaining();
            $invoice->total_remaining = $batchInvoice->remaining();
            $invoice->lines           = $this->getBatchInvoiceLines($batchInvoice);

            $invoices[] = $invoice;
        }

        return $invoices;
    }

    protected function getBatchInvoiceLines(BatchInvoiceHeader $batch)
    {
        $lines = [];
        foreach ($batch->lines as $line) {
            $lines[] = (object) [
                'invoiceId' => $line->invoice_id,
                'invoiceNumber' => !empty($line->invoice) ? $line->invoice->invoice_number : '',
                'invoiceType' => !empty($line->invoice) ? $line->invoice->type : '',
                'resiNumber' => !empty($line->invoice->resi) ? $line->invoice->resi->resi_number : '',
                'route' => !empty($line->invoice->resi->route) ? $line->invoice->resi->route->route_code : '',
                'customerSender' => !empty($line->invoice->resi->customer) ? $line->invoice->resi->customer->customer_name : '',
                'sender' => !empty($line->invoice->resi) ? $line->invoice->resi->sender_name : '',
                'customerReceiver' => !empty($line->invoice->resi->customerReceiver) ? $line->invoice->resi->customerReceiver->customer_name : '',
                'receiver' => !empty($line->invoice->resi) ? $line->invoice->resi->receiver_name : '',
                'payment' => !empty($line->invoice->resi) ? $line->invoice->resi->payment : '',
                'amount' => !empty($line->invoice) ? $line->invoice->totalInvoice() : 0,
                'remaining' => !empty($line->invoice) ? $line->invoice->remaining() : 0,
                'receipt' => !empty($line->invoice) ? $line->invoice->remaining() : 0,
            ];
        }

        return $lines;
    }

    public function getJsonBank(Request $request)
    {
        $query = \DB::table('gl.mst_bank')
                    ->select('mst_bank.*')
                    ->leftJoin('gl.dt_bank_branch', 'mst_bank.bank_id', '=', 'dt_bank_branch.bank_id')
                    ->where('mst_bank.active', '=', 'Y')
                    ->where('dt_bank_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where('type', '=', MasterBank::BANK)
                    ->where('bank_name', 'ilike', '%'.$request->get('search').'%')
                    ->orderBy('bank_name', 'asc')
                    ->take(10);

        return response()->json($query->get());
    }

    public function getJsonCekGiro(Request $request)
    {
        $search = $request->get('search');
        $query  = \DB::table('ar.cek_giro_header')
                    ->select('cek_giro_header.*', 'mst_customer.customer_name')
                    ->leftJoin('op.mst_customer', 'cek_giro_header.customer_id', '=', 'mst_customer.customer_id')
                    ->whereNull('cek_giro_header.payment_date')
                    ->where('status', '=', CekGiroHeader::CLOSED)
                    ->orderBy('cek_giro_header.created_date', 'desc')
                    ->take(10);

        if (!empty($search)) {
            $query->where(function($query) use ($search) {
                $query->where('cek_giro_header.cek_giro_number', 'ilike', '%'.$search.'%')
                        ->where('cek_giro_header.cek_giro_account_number', 'ilike', '%'.$search.'%')
                        ->where('cek_giro_header.person_name', 'ilike', '%'.$search.'%')
                        ->where('cek_giro_header.bank_name', 'ilike', '%'.$search.'%')
                        ->where('mst_customer.customer_name', 'ilike', '%'.$search.'%');
            });
        }

        $cekGiros = [];
        foreach ($query->get() as $cekGiro) {
            $modelCekGiro = CekGiroHeader::find($cekGiro->cek_giro_header_id);

            $createdDate  = !empty($cekGiro->created_date) ? new \DateTime($cekGiro->created_date) : null;
            $clearingDate = !empty($cekGiro->clearing_date) ? new \DateTime($cekGiro->clearing_date) : null;

            $cekGiro->date          = $createdDate !== null ? $createdDate->format('d-m-Y') : '';
            $cekGiro->clearing_date = $clearingDate !== null ? $clearingDate->format('d-m-Y') : '';
            $cekGiro->total_amount  = $modelCekGiro->totalAmount();
            $cekGiro->lines         = $this->getCekGiroLines($modelCekGiro);

            $cekGiros[] = $cekGiro;
        }

        return response()->json($cekGiros);
    }

    protected function getCekGiroLines(CekGiroHeader $cekGiroHeader)
    {
        $lines = [];
        foreach ($cekGiroHeader->lines as $line) {
            $lines[] = (object) [
                'lineId' => $line->cek_giro_line_id,
                'invoiceId' => $line->invoice_id,
                'invoiceNumber' => !empty($line->invoice) ? $line->invoice->invoice_number : '',
                'invoiceType' => !empty($line->invoice) ? $line->invoice->type : '',
                'resiNumber' => !empty($line->invoice->resi) ? $line->invoice->resi->resi_number : '',
                'route' => !empty($line->invoice->resi->route) ? $line->invoice->resi->route->route_code : '',
                'customerSender' => !empty($line->invoice->resi->customer) ? $line->invoice->resi->customer->customer_name : '',
                'sender' => !empty($line->invoice->resi) ? $line->invoice->resi->sender_name : '',
                'customerReceiver' => !empty($line->invoice->resi->customerReceiver) ? $line->invoice->resi->customerReceiver->customer_name : '',
                'receiver' => !empty($line->invoice->resi) ? $line->invoice->resi->receiver_name : '',
                'payment' => !empty($line->invoice->resi) ? $line->invoice->resi->payment : '',
                'amount' => !empty($line->invoice) ? $line->invoice->totalInvoice() : 0,
                'remaining' => !empty($line->invoice) ? $line->invoice->remaining() : 0,
                'receipt' => !empty($line->amount) ? $line->amount : 0,
            ];
        }

        return $lines;
    }

    protected function getReceiptNumber(Receipt $model)
    {
        $branch      = MasterBranch::find($model->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('ar.receipt')
                            ->where('branch_id', '=', $model->branch_id)
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->count();

        return 'RAR.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 6);
    }

    protected function getOptionReceiptMethod()
    {
        return [Receipt::CASH, Receipt::TRANSFER];
    }
}
