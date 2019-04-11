<?php

namespace App\Modules\Accountreceivables\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Generalledger\Model\Master\MasterBank;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Modules\Asset\Model\Transaction\AdditionAsset;
use App\Modules\Accountreceivables\Model\Transaction\Receipt;
use App\Modules\Accountreceivables\Model\Transaction\BatchInvoiceHeader;
use App\Modules\Accountreceivables\Model\Transaction\BatchInvoiceLine;
use App\Modules\Accountreceivables\Model\Transaction\Invoice;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Accountreceivables\Model\Master\MasterCekGiro;
use App\Modules\Operational\Service\Master\CustomerService;
use App\Modules\Payable\Model\Transaction\InvoiceHeader;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Generalledger\Service\Master\AccountCombinationService;
use App\Modules\Generalledger\Service\Master\JournalService;
use App\Modules\Generalledger\Model\Master\SettingJournal;
use App\Role;
use App\Service\Penomoran;
use App\Service\NotificationService;
use App\Modules\Operational\Service\Transaction\HistoryResiService;

class ReceiptOtherController extends Controller
{
    const RESOURCE = 'Accountreceivables\Transaction\ReceiptOther';
    const URL      = 'accountreceivables/transaction/receipt-other';

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

        return view('accountreceivables::transaction.receipt-other.index', [
            'models' => $query->paginate(10),
            'filters' => $filters,
            'resource' => self::RESOURCE,
            'url' => self::URL,
            'optionType' => $this->getOptionType(),
            'optionReceiptMethod' => $this->getOptionReceiptMethod(),
        ]);
    }

    protected function getQuery(Request $request, $filters){
        $query   = \DB::table('ar.receipt')
                        ->select('receipt.*')
                        ->leftJoin('op.trans_resi_header', 'receipt.resi_header_id', '=', 'trans_resi_header.resi_header_id')
                        ->leftJoin('ap.invoice_header as invoice_ap_header', 'receipt.invoice_ap_header_id', '=', 'invoice_ap_header.header_id')
                        ->where(function($query) {
                            $query->where('receipt.type', '=', Receipt::EXTRA_COST)
                                    ->orWhere('receipt.type', '=', Receipt::KASBON)
                                    ->orWhere('receipt.type', '=', Receipt::ASSET_SELLING)
                                    ->orWhere('receipt.type', '=', Receipt::OTHER);
                        })
                        ->where('receipt.branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->orderBy('receipt.created_date', 'desc')
                        ->distinct();

        if (!empty($filters['receiptNumber'])) {
            $query->where('receipt_number', 'ilike', '%'.$filters['receiptNumber'].'%');
        }

        if (!empty($filters['type'])) {
            $query->where('receipt.type', '=', $filters['type']);
        }

        if (!empty($filters['resiNumber'])) {
            $query->where('trans_resi_header.resi_number', 'ilike', '%'.$filters['resiNumber'].'%');
        }

        if (!empty($filters['invoiceApNumber'])) {
            $query->where('invoice_ap_header.invoice_number', 'ilike', '%'.$filters['invoiceApNumber'].'%');
        }

        if (!empty($filters['personName'])) {
            $query->where('person_name', 'ilike', '%'.$filters['personName'].'%');
        }

        if (!empty($filters['receiptMethod'])) {
            $query->where('receipt.receipt_method', '=', $filters['receiptMethod']);
        }

        if (!empty($filters['description'])) {
            $query->where('receipt.description', 'ilike', '%'.$filters['description'].'%');
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

        $model = new Receipt();

        return view('accountreceivables::transaction.receipt-other.add', [
            'title' => trans('shared/common.add'),
            'model' => $model,
            'url' => self::URL,
            'resource' => self::RESOURCE,
            'optionType' => $this->getOptionType(),
            'optionReceiptMethod' => $this->getOptionReceiptMethod(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        $model = Receipt::where('receipt_id', '=', $id)->first();
        if ($model === null || !($model->isExtraCost() || $model->isKasbon() || $model->isOther() || $model->isAssetSelling())) {
            abort(404);
        }

        if ($request->user()->cannot('accessBranch', $model->branch_id)) {
            abort(403);
        }

        return view('accountreceivables::transaction.receipt-other.add', [
            'title' => trans('shared/common.edit'),
            'model' => $model,
            'url' => self::URL,
            'resource' => self::RESOURCE,
            'optionType' => $this->getOptionType(),
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

        $html = view('accountreceivables::transaction.receipt-other.print-pdf', ['model' => $model])->render();

        \PDF::SetTitle(trans('accountreceivables/menu.receipt-other').' '.$model->receipt_number);
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

        $header = view('print.header-pdf', ['title' => trans('accountreceivables/menu.receipt-other')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('accountreceivables::transaction.receipt-other.print-pdf-index', [
            'models'  => $query->get(),
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('accountreceivables/menu.receipt-other'));
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output(trans('accountreceivables/menu.receipt-other').'.pdf');
        \PDF::reset();
    }

    public function printExcelIndex(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters);

        \Excel::create(trans('accountreceivables/menu.receipt-other'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('accountreceivables/menu.receipt-other'));
                });

                $sheet->cells('A3:M3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('accountreceivables/fields.receipt-number'),
                    trans('shared/common.type'),
                    trans('shared/common.date'),
                    trans('accountreceivables/fields.invoice-number'),
                    trans('payable/fields.trading'),
                    trans('asset/fields.asset-number'),
                    trans('inventory/fields.item'),
                    trans('shared/common.person-name'),
                    trans('shared/common.description'),
                    trans('accountreceivables/fields.receipt-method'),
                    trans('accountreceivables/fields.cash-or-bank'),
                    trans('accountreceivables/fields.amount'),
                ]);

                $currentRow = 4;
                $num = 1;
                foreach($query->get() as $model) {
                    $model = Receipt::find($model->receipt_id);
                    $date = !empty($model->created_date) ? new \DateTime($model->created_date) : null;

                    $data = [
                        $num++,
                        $model->receipt_number,
                        $model->type,
                        $date !== null ? $date->format('d-m-Y') : '',
                        !empty($model->invoiceApHeader) ? $model->invoiceApHeader->invoice_number : '',
                        !empty($model->invoiceApHeader) ? $model->invoiceApHeader->getTradingKasbonCode() . ' - ' . $model->invoiceApHeader->getTradingKasbonName() : '',
                        !empty($model->additionAsset) ? $model->additionAsset->asset_number : '',
                        !empty($model->additionAsset) ? $model->additionAsset->item->description : '',
                        $model->person_name,
                        $model->description,
                        $model->receipt_method,
                        !empty($model->bank) ? $model->bank->bank_name : '',
                        $model->amount,
                    ];

                    $sheet->row($currentRow++, $data);
                }

                $lastDataRow = $currentRow;
                $currentRow = $lastDataRow + 1;
                if (!empty($filters['receiptNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('accountreceivables/fields.receipt-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['receiptNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['type'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.type'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['type'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['invoiceNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('accountreceivables/fields.invoice-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['invoiceNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['personName'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.person-name'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['personName'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['receiptMethod'])) { 
                    $this->addLabelDescriptionCell($sheet, trans('accountreceivables/fields.receipt-method'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['receiptMethod'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['description'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.description'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['description'], 'C', $currentRow);
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
            'type' => 'required',
            'resiId' => 'required_if:type,'.Receipt::EXTRA_COST,
            'invoiceApId' => 'required_if:type,'.Receipt::KASBON,
            'assetId' => 'required_if:type,'.Receipt::ASSET_SELLING,
            'coaId' => 'required_if:type,'.Receipt::EXTRA_COST.'|required_if:type,'.Receipt::OTHER,
            'bankId' => 'required_if:receiptMethod,'.Receipt::TRANSFER,
            'cekGiroId' => 'required_if:receiptMethod,'.Receipt::CEK_GIRO,
            'amount' => 'required',
            'description' => 'required',
        ], [
            'resiId.required_if' => 'This field is required',
            'invoiceApId.required_if' => 'This field is required',
            'assetId.required_if' => 'This field is required',
            'personName.required_if' => 'This field is required',
            'coaId.required_if' => 'This field is required',
            'bankId.required_if' => 'This field is required',
            'cekGiroId.required_if' => 'This field is required',
        ]);

        if ($request->get('receiptMethod') === Receipt::CASH) {
            $kasBranch = $this->getCurrentBranchKas();
            if ($kasBranch === null) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(
                    ['errorMessage' => 'Kas '.\Session::get('currentBranch')->branch_name.' is not exist']
                );
            }
        }

        $id = $request->get('id');
        $model = !empty($id) ? Receipt::find($i) : new Receipt();
        $model->person_name = $request->get('personName');
        $model->type = $request->get('type');
        $model->receipt_method = $request->get('receiptMethod');
        $model->amount = str_replace(',', '', $request->get('amount'));
        $model->description = $request->get('description');
        $model->branch_id = \Session::get('currentBranch')->branch_id;

        if ($model->isExtraCost()) {
            $model->resi_header_id = $request->get('resiId');
            $model->coa_id = $request->get('coaId');
        } elseif ($model->isKasbon()) {
            $model->invoice_ap_header_id = $request->get('invoiceApId');
        } elseif ($model->isAssetSelling()) {
            $model->asset_id = $request->get('assetId');
        } elseif ($model->isOther()) {
            $model->coa_id = $request->get('coaId');
        }

        if ($model->isCash()) {
            $kasBranch = $this->getCurrentBranchKas();
            $model->bank_id = $kasBranch !== null ? $kasBranch->bank_id : null;
        } else {
            $model->bank_id = $request->get('bankId');
        }

        $model->created_date = $this->now;
        $model->created_by = \Auth::user()->id;
        $model->receipt_number = $this->getReceiptNumber($model);

        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        if ($model->isAssetSelling()) {
            $asset = AdditionAsset::find($model->asset_id);
            $asset->status_id = AdditionAsset::SOLD;

            try {
                $asset->save();
            } catch (\Exception $e) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }
        }

        $error = $this->createJournalReceiptOther($model);
        if (!empty($error)) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $error]);
        }

        if ($model->isExtraCost()) {
            $this->saveHistoryResi($model);
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('accountreceivables/menu.receipt')])
        );

        return redirect(self::URL);
    }

    protected function saveHistoryResi(Receipt $model)
    {
        $receipt = Receipt::find($model->receipt_id);
        HistoryResiService::saveHistory(
            $receipt->resi_header_id,
            'Receipt Extra Cost',
            'Receipt Number: '.$receipt->receipt_number.', Amount: '.number_format($receipt->amount).', Note: '.$receipt->description
        );
    }

    protected function createJournalReceiptOther(Receipt $model, $cekGiroNumber = null)
    {
        $receipt       = Receipt::find($model->receipt_id);
        $journalHeader = new JournalHeader();

        if ($receipt->isExtraCost()) {
            $category    = JournalHeader::RECEIPT_EXTRA_COST;
            $description = 'Receipt Number: '.$receipt->receipt_number.'. Resi Number: '.$receipt->resi->resi_number.'. '.$receipt->description;
        } elseif ($receipt->isKasbon()) {
            $category = JournalHeader::RECEIPT_KASBON;
            $description = 'Receipt Number: '.$receipt->receipt_number.'. Invoice AP Number: '.$receipt->invoiceApHeader->invoice_number;
        } elseif ($receipt->isAssetSelling()) {
            $category = JournalHeader::RECEIPT_ASSET_SELLING;
            $description = 'Receipt Number: '.$receipt->receipt_number.'. Asset Number: '.$receipt->additionAsset->asset_number;
        } else {
            $category = JournalHeader::RECEIPT_OTHER;
            $description = 'Receipt Number: '.$receipt->receipt_number.'. '.$receipt->description;
        }

        $journalHeader->category       = $category;
        $journalHeader->period         = new \DateTime($this->now->format('Y-m-1'));
        $journalHeader->status         = JournalHeader::OPEN;
        $journalHeader->description    = $description;
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

        if (!$receipt->isAssetSelling()) {
            /** KREDIT SELAIN ASSET SELLING **/
            $coaCode     = $receipt->isKasbon() ? $receipt->invoiceApHeader->type->coaD->coa_code : $receipt->coa->coa_code;
            $combination = AccountCombinationService::getCombination($coaCode);

            $line = new JournalLine();
            $line->journal_header_id      = $journalHeader->journal_header_id;
            $line->account_combination_id = $combination->account_combination_id;
            $line->debet                  = 0;
            $line->credit                 = $receipt->amount;
            $line->created_date           = $this->now;
            $line->created_by             = \Auth::user()->id;

            try {
                $line->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }

        } else {
            /** ASSET SELLING **/
            $tanggalAsset = new \DateTime($receipt->additionAsset->created_date);
            $dateInterval = $this->now->diff($tanggalAsset);
            $umurHari = intval($dateInterval->format("%a"));
            $umurHari = $umurHari > 0 ? $umurHari : 0;
            $jumlahHariSebulan = 30;
            $umurBulan = $umurHari > 0 ? round($umurHari/$jumlahHariSebulan) : 0;
            $penyusutan = $umurBulan * $receipt->additionAsset->depreciation->cost_month;
            $hargaDisusutkan = $receipt->additionAsset->po_cost - $penyusutan;
            $hargaDisusutkan = $hargaDisusutkan > 0 ? $hargaDisusutkan : 0;
            $labaRugi = $receipt->amount - $hargaDisusutkan;

            if ($penyusutan > 0) {
                /** PENYUSUTAN **/
                $combination = AccountCombinationService::getCombination($receipt->additionAsset->category->acumulated->coa_code);

                $line = new JournalLine();
                $line->journal_header_id      = $journalHeader->journal_header_id;
                $line->account_combination_id = $combination->account_combination_id;
                $line->debet                  = $penyusutan;
                $line->credit                 = 0;
                $line->created_date           = $this->now;
                $line->created_by             = \Auth::user()->id;

                try {
                    $line->save();
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            }

            if ($labaRugi > 0) {
                /** LABA **/
                $settingCoa  = SettingJournal::where('setting_name', SettingJournal::LABA_PENJUALAN_ASSET)->first();
                $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

                $line = new JournalLine();
                $line->journal_header_id      = $journalHeader->journal_header_id;
                $line->account_combination_id = $combination->account_combination_id;
                $line->debet                  = 0;
                $line->credit                 = $labaRugi;
                $line->created_date           = $this->now;
                $line->created_by             = \Auth::user()->id;

                try {
                    $line->save();
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            } elseif ($labaRugi < 0) {
                /** RUGI **/
                $settingCoa  = SettingJournal::where('setting_name', SettingJournal::RUGI_PENJUALAN_ASSET)->first();
                $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

                $line = new JournalLine();
                $line->journal_header_id      = $journalHeader->journal_header_id;
                $line->account_combination_id = $combination->account_combination_id;
                $line->debet                  = abs($labaRugi);
                $line->credit                 = 0;
                $line->created_date           = $this->now;
                $line->created_by             = \Auth::user()->id;

                try {
                    $line->save();
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            }

            /** ASSET TETAP **/
            $combination = AccountCombinationService::getCombination($receipt->additionAsset->category->clearing->coa_code);

            $line = new JournalLine();
            $line->journal_header_id      = $journalHeader->journal_header_id;
            $line->account_combination_id = $combination->account_combination_id;
            $line->debet                  = 0;
            $line->credit                 = $receipt->additionAsset->po_cost;
            $line->created_date           = $this->now;
            $line->created_by             = \Auth::user()->id;

            try {
                $line->save();
            } catch (\Exception $e) {
                return $e->getMessage();
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

    protected function getDataQueryInvoice(Request $request, $maxData, $iteration)
    {
        $search = $request->get('search');
        $type   = $request->get('type');
        $query  = \DB::table('ar.invoice')
                    ->select(
                        'invoice.*', 'trans_resi_header.resi_number', 'trans_resi_header.sender_name', 'trans_resi_header.receiver_name',
                        'trans_resi_header.payment', 'mst_route.route_code', 'customer_sender.customer_name as customer_sender_name',
                        'customer_receiver.customer_name as customer_receiver_name'
                    )
                    ->leftJoin('ar.receipt', 'invoice.invoice_id', '=', 'receipt.invoice_id')
                    ->leftJoin('op.trans_resi_header', 'invoice.resi_header_id', '=', 'trans_resi_header.resi_header_id')
                    ->leftJoin('op.mst_route', 'trans_resi_header.route_id', '=', 'mst_route.route_id')
                    ->leftJoin('op.mst_customer as customer_sender', 'trans_resi_header.customer_id', '=', 'customer_sender.customer_id')
                    ->leftJoin('op.mst_customer as customer_receiver', 'trans_resi_header.customer_receiver_id', '=', 'customer_receiver.customer_id')
                    ->orderBy('invoice.created_date', 'desc')
                    ->distinct();

        if (!empty($search)) {
            $query->where(function($query) use ($search) {
                $query->where('invoice.invoice_number', 'ilike', '%'.$search.'%')
                        ->orWhere('trans_resi_header.resi_number', 'ilike', '%'.$search.'%')
                        ->orWhere('mst_route.route_code', 'ilike', '%'.$search.'%')
                        ->orWhere('customer_sender.customer_name', 'ilike', '%'.$search.'%')
                        ->orWhere('customer_receiver.customer_name', 'ilike', '%'.$search.'%');
            });
        }

        if ($type == Receipt::DP) {
            $query->whereNull('receipt.receipt_id');
        }

        $invoices = [];
        $skip     = ($iteration - 1) * $maxData;
        foreach ($query->take($maxData)->skip($skip)->get() as $invoice) {
            $modelInvoice = Invoice::find($invoice->invoice_id);
            if ($modelInvoice->remaining() <= 0) {
                continue;
            }

            $createdDate        = !empty($modelInvoice->created_date) ? new \DateTime($modelInvoice->created_date) : null;
            $invoice->date      = $createdDate !== null ? $createdDate->format('d-m-Y') : '';
            $invoice->remaining = $modelInvoice->remaining();

            $invoices[] = $invoice;
        }

        return $invoices;
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

    public function getJsonResi(Request $request)
    {
        $search = $request->get('search');
        $query = \DB::table('op.trans_resi_header')
                    ->select(
                        'trans_resi_header.*', 'mst_route.route_code', 'customer_sender.customer_name as customer_sender_name',
                        'customer_receiver.customer_name as customer_receiver_name'
                    )
                    ->leftJoin('op.mst_route', 'trans_resi_header.route_id', '=', 'mst_route.route_id')
                    ->leftJoin('op.mst_customer as customer_sender', 'trans_resi_header.customer_id', '=', 'customer_sender.customer_id')
                    ->leftJoin('op.mst_customer as customer_receiver', 'trans_resi_header.customer_receiver_id', '=', 'customer_receiver.customer_id')
                    ->orderBy('trans_resi_header.resi_header_id', 'desc');

        if (!empty($search)) {
            $query->where(function($query) use ($search) {
                $query->where('trans_resi_header.resi_number', 'ilike', '%'.$search.'%')
                        ->orWhere('mst_route.route_code', 'ilike', '%'.$search.'%')
                        ->orWhere('customer_sender.customer_name', 'ilike', '%'.$search.'%')
                        ->orWhere('customer_receiver.customer_name', 'ilike', '%'.$search.'%');
            });
        }

        $arrayResi = [];
        foreach ($query->take(10)->get() as $resi) {
            $modelResi = TransactionResiHeader::find($resi->resi_header_id);
            $resi->customer_sender_name = !empty($resi->customer_sender_name) ? $resi->customer_sender_name : '';
            $resi->customer_receiver_name = !empty($resi->customer_receiver_name) ? $resi->customer_receiver_name : '';
            $resi->total_coly = $modelResi->totalColy();
            $resi->item_name = $modelResi->getItemAndUnitNames();

            $arrayResi[] = $resi;
        }

        return response()->json($arrayResi);
    }

    public function getJsonInvoiceAp(Request $request)
    {

        $maxData   = 10;
        $iteration = 1;
        $isFull    = false;
        $data      = [];

        while(!$isFull) {
            $dataQuery = $this->getDataQueryInvoiceAp($request, $maxData, $iteration);
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

    protected function getDataQueryInvoiceAp(Request $request, $maxData, $iteration)
    {
        $search = $request->get('search');
        $query = \DB::table('ap.invoice_header')
                    ->select('invoice_header.*', 'mst_vendor.vendor_code', 'mst_vendor.vendor_name', 'mst_driver.driver_code', 'mst_driver.driver_name')
                    ->leftJoin('ap.mst_vendor', 'invoice_header.vendor_id', '=', 'mst_vendor.vendor_id')
                    ->leftJoin('op.mst_driver', 'invoice_header.vendor_id', '=', 'mst_driver.driver_id')
                    ->where(function($query) {
                        $query->where('invoice_header.type_id', '=', InvoiceHeader::KAS_BON_EMPLOYEE)
                                ->orWhere('invoice_header.type_id', '=', InvoiceHeader::KAS_BON_DRIVER);
                    })
                    ->where('invoice_header.status', '<>', InvoiceHeader::CANCELED)
                    ->orderBy('invoice_header.header_id', 'desc');

        if (!empty($search)) {
            $query->where(function($query) use ($search) {
                $query->where('invoice_header.invoice_number', 'ilike', '%'.$search.'%')
                        ->orWhere(function($query) use ($search) {
                            $query->where('invoice_header.type_id', '=', InvoiceHeader::KAS_BON_EMPLOYEE)
                                    ->where('mst_vendor.vendor_code', 'ilike', '%'.$search.'%');
                        })
                        ->orWhere(function($query) use ($search) {
                            $query->where('invoice_header.type_id', '=', InvoiceHeader::KAS_BON_EMPLOYEE)
                                    ->where('mst_vendor.vendor_name', 'ilike', '%'.$search.'%');
                        })
                        ->orWhere(function($query) use ($search) {
                            $query->where('invoice_header.type_id', '=', InvoiceHeader::KAS_BON_DRIVER)
                                    ->where('mst_driver.driver_code', 'ilike', '%'.$search.'%');
                        })
                        ->orWhere(function($query) use ($search) {
                            $query->where('invoice_header.type_id', '=', InvoiceHeader::KAS_BON_DRIVER)
                                    ->where('mst_driver.driver_name', 'ilike', '%'.$search.'%');
                        })
                        ->orWhere('invoice_header.description', 'ilike', '%'.$search.'%');
            });
        }

        $arrayInvoice = [];
        $skip     = ($iteration - 1) * $maxData;
        foreach ($query->take($maxData)->skip($skip)->get() as $invoice) {
            $modelInvoice = InvoiceHeader::find($invoice->header_id);
            $invoice->type = $invoice->type_id == InvoiceHeader::KAS_BON_EMPLOYEE ? 'Kasbon Karyawan' : 'Kasbon Driver';
            $invoice->trading_code = $invoice->type_id == InvoiceHeader::KAS_BON_EMPLOYEE ? $invoice->vendor_code : $invoice->driver_code;
            $invoice->trading_name = $invoice->type_id == InvoiceHeader::KAS_BON_EMPLOYEE ? $invoice->vendor_name : $invoice->driver_name;
            $invoice->total_invoice = $modelInvoice->getTotalInvoice();
            $invoice->total_remaining = $modelInvoice->getTotalRemainAr();
            if ($invoice->total_remaining <= 0) {
                continue;
            }
            $arrayInvoice[] = $invoice;
        }

        return $arrayInvoice;
    }

    public function getJsonAsset(Request $request)
    {
        $search = $request->get('search');
        $query = \DB::table('ast.addition_asset')
                        ->select(
                            'addition_asset.*',
                            'po_headers.po_number',
                            'mst_item.item_code',
                            'mst_item.description as item_description',
                            'asset_category.category_name',
                            'assigment_asset.employee_name'
                        )
                        ->join('ast.asset_category', 'asset_category.asset_category_id', '=', 'addition_asset.asset_category_id')
                        ->join('ast.assigment_asset', 'assigment_asset.asset_id', '=', 'addition_asset.asset_id')
                        ->join('ast.retirement_asset', 'retirement_asset.asset_id', '=', 'addition_asset.asset_id')
                        ->join('inv.mst_item', 'addition_asset.item_id', '=', 'mst_item.item_id')
                        ->leftJoin('inv.trans_receipt_header', 'trans_receipt_header.receipt_id', '=', 'addition_asset.receipt_id')
                        ->leftJoin('po.po_headers', 'po_headers.header_id', '=', 'trans_receipt_header.po_header_id')
                        ->where('addition_asset.branch_id','=', \Session::get('currentBranch')->branch_id)
                        ->whereNotNull('retirement_asset.retirement_date')
                        ->orderBy('addition_asset.asset_number', 'asc')
                        ->take(10);

        if (!empty($search)) {
            $query->where(function($query) use($search){
                $query->where('addition_asset.asset_number', 'ilike', '%'.$search.'%')
                        ->orWhere('po_headers.po_number', 'ilike', '%'.$search.'%')
                        ->orWhere('mst_item.item_code', 'ilike', '%'.$search.'%')
                        ->orWhere('mst_item.description', 'ilike', '%'.$search.'%')
                        ->orWhere('asset_category.category_name', 'ilike', '%'.$search.'%')
                        ->orWhere('assigment_asset.employee_name', 'ilike', '%'.$search.'%');
            });
        }

        return $query->get();
    }

    public function getJsonCoa(Request $request)
    {
        $search = $request->get('search');
        $query = \DB::table('gl.mst_coa')
                    ->where('mst_coa.active', '=', 'Y')
                    ->where('mst_coa.segment_name', '=', MasterCoa::ACCOUNT)
                    ->where('mst_coa.identifier', '=', MasterCoa::REVENUE)
                    ->where(function($query) use ($search) {
                        $query->where('mst_coa.coa_code', 'ilike', '%'.$search.'%')
                                ->orWhere('mst_coa.description', 'ilike', '%'.$search.'%');
                    })
                    ->orderBy('mst_coa.coa_code', 'asc')
                    ->take(10);

        return response()->json($query->get());
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

        return 'RAR.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);

    }

    protected function getOptionReceiptMethod()
    {
        return [Receipt::CASH, Receipt::TRANSFER];
    }

    protected function getOptionType()
    {
        return [Receipt::KASBON, Receipt::ASSET_SELLING, Receipt::OTHER];
    }
}
