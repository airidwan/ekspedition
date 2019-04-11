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
use App\Modules\Payable\Model\Master\MasterVendor;

class PurchaseOrderController extends Controller
{
    const RESOURCE = 'Payable\Report\PurchaseOrder';
    const URL      = 'payable/report/purchase-order';
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
        if (!empty($filters['supplierId'])) {
            $invoice = $this->getQuery($request, $filters);
        }
        return view('payable::report.purchase-order.index', [
            'invoices'      => !empty($invoice) ? $invoice->get() : [],
            'filters'       => $filters,
            'resource'      => self::RESOURCE,
            'url'           => self::URL,
            'optionInvoice' => $this->optionInvocePurchaseOrder(),
            'optionSupplier' => $this->getOptionsSupplier(),

        ]);
    }

    public function getQuery(Request $request, $filters){
        $invoice   = \DB::table('ap.invoice_header')
                        ->select('invoice_header.*', 'mst_vendor.vendor_name')
                        ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'invoice_header.vendor_id')
                        ->where(function($invoice) {
                               $invoice->where('invoice_header.status', '=', InvoiceHeader::APPROVED)
                                    ->orWhere('invoice_header.status', '=', InvoiceHeader::CLOSED);
                          })
                        ->where('invoice_header.type_id', '=', InvoiceHeader::PURCHASE_ORDER)
                        ->orderBy('invoice_header.created_date', 'asc');

        // if (!empty($filters['invoiceId'])) {
        //     $invoice->where('invoice_header.header_id', '=', $filters['invoiceId']);
        // }

        if (!empty($filters['supplierId'])) {
            $invoice->where('invoice_header.vendor_id', '=', $filters['supplierId']);
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $invoice->where('invoice_header.created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $invoice->where('invoice_header.created_date', '<=', $date->format('Y-m-d 23:59:59'));
        }
        return $invoice;
    }

    public function optionInvocePurchaseOrder()
    {
        $query = \DB::table('ap.invoice_header')
                    ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'invoice_header.vendor_id')
                    // ->where('invoice_header.type_id', '=', InvoiceHeader::PURCHASE_ORDER)
                    ->where('invoice_header.type_id', '=', 123123)
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

    protected function getOptionsSupplier()
    {
        return \DB::table('ap.mst_vendor')
                ->leftJoin('ap.dt_vendor_branch', 'dt_vendor_branch.vendor_id', '=', 'mst_vendor.vendor_id')
                ->where('dt_vendor_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                ->where('mst_vendor.category', '=', MasterVendor::VENDOR)
                ->orderBy('vendor_name')->where('active', '=', 'Y')->get();
    }

    public function printExcel(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = [];
        if (!empty($filters['supplierId'])) {
            $query = $this->getQuery($request, $filters)->get();
        }

        \Excel::create(trans('payable/menu.purchase-order'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('payable/menu.purchase-order'));
                });

                $sheet->cells('A3:G3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('payable/fields.invoice-number'),
                    trans('purchasing/fields.po-number'),
                    trans('shared/common.date'),
                    trans('payable/fields.total-invoice'),
                    trans('payable/fields.total-payment'),
                    trans('payable/fields.total-remain'),
                ]);
                $totalInvoice = 0;
                $totalPayment = 0;
                $totalRemain = 0;
                foreach($query as $index => $model) {
                    $date = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                    $invoice = InvoiceHeader::find($model->header_id);
                    $data = [
                        $index + 1,
                        $model->invoice_number,
                        $invoice->getPoNumber(),
                        !empty($date) ? $date->format('d-m-Y') : '',
                        $invoice->getTotalInvoice(),
                        $invoice->getTotalPayment(),
                        $invoice->getTotalRemain(),
                    ];
                    $sheet->row($index + 4, $data);
                    $totalInvoice += $invoice->getTotalInvoice();
                    $totalPayment += $invoice->getTotalPayment();
                    $totalRemain += $invoice->getTotalRemain();
                }

                $currentRow = count($query) + 4;
                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'D', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalInvoice, 'E', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalPayment, 'F', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalRemain, 'G', $currentRow);
                
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
