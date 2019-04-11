<?php

namespace App\Modules\Accountreceivables\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Accountreceivables\Model\Transaction\Invoice;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Model\Master\MasterRoute;
use App\Modules\Operational\Service\Master\CustomerService;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Generalledger\Service\Master\AccountCombinationService;
use App\Modules\Generalledger\Service\Master\JournalService;
use App\Modules\Generalledger\Model\Master\SettingJournal;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Role;
use App\Service\Penomoran;
use App\Service\NotificationService;
use App\Modules\Operational\Service\Transaction\HistoryResiService;

class InvoiceController extends Controller
{
    const RESOURCE = 'Accountreceivables\Transaction\Invoice';
    const URL = 'accountreceivables/transaction/invoice';

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

        return view('accountreceivables::transaction.invoice.index', [
            'models'        => $query->paginate(10),
            'filters'       => $filters,
            'resource'      => self::RESOURCE,
            'url'           => self::URL,
            'optionRoute'   => $this->getOptionsRoute(),
            'optionRegion'  => $this->getOptionsRegion(),
            'optionType'    => [
                Invoice::INV_RESI,
                Invoice::INV_PICKUP,
                Invoice::INV_DO,
                Invoice::INV_EXTRA_COST,
            ],
            'optionPayment' => [
                TransactionResiHeader::CASH,
                TransactionResiHeader::BILL_TO_SENDER,
                TransactionResiHeader::BILL_TO_RECIEVER,
            ],
            'optionStatus'  => [
                Invoice::INPROCESS,
                Invoice::APPROVED,
                Invoice::CLOSED,
                Invoice::CANCELED,
            ],
        ]);
    }

    protected function getOptionsRoute()
    {
        return \DB::table('op.mst_route')
                    ->where('mst_route.active', '=', 'Y')
                    ->orderBy('route_code', 'asc')
                    ->get();
    }

    protected function getOptionsRegion()
    {
        return \DB::table('op.mst_region')
                    ->where('active', '=', 'Y')
                    ->orderBy('region_name', 'asc')
                    ->get();
    }

    protected function getQuery(Request $request, $filters){

        $query   = \DB::table('ar.invoice')
                        ->select('invoice.*')
                        ->leftJoin('op.mst_customer', 'invoice.customer_id', '=', 'mst_customer.customer_id')
                        ->leftJoin('op.trans_resi_header', 'invoice.resi_header_id', '=', 'trans_resi_header.resi_header_id')
                        ->leftJoin('op.mst_route', 'trans_resi_header.route_id', '=', 'mst_route.route_id')
                        ->leftJoin('op.mst_city', 'mst_route.city_end_id', '=', 'mst_city.city_id')
                        ->leftJoin('op.dt_region_city', 'mst_city.city_id', '=', 'dt_region_city.city_id')
                        ->leftJoin('op.mst_region', 'dt_region_city.region_id', '=', 'mst_region.region_id')
                        ->orderBy('invoice.created_date', 'desc')
                        ->distinct();

        if (!empty($filters['invoiceNumber'])) {
            $query->where('invoice_number', 'ilike', '%'.$filters['invoiceNumber'].'%');
        }

        if (!empty($filters['resiNumber'])) {
            $query->where('trans_resi_header.resi_number', 'ilike', '%'.$filters['resiNumber'].'%');
        }

        if (!empty($filters['payment'])) {
            $query->where('trans_resi_header.payment', 'ilike', '%'.$filters['payment'].'%');
        }

        if (!empty($filters['customer'])) {
            $query->where('mst_customer.customer_name', 'ilike', '%'.$filters['customer'].'%');
        }

        if (!empty($filters['billTo'])) {
            $query->where('bill_to', 'ilike', '%'.$filters['billTo'].'%');
        }

        if (!empty($filters['route'])) {
            $query->whereRaw('mst_route.route_id IN ('. implode(', ', $filters['route']) .')');
        }

        if (!empty($filters['region'])) {
            $query->whereNotNull('mst_region.region_id')->whereRaw('mst_region.region_id IN ('. implode(', ', $filters['region']) .')');
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $query->where('invoice.created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $query->where('invoice.created_date', '<=', $date->format('Y-m-d 23:59:59'));
        }

        if (!empty($filters['type'])) {
            $query->where('invoice.type', '=', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('invoice.status', '=', $filters['status']);
        }

        return $query;
    }

    public function printExcelIndex(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters);

        \Excel::create(trans('accountreceivables/menu.invoice'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('accountreceivables/menu.invoice'));
                });

                $sheet->cells('A3:U3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('accountreceivables/fields.invoice-number'),
                    trans('shared/common.date'),
                    trans('operational/fields.resi-number'),
                    trans('operational/fields.item-name'),
                    trans('operational/fields.destination-city'),
                    trans('shared/common.type'),
                    trans('accountreceivables/fields.bill'),
                    trans('operational/fields.payment'),
                    trans('operational/fields.customer'),
                    trans('operational/fields.sender'),
                    trans('operational/fields.customer'),
                    trans('operational/fields.receiver'),
                    trans('accountreceivables/fields.total-invoice'),
                    trans('accountreceivables/fields.discount'),
                    trans('accountreceivables/fields.total'),
                    trans('accountreceivables/fields.receipt'),
                    trans('accountreceivables/fields.remaining'),
                    trans('shared/common.description'),
                    trans('shared/common.note-nego'),
                    trans('shared/common.status'),
                ]);

                $currentRow = 4;
                $num = 1;
                foreach($query->get() as $model) {
                    $model = Invoice::find($model->invoice_id);
                    $date = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                    // dd($model->resi->activeNego());

                    $data = [
                        $num++,
                        $model->invoice_number,
                        $date !== null ? $date->format('d-m-Y') : '',
                        !empty($model->resi) ? $model->resi->resi_number : '',
                        !empty($model->resi) ? $model->resi->item_name : '',
                        !empty($model->resi->route->cityEnd) ? $model->resi->route->cityEnd->city_name : '',
                        $model->type,
                        $model->is_tagihan ? 'v' : 'x',
                        !empty($model->resi) ? $model->resi->getSingkatanPayment() : '',
                        !empty($model->resi->customer) ? $model->resi->customer->customer_name : '',
                        !empty($model->resi) ? $model->resi->sender_name : '',
                        !empty($model->resi->customerReceiver) ? $model->resi->customerReceiver->customer_name : '',
                        !empty($model->resi) ? $model->resi->receiver_name : '',
                        $model->amount,
                        $model->totalDiscount(),
                        $model->totalInvoice(),
                        $model->totalReceipt(),
                        $model->remaining(),
                        $model->description,
                        $model->req_approve_note,
                        $model->status,
                    ];

                    $sheet->row($currentRow++, $data);
                }

                $lastDataRow = $currentRow;
                $currentRow = $lastDataRow + 1;
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
                if (!empty($filters['payment'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.payment'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['payment'], 'C', $currentRow);
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
                if (!empty($filters['type'])) { 
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.type'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['type'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['status'])) { 
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.status'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['status'], 'C', $currentRow);
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

    public function addInvoiceExtraCost(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new Invoice();
        $model->status = Invoice::APPROVED;
        $model->type = Invoice::INV_EXTRA_COST;

        return view('accountreceivables::transaction.invoice.add-invoice-extra-cost', [
            'title' => trans('shared/common.add'),
            'model' => $model,
            'url' => self::URL,
            'resource' => self::RESOURCE,
        ]);
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
                    ->where('trans_resi_header.status', '=', TransactionResiHeader::APPROVED)
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

    public function saveAddInvoiceExtraCost(Request $request)
    {
        $this->validate($request, [
            'resiId' => 'required',
            'coaId' => 'required',
            'amount' => 'required',
            'description' => 'required',
        ]);

        $resi          = TransactionResiHeader::find($request->get('resiId'));
        $model         = new Invoice();
        $model->status = Invoice::APPROVED;
        $model->type   = Invoice::INV_EXTRA_COST;

        if ($resi->isBillToReceiver()) {
            if (!empty($resi->customer_receiver_id)) {
                $model->customer_id = $resi->customer_receiver_id;
            }

            $model->bill_to = !empty($resi->customerReceiver) ? $resi->customerReceiver->customer_name : $resi->receiver_name;
            $model->bill_to_address = $resi->receiver_address;
            $model->bill_to_phone = $resi->receiver_phone;
        } else {
            if (!empty($resi->customer_id)) {
                    $model->customer_id = $resi->customer_id;
            }

            $model->bill_to = !empty($resi->customer) ? $resi->customer->customer_name : $resi->sender_name;
            $model->bill_to_address = $resi->sender_address;
            $model->bill_to_phone = $resi->sender_phone;
        }

        $model->branch_id        = \Session::get('currentBranch')->branch_id;
        $model->created_date     = $this->now;
        $model->created_by       = \Auth::user()->id;
        $model->invoice_number   = $this->getInvoiceNumber($model);
        $model->resi_header_id   = $resi->resi_header_id;
        $model->coa_id           = $request->get('coaId');
        $model->amount           = str_replace(',', '', $request->get('amount'));
        $model->description      = str_replace("'", "`", $request->get('description'));
        $model->current_discount = 1;

        try {
            $model->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        $error = $this->createJournalInvoiceExtraCost($model);
        if (!empty($error)) {
            return $error;
        }

        HistoryResiService::saveHistory(
            $model->resi_header_id,
            'Invoice Extra Cost',
            'Invoice Number: '.$model->invoice_number.', Amount: '.number_format($model->amount).', Description: '.$model->description
        );

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('accountreceivables/fields.invoice-extra-cost').' '.$model->invoice_number])
        );

        return redirect(self::URL);
    }

    protected function createJournalInvoiceExtraCost(Invoice $model)
    {
        $invoice       = Invoice::find($model->invoice_id);
        $resi          = $invoice->resi;
        $journalHeader = new JournalHeader();

        $journalHeader->category       = JournalHeader::INVOICE_EXTRA_COST;
        $journalHeader->period         = new \DateTime($this->now->format('Y-m-1'));
        $journalHeader->status         = JournalHeader::OPEN;
        $journalHeader->description    = 'Invoice Number: '.$invoice->invoice_number.'. Resi Number: '.$resi->resi_number.'. Note: '.$invoice->description;
        $journalHeader->branch_id      = $invoice->branch_id;
        $journalHeader->journal_date   = $this->now;
        $journalHeader->created_date   = $this->now;
        $journalHeader->created_by     = \Auth::user()->id;
        $journalHeader->journal_number = JournalService::getJournalNumber($journalHeader);

        try {
            $journalHeader->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        /** PIUTANG USAHA **/
        $settingCoa  = SettingJournal::where('setting_name', SettingJournal::PIUTANG_USAHA)->first();
        $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

        $line = new JournalLine();
        $line->journal_header_id      = $journalHeader->journal_header_id;
        $line->account_combination_id = $combination->account_combination_id;
        $line->debet                  = $invoice->amount;
        $line->credit                 = 0;
        $line->created_date           = $this->now;
        $line->created_by             = \Auth::user()->id;

        try {
            $line->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        /** KREDIT **/
        $combination = AccountCombinationService::getCombination($invoice->coa->coa_code);

        $line = new JournalLine();
        $line->journal_header_id      = $journalHeader->journal_header_id;
        $line->account_combination_id = $combination->account_combination_id;
        $line->debet                  = 0;
        $line->credit                 = $invoice->amount;
        $line->created_date           = $this->now;
        $line->created_by             = \Auth::user()->id;

        try {
            $line->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    protected function getInvoiceNumber(Invoice $model)
    {
        $branch      = MasterBranch::find($model->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('ar.invoice')
                            ->where('branch_id', '=', $model->branch_id)
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->count();

        return 'IAR.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 6);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = Invoice::where('invoice_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        $data = [
            'title' => trans('shared/common.edit'),
            'model' => $model,
            'url' => self::URL,
            'resource' => self::RESOURCE,
        ];

        if ($request->user()->can('access', [self::RESOURCE, 'update'])) {
            return view('accountreceivables::transaction.invoice.add', $data);
        } else {
            return view('accountreceivables::transaction.invoice.detail', $data);
        }
    }

    public function printPdf(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $model = Invoice::where('invoice_id', '=', $id)->first();
        if ($model === null || !($model->isApproved() || $model->isClosed()) || !$model->isInvoiceResi()) {
            abort(404);
        }

        $header = view('print.header-pdf', ['title' => trans('accountreceivables/menu.invoice')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('accountreceivables::transaction.invoice.print-pdf', [
            'model'  => $model,
        ])->render();

        \PDF::SetTitle(trans('accountreceivables/menu.invoice'));
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A5');
        \PDF::writeHTML($html);
        \PDF::Output($model->invoice_number.'.pdf');
        \PDF::reset();
    }

    public function save(Request $request)
    {
        $id    = intval($request->get('id'));
        $model = Invoice::find($id);

        $this->validate($request, [
            'billTo' => 'required',
            'billToAddress' => 'required',
            'billToPhone' => 'required',
        ]);

        if ($request->get('btn-request-approve') !== null) {
            if (empty($request->get('requestApproveNote'))) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Request Approve note is required']);
            }
        }

        $discount = 0;
        if ($model->current_discount == 1) {
            $discount = intval(str_replace(',', '', $request->get('discount1')));
        } elseif ($model->current_discount == 2) {
            $discount = intval(str_replace(',', '', $request->get('discount2')));
        } elseif ($model->current_discount == 3) {
            $discount = intval(str_replace(',', '', $request->get('discount3')));
        }

        if ($model->isApproved()) {
            $discountExceed = $discount + $model->totalDiscount() - $model->amount;
            if ($discountExceed > 0) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(
                    ['errorMessage' => 'Total Discount exceed amount '.number_format($discountExceed)]
                );
            }
        }

        if ($model->isApproved()) {

            if (!empty($request->get('customerId'))) {
                $model->customer_id = $request->get('customerId');
            }

            $model->bill_to = str_replace("'", "`", $request->get('billTo'));
            $model->bill_to_address = str_replace("'", "`", $request->get('billToAddress'));
            $model->bill_to_phone = str_replace("'", "`", $request->get('billToPhone'));
            $model->description = str_replace("'", "`", $request->get('description'));
            $model->req_approve_note = str_replace("'", "`", $request->get('requestApproveNote'));
            $model->is_tagihan = !empty($request->get('isTagihan'));
            $model->last_updated_date = $this->now;
            $model->last_updated_by = \Auth::user()->id;

            if ($model->current_discount == 1) {
                $model->discount_1 = intval(str_replace(',', '', $request->get('discount1')));
                $model->discount_persen_1 = intval(str_replace(',', '', $request->get('discountPersen1')));
            } elseif ($model->current_discount == 2) {
                $model->discount_2 = intval(str_replace(',', '', $request->get('discount2')));
                $model->discount_persen_2 = intval(str_replace(',', '', $request->get('discountPersen2')));
            } elseif ($model->current_discount == 3) {
                $model->discount_3 = intval(str_replace(',', '', $request->get('discount3')));
                $model->discount_persen_3 = intval(str_replace(',', '', $request->get('discountPersen3')));
            }

            try {
                $model->save();
            } catch (\Exception $e) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }
        }

        if ($request->get('btn-request-approve') !== null && $model->isApproved()) {
            $model->status = Invoice::INPROCESS;
            $model->last_updated_date = $this->now;
            $model->last_updated_by = \Auth::user()->id;
            $model->approved_branch_id = \Session::get('currentBranch')->branch_id;
            $model->approved = null;
            $model->approved_by = null;
            $model->approved_date = null;
            $model->approved_note = null;

            try {
                $model->save();
            } catch (\Exception $e) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            /** notifikasi request approve **/
            NotificationService::createSpesificBranchNotification(
                'Invoice AR Request for Approval',
                'Invoice AR '.$model->invoice_number.', Discount: '.number_format($discount).', Note: '.$model->req_approve_note,
                ApproveInvoiceController::URL.'/edit/'.$model->invoice_id,
                [Role::BRANCH_MANAGER],
                $model->approved_branch_id
            );

            HistoryResiService::saveHistory(
                $model->resi_header_id,
                'Request Discount Invoice',
                $model->type.' Number: '.$model->invoice_number.', Discount: '.number_format($discount).', Note: '.$model->req_approve_note
            );
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('accountreceivables/menu.invoice-resi').' '.$model->invoice_number])
        );

        return redirect(self::URL);
    }

    protected function getPersentasePendapatanUtama(TransactionResiHeader $resi)
    {
        $persentase    = [];
        $route         = $resi->route;

        if ($route->details->count() == 0) {
            $persentase[$resi->branch_id] = 100;
        } else {
            foreach ($route->details as $detail) {
                if ($detail->city_start_id == $resi->branch->city_id) {
                    $persentase[$resi->branch->branch_id] = $detail->rate_kg / $route->rate_kg * 100;
                } else {
                    $mainBranch = MasterBranch::where('city_id', '=', $detail->city_start_id)->where('main_branch', '=', true)->first();
                    $persentase[$mainBranch->branch_id] = $detail->rate_kg / $route->rate_kg * 100;
                }
            }
        }

        return $persentase;
    }

    public function cancel(Request $request)
    {
        $id    = intval($request->get('id'));
        $model = Invoice::find($id);
        if ($model === null || $model->receipts()->count() > 0 || !$model->isInvoiceResi() || !$model->isApproved()) {
            abort(404);
        }

        $model->status = Invoice::CANCELED;
        $model->last_updated_date = $this->now;
        $model->last_updated_by = \Auth::user()->id;
        $model->description = $model->description.'. Canceled reason: '.$request->get('reason');

        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $model->resi->status = TransactionResiHeader::CANCELED;
        try {
            $model->resi->save();
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $error = $this->createJournalCancelInvoice($model);
        if (!empty($error)) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        /** notifikasi cancel **/
        NotificationService::createSpesificBranchNotification(
            'Invoice AR Canceled',
            'Invoice AR '.$model->invoice_number.'. Reason: '.$request->get('reason'),
            self::URL.'/edit/'.$model->invoice_id,
            [Role::BRANCH_MANAGER],
            $model->branch_id
        );

        HistoryResiService::saveHistory(
            $model->resi_header_id,
            'Cancel Resi',
            $request->get('reason')
        );

        $request->session()->flash(
            'successMessage',
            trans('shared/common.canceled-message', ['variable' => trans('accountreceivables/menu.invoice-resi').' '.$model->invoice_number])
        );

        return redirect(self::URL);
    }

    protected function createJournalCancelInvoice(Invoice $model)
    {
        $invoice       = Invoice::find($model->invoice_id);
        $resi          = $invoice->resi;
        $journalHeader = new JournalHeader();

        $journalHeader->category       = JournalHeader::CANCEL_INVOICE_RESI;
        $journalHeader->period         = new \DateTime($this->now->format('Y-m-1'));
        $journalHeader->status         = JournalHeader::OPEN;
        $journalHeader->description    = 'Invoice Number: '.$invoice->invoice_number.'. Resi Number: '.$resi->resi_number;
        $journalHeader->branch_id      = $invoice->branch_id;
        $journalHeader->journal_date   = $this->now;
        $journalHeader->created_date   = $this->now;
        $journalHeader->created_by     = \Auth::user()->id;
        $journalHeader->journal_number = JournalService::getJournalNumber($journalHeader);

        try {
            $journalHeader->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        /** PIUTANG USAHA **/
        $settingCoa  = SettingJournal::where('setting_name', SettingJournal::PIUTANG_USAHA)->first();
        $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

        $line = new JournalLine();
        $line->journal_header_id      = $journalHeader->journal_header_id;
        $line->account_combination_id = $combination->account_combination_id;
        $line->debet                  = 0;
        $line->credit                 = $resi->total();
        $line->created_date           = $this->now;
        $line->created_by             = \Auth::user()->id;

        try {
            $line->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        /** DISKON **/
        if (!empty($resi->discount)) {
            $settingCoa  = SettingJournal::where('setting_name', SettingJournal::DISKON)->first();
            $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

            $line = new JournalLine();
            $line->journal_header_id      = $journalHeader->journal_header_id;
            $line->account_combination_id = $combination->account_combination_id;
            $line->debet                  = 0;
            $line->credit                 = $resi->discount;
            $line->created_date           = $this->now;
            $line->created_by             = \Auth::user()->id;

            try {
                $line->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }

        /** PENDAPATAN UTAMA **/
        $persentase      = $this->getPersentasePendapatanUtama($resi);
        $totalPendapatan = 0;
        foreach ($persentase as $branchId => $persen) {
            $settingCoa       = SettingJournal::where('setting_name', SettingJournal::PENDAPATAN_UTAMA)->first();
            $combination      = AccountCombinationService::getCombination($settingCoa->coa->coa_code, null, $branchId);
            $pendapatan       = floor($persen / 100 * $resi->totalAmountAsli());
            $totalPendapatan += $pendapatan;

            $line = new JournalLine();
            $line->journal_header_id      = $journalHeader->journal_header_id;
            $line->account_combination_id = $combination->account_combination_id;
            $line->debet                  = $pendapatan;
            $line->credit                 = 0;
            $line->created_date           = $this->now;
            $line->created_by             = \Auth::user()->id;

            try {
                $line->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }

        /** PEMBULATAN **/
        $pembulatan = $totalPendapatan - $resi->totalAmount();
        if ($pembulatan != 0) {
            $settingCoa  = SettingJournal::where('setting_name', SettingJournal::PEMBULATAN)->first();
            $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

            $line = new JournalLine();
            $line->journal_header_id      = $journalHeader->journal_header_id;
            $line->account_combination_id = $combination->account_combination_id;
            $line->debet                  = 0;
            $line->credit                 = $pembulatan;
            $line->created_date           = $this->now;
            $line->created_by             = \Auth::user()->id;

            try {
                $line->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }

        if ($invoice->totalDiscount() > 0) {
            /** DISKON **/
            $settingCoa  = SettingJournal::where('setting_name', SettingJournal::DISKON)->first();
            $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

            $line = new JournalLine();
            $line->journal_header_id      = $journalHeader->journal_header_id;
            $line->account_combination_id = $combination->account_combination_id;
            $line->debet                  = 0;
            $line->credit                 = $invoice->totalDiscount();
            $line->created_date           = $this->now;
            $line->created_by             = \Auth::user()->id;

            try {
                $line->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }

            /** PIUTANG USAHA **/
            $settingCoa  = SettingJournal::where('setting_name', SettingJournal::PIUTANG_USAHA)->first();
            $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

            $line = new JournalLine();
            $line->journal_header_id      = $journalHeader->journal_header_id;
            $line->account_combination_id = $combination->account_combination_id;
            $line->debet                  = $invoice->totalDiscount();
            $line->credit                 = 0;
            $line->created_date           = $this->now;
            $line->created_by             = \Auth::user()->id;

            try {
                $line->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
    }

    public function getJsonCustomer(Request $request)
    {
        $search = $request->get('search');
        $query  = \DB::table('op.mst_customer')
                    ->select('mst_customer.customer_id', 'mst_customer.customer_name', 'mst_customer.address', 'mst_customer.phone_number')
                    ->where('mst_customer.active', '=', 'Y')
                    ->orderBy('mst_customer.customer_name', 'asc')
                    ->where('mst_customer.customer_name', 'ilike', '%'.$search.'%')
                    ->take(10);

        return response()->json($query->get());
    }
}
