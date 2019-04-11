<?php

namespace App\Modules\Operational\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Transaction\ReceiptOrReturnDeliveryHeader;
use App\Modules\Operational\Model\Transaction\ReceiptOrReturnDeliveryLine;
use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader;
use App\Modules\Operational\Model\Transaction\DeliveryOrderLine;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Transaction\ResiStock;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Model\Master\MasterDriver;
use App\Modules\Operational\Model\Master\MasterTruck;
use App\Service\Penomoran;
use App\Notification;
use App\Service\NotificationService;
use App\Role;
use App\Modules\Operational\Service\Transaction\HistoryResiService;

class ReceiptOrReturnDeliveryOrderController extends Controller
{
    const RESOURCE = 'Operational\Transaction\ReceiptOrReturnDeliveryOrder';
    const URL      = 'operational/transaction/receipt-or-return-delivery-order';

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
        $query = \DB::table('op.trans_receipt_or_return_delivery_header')
                    ->select(
                        'trans_receipt_or_return_delivery_header.*', 'trans_delivery_order_header.delivery_order_number',
                        'trans_delivery_order_header.type as delivery_order_type', 'driver.driver_code', 'driver.driver_name',
                        'driver_assistant.driver_code as driver_assistant_code', 'driver_assistant.driver_name as driver_assistant_name',
                        'mst_truck.police_number', 'mst_truck.type as truck_type', 'mst_vendor.vendor_code', 'mst_vendor.vendor_name'
                    )
                    ->join('op.trans_delivery_order_header', 'trans_receipt_or_return_delivery_header.delivery_order_header_id', '=', 'trans_delivery_order_header.delivery_order_header_id')
                    ->leftJoin('op.mst_driver as driver', 'trans_delivery_order_header.driver_id', '=', 'driver.driver_id')
                    ->leftJoin('op.mst_driver as driver_assistant', 'trans_delivery_order_header.assistant_id', '=', 'driver_assistant.driver_id')
                    ->leftJoin('op.mst_truck', 'trans_delivery_order_header.truck_id', '=', 'mst_truck.truck_id')
                    ->leftJoin('ap.mst_vendor', 'trans_delivery_order_header.partner_id', '=', 'mst_vendor.vendor_id')
                    ->where('trans_receipt_or_return_delivery_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('trans_receipt_or_return_delivery_header.created_date', 'desc');

        if (!empty($filters['receiptOrReturnNumber'])) {
            $query->where('receipt_or_return_delivery_number', 'ilike', '%'.$filters['receiptOrReturnNumber'].'%');
        }

        if (!empty($filters['deliveryOrderNumber'])) {
            $query->where('delivery_order_number', 'ilike', '%'.$filters['deliveryOrderNumber'].'%');
        }

        if (!empty($filters['policeNumber'])) {
            $query->where('police_number', 'ilike', '%'.$filters['policeNumber'].'%');
        }

        if (!empty($filters['vendor'])) {
            $query->where('vendor_name', 'ilike', '%'.$filters['vendor'].'%');
        }

        if (!empty($filters['driver'])) {
            $query->where('driver_name', 'ilike', '%'.$filters['driver'].'%');
        }

        if (!empty($filters['driverAssistant'])) {
            $query->where('driver_assistant_name', 'ilike', '%'.$filters['driverAssistant'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = new \DateTime($filters['dateFrom']);
            $query->where('trans_receipt_or_return_delivery_header.created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = new \DateTime($filters['dateTo']);
            $query->where('trans_receipt_or_return_delivery_header.created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        return view('operational::transaction.receipt-or-return-delivery-order.index', [
            'models'       => $query->paginate(10),
            'filters'      => $filters,
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new ReceiptOrReturnDeliveryHeader();

        return view('operational::transaction.receipt-or-return-delivery-order.add', [
            'title'        => trans('shared/common.add'),
            'model'        => $model,
            'url'          => self::URL,
            'optionStatus' => [ReceiptOrReturnDeliveryLine::RECEIVED, ReceiptOrReturnDeliveryLine::RETURNED]
        ]);
    }

    public function edit(Request $request, $id)
    {
        $model = ReceiptOrReturnDeliveryHeader::where('receipt_or_return_delivery_header_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        if ($request->user()->cannot('accessBranch', $model->branch_id)) {
            abort(403);
        }

        return view('operational::transaction.receipt-or-return-delivery-order.add', [
            'title'        => trans('shared/common.edit'),
            'model'        => $model,
            'url'          => self::URL,
            'optionStatus' => [ReceiptOrReturnDeliveryLine::RECEIVED, ReceiptOrReturnDeliveryLine::RETURNED]
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? ReceiptOrReturnDeliveryHeader::where('receipt_or_return_delivery_header_id', '=', $id)->first() : new ReceiptOrReturnDeliveryHeader();

        $this->validate($request, [
            'deliveryOrderId' => 'required',
        ]);

        $proceedExist         = false;
        $requiredReceivedBy   = [];
        $requiredReceivedDate = [];

        for ($i = 0; $i < count($request->get('lineId', [])); $i++) {
            $doLine = DeliveryOrderLine::find($request->get('deliveryOrderLineId')[$i]);
            if ($request->get('totalColy')[$i] > $doLine->total_coly) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Coly excedd!']);
            }

            if (!empty($request->get('status')[$i])) {
                $proceedExist = true;
            }

            if ($request->get('status')[$i] == ReceiptOrReturnDeliveryLine::RECEIVED && empty($request->get('receivedBy')[$i])) {
                $requiredReceivedBy[] = $request->get('resiNumber')[$i];
            }

            if ($request->get('status')[$i] == ReceiptOrReturnDeliveryLine::RECEIVED && empty($request->get('receivedBy')[$i])) {
                $requiredReceivedBy[] = $request->get('resiNumber')[$i];
            }

            if ($request->get('status')[$i] == ReceiptOrReturnDeliveryLine::RECEIVED && empty($request->get('receivedDate')[$i])) {
                $requiredReceivedDate[] = $request->get('resiNumber')[$i];
            }
        }

        if (!$proceedExist) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'You must proceed minimal 1 line']);
        }

        if (!empty($requiredReceivedBy)) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Received By on Line Resi '.$requiredReceivedBy[0].' is required']);
        }

        if (!empty($requiredReceivedDate)) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Received Date on Line Resi '.$requiredReceivedDate[0].' is required']);
        }

        if (empty($id)) {
            $model->delivery_order_header_id          = $request->get('deliveryOrderId');
            $model->note                              = $request->get('note');
            $model->branch_id                         = \Session::get('currentBranch')->branch_id;
            $model->created_date                      = $this->now;
            $model->created_by                        = \Auth::user()->id;
            $model->receipt_or_return_delivery_number = $this->getReceiptOrReturnDeliveryNumber($model);

            try {
                $model->save();
            } catch (\Exception $e) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }


            for ($i = 0; $i < count($request->get('lineId', [])); $i++) {
                if (empty($request->get('status')[$i])) {
                    continue;
                }

                $line                                       = new ReceiptOrReturnDeliveryLine();
                $line->receipt_or_return_delivery_header_id = $model->receipt_or_return_delivery_header_id;
                $line->status                               = $request->get('status')[$i];
                $line->created_date                         = $this->now;
                $line->created_by                           = \Auth::user()->id;
                $line->note                                 = $request->get('noteLine')[$i];
                $line->delivery_order_line_id               = $request->get('deliveryOrderLineId')[$i];

                $doLine = DeliveryOrderLine::find($request->get('deliveryOrderLineId')[$i]);
                $resi   = $doLine->resi;
                $check     = $this->checkExistStock($resi->resi_header_id, $model->branch_id);
                $stockResi = $check ? ResiStock::where('resi_header_id', '=', $resi->resi_header_id)->where('branch_id', '=', $model->branch_id)->first() : new ResiStock();
                $stockResi->branch_id      = $model->branch_id;
                $stockResi->resi_header_id = $resi->resi_header_id;
                $now = new \DateTime();

                if ($line->isReceived()) {
                    $receivedDate        = new \DateTime($request->get('receivedDate')[$i]);
                    $line->received_by   = $request->get('receivedBy')[$i];
                    $line->received_date = $receivedDate;
                    $line->total_coly    = $request->get('totalColy')[$i];

                    $this->saveHistoryResi('Receipt DO', $model->receipt_or_return_delivery_number, $doLine->resi_header_id, $line->total_coly);

                    $remain              = $doLine->total_coly - $request->get('totalColy')[$i];
                    if ($remain > 0) {
                        if ($check) {
                            $stockResi->coly  = $stockResi->coly + $remain;
                            $stockResi->last_updated_date = $now;
                            $stockResi->last_updated_by   = \Auth::user()->id;
                        }else{
                            $stockResi->coly = $remain;
                            $stockResi->created_date = $now;
                            $stockResi->created_by   = \Auth::user()->id;
                            $stockResi->last_updated_date = $now;
                            $stockResi->last_updated_by   = \Auth::user()->id;
                        }

                        $this->saveHistoryResi('Return DO', $model->receipt_or_return_delivery_number, $doLine->resi_header_id, $remain);
                    }
                }else{
                    $line->total_coly = $doLine->total_coly;
                    if ($check) {
                        $stockResi->coly  = $stockResi->coly + $doLine->total_coly;
                        $stockResi->last_updated_date = $now;
                        $stockResi->last_updated_by   = \Auth::user()->id;
                    }else{
                        $stockResi->coly = $doLine->total_coly;
                        $stockResi->created_date = $now;
                        $stockResi->created_by   = \Auth::user()->id;
                        $stockResi->last_updated_date = $now;
                        $stockResi->last_updated_by   = \Auth::user()->id;
                    }

                    $this->saveHistoryResi('Return DO', $model->receipt_or_return_delivery_number, $doLine->resi_header_id, $line->total_coly);
                }

                if ($stockResi->coly <= 0 ) {
                    $stockResi->delete();
                }else{
                    $stockResi->save();
                }

                try {
                    $line->save();
                } catch (\Exception $e) {
                    return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }
            }

            $doLinesUnprocessed = $this->getDeliveryOrderLinesUnprocessed($model->delivery_order_header_id);
            if (empty($doLinesUnprocessed)) {
                $deliveryOrder = DeliveryOrderHeader::find($model->delivery_order_header_id);
                if ($deliveryOrder !== null) {
                    $deliveryOrder->status = DeliveryOrderHeader::CLOSED;
                    $deliveryOrder->delivery_end_time = $this->now;

                    if(!empty($deliveryOrder->delivery_area_id)){
                        $truck          = MasterTruck::find($deliveryOrder->truck_id);

                        $driver         = MasterDriver::find($deliveryOrder->driver_id);
                        $driverSalary   = $driver !== null && $driver->isTripEmployee() ? $this->getDriverSalary($deliveryOrder->delivery_area_id, $truck->type, MasterDriver::DRIVER) : 0;

                        $assistant       = MasterDriver::find($deliveryOrder->assistant_id);
                        $assistantSalary = $assistant !== null && $assistant->isTripEmployee() ? $this->getDriverSalary($deliveryOrder->delivery_area_id, $truck->type, MasterDriver::ASSISTANT) : 0;

                        $deliveryOrder->driver_salary           = intval(!empty($driverSalary) ? $driverSalary->salary : 0);
                        $deliveryOrder->driver_assistant_salary = intval(!empty($assistantSalary) ? $assistantSalary->salary : 0);
                    }
                    
                    try {
                        $deliveryOrder->save();
                    } catch (\Exception $e) {
                        return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                    }
                }
            }
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.receipt-or-return-delivery-order').' '.$model->receipt_or_return_delivery_number])
        );

        return redirect(self::URL);
    }

