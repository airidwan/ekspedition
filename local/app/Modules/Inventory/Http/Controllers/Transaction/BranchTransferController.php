<?php

namespace App\Modules\Inventory\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Inventory\Model\Transaction\BranchTransferHeader;
use App\Modules\Inventory\Model\Transaction\BranchTransferLine;
use App\Modules\Inventory\Model\Transaction\WarehouseTransferHeader;
use App\Modules\Inventory\Model\Transaction\MoveOrderHeader;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Modules\Generalledger\Model\Master\SettingJournal;
use App\Modules\Generalledger\Model\Master\MasterAccountCombination;
use App\Modules\Generalledger\Service\Master\AccountCombinationService;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Operational\Service\Master\DriverService;
use App\Modules\Operational\Service\Master\TruckService;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Inventory\Model\Master\MasterWarehouse;
use App\Modules\Inventory\Model\Master\MasterStock;
use App\Modules\Inventory\Model\Master\MasterItem;
use App\Service\Penomoran;
use App\Notification;
use App\Service\NotificationService;
use App\Role;
use App\Modules\Inventory\Http\Controllers\Transaction\ReceiptBranchTransferController;

class BranchTransferController extends Controller
{
    const RESOURCE = 'Inventory\Transaction\BranchTransfer';
    const URL      = 'inventory/transaction/branch-transfer';

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
            $query = \DB::table('inv.trans_bt_header')
                    ->select('trans_bt_header.*', 'mst_driver.driver_code', 'mst_driver.driver_name', 'mst_truck.truck_code')
                    ->leftJoin('inv.trans_bt_line', 'trans_bt_line.bt_header_id', '=', 'trans_bt_header.bt_header_id')
                    ->leftJoin('inv.mst_warehouse as wh_from', 'wh_from.wh_id', '=', 'trans_bt_line.from_wh_id')
                    ->leftJoin('inv.mst_warehouse as wh_to', 'wh_to.wh_id', '=', 'trans_bt_line.to_wh_id')
                    ->leftJoin('inv.mst_item', 'mst_item.item_id', '=', 'trans_bt_line.item_id')
                    ->leftJoin('inv.mst_uom', 'mst_uom.uom_id', '=', 'mst_item.uom_id')
                    ->leftJoin('op.mst_driver', 'mst_driver.driver_id', '=', 'trans_bt_header.driver_id')
                    ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_bt_header.truck_id')
                    ->where('trans_bt_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('trans_bt_header.created_date', 'desc')
                    ->distinct();
        }else{
            $query = \DB::table('inv.trans_bt_line')
                    ->select(
                        'trans_bt_line.*',
                        'trans_bt_header.*', 
                        'mst_item.item_code',
                        'mst_item.description as item_name',
                        'wh_from.wh_code as wh_from_code',
                        'wh_to.wh_code as wh_to_code',
                        'mst_branch.branch_code as branch_to_code',
                        'mst_uom.uom_code'
                        )
                    ->leftJoin('inv.trans_bt_header', 'trans_bt_header.bt_header_id', '=', 'trans_bt_line.bt_header_id')
                    ->leftJoin('inv.mst_warehouse as wh_from', 'wh_from.wh_id', '=', 'trans_bt_line.from_wh_id')
                    ->leftJoin('inv.mst_warehouse as wh_to', 'wh_to.wh_id', '=', 'trans_bt_line.to_wh_id')
                    ->leftJoin('op.mst_branch', 'mst_branch.branch_id', '=', 'wh_to.branch_id')
                    ->leftJoin('inv.mst_item', 'mst_item.item_id', '=', 'trans_bt_line.item_id')
                    ->leftJoin('inv.mst_uom', 'mst_uom.uom_id', '=', 'mst_item.uom_id')
                    ->leftJoin('op.mst_driver', 'mst_driver.driver_id', '=', 'trans_bt_header.driver_id')
                    ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_bt_header.truck_id')
                    ->where('trans_bt_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('trans_bt_header.created_date', 'desc')
                    ->distinct();
        }
        

        if (!empty($filters['btNumber'])) {
            $query->where('trans_bt_header.bt_number', 'ilike', '%'.$filters['btNumber'].'%');
        }

        if (!empty($filters['pic'])) {
            $query->where('trans_bt_header.pic', 'ilike', '%'.$filters['pic'].'%');
        }

        if (!empty($filters['description'])) {
            $query->where('trans_bt_header.description', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['driverCode'])) {
            $query->where('mst_driver.driver_code', 'ilike', '%'.$filters['driverCode'].'%');
        }

        if (!empty($filters['truckCode'])) {
            $query->where('mst_truck.truck_code', 'ilike', '%'.$filters['truckCode'].'%');
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
            $query->where('trans_bt_header.status', '=', $filters['status']);
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = new \DateTime($filters['dateFrom']);
            $query->where('trans_bt_header.created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = new \DateTime($filters['dateTo']);
            $query->where('trans_bt_header.created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        return view('inventory::transaction.branch-transfer.index', [
            'models'          => $query->paginate(10),
            'filters'         => $filters,
            'optionStatus'    => $this->getOptionsStatus(),
            'optionWhFrom'    => \DB::table('inv.mst_warehouse')->where('branch_id', '=', \Session::get('currentBranch')->branch_id)->get(),
            'optionWhTo'      => \DB::table('inv.mst_warehouse')->where('branch_id', '!=', \Session::get('currentBranch')->branch_id)->get(),
            'resource'        => self::RESOURCE,
            'url'             => self::URL,
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new BranchTransferHeader();
        $model->status = BranchTransferHeader::INCOMPLETE;

        return view('inventory::transaction.branch-transfer.add', [
            'title'           => trans('shared/common.add'),
            'model'           => $model,
            'optionWarehouse' => $this->getOptionsWarehouse(),
            'optionStatus'    => $this->getOptionsStatus(),
            'optionItem'      => $this->getOptionsItem(),
            'optionCoa'       => $this->getOptionsCoa(),
            'optionTruck'     => TruckService::getActiveTruck(), 
            'optionDriver'    => DriverService::getActiveDriverAsistant(),
            'resource'        => self::RESOURCE,
            'url'             => self::URL,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = BranchTransferHeader::where('bt_header_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        return view('inventory::transaction.branch-transfer.add', [
            'title'           => trans('shared/common.edit'),
            'model'           => $model,
            'optionStatus'    => $this->getOptionsStatus(),
            'optionWarehouse' => $this->getOptionsWarehouse(),
            'optionItem'      => $this->getOptionsItem(),
            'optionCoa'       => $this->getOptionsCoa(),
            'optionTruck'     => TruckService::getActiveTruck(), 
            'optionDriver'    => DriverService::getActiveDriverAsistant(),
            'resource'        => self::RESOURCE,
            'url'             => self::URL,
        ]);
    }

    public function save(Request $request)
    {
        // var_dump($request->all());exit();
        $id = intval($request->get('id'));
        $model = !empty($id) ? BranchTransferHeader::where('bt_header_id', '=', $id)->first() : new BranchTransferHeader();

        $this->validate($request, [
            'pic'         => 'required',
            'description' => 'required',
            'driverName'  => 'required',
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
            $btExist  = $this->BranchTransferExist($id, $itemId, $request->get('fromWhId')[$i]);
            $moExist  = $this->getMoExist($itemId, $request->get('fromWhId')[$i]);         
            $wtExist  = $this->getWtExist($itemId, $request->get('fromWhId')[$i]);     
            $max      = intval($qtyStock->stock - $btExist->qty_need - $wtExist->qty_need - $moExist->qty_need);
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

        $model->description = $request->get('description');
        $model->pic         = $request->get('pic');
        $model->driver_id   = intval($request->get('driverId'));
        if(!empty($request->get('truckId'))){
            $model->truck_id    = $request->get('truckId');
        }

        $now = new \DateTime();
        if (empty($id)) {
            $model->status       = BranchTransferHeader::INCOMPLETE;
            $model->bt_number   = $this->BranchTransferNumber($model);
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
            $line = new BranchTransferLine();
            $line->bt_header_id = $model->bt_header_id;
            $line->item_id       = $request->get('itemId')[$i];
            $line->from_wh_id    = $request->get('fromWhId')[$i];
            $line->to_wh_id      = $request->get('toWhId')[$i];
            $line->qty_need      = $request->get('qtyNeed')[$i];
            $line->qty_remain    = $request->get('qtyNeed')[$i];
            $line->description   = $request->get('note')[$i];
            $line->created_date  = new \DateTime();
            $line->created_by    = \Auth::user()->id;

            $defaultJournalAccount = SettingJournal::where('setting_name', '=', SettingJournal::INTRANSIT_INVENTORY)->first();
            $defaultAccount        = $defaultJournalAccount->coa;
            $accountCode           = $defaultAccount->coa_code;

            $accountCombination  = AccountCombinationService::getCombination($accountCode);

            $line->account_comb_id = $accountCombination->account_combination_id;

            try {
                $line->save();
            } catch (\Exception $e) {
                return redirect(self::URL.'/edit/'.$model->bt_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            if ($request->get('btn-transact') !== null) {
                // insert journal
                $journalHeader           = new JournalHeader();
                $journalHeader->category = JournalHeader::BRANCH_TRANSFER_TRANSACT;
                $journalHeader->status   = JournalHeader::OPEN;

                $now                    = new \DateTime();
                $period                 = new \DateTime($now->format('Y-m-1'));
                $journalHeader->period  = $period;

                $journalHeader->description = $model->bt_number;
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

                $itemStock = MasterStock::where('item_id', '=', $line->item_id)->where('wh_id', '=', $line->from_wh_id)->first();

                // insert journal line debit
                $journalLine      = new JournalLine();
                $accountCombination = AccountCombinationService::getCombination($accountCode);

                $journalLine->journal_header_id      = $journalHeader->journal_header_id;
                $journalLine->account_combination_id = $accountCombination->account_combination_id;
                $journalLine->debet                  = $itemStock->average_cost * $line->qty_need;
                $journalLine->credit                 = 0;
                $journalLine->description            = 'Account Intransit';

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

                $accountCombination = AccountCombinationService::getCombination($accountCode);

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
            $model->status = BranchTransferHeader::INPROCESS;
            for ($i = 0; $i < count($request->get('lineId')); $i++) {
                $stockSender = MasterStock::where('item_id', '=', $request->get('itemId')[$i])
                                        ->where('wh_id', '=', $request->get('fromWhId')[$i])
                                        ->first();
                $stockSender->stock -= $request->get('qtyNeed')[$i];
                $stockSender->save();

            }
            try {
                    $model->save();
                } catch (\Exception $e) {
                    return redirect(self::URL.'/edit/'.$model->bt_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }

            $warehouse = MasterWarehouse::find($line->to_wh_id);
            $userNotif = NotificationService::getUserNotificationSpesificBranch([Role::WAREHOUSE_ADMIN], $warehouse->branch->branch_id);
            foreach ($userNotif as $user) {
                $notif             = new Notification();
                $notif->branch_id  = $warehouse->branch->branch_id;
                $notif->created_at = new \DateTime();
                $notif->url        = ReceiptBranchTransferController::URL;
                $notif->user_id    = $user->id;
                $notif->role_id    = $user->role_id;
                $notif->category   = 'Branch Transfer Transact';
                $notif->message    = 'Branch Transfer '.$model->bt_number. ' from ' .\Session::get('currentBranch')->branch_code.'.';
                $notif->save();
            }
        }
        
        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('inventory/menu.branch-transfer').' '.$model->bt_number])
        );

        return redirect(self::URL);
    }

    public function close(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'close'])) {
            abort(403);
        }

        $model = BranchTransferHeader::find($request->get('id'));
        if ($model === null || !$model->isInprocess()) {
            abort(404);
        }
       
        $model->status       = BranchTransferHeader::CLOSED_WARNING;
        $model->description .= ' (Close warning reason is '. $request->get('reasonClose', '').')';
        $model->save();

        $userNotif = NotificationService::getUserNotification([Role::BRANCH_MANAGER, Role::WAREHOUSE_MANAGER]);
        foreach ($userNotif as $user) {
            $notif             = new Notification();
            $notif->branch_id  = \Session::get('currentBranch')->branch_id;
            $notif->category   = 'Branch Transfer Force Close';
            $notif->message    = 'Branch Transfer Force Close '.$model->bt_number. '. ' . $request->get('reasonClose', '');
            // $notif->url        = self::URL.'/edit/'.$model->bt_header_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->save();
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.close-message', ['variable' => trans('inventory/menu.branch-transfer').' '.$model->manifest_number])
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
        $model   = BranchTransferHeader::find($id);

        $header = view('print.header-pdf', ['title' => trans('inventory/menu.branch-transfer')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('inventory::transaction.branch-transfer.print-pdf-detail', [
            'model'  => $model,
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle('Branch Transfer');
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A5');
        \PDF::writeHTML($html);
        \PDF::Output($model->bt_number.'.pdf');
        \PDF::reset();
    }

    public function cancelBt(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'cancel'])) {
            abort(403);
        }

        $model = BranchTransferHeader::find($request->get('id'));
        if ($model === null) {
            abort(404);
        }

        $model->status = BranchTransferHeader::CANCELED;
        $model->description = $model->description .'. Canceled reason : '.$request->get('reason');   
        $model->last_updated_date = new \DateTime();
        $model->last_updated_by   = \Auth::user()->id;

        $model->save();

        $userNotif = NotificationService::getUserNotification([Role::BRANCH_MANAGER, Role::WAREHOUSE_MANAGER ]);
        foreach ($userNotif as $user) {
            $notif             = new Notification();
            $notif->branch_id  = \Session::get('currentBranch')->branch_id;
            // $notif->url        = self::URL.'/edit/'.$model->bt_header_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->category   = 'Canceled Branch Transfer';
            $notif->message    = 'Canceled Branch Transfer'.$model->bt_number. '. ' . $request->get('reason', '');
            $notif->save();
        }

        $request->session()->flash(
            'successMessage',
            trans('purchasing/fields.canceled-message', ['variable' => trans('inventory/menu.branch-transfer').' '.$model->bt_number])
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

    protected function BranchTransferExist($headerId, $itemId, $whId){

        return \DB::table('inv.trans_bt_line')
                    ->selectRaw('sum(qty_need) as qty_need')
                    ->join('inv.trans_bt_header', 'trans_bt_header.bt_header_id', '=', 'trans_bt_line.bt_header_id')
                    ->where('trans_bt_header.bt_header_id', '!=', $headerId)
                    ->where('item_id', '=', $itemId)
                    ->where('from_wh_id', '=', $whId)
                    ->where('trans_bt_header.status', '=', BranchTransferHeader::INCOMPLETE)
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

    protected function BranchTransferNumber(BranchTransferHeader $model)
    {
        $branch      = MasterBranch::find(\Session::get('currentBranch')->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('inv.trans_bt_header')
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->where('branch_id', '=', $branch->branch_id)
                            ->count();

        return 'BT.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }

    protected function getOptionsStatus()
    {
        return [
            BranchTransferHeader::INCOMPLETE,
            BranchTransferHeader::INPROCESS,
            BranchTransferHeader::COMPLETE,
            BranchTransferHeader::CANCELED,
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
                    ->select('mst_warehouse.*', 'mst_branch.branch_code', 'mst_branch.branch_name')
                    ->join('op.mst_branch', 'mst_branch.branch_id', '=', 'mst_warehouse.branch_id')
                    ->where('mst_warehouse.branch_id', '!=', \Session::get('currentBranch')->branch_id)
                    ->where('mst_warehouse.active', '=', 'Y')
                    ->distinct()
                    ->get();
    }
}
