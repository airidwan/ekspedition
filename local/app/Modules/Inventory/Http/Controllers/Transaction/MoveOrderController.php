<?php

namespace App\Modules\Inventory\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Inventory\Model\Transaction\MoveOrderHeader;
use App\Modules\Inventory\Model\Transaction\MoveOrderLine;
use App\Modules\Inventory\Model\Transaction\BranchTransferHeader;
use App\Modules\Inventory\Model\Transaction\WarehouseTransferHeader;
use App\Modules\Operational\Service\Master\DriverService;
use App\Modules\Operational\Service\Master\TruckService;
use App\Modules\Asset\Service\Transaction\AssetService;
use App\Modules\Asset\Model\Transaction\ServiceAsset;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Modules\Generalledger\Model\Master\SettingJournal;
use App\Modules\Generalledger\Model\Master\MasterAccountCombination;
use App\Modules\Generalledger\Service\Master\AccountCombinationService;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Model\Master\MasterTruck;
use App\Modules\Inventory\Model\Master\MasterStock;
use App\Modules\Inventory\Model\Master\MasterItem;
use App\Modules\Payable\Model\Master\MasterVendor;
use App\Service\Penomoran;
use App\Notification;
use App\Service\NotificationService;
use App\Modules\Payable\Service\Master\VendorService;
use App\Role;

