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
use App\Service\Terbilang;

class KasbonHistoryController extends Controller
{
    const RESOURCE = 'Payable\Report\KasbonHistory';
    const URL      = 'payable/report/kasbon-history';
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
        if (!empty($filters['vendorId']) || !empty($filters['driverId'])) {
            $kasbonPayment = $this->getQueryKasbonPayment($request, $filters);            
            $kasbonReceipt = $this->getQueryKasbonReceipt($request, $filters);            
        }

        return view('payable::report.kasbon-history.index', [
            'kasbonPayments' => !empty($kasbonPayment) ? $kasbonPayment->get() : [],
            'kasbonReceipts' => !empty($kasbonReceipt) ? $kasbonReceipt->get() : [],
            'filters'        => $filters,
            'resource'       => self::RESOURCE,
            'url'            => self::URL,
            'optionDriver'   => DriverService::getActiveDriverAsistant(),
            'optionVendor'   => VendorService::getQueryVendorEmployee(),
            'optionType'     => $this->optionType(),

        ]);
    }

    public function printExcel(Request $request)
    {
        $filters = \Session::get('filters');
        $queryPayment = [];
        $queryReceipt = [];
        if (!empty($filters['vendorId']) || !empty($filters['driverId'])) {
            $queryPayment = $this->getQueryKasbonPayment($request, $filters)->get();            
            $queryReceipt = $this->getQueryKasbonReceipt($request, $filters)->get();
        }

        \Excel::create(trans('payable/menu.kasbon-history'), function($excel) use ($queryPayment, $queryReceipt, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($queryPayment, $queryReceipt, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('payable/menu.kasbon-history'));
                });

                // Payment
                $sheet->cells('A3:E3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('shared/common.date'),
                    trans('payable/fields.payment-number'),
                    trans('payable/fields.invoice-number'),
                    trans('payable/fields.payment-amount'),
                ]);
                $totalAmountPayment = 0;
                foreach($queryPayment as $index => $model) {
                    $date = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                    $totalAmountPayment += $model->total_amount; 
                    $data = [
                        $index + 1,
                        !empty($date) ? $date->format('d-m-Y') : '',
                        $model->payment_number,
                        $model->invoice_number,
                        $model->total_amount,
                    ];
                    $sheet->row($index + 4, $data);
                }

                $currentRow = count($queryPayment) + 4;
                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'D', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalAmountPayment, 'E', $currentRow);

                // Receipt
                $sheet->cells('G3:M3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $this->addLabelDescriptionCell($sheet, trans('shared/common.num'), 'G', 3);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.date'), 'H', 3);
                $this->addLabelDescriptionCell($sheet, trans('accountreceivables/fields.receipt-number'), 'I', 3);
                $this->addLabelDescriptionCell($sheet, trans('payable/fields.invoice-number'), 'J', 3);
                $this->addLabelDescriptionCell($sheet, trans('payable/fields.receipt-amount'), 'K', 3);

                $totalAmountReceipt = 0;
                foreach($queryReceipt as $index => $model) {
                    $date = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                    $totalAmountReceipt += $model->amount;
                    $this->addValueDescriptionCell($sheet, $index + 1, 'G', $index + 4);
                    $this->addValueDescriptionCell($sheet, $this->now->format('d-m-Y'), 'H', $index + 4);
                    $this->addValueDescriptionCell($sheet, $model->receipt_number, 'I', $index + 4);
                    $this->addValueDescriptionCell($sheet, $model->invoice_number, 'J', $index + 4);
                    $this->addValueDescriptionCell($sheet, $model->amount, 'K', $index + 4);
                }

                $currentRow = count($queryReceipt) + 4;
                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'J', $currentRow);
                $this->addLabelDescriptionCell($sheet,  $totalAmountReceipt, 'K', $currentRow);

                $max = count($queryReceipt) > count($queryPayment) ? count($queryReceipt) : count($queryPayment);

                $currentRow = $max + 6;
                $remain     = $totalAmountReceipt - $totalAmountPayment;
                $min        = $remain < 0 ? '(Min) ' : '';
                $terbilang  = $min.trim(ucwords(Terbilang::rupiah(abs($remain))));
                $this->addLabelDescriptionCell($sheet, trans('payable/fields.total-remain'), 'B', $currentRow);
                $this->addLabelDescriptionCell($sheet, $remain, 'C', $currentRow);
                $this->addLabelDescriptionCell($sheet, $terbilang, 'C', $currentRow+1);


                $currentRow += 3;
                $tempRow     = $currentRow;
                if (!empty($filters['driverName'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.driver-name'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['driverName'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['vendorName'])) {
                    $this->addLabelDescriptionCell($sheet, trans('payable/fields.vendor-name'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['vendorName'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['address'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.address'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['address'], 'C', $currentRow);
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

                $this->addLabelDescriptionCell($sheet, trans('shared/common.date'), 'E', $tempRow);
                $this->addValueDescriptionCell($sheet, $this->now->format('d-m-Y'), 'F', $tempRow);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.user'), 'E', $tempRow + 1);
                $this->addValueDescriptionCell($sheet, \Auth::user()->full_name, 'F', $tempRow + 1);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.branch'), 'E', $tempRow + 2);
                $this->addValueDescriptionCell($sheet, \Session::get('currentBranch')->branch_name, 'F', $tempRow + 2);

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

    public function getQueryKasbonPayment(Request $request, $filters){
        $kasbonPayment   = \DB::table('ap.payment')
                            ->select('payment.*', 'invoice_header.invoice_number')
                            ->leftJoin('ap.invoice_header', 'invoice_header.header_id', '=', 'payment.invoice_header_id')
                            ->where('payment.status', '=', Payment::APPROVED)
                            ->distinct()
                            ->orderBy('payment.created_date', 'asc');

        if ($filters['type'] == InvoiceHeader::KAS_BON_DRIVER) {
            $kasbonPayment->where('invoice_header.type_id', '=', InvoiceHeader::KAS_BON_DRIVER)
                   ->where('invoice_header.vendor_id', '=', $filters['driverId']);
        }else{
            $kasbonPayment->where('invoice_header.type_id', '=', InvoiceHeader::KAS_BON_EMPLOYEE)
                   ->where('invoice_header.vendor_id', '=', $filters['vendorId']);
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $kasbonPayment->where('payment.created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $kasbonPayment->where('payment.created_date', '<=', $date->format('Y-m-d 23:59:59'));
        }
        return $kasbonPayment;
    }

    public function getQueryKasbonReceipt(Request $request, $filters){
        $kasbonReceipt   = \DB::table('ar.receipt')
                            ->select('receipt.*', 'invoice_header.invoice_number')
                            ->leftJoin('ap.invoice_header', 'invoice_header.header_id', '=', 'receipt.invoice_ap_header_id')
                            ->distinct()
                            ->orderBy('receipt.created_date', 'asc');

            if ($filters['type'] == InvoiceHeader::KAS_BON_DRIVER) {
                $kasbonReceipt->where('invoice_header.type_id', '=', InvoiceHeader::KAS_BON_DRIVER)
                       ->where('invoice_header.vendor_id', '=', $filters['driverId']);
            }else{
                $kasbonReceipt->where('invoice_header.type_id', '=', InvoiceHeader::KAS_BON_EMPLOYEE)
                       ->where('invoice_header.vendor_id', '=', $filters['vendorId']);
            }

            if (!empty($filters['dateFrom'])) {
                $date = new \DateTime($filters['dateFrom']);
                $kasbonReceipt->where('receipt.created_date', '>=', $date->format('Y-m-d 00:00:00'));
            }

            if (!empty($filters['dateTo'])) {
                $date = new \DateTime($filters['dateTo']);
                $kasbonReceipt->where('receipt.created_date', '<=', $date->format('Y-m-d 23:59:59'));
            }
            return $kasbonReceipt;
    }

    public function optionType()
    {
        return \DB::table('ap.mst_ap_type')
                    ->where(function ($query) {
                            $query->where('mst_ap_type.type_id', '=', InvoiceHeader::KAS_BON_EMPLOYEE)
                                  ->orWhere('mst_ap_type.type_id', '=', InvoiceHeader::KAS_BON_DRIVER);
                        })
                    ->get();
    }
}