    protected function getDriverSalary($deliveryAreaId, $vehicleType, $driverPosition){
        return \DB::table('op.mst_do_pickup_driver_salary')
                    ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)        
                    ->where('delivery_area_id', '=', $deliveryAreaId)        
                    ->where('vehicle_type', '=', $vehicleType)
                    ->where('driver_position', '=', $driverPosition)
                    ->first();        
    }

    protected function saveHistoryResi($process, $receiptorReturnNumber, $resiId, $coly)
    {
        HistoryResiService::saveHistory(
            $resiId,
            $process,
            'Receipt or Return Number: '.$receiptorReturnNumber.'. Coly: '.$coly
        );
    }

    function checkExistStock($resiId, $branchId){
        if (\DB::table('op.mst_stock_resi')
                ->where('mst_stock_resi.resi_header_id', '=', $resiId)
                ->where('mst_stock_resi.branch_id', '=', $branchId)
                ->count() > 0) {
            return true;
        }
        return false;
    }

    protected function getReceiptOrReturnDeliveryNumber(ReceiptOrReturnDeliveryHeader $model)
    {
        $branch      = MasterBranch::find($model->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('op.trans_receipt_or_return_delivery_header')
                            ->where('branch_id', '=', $model->branch_id)
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->count();

        return 'RRD.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }

    public function getJsonDeliveryOrder(Request $request)
    {
        $search = $request->get('search');
        $query  = \DB::table('op.trans_delivery_order_header')
                    ->select(
                        'trans_delivery_order_header.*', 'mst_truck.police_number', 'driver.driver_code', 'driver.driver_name',
                        'driver_assistant.driver_code as driver_assistant_code', 'driver_assistant.driver_name as driver_assistant_name',
                        'mst_vendor.vendor_code', 'mst_vendor.vendor_name'
                    )
                    ->leftJoin('op.mst_driver as driver', 'trans_delivery_order_header.driver_id', '=', 'driver.driver_id')
                    ->leftJoin('op.mst_driver as driver_assistant', 'trans_delivery_order_header.assistant_id', '=', 'driver_assistant.driver_id')
                    ->leftJoin('op.mst_truck', 'trans_delivery_order_header.truck_id', '=', 'mst_truck.truck_id')
                    ->leftJoin('ap.mst_vendor', 'trans_delivery_order_header.partner_id', '=', 'mst_vendor.vendor_id')
                    ->where('trans_delivery_order_header.status', '=', DeliveryOrderHeader::ON_THE_ROAD)
                    ->where('trans_delivery_order_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where('trans_delivery_order_header.delivery_order_number', 'ilike', '%'.$search.'%')
                    ->take(10);

        $arrayDo = [];
        foreach ($query->get() as $do) {
            $do->vendor_code = empty($do->vendor_code) ? '' : $do->vendor_code;
            $do->vendor_name = empty($do->vendor_name) ? '' : $do->vendor_name;
            $do->lines = $this->getDeliveryOrderLinesUnprocessed($do->delivery_order_header_id);
            $arrayDo[] = $do;
        }

        return response()->json($arrayDo);
    }

    protected function getDeliveryOrderLinesUnprocessed($deliveryOrderHeaderId)
    {
        $sqlReceiptOrReturn = 'SELECT delivery_order_line_id FROM op.trans_receipt_or_return_delivery_line';

        $query = \DB::table('op.trans_resi_header')
                    ->select(
                        'trans_delivery_order_line.*','trans_resi_header.resi_number', 'trans_resi_header.receiver_name',
                        'trans_resi_header.receiver_address', 'mst_customer.customer_name'
                    )
                    ->join('op.trans_delivery_order_line', 'trans_resi_header.resi_header_id', '=', 'trans_delivery_order_line.resi_header_id')
                    ->leftJoin('op.mst_customer', 'trans_resi_header.customer_receiver_id', '=', 'mst_customer.customer_id')
                    ->where('trans_delivery_order_line.delivery_order_header_id', '=', $deliveryOrderHeaderId)
                    ->whereRaw('trans_delivery_order_line.delivery_order_line_id NOT IN (' . $sqlReceiptOrReturn . ')');

        return $query->get();
    }
}
