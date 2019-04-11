<?php

namespace App\Modules\Purchasing\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Purchasing\Http\Controllers\Transaction\PurchaseApproveController;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
use App\Modules\Purchasing\Model\Transaction\PurchaseOrderHeader;
use App\Modules\Operational\Model\Transaction\PickupFormHeader;
use App\Modules\Purchasing\Model\Transaction\PurchaseOrderLine;
use App\Modules\Purchasing\Model\Master\MasterTypePo;
use App\Modules\Asset\Service\Transaction\AssetService;
use App\Modules\Operational\Service\Transaction\ManifestService;
use App\Modules\Operational\Service\Transaction\DeliveryOrderService;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Payable\Model\Master\MasterVendor;
use App\Notification;
use App\Service\Penomoran;
use App\Service\NotificationService;
use App\Role;

class PurchaseOrderController extends Controller
{
    const RESOURCE = 'Purchasing\Transaction\PurchaseOrder';
    const URL = 'purchasing/transaction/purchase-order';

    public function __construct()
    {
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

        if (empty($filters['jenis']) || $filters['jenis'] == 'headers') {
            $query = \DB::table('po.po_headers')
                        ->select('po_headers.*', 'mst_vendor.vendor_code', 'mst_vendor.vendor_name', 'mst_po_type.type_name')
                        ->leftJoin('po.po_lines', 'po_lines.header_id', '=', 'po_headers.header_id')
                        ->leftJoin('inv.mst_item', 'mst_item.item_id', '=', 'po_lines.item_id')
                        ->leftJoin('po.mst_po_type', 'mst_po_type.type_id', '=', 'po_headers.type_id')
                        ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'po_headers.supplier_id')
                        ->orderBy('po_headers.po_date', 'desc')
                        ->distinct();
        } else {
            $query = \DB::table('po.po_lines')
                        ->select('po_lines.*', 'po_headers.po_number', 'po_headers.po_date','mst_vendor.vendor_code', 'mst_vendor.vendor_name', 'mst_po_type.type_name', 'mst_item.item_code', 'mst_item.description', 'mst_uom.uom_code', 'mst_warehouse.wh_code', 'mst_category.description as category_description')
                        ->leftJoin('po.po_headers', 'po_headers.header_id', '=', 'po_lines.header_id')
                        ->leftJoin('inv.mst_item', 'mst_item.item_id', '=', 'po_lines.item_id')
                        ->leftJoin('inv.mst_warehouse', 'mst_warehouse.wh_id', '=', 'po_lines.wh_id')
                        ->leftJoin('inv.mst_category', 'mst_category.category_id', '=', 'mst_item.category_id')
                        ->leftJoin('inv.mst_uom', 'mst_uom.uom_id', '=', 'mst_item.uom_id')
                        ->leftJoin('po.mst_po_type', 'mst_po_type.type_id', '=', 'po_headers.type_id')
                        ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'po_headers.supplier_id')
                        ->orderBy('po_headers.po_date', 'desc')
                        ->distinct();
        }

        if (!empty($filters['poNumber'])) {
            $query->where('po_headers.po_number', 'ilike', '%'.$filters['poNumber'].'%');
        }

        if (!empty($filters['itemCode'])) {
            $query->where('mst_item.item_code', 'ilike', '%'.$filters['itemCode'].'%');
        }

        if (!empty($filters['itemDescription'])) {
            $query->where('mst_item.description', 'ilike', '%'.$filters['itemDescription'].'%');
        }

        $query->where('po_headers.branch_id', '=', $request->session()->get('currentBranch')->branch_id);

        if (!empty($filters['supplier'])) {
            $query->where('po_headers.supplier_id', '=', $filters['supplier']);
        }

        if (!empty($filters['type'])) {
            $query->where('po_headers.type_id', '=', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('po_headers.status', '=', $filters['status']);
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = new \DateTime($filters['dateFrom']);
            $query->where('po_headers.po_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = new \DateTime($filters['dateTo']);
            $query->where('po_headers.po_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        return view('purchasing::transaction.purchase-order.index', [
            'models' => $query->paginate(10),
            'filters' => $filters,
            'resource' => self::RESOURCE,
            'url' => self::URL,
            'optionsBranch' => $this->getOptionsBranch(),
            'optionsSupplier' => $this->getOptionsSupplier(),
            'optionsType' => $this->getOptionsType(),
            'optionsStatus' => $this->getOptionsStatus(),
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new PurchaseOrderHeader();
        $model->status = PurchaseOrderHeader::INCOMPLETE;
        $model->totalPrice = 0;

        return view('purchasing::transaction.purchase-order.add', [
            'title' => trans('shared/common.add'),
            'model' => $model,
            'resource' => self::RESOURCE,
            'url' => self::URL,
            'optionsSupplier' => $this->getOptionsSupplier(),
            'optionsType' => $this->getOptionsType(),
            'optionsStatus' => $this->getOptionsStatus(),
            'optionsItem' => $this->getOptionsItem(),
            'optionsWarehouse' => $this->getOptionsWarehouse(),
            'optionLineType' => $this->getOptionLineType(),
            'optionService' => AssetService::getAllServiceOrder(),
            'optionManifest' => ManifestService::getManifestPo(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = PurchaseOrderHeader::where('header_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        if ($request->user()->cannot('accessBranch', $model->branch_id)) {
            abort(403);
        }

        $data = [
            'title' => trans('shared/common.edit'),
            'model' => $model,
            'resource' => self::RESOURCE,
            'url' => self::URL,
            'optionsSupplier' => $this->getOptionsSupplier(),
            'optionsType' => $this->getOptionsType(),
            'optionsStatus' => $this->getOptionsStatus(),
            'optionsItem' => $this->getOptionsItem(),
            'optionsWarehouse' => $this->getOptionsWarehouse(),
            'optionLineType' => $this->getOptionLineType(),
            'optionService' => AssetService::getAllServiceOrder(),
            'optionManifest' => ManifestService::getManifestPo(),
        ];

        if ($request->user()->can('access', [self::RESOURCE, 'update'])) {
            return view('purchasing::transaction.purchase-order.add', $data);
        } else {
            return view('purchasing::transaction.purchase-order.detail', $data);
        }
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? PurchaseOrderHeader::find($id) : new PurchaseOrderHeader();

        $this->validate($request, [
            'supplier' => 'required',
            'type' => 'required',
        ]);

        if (empty($request->get('itemId'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Lines are required']);
        }

        if (empty($model->branch_id)) {
            $model->branch_id = $request->session()->get('currentBranch')->branch_id;
        }

        $now = new \DateTime();
        if (empty($model->po_date)) {
            $model->po_date = $now;
        }

        if (empty($model->po_number)) {
            $model->po_number = $this->getPoNumber($model);
        }

        $model->description = $request->get('description');
        $model->supplier_id = $request->get('supplier');
        $model->type_id = $request->get('type');

        if ($request->get('btn-approve-admin') !== null) {
            $model->status = PurchaseOrderHeader::APPROVED;
            $model->approved_by   = \Auth::user()->id;
            $model->approved_date = new \DateTime(); 
        } elseif ($request->get('btn-approve-kacab') !== null) {
            $model->status = PurchaseOrderHeader::INPROCESS;
        } elseif (empty($model->status)) {
            $model->status = PurchaseOrderHeader::INCOMPLETE;
        }

        if (empty($id)) {
            $model->created_date = $now;
            $model->created_by = \Auth::user()->id;
        } else {
            $model->last_updated_date = $now;
            $model->last_updated_by = \Auth::user()->id;
        }

        try {
            $model->save();            
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }
        if ($request->get('btn-approve-kacab') !== null){
            $userNotif = NotificationService::getUserNotification([Role::BRANCH_MANAGER]);
            foreach ($userNotif as $user) {
                $notif             = new Notification();
                $notif->branch_id  = \Session::get('currentBranch')->branch_id;
                $notif->category   = 'Request Approval Purchase Order';
                $notif->message    = 'Need approval Purchase Order - '.$model->po_number;
                $notif->url        = PurchaseApproveController::URL.'/edit/'.$model->header_id;
                $notif->created_at = new \DateTime();
                $notif->user_id    = $user->id;
                $notif->role_id    = $user->role_id;
                $notif->save();
            }
        }

        $totalPricePo = 0;
        $lines = $model->purchaseOrderLines()->get();
        $lineIds = $request->get('lineId');

        foreach ($lines as $line) {
            $i = array_search($line->line_id, $lineIds);
            if (in_array($line->line_id, $lineIds)) {
                $line->item_id         = $request->get('itemId')[$i];
                $line->wh_id           = $request->get('warehouse')[$i];
                $line->type            = $request->get('lineType')[$i];
                if (!empty($request->get('serviceId')[$i])) {
                    $line->service_asset_id = $request->get('serviceId')[$i];
                }

                if (!empty($request->get('manifestId')[$i])) {
                    $line->manifest_header_id = $request->get('manifestId')[$i];
                }

                if (!empty($request->get('deliveryOrderId')[$i])) {
                    $line->delivery_order_header_id = $request->get('deliveryOrderId')[$i];
                }

                if (!empty($request->get('pickupFormId')[$i])) {
                    $line->pickup_form_header_id = $request->get('pickupFormId')[$i];
                }

                $line->quantity_need   = str_replace(',', '', $request->get('qty')[$i]);
                $line->quantity_remain = str_replace(',', '', $request->get('qty')[$i]);
                $line->unit_price      = str_replace(',', '', $request->get('unitPrice')[$i]);
                $line->total_price     = $line->quantity_need * $line->unit_price;

                $line->last_updated_date = new \DateTime();
                $line->last_updated_by = \Auth::user()->id;
                $line->save();

                $totalPricePo += $line->total_price;

            } else {
                $line->active = 'N';
                try {
                    $line->save();
                } catch (\Exception $e) {
                    return redirect(self::URL.'/edit/'.$model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }
            }
        }

        for ($i = 0; $i < count($request->get('lineId')); $i++) {
            if (empty($request->get('lineId')[$i])) {
                $line = new PurchaseOrderLine();
                $line->header_id = $model->header_id;
                $line->item_id = $request->get('itemId')[$i];
                $line->wh_id = $request->get('warehouse')[$i];
                $line->type            = $request->get('lineType')[$i];

                if (!empty($request->get('serviceId')[$i])) {
                    $line->service_asset_id = $request->get('serviceId')[$i];
                }

                if (!empty($request->get('manifestId')[$i])) {
                    $line->manifest_header_id = $request->get('manifestId')[$i];
                    $manifest = ManifestHeader::find($line->manifest_header_id);
                    $manifest->description = $manifest->description.' . '.$model->po_number.' at '.$now->format('d-m-Y').'. '.$model->description; 
                    $manifest->save();
                }

                if (!empty($request->get('deliveryOrderId')[$i])) {
                    $line->delivery_order_header_id = $request->get('deliveryOrderId')[$i];
                }

                if (!empty($request->get('pickupFormId')[$i])) {
                    $line->pickup_form_header_id = $request->get('pickupFormId')[$i];
                }

                $line->quantity_need = str_replace(',', '', $request->get('qty')[$i]);
                $line->quantity_remain = str_replace(',', '', $request->get('qty')[$i]);
                $line->unit_price = str_replace(',', '', $request->get('unitPrice')[$i]);
                $line->total_price = $line->quantity_need * $line->unit_price;
                $line->active = 'Y';

                $line->created_date = new \DateTime();
                $line->created_by = \Auth::user()->id;
                try {
                    $line->save();
                } catch (\Exception $e) {
                    return redirect(self::URL.'/edit/'.$model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }

                $totalPricePo += $line->total_price;
            }
        }

        $model->total = $totalPricePo;
        try {
            $line->save();
            $model->save();
        } catch (\Exception $e) {
            return redirect(self::URL.'/edit/'.$model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('purchasing/menu.purchase-order').' '.$model->po_number])
        );

        return redirect(self::URL);
    }

    public function printPdfDetail(Request $request, $id)
    {
        $filters = \Session::get('filters');
        $model= PurchaseOrderHeader::find($id);

        $header = view('print.header-pdf', ['title' => trans('purchasing/menu.purchase-order')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('purchasing::transaction.purchase-order.print-pdf-detail', [
            'model'  => $model,
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('purchasing/menu.purchase-order'));
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A5');
        \PDF::writeHTML($html);
        \PDF::Output($model->po_number.'.pdf');
        \PDF::reset();
    }

    public function getJsonManifest(Request $request)
    {
        $search   = $request->get('search');
        $id       = $request->get('id');

        if($request->get('typePo') == MasterTypePo::TRUCK_RENT_PER_TRIP){
            $query = ManifestService::getManifestPoTruckRent();
        }else{
            $query = ManifestService::getManifestPo();
        }

        $query->select(
                    'trans_manifest_header.*', 
                    'driver.driver_name', 
                    'assistant.driver_name as assistant_name', 
                    'mst_truck.police_number', 
                    'mst_route.route_code'
                    )
              ->where(function ($query) use ($search) {
                    $query->where('trans_manifest_header.manifest_number', 'ilike', '%'.$search.'%')
                      ->orWhere('trans_manifest_header.description', 'ilike', '%'.$search.'%')
                      ->orWhere('driver.driver_name', 'ilike', '%'.$search.'%')
                      ->orWhere('assistant.driver_name', 'ilike', '%'.$search.'%')
                      ->orWhere('mst_truck.police_number', 'ilike', '%'.$search.'%')
                      ->orWhere('mst_route.route_code', 'ilike', '%'.$search.'%');
                })
              ->take(10);
        
        return response()->json($query->get());
    }

    public function getJsonDeliveryOrder(Request $request)
    {
        $search   = $request->get('search');
        $id       = $request->get('id');

        $query = DeliveryOrderService::getQueryDeliveryOrderMinimumApprovedAllBranch()
                    ->where(function ($query) use ($search) {
                        $query->where('trans_delivery_order_header.delivery_order_number', 'ilike', '%'.$search.'%')
                          ->orWhere('trans_delivery_order_header.note', 'ilike', '%'.$search.'%')
                          ->orWhere('trans_delivery_order_header.status', 'ilike', '%'.$search.'%')
                          ->orWhere('driver.driver_name', 'ilike', '%'.$search.'%')
                          ->orWhere('assistant.driver_name', 'ilike', '%'.$search.'%')
                          ->orWhere('mst_truck.police_number', 'ilike', '%'.$search.'%')
                          ->orWhere('mst_vendor.vendor_name', 'ilike', '%'.$search.'%');
                    })
                    ->take(10);
        
        return response()->json($query->get());
    }

    public function getJsonPickupForm(Request $request)
    {
        $search   = $request->get('search');
        $id       = $request->get('id');

        $query = \DB::table('op.trans_pickup_form_header')
                    ->select('trans_pickup_form_header.*', 'mst_driver.driver_name', 'mst_truck.police_number')
                    ->leftJoin('op.mst_driver', 'mst_driver.driver_id', '=', 'trans_pickup_form_header.driver_id')
                    ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_pickup_form_header.truck_id')
                    ->where('trans_pickup_form_header.status', '=', PickupFormHeader::CLOSED)
                    // ->where('trans_pickup_form_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where(function ($query) use ($search) {
                        $query->where('trans_pickup_form_header.pickup_form_number', 'ilike', '%'.$search.'%')
                          ->orWhere('trans_pickup_form_header.note', 'ilike', '%'.$search.'%')
                          ->orWhere('mst_driver.driver_name', 'ilike', '%'.$search.'%')
                          ->orWhere('mst_truck.police_number', 'ilike', '%'.$search.'%');
                    })
                    ->distinct()
                    ->take(10);
        
        return response()->json($query->get());
    }

    public function delete(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'delete'])) {
            abort(403);
        }

        $model = PurchaseOrderHeader::find($request->get('id'));
        if ($model === null) {
            abort(404);
        }

        $model->status = PurchaseOrderHeader::CANCELED;
        $model->save();

        $request->session()->flash(
            'successMessage',
            trans('shared/common.canceled-message', ['variable' => trans('purchasing/menu.purchase-order').' '.$model->po_number])
        );

        return redirect(self::URL);
    }

    public function cancelPo(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'delete'])) {
            abort(403);
        }

        $model = PurchaseOrderHeader::find($request->get('id'));
        if ($model === null) {
            abort(404);
        }

        if ($model->getTotalInvoice() > 0) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $model->po_number.' '.trans('purchasing/fields.cannot-cancel-invoice-exist'). '. Invoice exist on '.number_format($model->getTotalInvoice()) ]);
        }

        $model->status = PurchaseOrderHeader::CANCELED;
        $model->canceled_date = new \DateTime();
        $model->canceled_by = \Auth::user()->id;
        $model->canceled_reason = $request->get('reason', '');

        $model->save();

        $userNotif = NotificationService::getUserNotification([Role::BRANCH_MANAGER]);

            foreach ($userNotif as $user) {
                $notif             = new Notification();
                $notif->branch_id  = \Session::get('currentBranch')->branch_id;
                $notif->category   = 'Purchase Approve Canceled';
                $notif->message    = 'Purchase Order Canceled '.$model->po_number. '. ' . $request->get('reason', '');
                $notif->url        = self::URL.'/edit/'.$model->header_id;
                $notif->created_at = new \DateTime();
                $notif->user_id    = $user->id;
                $notif->role_id    = $user->role_id;
                $notif->save();
            }

        $request->session()->flash(
            'successMessage',
            trans('purchasing/fields.canceled-message', ['variable' => trans('purchasing/menu.purchase-order').' '.$model->po_number])
        );

        return redirect(self::URL);
    }

    protected function getPoNumber(PurchaseOrderHeader $model)
    {
        $date = $model->po_date instanceof \DateTime ? $model->po_date : new \DateTime($model->po_date);
        $branch = MasterBranch::find($model->branch_id);
        $count = \DB::table('po.po_headers')
                        ->where('branch_id', '=', $model->branch_id)
                        ->where('po_date', '>=', $date->format('Y-1-1 00:00:00'))
                        ->where('po_date', '<=', $date->format('Y-12-31 23:59:59'))
                        ->count();

        return 'PO.'.$branch->branch_code.'.'.$date->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }

    protected function getOptionsBranch()
    {
        return \DB::table('op.mst_branch')->where('active', '=', 'Y')->orderBy('branch_name')->get();
    }

    protected function getOptionsSupplier()
    {
        return \DB::table('ap.mst_vendor')
                ->leftJoin('ap.dt_vendor_branch', 'dt_vendor_branch.vendor_id', '=', 'mst_vendor.vendor_id')
                ->where('dt_vendor_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                ->where('mst_vendor.category', '=', MasterVendor::VENDOR)
                ->orderBy('vendor_name')->where('active', '=', 'Y')->get();
    }

    protected function getOptionsType()
    {
        return \DB::table('po.mst_po_type')->orderBy('type_name')->where('active', '=', 'Y')->get();
    }

    protected function getOptionsStatus()
    {
        return [
            PurchaseOrderHeader::INCOMPLETE,
            PurchaseOrderHeader::INPROCESS,
            PurchaseOrderHeader::APPROVED,
            PurchaseOrderHeader::CANCELED,
            PurchaseOrderHeader::CLOSED,
        ];
    }

    protected function getOptionLineType()
    {
        return [
            PurchaseOrderHeader::GOODS,
            PurchaseOrderHeader::SERVICE,
        ];
    }

    protected function getOptionsWarehouse()
    {
        return \DB::table('inv.mst_warehouse')->orderBy('wh_code')->where('active', '=', 'Y')->where('branch_id', '=', \Session::get('currentBranch')->branch_id)->get();
    }

    protected function getOptionsItem()
    {
        return \DB::table('inv.v_mst_item')->orderBy('item_code')->where('active', '=', 'Y')->get();
    }
}