class MoveOrderController extends Controller
{
    const RESOURCE = 'Inventory\Transaction\MoveOrder';
    const URL      = 'inventory/transaction/move-order';

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
            $query = \DB::table('inv.trans_mo_header')
                    ->select(
                        'trans_mo_header.mo_header_id', 
                        'trans_mo_header.mo_number', 
                        'trans_mo_header.type', 
                        'trans_mo_header.pic', 
                        'trans_mo_header.description', 
                        'trans_mo_header.status', 
                        'trans_mo_header.created_date', 
                        'mst_truck.police_number', 
                        'mst_driver.driver_name', 
                        'mst_vendor.vendor_name', 
                        'service_asset.service_number')
                    ->leftJoin('inv.trans_mo_line', 'trans_mo_line.mo_header_id', '=', 'trans_mo_header.mo_header_id')
                    ->leftJoin('inv.mst_warehouse', 'mst_warehouse.wh_id', '=', 'trans_mo_line.wh_id')
                    ->leftJoin('inv.mst_item', 'mst_item.item_id', '=', 'trans_mo_line.item_id')
                    ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_mo_header.truck_id')
                    ->leftJoin('op.mst_driver', 'mst_driver.driver_id', '=', 'trans_mo_header.driver_id')
                    ->leftJoin('ast.service_asset', 'service_asset.service_asset_id', '=', 'trans_mo_header.service_asset_id')
                    ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'trans_mo_header.vendor_id')
                    ->where('trans_mo_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('trans_mo_header.created_date', 'desc')
                    ->distinct();
        } else {
            $query = \DB::table('inv.trans_mo_line')
                    ->select(
                        'trans_mo_header.mo_header_id', 
                        'trans_mo_header.mo_number', 
                        'trans_mo_header.type', 
                        'trans_mo_header.pic', 
                        'trans_mo_header.description', 
                        'trans_mo_header.status', 
                        'trans_mo_header.created_date', 
                        'trans_mo_line.qty_need', 
                        'mst_vendor.vendor_name',
                        'mst_warehouse.wh_code', 
                        'mst_warehouse.wh_id', 
                        'mst_truck.police_number', 
                        'mst_driver.driver_name', 
                        'service_asset.service_number', 
                        'mst_item.item_code', 
                        'mst_item.description as item_name',
                        'trans_mo_line.description as line_description',
                        'mst_uom.uom_code')
                    ->join('inv.trans_mo_header', 'trans_mo_header.mo_header_id', '=', 'trans_mo_line.mo_header_id')
                    ->leftJoin('inv.mst_warehouse', 'mst_warehouse.wh_id', '=', 'trans_mo_line.wh_id')
                    ->leftJoin('inv.mst_item', 'mst_item.item_id', '=', 'trans_mo_line.item_id')
                    ->leftJoin('inv.mst_uom', 'mst_uom.uom_id', '=', 'mst_item.uom_id')
                    ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_mo_header.truck_id')
                    ->leftJoin('op.mst_driver', 'mst_driver.driver_id', '=', 'trans_mo_header.driver_id')
                    ->leftJoin('ast.service_asset', 'service_asset.service_asset_id', '=', 'trans_mo_header.service_asset_id')
                    ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'trans_mo_header.vendor_id')
                    ->where('trans_mo_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('trans_mo_header.created_date', 'desc')
                    ->distinct();
        }

        if (!empty($filters['moNumber'])) {
            $query->where('mo_number', 'ilike', '%'.$filters['moNumber'].'%');
        }

        if (!empty($filters['pic'])) {
            $query->where(function($query) use ($filters) {
                $query->where('trans_mo_header.pic', 'ilike', '%'.$filters['pic'].'%')
                       ->orWhere('mst_vendor.vendor_name', 'ilike', '%'.$filters['pic'].'%');
            });
        }

        if (!empty($filters['description'])) {
            $query->where('trans_mo_header.description', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['itemCode'])) {
            $query->where('mst_item.item_code', 'ilike', '%'.$filters['itemCode'].'%');
        }

        if (!empty($filters['itemName'])) {
            $query->where('mst_item.description', 'ilike', '%'.$filters['itemName'].'%');
        }

        if (!empty($filters['status'])) {
            $query->where('trans_mo_header.status', '=', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('trans_mo_header.type', '=', $filters['type']);
        }

        if (!empty($filters['warehouse'])) {
            $query->where('mst_warehouse.wh_id', '=', $filters['warehouse']);
        }

        if (!empty($filters['driverName'])) {
            $query->where('mst_driver.driver_name', 'ilike', '%'.$filters['driverName'].'%');
        }

        if (!empty($filters['policeNumber'])) {
            $query->where('mst_truck.police_number', 'ilike', '%'.$filters['policeNumber'].'%');
        }

        if (!empty($filters['serviceNumber'])) {
            $query->where('service_asset.service_number', 'ilike', '%'.$filters['serviceNumber'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = new \DateTime($filters['dateFrom']);
            $query->where('trans_mo_header.created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = new \DateTime($filters['dateTo']);
            $query->where('trans_mo_header.created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        return view('inventory::transaction.move-order.index', [
            'models'          => $query->paginate(10),
            'filters'         => $filters,
            'optionStatus'    => $this->getOptionsStatus(),
            'optionType'      => $this->getOptionsType(),
            'optionWarehouse' => \DB::table('inv.mst_warehouse')->where('branch_id', '=', \Session::get('currentBranch')->branch_id)->get(),
            'resource'        => self::RESOURCE,
            'url'             => self::URL,
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new MoveOrderHeader();
        $model->status = MoveOrderHeader::INCOMPLETE;

        return view('inventory::transaction.move-order.add', [
            'title'         => trans('shared/common.add'),
            'model'         => $model,
            'optionStatus'  => $this->getOptionsStatus(),
            'optionType'    => $this->getOptionsType(),
            'optionItem'    => $this->getOptionsItem(),
            'optionCoa'     => $this->getOptionsCoa(),
            'optionService' => AssetService::getAllServiceOrder(),
            'optionTruck'   => TruckService::getActiveTruck(), 
            'optionDriver'  => DriverService::getActiveDriverAsistant(),
            'optionVendor'  => VendorService::getQueryVendorEmployee(),
            'resource'      => self::RESOURCE,
            'url'           => self::URL,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $model = MoveOrderHeader::where('mo_header_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        $data = [
            'title'         => trans('shared/common.edit'),
            'model'         => $model,
            'optionStatus'  => $this->getOptionsStatus(),
            'optionType'    => $this->getOptionsType(),
            'optionItem'    => $this->getOptionsItem(),
            'optionCoa'     => $this->getOptionsCoa(),
            'optionService' => AssetService::getAllServiceOrder(),
            'optionDriver'  => DriverService::getActiveDriverAsistant(),
            'optionTruck'   => TruckService::getActiveTruck(), 
            'optionVendor'  => VendorService::getQueryVendorEmployee(),
            'resource'      => self::RESOURCE,
            'url'           => self::URL,
        ];

        if ($request->user()->can('access', [self::RESOURCE, 'update'])) {
            return view('inventory::transaction.move-order.add', $data);
        } else {
            return view('inventory::transaction.move-order.detail', $data);
        }
    }

    public function save(Request $request)
    {
        // var_dump($request->all());exit();
        $id = intval($request->get('id'));
        $model = !empty($id) ? MoveOrderHeader::where('mo_header_id', '=', $id)->first() : new MoveOrderHeader();

        $this->validate($request, [
            'description' => 'required',
            'type'        => 'required',
        ]);

        if ($request->get('type') == MoveOrderHeader::SERVICE) {
            $this->validate($request, [
                'serviceNumber'  => 'required',
            ]);
        }

        if (empty($request->get('itemId'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Lines are required']);
        }

        $message = [];
        $itemIds = $request->get('itemId');
        foreach ($request->get('itemId') as $itemId) {
            $i = array_search($itemId, $itemIds);
            $qtyForm  = intval(str_replace(',', '', $request->get('qtyNeed')[$i]));
            $qtyStock = $this->getStock($itemId, $request->get('whId')[$i]);         
            $moExist  = $this->getMoExist($id, $itemId, $request->get('whId')[$i]);         
            $btExist  = $this->getBtExist($itemId, $request->get('whId')[$i]);         
            $wtExist  = $this->getWtExist($itemId, $request->get('whId')[$i]);         
            $max      = intval($qtyStock->stock - $moExist->qty_need - $btExist->qty_need - $wtExist->qty_need);
            if ($qtyForm > $max)  {
                $message [] = $request->get('itemCode')[$i]. ' in '. $request->get('warehouse')[$i] .'  remain is '. number_format($max) .'.';
            }
        }
        if(!empty($message)){
            $string = '';
            foreach ($message as $mess) {
                $string = $string.' '.$mess;
            }
            $stringMessage = 'Quantity exceed! May be move order or branch/wh transfer exist in request (Incomplete status)'. $string;
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $stringMessage]);
        }

        if (empty($model->branch_id)) {
            $model->branch_id = $request->session()->get('currentBranch')->branch_id;
        }

        $model->driver_id        = intval($request->get('driverId'));
        $model->truck_id         = intval($request->get('truckId'));
        $model->vendor_id        = intval($request->get('vendorId'));
        $model->service_asset_id = intval($request->get('serviceId'));
        $model->type             = $request->get('type');
        $model->description      = $request->get('description');

        $pic = MasterVendor::find(intval($request->get('vendorId')));
        $model->pic              = !empty($pic) ? $pic->vendor_name : '';

        $now = new \DateTime();
        if (empty($id)) {
            $model->status  = MoveOrderHeader::INCOMPLETE;
            $model->mo_number = $this->getMoveOrderNumber($model);
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

        $lineIds = $request->get('lineId');
        $lines = $model->lines()->delete();

        $defaultJournalSubAccount = SettingJournal::where('setting_name', '=', SettingJournal::DEFAULT_SUB_ACCOUNT)->first();
        $defaultSubAccount        = $defaultJournalSubAccount->coa;
        $defaultSubAccountCode    = $defaultSubAccount->coa_code;

        for ($i = 0; $i < count($request->get('lineId')); $i++) {


            $line = new MoveOrderLine();
            $line->mo_header_id = $model->mo_header_id;
            $line->item_id = $request->get('itemId')[$i];
            $line->wh_id = $request->get('whId')[$i];
            $line->qty_need = intval(str_replace(',', '', $request->get('qtyNeed')[$i]));
            $line->description = $request->get('note')[$i];

            $line->created_date = new \DateTime();
            $line->created_by = \Auth::user()->id;
            
            $itemStock  = MasterStock::where('item_id', '=', $line->item_id)->where('wh_id', '=', $line->wh_id)->first();
            $line->cost = intval($itemStock->average_cost * $line->qty_need);

            $coa = MasterCoa::find($request->get('coaId')[$i]);
            $accountCode        = $coa->coa_code;

            if (!empty($request->get('truckId'))) {
                $modelTruck     = MasterTruck::find($request->get('truckId'));
                $subAccountCode = !empty($modelTruck) ? $modelTruck->subaccount_code : $defaultSubAccountCode;
            }else if($model->type == MoveOrderHeader::SERVICE){
                $modelService   = ServiceAsset::find($model->service_asset_id);
                $modelAsset     = !empty($modelService) ? $modelService->addAsset : null;
                $modelTruck     = !empty($modelAsset) ? $modelAsset->truck : null;
                $subAccountCode = !empty($modelTruck) ? $modelTruck->subaccount_code : $defaultSubAccountCode;
            }else{
                $subAccountCode = $defaultSubAccountCode;
            }

            $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

            $line->account_comb_id = $accountCombination->account_combination_id;

            try {
                $line->save();
            } catch (\Exception $e) {
                return redirect(self::URL.'/edit/'.$model->mo_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            if ($request->get('btn-transact') !== null) {
                // insert journal
                $journalHeader           = new JournalHeader();
                $journalHeader->category = JournalHeader::MOVE_ORDER;
                $journalHeader->status   = JournalHeader::OPEN;

                $now                    = new \DateTime();
                $period                 = new \DateTime($now->format('Y-m-1'));
                $journalHeader->period  = $period;

                $journalHeader->description = $model->mo_number;
                $journalHeader->branch_id   = \Session::get('currentBranch')->branch_id;

                $journalHeader->journal_date = $now;
                $journalHeader->created_date = $now;
                $journalHeader->created_by = \Auth::user()->id;
                $journalHeader->journal_number = $this->getJournalNumber($journalHeader);

                try {
                    $journalHeader->save();
                } catch (\Exception $e) {
                    return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }

                $itemStock = MasterStock::where('item_id', '=', $line->item_id)->where('wh_id', '=', $line->wh_id)->first();

                // insert journal line debit
                $journalLine      = new JournalLine();
                $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

                $journalLine->journal_header_id      = $journalHeader->journal_header_id;
                $journalLine->account_combination_id = $accountCombination->account_combination_id;
                $journalLine->debet                  = $itemStock->average_cost * $line->qty_need;
                $journalLine->credit                 = 0;
                $journalLine->description            = 'Account Choice';

                $journalLine->created_date = $now;
                $journalLine->created_by = \Auth::user()->id;

                try {
                    $journalLine->save();
                } catch (\Exception $e) {
                    return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }

                // insert journal line credit
                $journalLine   = new JournalLine();
                $modelItem     = MasterItem::find($line->item_id);
                $modelCategory = $modelItem->category;
                $account       = $modelCategory->coa;
                $accountCode   = $account->coa_code;

                $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

                $journalLine->journal_header_id      = $journalHeader->journal_header_id;
                $journalLine->account_combination_id = $accountCombination->account_combination_id;
                $journalLine->debet                  = 0;
                $journalLine->credit                 = $itemStock->average_cost * $line->qty_need;
                $journalLine->description            = $modelItem->description;

                $journalLine->created_date = $now;
                $journalLine->created_by = \Auth::user()->id;

                try {
                    $journalLine->save();
                } catch (\Exception $e) {
                    return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }
            }
        }

        if ($request->get('btn-transact') !== null) {
            $model->status = MoveOrderHeader::COMPLETE;
            for ($i = 0; $i < count($request->get('lineId')); $i++) {
                $stock = MasterStock::where('item_id', '=', $request->get('itemId')[$i])
                                        ->where('wh_id', '=', $request->get('whId')[$i])
                                        ->first();
                $stock->stock -= intval(str_replace(',', '', $request->get('qtyNeed')[$i]));
                $stock->save();
            }
            try {
                    $model->save();
                } catch (\Exception $e) {
                    return redirect(self::URL.'/edit/'.$model->mo_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }
        }
        
        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('inventory/menu.move-order').' '.$model->mo_number])
        );

        return redirect(self::URL);
    }

    protected function getJournalNumber(JournalHeader $model)
    {
        $branch      = MasterBranch::find($model->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('gl.trans_journal_header')
                            ->where('branch_id', '=', $model->branch_id)
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->count();

        return 'J.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 5);
    }

    public function printPdfDetail(Request $request, $id)
    {
        $filters = \Session::get('filters');
        $model= MoveOrderHeader::find($id);

        $header = view('print.header-pdf', ['title' => trans('inventory/menu.move-order')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('inventory::transaction.move-order.print-pdf-detail', [
            'model'  => $model,
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle('Move Order');
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A5');
        \PDF::writeHTML($html);
        \PDF::Output($model->mo_number.'.pdf');
        \PDF::reset();
    }

    public function cancelMo(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'cancel'])) {
            abort(403);
        }

        $model = MoveOrderHeader::find($request->get('id'));
        if ($model === null) {
            abort(404);
        }

        $model->status = MoveOrderHeader::CANCELED;
        $model->description = $model->description .'. Canceled reason : '.$request->get('reason');   
        $model->last_updated_date = new \DateTime();
        $model->last_updated_by   = \Auth::user()->id;

        $model->save();

        $userNotif = NotificationService::getUserNotification([Role::BRANCH_MANAGER]);
        foreach ($userNotif as $user) {
            $notif             = new Notification();
            $notif->branch_id  = \Session::get('currentBranch')->branch_id;
            $notif->url        = self::URL.'/edit/'.$model->mo_header_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->category   = 'Canceled Move Order';
            $notif->message    = 'Canceled Move Order '.$model->mo_number. '. ' . $request->get('reason', '');
            $notif->save();
        }

        $request->session()->flash(
            'successMessage',
            trans('purchasing/fields.canceled-message', ['variable' => trans('inventory/menu.move-order').' '.$model->mo_number])
            );

        return redirect(self::URL);
    }

    protected function getStock($itemId, $whId){
        return \DB::table('inv.mst_stock_item')
                    ->selectRaw('sum(stock) as stock')
                    ->where('item_id', '=', $itemId)
                    ->where('wh_id', '=', $whId)
                    ->first();
    }

    protected function getMoExist($headerId, $itemId, $whId){

        return \DB::table('inv.trans_mo_line')
                    ->selectRaw('sum(qty_need) as qty_need')
                    ->join('inv.trans_mo_header', 'trans_mo_header.mo_header_id', '=', 'trans_mo_line.mo_header_id')
                    ->where('trans_mo_header.mo_header_id', '!=', $headerId)
                    ->where('item_id', '=', $itemId)
                    ->where('wh_id', '=', $whId)
                    ->where('trans_mo_header.status', '=', MoveOrderHeader::INCOMPLETE)
                    ->first();
    }

    protected function getBtExist($itemId, $whId){

        return \DB::table('inv.trans_bt_line')
                    ->selectRaw('sum(qty_need) as qty_need')
                    ->join('inv.trans_bt_header', 'trans_bt_header.bt_header_id', '=', 'trans_bt_line.bt_header_id')
                    ->where('item_id', '=', $itemId)
                    ->where('from_wh_id', '=', $whId)
                    ->where('trans_bt_header.status', '=', BranchTransferHeader::INCOMPLETE)
                    ->first();
    }

    protected function getWtExist( $itemId, $whId){

        return \DB::table('inv.trans_wht_line')
                    ->selectRaw('sum(qty_need) as qty_need')
                    ->join('inv.trans_wht_header', 'trans_wht_header.wht_header_id', '=', 'trans_wht_line.wht_header_id')
                    ->where('item_id', '=', $itemId)
                    ->where('from_wh_id', '=', $whId)
                    ->where('trans_wht_header.status', '=', WarehouseTransferHeader::INCOMPLETE)
                    ->first();
    }

    protected function getCoa($coaCode, $segmentName){
        $coa = MasterCoa::where('coa_code', '=', $coaCode)->where('segment_name', '=', $segmentName)->first();
        return [
            'coaId'             => $coa->coa_id,
            'coaDescription'    => $coa->description,
        ];
    }

    protected function getMoveOrderNumber(MoveOrderHeader $model)
    {
        $branch      = MasterBranch::find(\Session::get('currentBranch')->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('inv.trans_mo_header')
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->count();

        return 'MO.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }

    protected function getOptionsStatus()
    {
        return [
            MoveOrderHeader::INCOMPLETE,
            MoveOrderHeader::COMPLETE,
        ];
    }

    protected function getOptionsType()
    {
        return [
            MoveOrderHeader::STANDART,
            MoveOrderHeader::SERVICE,
        ];
    }

    protected function getOptionsItem(){
        return \DB::table('inv.mst_stock_item')
                    ->select('mst_stock_item.*', 'mst_item.item_code', 'mst_item.description', 'mst_warehouse.wh_code', 'mst_uom.uom_id', 'mst_uom.uom_code')
                    ->join('inv.mst_warehouse', 'mst_warehouse.wh_id', '=', 'mst_stock_item.wh_id')
                    ->join('inv.mst_item', 'mst_item.item_id', '=', 'mst_stock_item.item_id')
                    ->join('inv.mst_uom', 'mst_uom.uom_id', '=', 'mst_item.uom_id')
                    ->where('mst_stock_item.stock', '>', 0)
                    ->where('mst_warehouse.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->get();
    }

    protected function getOptionsCoa(){
        return \DB::table('gl.mst_coa')
                    ->where('segment_name', '=', MasterCoa::ACCOUNT)
                    ->where('identifier', '=', MasterCoa::EXPENSE)
                    ->where('active', '=', 'Y')
                    ->get();
    }
}
