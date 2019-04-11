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
use App\Service\Terbilang;

class RemainingDriverKasbonController extends Controller
{
    const RESOURCE = 'Payable\Report\RemainingDriverKasbon';
    const URL      = 'payable/report/remaining-driver-kasbon';
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
        if (!empty($filters['driverId'])) {
            $queryManifest = $this->getQueryManifest($request, $filters);            
            $queryPickup   = $this->getQueryPickup($request, $filters);            
            $queryDo       = $this->getQueryDo($request, $filters);            
            $queryKasbon   = $this->getQueryKasbon($request, $filters);            
        }

        return view('payable::report.remaining-driver-kasbon.index', [
            'manifests' => !empty($queryManifest) ? $queryManifest : [],
            'pickups'   => !empty($queryPickup) ? $queryPickup : [],
            'dos'       => !empty($queryDo) ? $queryDo : [],
            'kasbons'   => !empty($queryKasbon) ? $queryKasbon : [],
            'filters'   => $filters,
            'resource'  => self::RESOURCE,
            'url'       => self::URL,
            'optionDriver' => DriverService::getActiveDriverAsistant(),
        ]);
    }

    public function getQueryManifest(Request $request, $filters){
        $manifest   = \DB::table('op.trans_manifest_header')
                        ->select('trans_manifest_header.*')
                        ->leftJoin('op.mst_driver as driver', 'driver.driver_id', '=', 'trans_manifest_header.driver_id')
                        ->leftJoin('op.mst_driver as assistant', 'assistant.driver_id', '=', 'trans_manifest_header.driver_assistant_id')
                        ->where(function($query) use ($filters){
                            $query->where('driver.driver_id', '=', $filters['driverId'])
                                    ->orWhere('assistant.driver_id', '=', $filters['driverId']);
                        })
                        ->where(function($query){
                            $query->where('trans_manifest_header.status', '=', ManifestHeader::ARRIVED)
                                    ->orWhere('trans_manifest_header.status', '=', ManifestHeader::CLOSED);
                        })
                        ->orderBy('trans_manifest_header.created_date', 'asc');

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $manifest->where('trans_manifest_header.created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $manifest->where('trans_manifest_header.created_date', '<=', $date->format('Y-m-d 23:59:59'));
        }

        $manifestArr = [];
        foreach ($manifest->get() as $manifest) {
            $modelManifest = ManifestHeader::find($manifest->manifest_header_id);
            if ($filters['driverId'] == $manifest->driver_id) {
                $manifest->total_remain = $modelManifest->getRemainingSalaryDriver($filters['driverId']);
            }else{
                $manifest->total_remain = $modelManifest->getRemainingSalaryAssistant($filters['driverId']);
            }
            if ($manifest->total_remain <= 0) {
                continue;
            }
            $manifestArr [] = $manifest;
        }
        return $manifestArr;
    }

    public function getQueryPickup(Request $request, $filters){
        $pickup   = \DB::table('op.trans_pickup_form_header')
                        ->select('trans_pickup_form_header.*')
                        ->leftJoin('op.mst_driver as driver', 'driver.driver_id', '=', 'trans_pickup_form_header.driver_id')
                        ->where('driver.driver_id', '=', $filters['driverId'])
                        ->where('trans_pickup_form_header.status', '=', PickupFormHeader::CLOSED)
                        ->orderBy('trans_pickup_form_header.created_date', 'asc');

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $pickup->where('trans_pickup_form_header.created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $pickup->where('trans_pickup_form_header.created_date', '<=', $date->format('Y-m-d 23:59:59'));
        }

        $pickupArr = [];
        foreach ($pickup->get() as $pickup) {
            $modelPickup = PickupFormHeader::find($pickup->pickup_form_header_id);
            $pickup->total_remain = $modelPickup->getRemainingSalaryDriver($filters['driverId']);
            if ($pickup->total_remain <= 0) {
                continue;
            }
            $pickupArr [] = $pickup;
        }
        return $pickupArr;
    }

    public function getQueryDo(Request $request, $filters){
        $do   = \DB::table('op.trans_delivery_order_header')
                        ->select('trans_delivery_order_header.*')
                        ->leftJoin('op.mst_driver as driver', 'driver.driver_id', '=', 'trans_delivery_order_header.driver_id')
                        ->leftJoin('op.mst_driver as assistant', 'assistant.driver_id', '=', 'trans_delivery_order_header.assistant_id')
                        ->where(function($query) use ($filters){
                            $query->where('driver.driver_id', '=', $filters['driverId'])
                                    ->orWhere('assistant.driver_id', '=', $filters['driverId']);
                        })
                        ->where('trans_delivery_order_header.status', '=', DeliveryOrderHeader::CLOSED)
                        ->orderBy('trans_delivery_order_header.created_date', 'asc');

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $do->where('trans_delivery_order_header.created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $do->where('trans_delivery_order_header.created_date', '<=', $date->format('Y-m-d 23:59:59'));
        }

        $doArr = [];
        foreach ($do->get() as $do) {
            $modelDo = DeliveryOrderHeader::find($do->delivery_order_header_id);
            if ($filters['driverId'] == $do->driver_id) {
                $do->total_remain = $modelDo->getRemainingSalaryDriver($filters['driverId']);
            }else{
                $do->total_remain = $modelDo->getRemainingSalaryAssistant($filters['driverId']);
            }
            if ($do->total_remain <= 0) {
                continue;
            }
            $doArr [] = $do;
        }
        return $doArr;
    }

    public function getQueryKasbon(Request $request, $filters){
        $kasbon   = \DB::table('ap.invoice_header')
                        ->select('invoice_header.*')
                        ->leftJoin('ap.payment', 'payment.invoice_header_id', '=', 'invoice_header.header_id')
                        ->where('invoice_header.vendor_id', '=', $filters['driverId'])
                        ->where('invoice_header.type_id', '=', InvoiceHeader::KAS_BON_DRIVER)
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

        $kasbonArr = [];
        foreach ($kasbon->get() as $kasbon) {
            $modelKasbon = InvoiceHeader::find($kasbon->header_id);
            $kasbon->total_remain   = $modelKasbon->getTotalRemainAr();
            $kasbon->invoice_amount = $modelKasbon->getTotalAmount();
            $kasbon->payment_amount = $modelKasbon->getTotalPayment();
            $kasbon->receipt_amount = $modelKasbon->getTotalPaymentAr();
            if ($kasbon->total_remain <= 0) {
                continue;
            }
            $kasbonArr [] = $kasbon;
        }
        return $kasbonArr;
    }

    public function printExcel(Request $request)
    {
        $filters = \Session::get('filters');

        $queryManifest = $this->getQueryManifest($request, $filters);            
        $queryPickup   = $this->getQueryPickup($request, $filters);            
        $queryDo       = $this->getQueryDo($request, $filters);            
        $queryKasbon   = $this->getQueryKasbon($request, $filters);

        \Excel::create(trans('payable/menu.remaining-employee-kasbon'), function($excel) use ($queryManifest, $queryPickup, $queryDo, $queryKasbon, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($queryManifest, $queryPickup, $queryDo, $queryKasbon, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('payable/menu.remaining-employee-kasbon'));
                });

                // Manifest
                $sheet->cells('A3:E3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('operational/fields.manifest-number'),
                    trans('shared/common.date'),
                    trans('payable/fields.amount'),
                    trans('payable/fields.remaining'),
                ]);
                $totalAmountManifest = 0;
                foreach($queryManifest as $index => $model) {
                    $date = !empty($model->shipment_date) ? new \DateTime($model->shipment_date) : null;
                    if ($model->driver_id == $filters['driverId']) {
                        $manifestAmount = $model->driver_salary;
                    }else{
                        $manifestAmount = $model->driver_assistant_salary;
                    }
                    if (empty($manifestAmount)) {
                        continue;
                    }else{
                        $totalAmountManifest += $model->total_remain;
                    }
                    $data = [
                        $index + 1,
                        $model->manifest_number,
                        !empty($date) ? $date->format('d-m-Y') : '',
                        $manifestAmount,
                        $model->total_remain,
                    ];
                    $sheet->row($index + 4, $data);
                }

                $currentRow = count($queryManifest) + 4;
                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'D', $currentRow);
                $this->addValueDescriptionCell($sheet,  $totalAmountManifest, 'E', $currentRow);

                // Pickup
                $currentRow += 2;
                $sheet->cells('A'.$currentRow.':E'.$currentRow, function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row($currentRow, [
                    trans('shared/common.num'),
                    trans('operational/fields.pickup-number'),
                    trans('shared/common.date'),
                    trans('payable/fields.amount'),
                    trans('payable/fields.remaining'),
                ]);

                $currentRow++;
                $totalAmountPickup = 0;
                foreach($queryPickup as $index => $model) {
                    $date = !empty($model->pickup_time) ? new \DateTime($model->pickup_time) : null;
                    if ($model->driver_id == $filters['driverId']) {
                        $pickupAmount = $model->driver_salary;
                    }else{
                        $pickupAmount = $model->driver_assistant_salary;
                    }
                    if (empty($pickupAmount)) {
                        continue;
                    }else{
                        $totalAmountPickup += $model->total_remain;
                    }
                    $data = [
                        $index + 1,
                        $model->pickup_form_number,
                        !empty($date) ? $date->format('d-m-Y') : '',
                        $pickupAmount,
                        $model->total_remain,
                    ];
                    $sheet->row($index + $currentRow, $data);
                }
                $currentRow = count($queryPickup) + $currentRow;
                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'D', $currentRow);
                $this->addValueDescriptionCell($sheet,  $totalAmountPickup, 'E', $currentRow);

                // Pickup
                $currentRow += 2;
                $sheet->cells('A'.$currentRow.':E'.$currentRow, function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row($currentRow, [
                    trans('shared/common.num'),
                    trans('operational/fields.do-number'),
                    trans('shared/common.date'),
                    trans('payable/fields.amount'),
                    trans('payable/fields.remaining'),
                ]);

                $currentRow++;
                $totalAmountDo = 0;
                foreach($queryDo as $index => $model) {
                    $date = !empty($model->delivery_start_time) ? new \DateTime($model->delivery_start_time) : null;
                    if ($model->driver_id == $filters['driverId']) {
                        $doAmount = $model->driver_salary;
                    }else{
                        $doAmount = $model->driver_assistant_salary;
                    }
                    if (empty($doAmount)) {
                        continue;
                    }else{
                        $totalAmountDo += $model->total_remain;
                    }
                    $data = [
                        $index + 1,
                        $model->delivery_order_number,
                        !empty($date) ? $date->format('d-m-Y') : '',
                        $doAmount,
                        $model->total_remain,
                    ];
                    $sheet->row($index + $currentRow, $data);
                }
                $currentRow = count($queryDo) + $currentRow;
                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'D', $currentRow);
                $this->addValueDescriptionCell($sheet,  $totalAmountDo, 'E', $currentRow);

                // Kasbon

                $sheet->cells('G3:M3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $this->addLabelDescriptionCell($sheet, trans('shared/common.num'), 'G', 3);
                $this->addLabelDescriptionCell($sheet, trans('payable/fields.kas-bon'), 'H', 3);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.date'), 'I', 3);
                $this->addLabelDescriptionCell($sheet, trans('payable/fields.invoice-amount'), 'J', 3);
                $this->addLabelDescriptionCell($sheet, trans('payable/fields.payment-amount'), 'K', 3);
                $this->addLabelDescriptionCell($sheet, trans('payable/fields.receipt-amount'), 'L', 3);
                $this->addLabelDescriptionCell($sheet, trans('payable/fields.remaining'), 'M', 3);

                $totalAmountKasbon = 0;
                foreach($queryKasbon as $index => $model) {
                    $date = !empty($model->approved_date) ? new \DateTime($model->approved_date) : null;
                    $totalAmountKasbon += $model->total_remain;
                    $this->addValueDescriptionCell($sheet, $index + 1, 'G', $index + 4);
                    $this->addValueDescriptionCell($sheet, $model->invoice_number, 'H', $index + 4);
                    $this->addValueDescriptionCell($sheet, $this->now->format('d-m-Y'), 'I', $index + 4);
                    $this->addValueDescriptionCell($sheet, $model->invoice_amount, 'J', $index + 4);
                    $this->addValueDescriptionCell($sheet, $model->payment_amount, 'K', $index + 4);
                    $this->addValueDescriptionCell($sheet, $model->receipt_amount, 'L', $index + 4);
                    $this->addValueDescriptionCell($sheet, $model->total_remain, 'M', $index + 4);
                }

                $currentRow = count($queryKasbon) + 4;
                $this->addLabelDescriptionCell($sheet, trans('shared/common.total'), 'L', $currentRow);
                $this->addValueDescriptionCell($sheet,  $totalAmountKasbon, 'M', $currentRow);

                $countManifestDkk = count($queryManifest) + count($queryPickup) + count($queryDo) + 8;
                $max = count($queryKasbon) > $countManifestDkk ? count($queryKasbon) : $countManifestDkk;

                $currentRow = $max + 4;

                $remain    = $totalAmountManifest + $totalAmountPickup + $totalAmountDo - $totalAmountKasbon;
                $terbilang = $remain < 0 ? '(Min)' : '' .trim(ucwords(Terbilang::rupiah(abs($remain))));
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
                if (!empty($filters['driverCode'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.driver-code'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['driverCode'], 'C', $currentRow);
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
}
