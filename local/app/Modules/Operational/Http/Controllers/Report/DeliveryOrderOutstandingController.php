<?php

namespace App\Modules\Operational\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader;
use App\Modules\Operational\Model\Transaction\DeliveryOrderLine;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Http\Controllers\Transaction\CostDeliveryOrderController;
use App\Modules\Operational\Model\Transaction\ResiStock;
use App\Modules\Operational\Service\Master\DriverService;
use App\Modules\Operational\Service\Master\TruckService;
use App\Modules\Payable\Service\Master\VendorService;
use App\Modules\Operational\Service\Transaction\PickupService;
use App\Modules\Marketing\Model\Transaction\PickupRequest;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Service\Penomoran;
use App\Notification;
use App\Service\NotificationService;
use App\Role;
use App\Service\TimezoneDateConverter;

class DeliveryOrderOutstandingController extends Controller
{
    const RESOURCE = 'Operational\Report\DeliveryOrderOutstanding';
    const URL      = 'operational/report/delivery-order-outstanding';
    protected $now;

    public function __construct()
    {
        $this->now = new \DateTime();
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        if ($request->isMethod('post')) {
            $request->session()->put('filters', $request->all());
        } elseif (empty($request->get('page'))) {
            $request->session()->forget('filters');
        }

        $filters = $request->session()->get('filters');
        $query   = $this->getQuery($request, $filters);

        return view('operational::report.delivery-order-outstanding.index', [
            'models'       => $query->paginate(10),
            'filters'      => $filters,
            'optionStatus' => $this->getOptionsStatus(),
            'optionType'   => $this->getOptionsType(),
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = DeliveryOrderHeader::where('delivery_order_header_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        return view('operational::report.delivery-order-outstanding.add', [
            'title'        => trans('shared/common.edit'),
            'model'        => $model,
            'optionResi'   => [],
            'optionStatus' => $this->getOptionsStatus(),
            'optionType'   => $this->getOptionsType(),
            'optionDriver' => [],
            'optionTruck'  => [],
            'optionPartner'=> [],
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
        ]);
    }

    public function printExcelIndex(Request $request)
    {
        $filters = \Session::get('filters');
        $filters['jenis'] = 'headers';
        $query   = $this->getQuery($request, $filters);

        \Excel::create(trans('operational/menu.delivery-order-outstanding'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.delivery-order-outstanding'));
                });

                $sheet->cells('A3:K3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('operational/fields.do-number'),
                    trans('operational/fields.driver'),
                    trans('operational/fields.driver-assistant'),
                    trans('operational/fields.police-number'),
                    trans('shared/common.type'),
                    trans('operational/fields.partner-name'),
                    trans('shared/common.start-time'),
                    trans('shared/common.end-time'),
                    trans('shared/common.date'),
                    trans('shared/common.status'),
                ]);

                $currentRow = 4;
                foreach($query->get() as $index => $model) {
                    $startTime  = !empty($model->delivery_start_time) ? new \DateTime($model->delivery_start_time) : null;
                    $endTime    = !empty($model->delivery_end_time) ? new \DateTime($model->delivery_end_time) : null;

                    $data = [
                        $index+1,
                        $model->delivery_order_number,
                        $model->driver_name,
                        $model->assistant_name,
                        $model->police_number,
                        $model->type,
                        $model->vendor_name,
                        !empty($startTime) ? $startTime->format('H:i') : '',
                        !empty($endTime) ? $endTime->format('H:i') : '',
                        !empty($startTime) ? $startTime->format('d-M-Y') : '',
                        $model->status,
                    ];

                    $sheet->row($currentRow++, $data);
                }

                $lastDataRow = $currentRow;
                $currentRow = $lastDataRow + 1;
                if (!empty($filters['deliveryOrderNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.do-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['deliveryOrderNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['driver'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.driver'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['driver'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['policeNumber'])) { 
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.police-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['policeNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['resiNumber'])) { 
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.resi-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['resiNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['receiverName'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.receiver-name'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['receiverName'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['partnerName'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.partner-name'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['partnerName'], 'C', $currentRow);
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

    protected function getQuery(Request $request, $filters){
        $sqlReceiptOrReturn = 'SELECT delivery_order_line_id FROM op.trans_receipt_or_return_delivery_line';

        if (empty($filters['jenis']) || $filters['jenis'] == 'headers') {
            $query = \DB::table('op.trans_delivery_order_header')
                            ->select(
                                'trans_delivery_order_header.delivery_order_header_id',
                                'trans_delivery_order_header.delivery_order_number',
                                'trans_delivery_order_header.delivery_start_time',
                                'trans_delivery_order_header.delivery_end_time',
                                'trans_delivery_order_header.status',
                                'trans_delivery_order_header.type',
                                'trans_delivery_order_header.created_date',
                                'driver.driver_name',
                                'assistant.driver_name as assistant_name',
                                'mst_truck.police_number',
                                'mst_vendor.vendor_name'
                                )
                            ->leftJoin('op.trans_delivery_order_line', 'trans_delivery_order_line.delivery_order_header_id', '=', 'trans_delivery_order_header.delivery_order_header_id')
                            ->leftJoin('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_delivery_order_line.resi_header_id')
                            ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'trans_delivery_order_header.partner_id')
                            ->leftJoin('op.mst_driver as driver', 'driver.driver_id', '=', 'trans_delivery_order_header.driver_id')
                            ->leftJoin('op.mst_driver as assistant', 'assistant.driver_id', '=', 'trans_delivery_order_header.assistant_id')
                            ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_delivery_order_header.truck_id')
                            ->whereRaw('trans_delivery_order_line.delivery_order_line_id NOT IN (' . $sqlReceiptOrReturn . ')')
                            ->where(function($query){
                                $query->where('trans_delivery_order_header.status', '=', DeliveryOrderHeader::APPROVED)
                                      ->orWhere('trans_delivery_order_header.status', '=', DeliveryOrderHeader::CONFIRMED)
                                      ->orWhere('trans_delivery_order_header.status', '=', DeliveryOrderHeader::ON_THE_ROAD);
                            })
                            ->where('trans_delivery_order_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->distinct()
                            ->orderBy('trans_delivery_order_header.created_date', 'desc');
        }else{
            $query = \DB::table('op.trans_delivery_order_line')
                            ->select(
                                'trans_delivery_order_line.total_coly as coly_send',
                                'trans_delivery_order_header.delivery_order_header_id',
                                'trans_delivery_order_header.delivery_order_number',
                                'trans_delivery_order_header.created_date',
                                'trans_resi_header.resi_number',
                                'trans_resi_header.item_name',
                                'trans_resi_header.receiver_name',
                                'trans_resi_header.receiver_address',
                                'trans_resi_header.receiver_phone'
                                )
                            ->leftJoin('op.trans_delivery_order_header', 'trans_delivery_order_header.delivery_order_header_id', '=', 'trans_delivery_order_line.delivery_order_header_id')
                            ->leftJoin('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_delivery_order_line.resi_header_id')
                            ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'trans_delivery_order_header.partner_id')
                            ->leftJoin('op.mst_driver as driver', 'driver.driver_id', '=', 'trans_delivery_order_header.driver_id')
                            ->leftJoin('op.mst_driver as assistant', 'assistant.driver_id', '=', 'trans_delivery_order_header.assistant_id')
                            ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_delivery_order_header.truck_id')
                            ->where('trans_delivery_order_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->where(function($query){
                                $query->where('trans_delivery_order_header.status', '=', DeliveryOrderHeader::APPROVED)
                                      ->orWhere('trans_delivery_order_header.status', '=', DeliveryOrderHeader::CONFIRMED)
                                      ->orWhere('trans_delivery_order_header.status', '=', DeliveryOrderHeader::ON_THE_ROAD);
                            })
                            ->whereRaw('trans_delivery_order_line.delivery_order_line_id NOT IN (' . $sqlReceiptOrReturn . ')')
                            ->distinct()
                            ->orderBy('trans_delivery_order_header.created_date', 'desc');
        }

        if (!empty($filters['deliveryOrderNumber'])) {
            $query->where('trans_delivery_order_header.delivery_order_number', 'ilike', '%'.$filters['deliveryOrderNumber'].'%');
        }

        if (!empty($filters['partnerName'])) {
            $query->where('mst_vendor.vendor_name', 'ilike', '%'.$filters['partnerName'].'%');
        }

        if (!empty($filters['resiNumber'])) {
            $query->where('trans_resi_header.resi_number', 'ilike', '%'.$filters['resiNumber'].'%');
        }

        if (!empty($filters['itemName'])) {
            $query->where('trans_resi_header.item_name', 'ilike', '%'.$filters['itemName'].'%');
        }

        if (!empty($filters['receiverName'])) {
            $query->where('trans_resi_header.receiver_name', 'ilike', '%'.$filters['receiverName'].'%');
        }

        if (!empty($filters['driver'])) {
            $query->where('driver.driver_name', 'ilike', '%'.$filters['driver'].'%');
        }

        if (!empty($filters['type'])) {
            $query->where('trans_delivery_order_header.type', '=', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('trans_delivery_order_header.status', '=', $filters['status']);
        }

        if (!empty($filters['policeNumber'])) {
            $query->where('mst_truck.police_number', 'ilike', '%'.$filters['policeNumber'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = TimezoneDateConverter::getServerDateTime($filters['dateFrom']);
            $query->where('trans_delivery_order_header.delivery_start_time', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = TimezoneDateConverter::getServerDateTime($filters['dateTo']);
            $query->where('trans_delivery_order_header.delivery_start_time', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        return $query;
    }

    protected function getOptionsStatus()
    {
        return [
            DeliveryOrderHeader::APPROVED,
            DeliveryOrderHeader::CONFIRMED,
            DeliveryOrderHeader::ON_THE_ROAD,
        ];
    }

    protected function getOptionsType()
    {
        return [
            DeliveryOrderHeader::REGULAR,
            DeliveryOrderHeader::TRANSITION,
        ];
    }
}
