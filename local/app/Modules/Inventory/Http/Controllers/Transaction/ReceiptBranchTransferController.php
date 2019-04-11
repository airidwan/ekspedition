<?php

namespace App\Modules\Inventory\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Inventory\Model\Transaction\ReceiptBranchTransferHeader;
use App\Modules\Inventory\Model\Transaction\ReceiptBranchTransferLine;
use App\Modules\Inventory\Model\Master\MasterItem;
use App\Modules\Inventory\Model\Master\MasterCategory;
use App\Modules\Inventory\Model\Master\MasterStock;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Asset\Model\Master\MasterAsset;
use App\Modules\Inventory\Model\Transaction\BranchTransferHeader;
use App\Modules\Inventory\Model\Transaction\BranchTransferLine;
use App\Modules\Inventory\Service\Transaction\BranchTransferService;
use App\Modules\Generalledger\Model\Master\SettingJournal;
use App\Modules\Generalledger\Model\Master\MasterAccountCombination;
use App\Modules\Generalledger\Service\Master\AccountCombinationService;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Service\NotificationService;
use App\Modules\Inventory\Http\Controllers\Transaction\BranchTransferController;
use App\Service\Penomoran;
use App\Notification;
use App\Role;

class ReceiptBranchTransferController extends Controller
{
    const RESOURCE = 'Inventory\Transaction\ReceiptBranchTransfer';
    const URL      = 'inventory/transaction/receipt-branch-transfer';

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
            return redirect(self::URL.'?page=1');
        } elseif (empty($request->get('page'))) {
            $request->session()->forget('filters');
        }

        $filters = $request->session()->get('filters');

        if (empty($filters['jenis']) || $filters['jenis'] == 'headers') {
            $query   = \DB::table('inv.v_trans_receipt_bt_header')
                            ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->orderBy('receipt_bt_number', 'desc');
        }else{
            $query   = \DB::table('inv.v_trans_receipt_bt_line')
                            ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->orderBy('receipt_bt_number', 'desc');
        }

        if (!empty($filters['branchTransferNumber'])) {
            $query->where('bt_number', 'ilike', '%'.$filters['branchTransferNumber'].'%');
        }

        if (!empty($filters['description']) && $filters['jenis'] == 'headers') {
            $query->where('description_bt', 'ilike', '%'.$filters['description'].'%');
        }
        if (!empty($filters['description']) && $filters['jenis'] == 'lines') {
            $query->where('description_item', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['receiptNumber'])) {
            $query->where('receipt_bt_number', 'ilike', '%'.$filters['receiptNumber'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = new \DateTime($filters['dateFrom']);
            $query->where('created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = new \DateTime($filters['dateTo']);
            $query->where('created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        return view('inventory::transaction.receipt-branch-transfer.index', [
            'models'          => $query->paginate(10),
            'filters'         => $filters,
            'optionWarehouse' => \DB::table('inv.v_mst_warehouse')->where('branch_id', '=', \Session::get('currentBranch')->branch_id)->get(),
            'resource'        => self::RESOURCE,
            'url'             => self::URL,
            ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new ReceiptBranchTransferLine();

        return view('inventory::transaction.receipt-branch-transfer.add', [
            'title'            => trans('shared/common.add'),
            'model'            => $model,
            'url'              => self::URL,
            'resource'         => self::RESOURCE,
            'optionBT'         => \DB::table('inv.trans_bt_header')
                                    ->select('trans_bt_header.*', 'mst_driver.driver_name', 'mst_truck.truck_code', 'mst_truck.police_number')
                                    ->join('inv.trans_bt_line', 'trans_bt_line.bt_header_id', '=', 'trans_bt_header.bt_header_id')
                                    ->leftJoin('op.mst_driver', 'mst_driver.driver_id', '=', 'trans_bt_header.driver_id')
                                    ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_bt_header.truck_id')
                                    ->join('inv.mst_warehouse','mst_warehouse.wh_id', '=', 'trans_bt_line.to_wh_id')
                                    ->join('op.mst_branch', 'mst_branch.branch_id', '=', 'mst_warehouse.branch_id')
                                    ->where('mst_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)                                    
                                    ->where('trans_bt_line.qty_remain', '<>', 0)
                                    ->where('trans_bt_header.status', '=', BranchTransferHeader::INPROCESS)
                                    ->distinct()
                                    ->get(),
            'optionWarehouse'  => \DB::table('inv.v_mst_warehouse')
                                    ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                                    ->get(),
            ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = ReceiptBranchTransferHeader::find($id);

        if ($model === null) {
            abort(404);
        }
        return view('inventory::transaction.receipt-branch-transfer.add', [
            'title'            => trans('shared/common.edit'),
            'model'            => $model,
            'url'              => self::URL,
            'resource'         => self::RESOURCE,
            'optionBT'         => [],
            'optionWarehouse'  => [],
            ]);
    }

    public function save(Request $request)
    {
        // var_dump($request->all());exit();
        $id    = intval($request->get('id'));

        $this->validate($request, [
            'btHeaderId' => 'required',
            ]);

        if (empty($request->get('btLineId'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'You must choose minimal of one item']);
        }
        $modelBT = BranchTransferHeader::where('bt_header_id', '=', $request->get('btHeaderId'))->first();

        $opr = empty($id) ? 'I' : 'U';

        $now = new \DateTime();
        $modelHeader   = new ReceiptBranchTransferHeader();
        $modelHeader->receipt_bt_number = $this->getBranchTransferNumber($modelHeader);
        $modelHeader->branch_id         = \Session::get('currentBranch')->branch_id;
        $modelHeader->receipt_bt_date   = $now;
        $modelHeader->bt_header_id      = $modelBT->bt_header_id;
        $modelHeader->created_date      = $now;
        $modelHeader->created_by        = \Auth::user()->id;
        $modelHeader->save();

        foreach ($request->get('btLineId') as $btLineId) {
            $whId            = 'whId-'.$btLineId;
            $receiptQuantity = 'receiptQuantity-'.$btLineId;
            $categoryCode    = 'categoryCode-'.$btLineId;
            $quantity        = 'quantity-'.$btLineId;
            $descriptionLine = 'descriptionLine-'.$btLineId;

            $modelLine = new ReceiptBranchTransferLine();
            $modelLine->bt_line_id           = $btLineId;
            $modelLine->receipt_bt_header_id = $modelHeader->receipt_bt_header_id;
            $modelLine->wh_id                = $request->get($whId);
            $modelLine->receipt_bt_quantity  = intval($request->get($receiptQuantity));
            $modelLine->description          = $request->get($descriptionLine);

            if ($opr == 'I') {
                $modelLine->created_date = $now;
                $modelLine->created_by   = \Auth::user()->id;
            }else{
                $modelLine->last_updated_date = $now;
                $modelLine->last_updated_by   = \Auth::user()->id;
            }

            try {
                $modelLine->save();
            } catch (\Exception $e) {
                return redirect(self::URL)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            $btLine        = BranchTransferLine::where('bt_line_id', '=', $btLineId)->first();
            
            $check         = $this->checkExistStock($btLine->item_id, $request->get($whId));
            $stockReceiver = $check ? MasterStock::where('item_id', '=', $btLine->item_id)->where('wh_id', '=', $request->get($whId))->first() : new MasterStock();

            $stockReceiver->wh_id   = $request->get($whId);
            $stockReceiver->item_id = $btLine->item_id;

            $stockSender   = MasterStock::where('item_id', '=', $btLine->item_id)->where('wh_id', '=', $btLine->from_wh_id)->first();
            if (empty($stockReceiver->average_cost)) {
                $stockReceiver->average_cost = $stockSender->average_cost;
            }else{
                $stockReceiver->average_cost = (($stockReceiver->average_cost * $stockReceiver->stock) + ($stockSender->average_cost * $modelLine->receipt_bt_quantity)) / ($stockReceiver->stock + $modelLine->receipt_bt_quantity);
            }

            if ($check) {
                $stockReceiver->stock = $stockReceiver->stock + $modelLine->receipt_bt_quantity;
                $stockReceiver->last_updated_date = $now;
                $stockReceiver->last_updated_by   = \Auth::user()->id;
            }else{
                $stockReceiver->stock = $modelLine->receipt_bt_quantity;
                $stockReceiver->created_date = $now;
                $stockReceiver->created_by   = \Auth::user()->id;
            }
            
            // $stockSender->stock = $stockSender->stock - $modelLine->receipt_bt_quantity;
            // $stockSender->save();
            $stockReceiver->save();

            $quantityRemain = $btLine->qty_remain;
            $btLine->qty_remain = $quantityRemain - intval($request->get($receiptQuantity));
            $btLine->save();

            // insert journal
            $journalHeader           = new JournalHeader();
            $journalHeader->category = JournalHeader::BRANCH_TRANSFER_RECEIPT;
            $journalHeader->status   = JournalHeader::OPEN;

            $now                    = new \DateTime();
            $period                 = new \DateTime($now->format('Y-m-1'));
            $journalHeader->period  = $period;

            $journalHeader->description = $modelHeader->receipt_bt_number;
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

            // insert journal line debit
            $journalLine      = new JournalLine();
            $modelItem     = MasterItem::find($btLine->item_id);
            $modelCategory = $modelItem->category;
            $account       = $modelCategory->coa;
            $accountCode   = $account->coa_code;

            $accountCombination = AccountCombinationService::getCombination($accountCode);

            $journalLine->journal_header_id      = $journalHeader->journal_header_id;
            $journalLine->account_combination_id = $accountCombination->account_combination_id;
            $journalLine->debet                  = $stockSender->average_cost * $modelLine->receipt_bt_quantity;
            $journalLine->credit                 = 0;
            $journalLine->description            = $modelItem->description;

            $journalLine->created_date = $now;
            $journalLine->created_by = \Auth::user()->id;

            try {
                $journalLine->save();
            } catch (\Exception $e) {
                return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            // insert journal line credit
            $journalLine   = new JournalLine();
            

            $defaultJournalAccount = SettingJournal::where('setting_name', '=', SettingJournal::INTRANSIT_INVENTORY)->first();
            $defaultAccount        = $defaultJournalAccount->coa;
            $accountCode           = $defaultAccount->coa_code;

            $accountCombination = AccountCombinationService::getCombination($accountCode);

            $journalLine->journal_header_id      = $journalHeader->journal_header_id;
            $journalLine->account_combination_id = $accountCombination->account_combination_id;
            $journalLine->debet                  = 0;
            $journalLine->credit                 = $stockSender->average_cost * $modelLine->receipt_bt_quantity;
            $journalLine->description            = 'Account Intransit';

            $journalLine->created_date = $now;
            $journalLine->created_by = \Auth::user()->id;

            try {
                $journalLine->save();
            } catch (\Exception $e) {
                return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }
        }

        if ($this->checkBranchTransferComplete($modelBT->bt_header_id) ) {
            $modelBT->status = BranchTransferHeader::COMPLETE;
            $modelBT->save();
        }
        
        $userNotif = NotificationService::getUserNotificationSpesificBranch([Role::WAREHOUSE_ADMIN], $modelBT->branch_id);
        foreach ($userNotif as $user) {
            $notif             = new Notification();
            $notif->branch_id  = $modelBT->branch_id;
            $notif->category   = 'Receipt Branch Transfer';
            $notif->message    = 'Branch Transfer '.$modelBT->bt_number.' receipted on '.\Session::get('currentBranch')->branch_code;
            $notif->url        = BranchTransferController::URL.'/edit/'.$modelBT->bt_header_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->save();
        }

        $userNotif = NotificationService::getUserNotification([Role::WAREHOUSE_MANAGER]);
        foreach ($userNotif as $user) {
            $notif             = new Notification();
            $notif->branch_id  = \Session::get('currentBranch')->branch_id;
            $notif->category   = 'Receipt Branch Transfer';
            $notif->message    = 'Receipting Branch Transfer from '.$modelBT->bt_number;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->save();
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('inventory/menu.receipt-branch-transfer').' '.$request->get('branchTransferNumber')])
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

    protected function getBranchTransferNumber(ReceiptBranchTransferHeader $model)
    {
        $branch      = MasterBranch::find(\Session::get('currentBranch')->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('inv.trans_receipt_bt_header')
        ->where('branch_id', '=', $branch->branch_id)
        ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
        ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
        ->count();

        return 'RBT.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }

    function checkBranchTransferComplete($headerId){
        if (\DB::table('inv.trans_bt_header')
                ->join('inv.trans_bt_line', 'trans_bt_line.bt_header_id', '=', 'trans_bt_header.bt_header_id')
                ->where('trans_bt_header.bt_header_id', '=', $headerId)
                ->where('trans_bt_line.qty_remain', '>', 0)
                ->where('trans_bt_header.status', '=', BranchTransferHeader::INPROCESS)
                ->count() <= 0) {
            return true;
        }
        return false;
    }

    function checkExistStock($itemId, $whId){
        if (\DB::table('inv.v_mst_stock_item')
                ->where('v_mst_stock_item.item_id', '=', $itemId)
                ->where('v_mst_stock_item.wh_id', '=', $whId)
                ->count() > 0) {
            return true;
        }
        return false;
    }
}
