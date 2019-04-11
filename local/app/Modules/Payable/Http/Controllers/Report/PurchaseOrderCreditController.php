<?php

namespace App\Modules\Payable\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Payable\Model\Transaction\InvoiceHeader;
use App\Modules\Payable\Model\Transaction\Payment;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
use App\Modules\Operational\Model\Transaction\PickupFormHeader;
use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader;
use App\Modules\Operational\Service\Master\DriverService;
use App\Modules\Payable\Service\Master\VendorService;
use App\Modules\Purchasing\Model\Transaction\PurchaseOrderHeader;

class PurchaseOrderCreditController extends Controller
{
    const RESOURCE = 'Payable\Report\PurchaseOrderCredit';
    const URL      = 'payable/report/purchase-order-credit';
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
        if (!empty($filters['invoiceId'])) {
            $payment = $this->getQuery($request, $filters);
        }

        return view('payable::report.purchase-order-credit.index', [
            'payments'      => !empty($payment) ? $payment->get() : [],
            'filters'       => $filters,
            'resource'      => self::RESOURCE,
            'url'           => self::URL,
            'optionInvoice' => $this->optionInvocePoCredit(),

        ]);
    }

    public function getQuery(Request $request, $filters){
        $payment   = \DB::table('ap.payment')
                        ->select('payment.*', 'invoice_header.invoice_number', 'mst_vendor.vendor_name')
                        ->leftJoin('ap.invoice_header', 'invoice_header.header_id', '=', 'payment.invoice_header_id')
                        ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'invoice_header.vendor_id')
                        ->where('payment.status', '=', Payment::APPROVED)
                        ->where('invoice_header.type_id', '=', InvoiceHeader::PURCHASE_ORDER_CREDIT)
                        ->where('invoice_header.header_id', '=', $filters['invoiceId'])
                        ->distinct()
                        ->orderBy('payment.created_date', 'asc');

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $payment->where('payment.created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $payment->where('payment.created_date', '<=', $date->format('Y-m-d 23:59:59'));
        }
        return $payment;
    }

    public function optionInvocePoCredit()
    {
        $query = \DB::table('ap.invoice_header')
                    ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'invoice_header.vendor_id')
                    ->where('invoice_header.type_id', '=', InvoiceHeader::PURCHASE_ORDER_CREDIT)
                    ->where('invoice_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->get();

        $arrInvoice = [];
        foreach ($query as $invoice) {
            $model = InvoiceHeader::find($invoice->header_id);
            $poNumber = '';
            foreach ($model->lines as $line) {
                $po        = PurchaseOrderHeader::find($line->po_header_id);
                $poNumber .= $po->po_number.', ';
            }
            $invoice->po_number     = substr($poNumber, 0, -2).'.';
            $invoice->total_invoice = $model->getTotalInvoice();
            $arrInvoice[] = $invoice;
        }
        return $arrInvoice;
    }

    public function printExcel(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = [];
        if (!empty($filters['invoiceId'])) {
            $query = $this->getQuery($request, $filters)->get();
        }

        \Excel::create(trans('payable/menu.purchase-order-credit'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('payable/menu.purchase-order-credit'));
                });

                $sheet->cells('A3:F3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('shared/common.date'),
                    trans('payable/fields.payment-number'),
                    trans('payable/fields.total-amount'),
                    trans('payable/fields.total-interest'),
                    trans('payable/fields.payment-amount'),
                ]);
                $totalAmountPayment = 0;
                foreach($query as $index => $model) {
                    $date = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                    $data = [
                        $index + 1,
                        !empty($date) ? $date->format('d-m-Y') : '',
                        $model->payment_number,
                        $model->total_amount,
                        $model->total_interest,
                        $model->total_amount + $model->total_interest,
                    ];
                    $sheet->row($index + 4, $data);
                    $totalAmountPayment += ($model->total_amount + $model->total_interest);
                }

                $currentRow = count($query) + 4;
                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'E', $currentRow);
                $this->addValueDescriptionCell($sheet,  $totalAmountPayment, 'F', $currentRow);
                
                $currentRow = count($query) + 6;
                if (!empty($filters['invoiceNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('payable/fields.invoice-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['invoiceNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['poNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('purchasing/fields.po-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['poNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['totalInvoice'])) {
                    $this->addLabelDescriptionCell($sheet, trans('payable/fields.invoice-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['totalInvoice'], 'C', $currentRow);
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
}
