<?php

namespace App\Modules\Operational\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Accountreceivables\Model\Transaction\Invoice;
use App\Modules\Accountreceivables\Http\Controllers\Transaction\ReceiptController;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Transaction\TransactionResiLine;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Service\Master\CustomerService;
use App\Modules\Operational\Service\Master\RouteService;
use App\Modules\Operational\Service\Master\UnitService;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader;

class ResiAllBranchController extends Controller
{
    const RESOURCE = 'Operational\Report\ResiAllBranch';
    const URL      = 'operational/report/resi-all-branch';
    const URL_RESI = 'operational/transaction/transaction-resi';

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

        return view('operational::report.resi-all-branch.index', [
            'models'        => $query->paginate(10),
            'filters'       => $filters,
            'resource'      => self::RESOURCE,
            'url'           => self::URL,
            'urlResi'       => self::URL_RESI,
            'optionRoute'   => RouteService::getActiveRoute(),
            'optionBranch'  => MasterBranch::where('branch_code_numeric', '<>', MasterBranch::KODE_NUMERIC_HO)->orderBy('branch_name')->get(),
            'optionPayment' => [
                TransactionResiHeader::CASH,
                TransactionResiHeader::BILL_TO_SENDER,
                TransactionResiHeader::BILL_TO_RECIEVER,
            ],
            'optionStatus'  => [
                TransactionResiHeader::INCOMPLETE,
                TransactionResiHeader::INPROCESS,
                TransactionResiHeader::APPROVED,
            ]
        ]);
    }

    public function getQuery(Request $request, $filters){
        $query   = \DB::table('op.trans_resi_header')
                        ->leftJoin('op.mst_customer as customer_sender', 'trans_resi_header.customer_id', '=', 'customer_sender.customer_id')
                        ->leftJoin('op.mst_customer as customer_receiver', 'trans_resi_header.customer_receiver_id', '=', 'customer_receiver.customer_id')
                        ->orderBy('trans_resi_header.created_date', 'desc');

        if (!empty($filters['branchId'])) {
            $query->where('trans_resi_header.branch_id', '=', $filters['branchId']);
        }

        if (!empty($filters['resiNumber'])) {
            $query->where('resi_number', 'ilike', '%'.$filters['resiNumber'].'%');
        }

        if (!empty($filters['customer'])) {
            $query->where(function($query) use ($filters) {
                $query->where('customer_sender.customer_name', 'ilike', '%'.$filters['customer'].'%')
                        ->orWhere('customer_receiver.customer_name', 'ilike', '%'.$filters['customer'].'%');
            });
        }

        if (!empty($filters['sender'])) {
            $query->where('sender_name', 'ilike', '%'.$filters['sender'].'%');
        }

        if (!empty($filters['receiver'])) {
            $query->where('receiver_name', 'ilike', '%'.$filters['receiver'].'%');
        }

        if (!empty($filters['route'])) {
            $query->where('route_id', '=', $filters['route']);
        }

        if (!empty($filters['payment'])) {
            $query->where('payment', '=', $filters['payment']);
        }

        if (!empty($filters['insurance'])) {
            if ($filters['insurance'] == 'insurance') {
                $query->where('insurance', '=', true);
            } else {
                $query->where(function($query) {
                    $query->whereNull('insurance')
                            ->orWhere('insurance', '=', false);
                });
            }
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $query->where('trans_resi_header.created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $query->where('trans_resi_header.created_date', '<=', $date->format('Y-m-d 23:59:59'));
        }

        if (!empty($filters['status'])) {
            $query->where('trans_resi_header.status', '=', $filters['status']);
        }
        return $query;
    }

    public function edit(Request $request, $id)
    {
        $model = TransactionResiHeader::where('resi_header_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }
        $manifest = \DB::table('op.trans_manifest_header')
                            ->select(
                                'trans_manifest_header.manifest_number',
                                'trans_manifest_header.status',
                                'trans_manifest_header.shipment_date',
                                'trans_manifest_header.arrive_date',
                                'mst_driver.driver_name',
                                'mst_truck.police_number',
                                'trans_manifest_line.coly_sent'
                                )
                            ->join('op.trans_manifest_line', 'trans_manifest_line.manifest_header_id', '=', 'trans_manifest_header.manifest_header_id')
                            ->join('op.mst_driver', 'mst_driver.driver_id', '=', 'trans_manifest_header.driver_id')
                            ->join('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_manifest_header.truck_id')
                            ->where('trans_manifest_line.resi_header_id', '=', $id)
                            ->where('trans_manifest_header.status', '<>', ManifestHeader::DELETED)
                            ->get();

        $receiptReturnDo = \DB::table('op.trans_receipt_or_return_delivery_header')
                            ->select(
                                'trans_receipt_or_return_delivery_header.receipt_or_return_delivery_number',
                                'trans_delivery_order_header.delivery_order_number',
                                'mst_driver.driver_name',
                                'mst_truck.police_number',
                                'trans_delivery_order_header.status',
                                'trans_receipt_or_return_delivery_line.received_date',
                                'trans_receipt_or_return_delivery_line.received_by',
                                'trans_receipt_or_return_delivery_line.status',
                                'trans_receipt_or_return_delivery_line.note',
                                'trans_receipt_or_return_delivery_line.total_coly'
                                )
                            ->join('op.trans_receipt_or_return_delivery_line', 'trans_receipt_or_return_delivery_line.receipt_or_return_delivery_header_id', '=', 'trans_receipt_or_return_delivery_header.receipt_or_return_delivery_header_id')
                            ->join('op.trans_delivery_order_line', 'trans_delivery_order_line.delivery_order_line_id', '=', 'trans_receipt_or_return_delivery_line.delivery_order_line_id')
                            ->join('op.trans_delivery_order_header', 'trans_delivery_order_header.delivery_order_header_id', '=', 'trans_delivery_order_line.delivery_order_header_id')
                            ->join('op.mst_driver', 'mst_driver.driver_id', '=', 'trans_delivery_order_header.driver_id')
                            ->join('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_delivery_order_header.truck_id')
                            ->where('trans_delivery_order_line.resi_header_id', '=', $id)
                            ->get();

        $lge = \DB::table('op.trans_customer_taking_transact')
                            ->select(
                                'trans_customer_taking_transact.customer_taking_transact_number',
                                'trans_customer_taking_transact.taker_name',
                                'trans_customer_taking_transact.created_date',
                                'trans_customer_taking_transact.coly_taken'
                                )
                            ->join('op.trans_customer_taking', 'trans_customer_taking.customer_taking_id', '=', 'trans_customer_taking_transact.customer_taking_id')
                            ->where('trans_customer_taking.resi_header_id', '=', $id)
                            ->get();

        $invoice = \DB::table('ar.invoice')
                            ->select(
                                'invoice.invoice_id',
                                'invoice.invoice_number',
                                'invoice.type',
                                'invoice.bill_to',
                                'invoice.bill_to_address',
                                'invoice.bill_to_phone',
                                'invoice.amount',
                                'invoice.created_date'
                                )
                            ->where('invoice.resi_header_id', $id)
                            ->get();
        $arrInvoice = [];
        foreach ($invoice as $invoice) {
            $modelInvoice = Invoice::find($invoice->invoice_id);
            $invoice->discount_inprocess = $modelInvoice->getDiscountInprocess();
            $invoice->discount_approved  = $modelInvoice->totalDiscount();
            $invoice->total_invoice      = $modelInvoice->totalInvoice();
            $invoice->total_receipt      = $modelInvoice->totalReceipt();
            $invoice->remaining          = $modelInvoice->remaining();
            $arrInvoice[]                = $invoice;
         }

        $receipt = \DB::table('ar.receipt')
                            ->select(
                                'receipt.receipt_number',
                                'receipt.receipt_method',
                                'receipt.person_name',
                                'receipt.amount',
                                'receipt.created_date',
                                'invoice.invoice_number',
                                'invoice.type'
                                )
                            ->join('ar.invoice', 'invoice.invoice_id', '=', 'receipt.invoice_id')
                            ->where('invoice.resi_header_id', $id)
                            ->get();

        $data = [
            'title'         => trans('shared/common.edit'),
            'model'         => $model,
            'url'           => self::URL,
            'resource'      => self::RESOURCE,
            'urlResi'       => self::URL_RESI,
            'manifest'      => $manifest,
            'receiptReturnDo'=> $receiptReturnDo,
            'lge'           => $lge,
            'invoice'       => $arrInvoice,
            'receipt'       => $receipt,
            'optionCustomer'=> CustomerService::getActiveCustomer(),
            'optionRoute'   => RouteService::getActiveRoute(),
            'optionUnit'    => UnitService::getActiveRouteUnit($model->route_id),
        ];

        return view('operational::report.resi-all-branch.add', $data);
    }

    public function printExcelIndex(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters);

        \Excel::create(trans('operational/menu.resi'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.resi'));
                });

                $sheet->cells('A3:Y3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('operational/fields.resi-number'),
                    trans('operational/fields.date'),
                    trans('operational/fields.customer'),
                    trans('operational/fields.sender'),
                    trans('operational/fields.address'),
                    trans('operational/fields.customer'),
                    trans('operational/fields.receiver'),
                    trans('operational/fields.address'),
                    trans('operational/fields.route'),
                    trans('operational/fields.payment'),
                    trans('operational/fields.insurance'),
                    trans('operational/fields.item-name'),
                    trans('operational/fields.item-unit'),
                    trans('operational/fields.coly'),
                    trans('operational/fields.weight'),
                    trans('operational/fields.total-price'),
                    trans('operational/fields.volume'),
                    trans('operational/fields.total-price'),
                    trans('operational/fields.qty-unit'),
                    trans('operational/fields.total-price'),
                    trans('operational/fields.total-amount'),
                    trans('operational/fields.discount'),
                    trans('shared/common.total'),
                    trans('operational/fields.description'),
                    trans('shared/common.status'),
                ]);

                $currentRow = 4;
                foreach($query->get() as $model) {
                    $model = TransactionResiHeader::find($model->resi_header_id);
                    $resiDate = !empty($model->created_date) ? new \DateTime($model->created_date) : null;

                    $data = [
                        $model->resi_number,
                        $resiDate !== null ? $resiDate->format('d-m-Y') : '',
                        !empty($model->customer) ? $model->customer->customer_name : '',
                        $model->sender_name,
                        $model->sender_address,
                        !empty($model->customerReceiver) ? $model->customerReceiver->customer_name : '',
                        $model->receiver_name,
                        $model->receiver_address,
                        $model->route !== null ? $model->route->route_code : '',
                        $model->getSingkatanPayment(),
                        $model->insurance ? 'V' : 'X',
                        $model->itemName(),
                        $model->itemUnit(),
                        $model->totalColy(),
                        $model->totalWeight(),
                        $model->totalWeightPrice(),
                        $model->totalVolume(),
                        $model->totalVolumePrice(),
                        $model->totalUnit(),
                        $model->totalUnitPrice(),
                        $model->totalAmount(),
                        $model->discount,
                        $model->total(),
                        $model->description,
                        $model->status,
                    ];

                    $sheet->row($currentRow++, $data);
                }

                $lastDataRow = $currentRow;
                $currentRow = $lastDataRow + 1;
                if (!empty($filters['resiNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.resi-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['resiNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['customer'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.customer'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['customer'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['sender'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.sender'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['sender'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['receiver'])) { 
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.receiver'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['receiver'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['route'])) {
                    $route = \DB::table('op.mst_route')->where('route_id', '=', $filters['route'])->first();
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.route'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $route->route_code, 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['branchId'])) {
                    $branch = \DB::table('op.mst_branch')->where('branch_id', '=', $filters['branchId'])->first();
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.branch'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $branch->branch_name, 'C', $currentRow);
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
                if (!empty($filters['payment'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.payment'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['payment'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['insurance'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.insurance'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['insurance'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['status'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.status'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['payment'], 'C', $currentRow);
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
}
