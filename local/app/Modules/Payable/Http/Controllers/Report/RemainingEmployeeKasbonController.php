<?php

namespace App\Modules\Payable\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Payable\Model\Transaction\InvoiceHeader;
use App\Modules\Payable\Model\Transaction\Payment;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
use App\Modules\Payable\Service\Master\VendorService;

class RemainingEmployeeKasbonController extends Controller
{
    const RESOURCE = 'Payable\Report\RemainingEmployeeKasbon';
    const URL      = 'payable/report/remaining-employee-kasbon';
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

        return view('payable::report.remaining-employee-kasbon.index', [
            'kasbons'   => !empty($query) ? $query : [],
            'filters'   => $filters,
            'resource'  => self::RESOURCE,
            'url'       => self::URL,
            'optionEmployee' => VendorService::getQueryVendorEmployee()->get(),
        ]);
    }

    public function getQuery(Request $request, $filters){
        $kasbonArr = [];
        if (!empty($filters['vendorId'])) {
            $kasbon   = \DB::table('ap.invoice_header')
                            ->select('invoice_header.*')
                            ->leftJoin('ap.payment', 'payment.invoice_header_id', '=', 'invoice_header.header_id')
                            ->where('invoice_header.vendor_id', '=', $filters['vendorId'])
                            ->where('invoice_header.type_id', '=', InvoiceHeader::KAS_BON_EMPLOYEE)
                            ->where(function($query){
                                $query->where('invoice_header.status', '=', InvoiceHeader::APPROVED)
                                      ->orWhere('invoice_header.status', '=', InvoiceHeader::CLOSED);
                                
                            })
                            ->distinct()
                            ->orderBy('invoice_header.created_date', 'asc');

            if (!empty($filters['dateFrom'])) {
                $date = new \DateTime($filters['dateFrom']);
                $kasbon->where('payment.created_date', '>=', $date->format('Y-m-d 00:00:00'));
            }

            if (!empty($filters['dateTo'])) {
                $date = new \DateTime($filters['dateTo']);
                $kasbon->where('payment.created_date', '<=', $date->format('Y-m-d 23:59:59'));
            }

            foreach ($kasbon->get() as $kasbon) {
                $modelKasbon = InvoiceHeader::find($kasbon->header_id);
                $kasbon->total_remain = $modelKasbon->getTotalRemainAr();
                $kasbon->invoice_amount = $modelKasbon->getTotalAmount();
                $kasbon->payment_amount = $modelKasbon->getTotalPayment();
                $kasbon->receipt_amount = $modelKasbon->getTotalPaymentAr();
                if ($kasbon->total_remain <= 0) {
                    continue;
                }
                $kasbonArr [] = $kasbon;
            }
        }
        return $kasbonArr;
    }

    public function printExcel(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters);

        \Excel::create(trans('payable/menu.remaining-employee-kasbon'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('payable/menu.remaining-employee-kasbon'));
                });

                $sheet->cells('A3:G3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('payable/fields.kas-bon'),
                    trans('shared/common.date'),
                    trans('payable/fields.invoice-amount'),
                    trans('payable/fields.payment-amount'),
                    trans('payable/fields.receipt-amount'),
                    trans('payable/fields.remaining'),
                ]);
                $totalAmountKasbon = 0;
                foreach($query as $index => $model) {
                    $date = !empty($model->approved_date) ? new \DateTime($model->approved_date) : null;
                    $data = [
                        $index + 1,
                        $model->invoice_number,
                        !empty($date) ? $date->format('d-m-Y') : '',
                        $model->invoice_amount,
                        $model->payment_amount,
                        $model->receipt_amount,
                        $model->total_remain,
                    ];
                    $sheet->row($index + 4, $data);
                    $totalAmountKasbon += $model->total_remain;
                }

                $currentRow = count($query) + 4;
                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'F', $currentRow);
                $this->addValueDescriptionCell($sheet,  $totalAmountKasbon, 'G', $currentRow);
                
                $currentRow = count($query) + 6;
                if (!empty($filters['vendorName'])) {
                    $this->addLabelDescriptionCell($sheet, trans('payable/fields.vendor-name'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['vendorName'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['vendorCode'])) {
                    $this->addLabelDescriptionCell($sheet, trans('inventory/fields.vendor-code'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['vendorCode'], 'C', $currentRow);
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
