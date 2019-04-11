<?php

namespace App\Modules\Inventory\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Inventory\Model\Transaction\WarehouseTransferHeader;
use App\Modules\Inventory\Model\Transaction\WarehouseTransferLine;
use App\Modules\Inventory\Model\Transaction\BranchTransferHeader;
use App\Modules\Inventory\Model\Transaction\MoveOrderHeader;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Modules\Generalledger\Model\Master\MasterAccountCombination;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Inventory\Model\Master\MasterStock;
use App\Service\Penomoran;
use App\Notification;
use App\Service\NotificationService;
use App\Role;


class WarehouseTransferController extends Controller
{
    const RESOURCE = 'Inventory\Transaction\WarehouseTransfer';
    const URL      = 'inventory/transaction/warehouse-transfer';

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
            $query = \DB::table('inv.trans_wht_header')
                    ->select('trans_wht_header.*')
                    ->leftJoin('inv.trans_wht_line', 'trans_wht_line.wht_header_id', '=', 'trans_wht_header.wht_header_id')
                    ->leftJoin('inv.mst_warehouse as wh_from', 'wh_from.wh_id', '=', 'trans_wht_line.from_wh_id')
                    ->leftJoin('inv.mst_warehouse as wh_to', 'wh_to.wh_id', '=', 'trans_wht_line.to_wh_id')
                    ->leftJoin('inv.mst_item', 'mst_item.item_id', '=', 'trans_wht_line.item_id')
                    ->leftJoin('inv.mst_uom', 'mst_uom.uom_id', '=', 'mst_item.uom_id')
                    ->where('trans_wht_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('trans_wht_header.created_date', 'desc')
                    ->distinct();
        }else{
            $query = \DB::table('inv.trans_wht_line')
                    ->select(
                        'trans_wht_line.*',
                        'trans_wht_header.wht_number',
                        'trans_wht_header.status',
                        'mst_item.item_code',
                        'mst_item.description as item_name',
                        'wh_from.wh_code as wh_from_code',
                        'wh_to.wh_code as wh_to_code',
                        'mst_uom.uom_code'
                        )
                    ->leftJoin('inv.trans_wht_header', 'trans_wht_header.wht_header_id', '=', 'trans_wht_line.wht_header_id')
                    ->leftJoin('inv.mst_warehouse as wh_from', 'wh_from.wh_id', '=', 'trans_wht_line.from_wh_id')
                    ->leftJoin('inv.mst_warehouse as wh_to', 'wh_to.wh_id', '=', 'trans_wht_line.to_wh_id')
                    ->leftJoin('inv.mst_item', 'mst_item.item_id', '=', 'trans_wht_line.item_id')
                    ->leftJoin('inv.mst_uom', 'mst_uom.uom_id', '=', 'mst_item.uom_id')
                    ->where('trans_wht_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('trans_wht_header.created_date', 'desc');
        }

        if (!empty($filters['whtNumber'])) {
            $query->where('trans_wht_header.wht_number', 'ilike', '%'.$filters['whtNumber'].'%');
        }

        if (!empty($filters['pic'])) {
            $query->where('trans_wht_header.pic', 'ilike', '%'.$filters['pic'].'%');
        }

        if (!empty($filters['description'])) {
            $query->where('trans_wht_header.description', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['itemCode'])) {
            $query->where('mst_item.item_code', 'ilike', '%'.$filters['itemCode'].'%');
        }

        if (!empty($filters['itemName'])) {
            $query->where('mst_item.description', 'ilike', '%'.$filters['itemName'].'%');
        }

        if (!empty($filters['fromWarehouse'])) {
            $query->where('wh_from.wh_id', '=', $filters['fromWarehouse']);
        }

        if (!empty($filters['toWarehouse'])) {
            $query->where('wh_to.wh_id', '=', $filters['toWarehouse']);
        }

        if (!empty($filters['status'])) {
            $query->where('trans_wht_header.status', '=', $filters['status']);
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = new \DateTime($filters['dateFrom']);
            $query->where('trans_wht_header.created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = new \DateTime($filters['dateTo']);
            $query->where('trans_wht_header.created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        return view('inventory::transaction.warehouse-transfer.index', [
            'models'          => $query->paginate(10),
            'filters'         => $filters,
            'optionStatus'    => $this->getOptionsStatus(),
            'optionWarehouse' => $this->getOptionsWarehouse(),
            'resource'        => self::RESOURCE,
            'url'             => self::URL,
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new WarehouseTransferHeader();
        $model->status = WarehouseTransferHeader::INCOMPLETE;

        return view('inventory::transaction.warehouse-transfer.add', [
            'title'           => trans('shared/common.add'),
            'model'           => $model,
            'optionStatus'    => $this->getOptionsStatus(),
            'optionWarehouse' => $this->getOptionsWarehouse(),
            'optionItem'      => $this->getOptionsItem(),
            'optionCoa'       => $this->getOptionsCoa(),
            'resource'        => self::RESOURCE,
            'url'             => self::URL,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = WarehouseTransferHeader::where('wht_header_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        return view('inventory::transaction.warehouse-transfer.add', [
            'title'           => trans('shared/common.edit'),
            'model'           => $model,
            'optionStatus'    => $this->getOptionsStatus(),
            'optionWarehouse' => $this->getOptionsWarehouse(),
            'optionItem'      => $this->getOptionsItem(),
            'optionCoa'       => $this->getOptionsCoa(),
            'resource'        => self::RESOURCE,
            'url'             => self::URL,
        ]);
    }

    public function save(Request $request)
    {
        // var_dump($request->all());exit();
        $id = intval($request->get('id'));
        $model = !empty($id) ? WarehouseTransferHeader::where('wht_header_id', '=', $id)->first() : new WarehouseTransferHeader();

        $this->validate($request, [
            'pic'         => 'required',
            'description' => 'required',
        ]);

        if (empty($request->get('itemId'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Lines are required']);
        }

        $message = [];
        $itemIds = $request->get('itemId');
        foreach ($request->get('itemId') as $itemId) {
            $i = array_search($itemId, $itemIds);
            $qtyForm  = intval(str_replace(',', '', $request->get('qtyNeed')[$i]));
            $qtyStock = $this->getStock($itemId, $request->get('fromWhId')[$i]);       
            $wtExist  = $this->getWarehouseTransferExist($id, $itemId, $request->get('fromWhId')[$i]);
            $btExist  = $this->getBtExist($itemId, $request->get('fromWhId')[$i]);
            $moExist  = $this->getMoExist($itemId, $request->get('fromWhId')[$i]);         
            $max      = intval($qtyStock->stock - $wtExist->qty_need - $btExist->qty_need - $moExist->qty_need);
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

        $model->pic         = $request->get('pic');
        $model->description = $request->get('description');

        $now = new \DateTime();
        if (empty($id)) {
            $model->status       = WarehouseTransferHeader::INCOMPLETE;
            $model->wht_number   = $this->getWarehouseTransferNumber($model);
            $model->created_date = $now;
            $model->created_by   = \Auth::user()->id;
        } else {
            $model->last_updated_date = $now;
            $model->last_updated_by   = \Auth::user()->id;
        }

        try {
            $model->save();            
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $lineIds = $request->get('lineId');
        $lines = $model->lines()->delete();

        for ($i = 0; $i < count($request->get('lineId')); $i++) {
            $line = new WarehouseTransferLine();
            $line->wht_header_id = $model->wht_header_id;
            $line->item_id       = $request->get('itemId')[$i];
            $line->from_wh_id    = $request->get('fromWhId')[$i];
            $line->to_wh_id      = $request->get('toWhId')[$i];
            $line->qty_need      = $request->get('qtyNeed')[$i];
            $line->description   = $request->get('note')[$i];
            $line->created_date  = new \DateTime();
            $line->created_by    = \Auth::user()->id;

            try {
                $line->save();
            } catch (\Exception $e) {
                return redirect(self::URL.'/edit/'.$model->wht_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }
        }

        if ($request->get('btn-transact') !== null) {
            for ($i = 0; $i < count($request->get('lineId')); $i++) {
                $stockSender = MasterStock::where('item_id', '=', $request->get('itemId')[$i])
                                        ->where('wh_id', '=', $request->get('fromWhId')[$i])
                                        ->first();

                $stockReceiver = MasterStock::where('item_id', '=', $request->get('itemId')[$i])
                                        ->where('wh_id', '=', $request->get('toWhId')[$i])
                                        ->first();
                if (empty($stockReceiver)) {
                    $stockReceiver = new MasterStock;
                    $stockReceiver->item_id      = $request->get('itemId')[$i];
                    $stockReceiver->wh_id        = $request->get('toWhId')[$i];
                    $stockReceiver->stock        = $request->get('qtyNeed')[$i];
                    $stockReceiver->average_cost = $stockSender->average_cost;
                    $stockReceiver->created_date = $now;
                    $stockReceiver->created_by   = \Auth::user()->id;
                }else{
                    if (empty($stockReceiver->average_cost)) {
                        $stockReceiver->average_cost = $modelSender->average_cost;
                    }else{
                        $stockReceiver->average_cost = (($stockReceiver->average_cost * $stockReceiver->stock) + ($stockSender->average_cost * $request->get('qtyNeed')[$i])) / ($stockReceiver->stock + $request->get('qtyNeed')[$i]); 
                    }
                    $stockReceiver->stock += $request->get('qtyNeed')[$i];
                    $stockReceiver->last_updated_date = $now;
                    $stockReceiver->last_updated_by   = \Auth::user()->id;
                }

                $stockSender->stock -= $request->get('qtyNeed')[$i];
                $stockSender->save();
                $stockReceiver->save();
            }
            $model->status = WarehouseTransferHeader::COMPLETE;
            try {
                    $model->save();
                } catch (\Exception $e) {
                    return redirect(self::URL.'/edit/'.$model->wht_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }
        }
        
        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('inventory/menu.warehouse-transfer').' '.$model->wht_number])
        );

        return redirect(self::URL);
    }

     public function cancelWt(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'cancel'])) {
            abort(403);
        }

        $model = WarehouseTransferHeader::find($request->get('id'));
        if ($model === null) {
            abort(404);
        }

        $model->status = WarehouseTransferHeader::CANCELED;
        $model->description = $model->description .'. Canceled reason : '.$request->get('reason');   
        $model->last_updated_date = new \DateTime();
        $model->last_updated_by   = \Auth::user()->id;

        $model->save();

        $userNotif = NotificationService::getUserNotification([Role::BRANCH_MANAGER]);
        foreach ($userNotif as $user) {
            $notif             = new Notification();
            $notif->branch_id  = \Session::get('currentBranch')->branch_id;
            $notif->url        = self::URL.'/edit/'.$model->wht_header_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->category   = 'Canceled Warehouse Transfer';
            $notif->message    = 'Canceled Warehouse Transfer '.$model->wht_number. '. ' . $request->get('reason', '');
            $notif->save();
        }

        $request->session()->flash(
            'successMessage',
            trans('purchasing/fields.canceled-message', ['variable' => trans('inventory/menu.warehouse-transfer').' '.$model->wht_number])
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

    protected function getWarehouseTransferExist($headerId, $itemId, $whId){

        return \DB::table('inv.trans_wht_line')
                    ->selectRaw('sum(qty_need) as qty_need')
                    ->join('inv.trans_wht_header', 'trans_wht_header.wht_header_id', '=', 'trans_wht_line.wht_header_id')
                    ->where('trans_wht_header.wht_header_id', '!=', $headerId)
                    ->where('item_id', '=', $itemId)
                    ->where('from_wh_id', '=', $whId)
                    ->where('trans_wht_header.status', '=', WarehouseTransferHeader::INCOMPLETE)
                    ->first();
    }

    protected function getMoExist($itemId, $whId){

        return \DB::table('inv.trans_mo_line')
                    ->selectRaw('sum(qty_need) as qty_need')
                    ->join('inv.trans_mo_header', 'trans_mo_header.mo_header_id', '=', 'trans_mo_line.mo_header_id')
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

    protected function getCoa($coaCode, $segmentName){
        $coa = MasterCoa::where('coa_code', '=', $coaCode)->where('segment_name', '=', $segmentName)->first();
        return [
            'coaId'             => $coa->coa_id,
            'coaDescription'    => $coa->description,
        ];
    }

    protected function getWarehouseTransferNumber(WarehouseTransferHeader $model)
    {
        $branch      = MasterBranch::find(\Session::get('currentBranch')->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('inv.trans_wht_header')
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->where('branch_id', '=', $branch->branch_id)
                            ->count();

        return 'WHT.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }

    protected function getOptionsStatus()
    {
        return [
            WarehouseTransferHeader::INCOMPLETE,
            WarehouseTransferHeader::COMPLETE,
            WarehouseTransferHeader::CANCELED,
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
                    ->where('active', '=', 'Y')
                    ->get();
    }

    protected function getOptionsWarehouse(){
        return \DB::table('inv.mst_warehouse')
                    ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where('active', '=', 'Y')
                    ->get();
    }
}
