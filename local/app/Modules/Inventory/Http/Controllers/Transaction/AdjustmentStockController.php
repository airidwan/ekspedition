<?php

namespace App\Modules\Inventory\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Inventory\Model\Transaction\AdjustmentStockHeader;
use App\Modules\Inventory\Model\Transaction\AdjustmentStockLine;
use App\Modules\Inventory\Model\Transaction\MoveOrderHeader;
use App\Modules\Inventory\Model\Transaction\BranchTransferHeader;
use App\Modules\Inventory\Model\Transaction\WarehouseTransferHeader;
use App\Modules\Operational\Service\Transaction\OfficialReportService;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Modules\Generalledger\Model\Master\SettingJournal;
use App\Modules\Generalledger\Model\Master\MasterAccountCombination;
use App\Modules\Generalledger\Service\Master\AccountCombinationService;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Model\Transaction\OfficialReport;
use App\Modules\Inventory\Model\Master\MasterStock;
use App\Modules\Inventory\Model\Master\MasterItem;
use App\Modules\Inventory\Model\Master\MasterCategory;
use App\Service\Penomoran;
use App\Notification;
use App\Service\NotificationService;
use App\Role;

class AdjustmentStockController extends Controller
{
    const RESOURCE = 'Inventory\Transaction\AdjustmentStock';
    const URL      = 'inventory/transaction/adjustment-stock';

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
            $query = \DB::table('inv.trans_adjustment_header')
                        ->select('trans_adjustment_header.*', 'trans_official_report.official_report_number')
                        ->leftJoin('inv.trans_adjustment_line', 'trans_adjustment_line.adjustment_header_id', '=', 'trans_adjustment_header.adjustment_header_id')
                        ->leftJoin('inv.mst_item', 'mst_item.item_id', '=', 'trans_adjustment_line.item_id')
                        ->leftJoin('inv.mst_warehouse', 'mst_warehouse.wh_id', '=', 'trans_adjustment_line.wh_id')
                        ->leftJoin('op.trans_official_report', 'trans_official_report.official_report_id', '=', 'trans_adjustment_header.official_report_id')
                        ->where('trans_adjustment_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->orderBy('trans_adjustment_header.created_date', 'desc')
                        ->distinct();
        }else{
            $query = \DB::table('inv.trans_adjustment_line')
                        ->select(
                            'trans_adjustment_line.qty_adjustment', 
                            'trans_adjustment_line.description', 
                            'trans_adjustment_header.adjustment_header_id', 
                            'trans_adjustment_header.adjustment_number', 
                            'trans_adjustment_header.type', 
                            'mst_item.item_code',
                            'mst_item.description as item_name',
                            'mst_uom.uom_code',
                            'mst_warehouse.wh_code'
                            )
                        ->leftJoin('inv.trans_adjustment_header', 'trans_adjustment_header.adjustment_header_id', '=', 'trans_adjustment_line.adjustment_header_id')
                        ->leftJoin('op.trans_official_report', 'trans_official_report.official_report_id', '=', 'trans_adjustment_header.official_report_id')
                        ->leftJoin('inv.mst_item', 'mst_item.item_id', '=', 'trans_adjustment_line.item_id')
                        ->leftJoin('inv.mst_uom', 'mst_uom.uom_id', '=', 'mst_item.uom_id')
                        ->leftJoin('inv.mst_warehouse', 'mst_warehouse.wh_id', '=', 'trans_adjustment_line.wh_id')
                        ->where('trans_adjustment_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->orderBy('trans_adjustment_header.created_date', 'desc');
        }

        if (!empty($filters['adjustmentNumber'])) {
            $query->where('trans_adjustment_header.adjustment_number', 'ilike', '%'.$filters['adjustmentNumber'].'%');
        }

        if (!empty($filters['itemCode'])) {
            $query->where('mst_item.item_code', 'ilike', '%'.$filters['itemCode'].'%');
        }

        if (!empty($filters['itemName'])) {
            $query->where('mst_item.description', 'ilike', '%'.$filters['itemName'].'%');
        }

        if (!empty($filters['warehouse'])) {
            $query->where('mst_warehouse.wh_id', '=', $filters['warehouse']);
        }

        if (!empty($filters['status'])) {
            $query->where('trans_adjustment_header.status', '=', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('trans_adjustment_header.type', '=', $filters['type']);
        }

        if (!empty($filters['officialReportNumber'])) {
            $query->where('trans_official_report.official_report_number', 'ilike', '%'.$filters['officialReportNumber'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = new \DateTime($filters['dateFrom']);
            $query->where('trans_adjustment_header.created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = new \DateTime($filters['dateTo']);
            $query->where('trans_adjustment_header.created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        return view('inventory::transaction.adjustment-stock.index', [
            'models'       => $query->paginate(10),
            'filters'      => $filters,
            'optionStatus' => $this->getOptionsStatus(),
            'optionType'     => $this->getOptionsType(),
            'optionWarehouse' => \DB::table('inv.mst_warehouse')->where('branch_id', '=', \Session::get('currentBranch')->branch_id)->get(),
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new AdjustmentStockHeader();
        $model->status = AdjustmentStockHeader::INCOMPLETE;

        return view('inventory::transaction.adjustment-stock.add', [
            'title'          => trans('shared/common.add'),
            'model'          => $model,
            'optionStatus'   => $this->getOptionsStatus(),
            'optionType'     => $this->getOptionsType(),
            'optionWarehouse'=> \DB::table('inv.mst_warehouse')->where('branch_id', '=', \Session::get('currentBranch')->branch_id)->get(),
            'optionItem'     => $this->getOptionsItem(),
            'optionAllItem'  => $this->getOptionsAllItemStock(),
            'optionCoa'      => $this->getOptionsCoa(),
            'optionOfficial' => OfficialReportService::getAdjustmentOfficialReport(),
            'resource'       => self::RESOURCE,
            'url'            => self::URL,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = AdjustmentStockHeader::where('adjustment_header_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        return view('inventory::transaction.adjustment-stock.add', [
            'title'          => trans('shared/common.edit'),
            'model'          => $model,
            'optionStatus'   => $this->getOptionsStatus(),
            'optionType'     => $this->getOptionsType(),
            'optionItem'     => $this->getOptionsItem(),
            'optionAllItem'  => $this->getOptionsAllItemStock(),
            'optionWarehouse'=> \DB::table('inv.mst_warehouse')->where('branch_id', '=', \Session::get('currentBranch')->branch_id)->get(),
            'optionCoa'      => $this->getOptionsCoa(),
            'optionOfficial' => OfficialReportService::getAdjustmentOfficialReport(),
            'resource'       => self::RESOURCE,
            'url'            => self::URL,
        ]);
    }

    public function save(Request $request)
    {
        // var_dump($request->all());exit();
        $id = intval($request->get('id'));
        $model = !empty($id) ? AdjustmentStockHeader::where('adjustment_header_id', '=', $id)->first() : new AdjustmentStockHeader();

        $this->validate($request, [
            'officialReportId' => 'required',
            'description' => 'required',
            'type'        => 'required',
        ]);

        if (empty($request->get('itemId'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Lines are required']);
        }

        if ($request->get('type') == AdjustmentStockHeader::ADJUSTMENT_MIN) {
            $message = [];
            $itemIds = $request->get('itemId');
            foreach ($request->get('itemId') as $itemId) {
                $i = array_search($itemId, $itemIds);
                $qtyForm  = intval(str_replace(',', '', $request->get('qtyAdjustment')[$i]));
                $qtyStock = $this->getStock($itemId, $request->get('whId')[$i]);         
                $adjExist = $this->getAdjustmentExist($id, $itemId, $request->get('whId')[$i]);
                $moExist  = $this->getMoExist($itemId, $request->get('whId')[$i]);         
                $btExist  = $this->getBtExist($itemId, $request->get('whId')[$i]);         
                $wtExist  = $this->getWtExist($itemId, $request->get('whId')[$i]);         
                $max      = intval($qtyStock->stock - $adjExist->qty_adjustment - $moExist->qty_need - $btExist->qty_need - $wtExist->qty_need);
                if ($qtyForm > $max)  {
                    $message [] = $request->get('itemCode')[$i]. ' in '. $request->get('warehouse')[$i] .'  remain is '. number_format($max) .'.';
                }
            }
            if(!empty($message)){
                $string = '';
                foreach ($message as $mess) {
                    $string = $string.' '.$mess;
                }
                $stringMessage = 'Quantity exceed!'. $string. 'You must cancel Move Order / Branch Transfer/ Warehouse Transfer on Existing!';
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $stringMessage]);
            }
        }

        if (empty($model->branch_id)) {
            $model->branch_id = $request->session()->get('currentBranch')->branch_id;
        }

        $model->official_report_id = intval($request->get('officialReportId'));
        $model->type               = $request->get('type');
        $model->description        = $request->get('description');

        $now = new \DateTime();
        if (empty($id)) {
            $model->status            = AdjustmentStockHeader::INCOMPLETE;
            $model->adjustment_number = $this->getAdjustmentStockNumber($model);
            $model->created_date      = $now;
            $model->created_by        = \Auth::user()->id;
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
            $line = new AdjustmentStockLine();
            $line->adjustment_header_id = $model->adjustment_header_id;
            $line->item_id              = $request->get('itemId')[$i];
            $line->wh_id                = $request->get('whId')[$i];
            $line->qty_adjustment       = $request->get('qtyAdjustment')[$i];
            $line->description          = $request->get('note')[$i];
            $line->created_date         = new \DateTime();
            $line->created_by           = \Auth::user()->id;
            $line->price                = intval(str_replace(',', '', $request->get('price')[$i]));

            $defaultJournalAccount      = SettingJournal::where('setting_name', '=', SettingJournal::INVENTORY_ADJUSTMENT)->first();
            $defaultAccount             = $defaultJournalAccount->coa;
            $accountCode                = $defaultAccount->coa_code;
            $accountCombination         = AccountCombinationService::getCombination($accountCode);
            $line->account_comb_id      = $accountCombination->account_combination_id;

            try {
                $line->save();
            } catch (\Exception $e) {
                return redirect(self::URL.'/edit/'.$model->adjustment_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }
        }

        if ($request->get('btn-transact') !== null) {
            $model->status = AdjustmentStockHeader::COMPLETE;

            $modelOR = OfficialReport::find($model->official_report_id);
            $modelOR->status = OfficialReport::CLOSED;
            $modelOR->respon = $model->respon.' Adjustment Stock number '.$model->adjustment_number;
            $modelOR->respon_date = $now;
            $modelOR->respon_by   = \Auth::user()->id;

            for ($i = 0; $i < count($request->get('lineId')); $i++) {
                $check = $this->checkExistStock($request->get('itemId')[$i], $request->get('whId')[$i]);
                $itemStock = $check ? MasterStock::where('item_id', '=', $request->get('itemId')[$i])->where('wh_id', '=', $request->get('whId')[$i])->first() : new MasterStock();
                $itemStock->item_id = $request->get('itemId')[$i];
                $itemStock->wh_id   = $request->get('whId')[$i];
                
                if ($request->get('type') == AdjustmentStockHeader::ADJUSTMENT_MIN) {
                    $price = intval(str_replace(',', '', $request->get('price')[$i]));
                    $itemStock->stock -= $request->get('qtyAdjustment')[$i];

                    // insert journal
                    $journalHeader           = new JournalHeader();
                    $journalHeader->category = JournalHeader::ADJUSTMENT;
                    $journalHeader->status   = JournalHeader::OPEN;

                    $now                    = new \DateTime();
                    $period                 = new \DateTime($now->format('Y-m-1'));
                    $journalHeader->period  = $period;

                    $journalHeader->description = $model->adjustment_number;
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
                    $journalLine           = new JournalLine();
                    $journalLine->journal_header_id      = $journalHeader->journal_header_id;
                    $journalLine->account_combination_id = $line->account_comb_id;
                    $journalLine->debet                  = $request->get('qtyAdjustment')[$i] * $price;
                    $journalLine->credit                 = 0;
                    $journalLine->description            = 'Inventory Adjustment';

                    $journalLine->created_date = $now;
                    $journalLine->created_by = \Auth::user()->id;

                    try {
                        $journalLine->save();
                    } catch (\Exception $e) {
                        return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                    }

                    // insert journal line credit
                    $journalLine   = new JournalLine();
                    
                    $modelItem     = MasterItem::find($request->get('itemId')[$i]);
                    $modelCategory = $modelItem->category;
                    $account       = $modelCategory->coa;
                    $accountCode   = $account->coa_code;

                    $accountCombination = AccountCombinationService::getCombination($accountCode);

                    $journalLine->journal_header_id      = $journalHeader->journal_header_id;
                    $journalLine->account_combination_id = $accountCombination->account_combination_id;
                    $journalLine->debet                  = 0;
                    $journalLine->credit                 = $request->get('qtyAdjustment')[$i] * $price;
                    $journalLine->description            = $modelItem->description;

                    $journalLine->created_date = $now;
                    $journalLine->created_by = \Auth::user()->id;

                    try {
                        $journalLine->save();
                    } catch (\Exception $e) {
                        return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                    }

                }else{
                    $price = intval(str_replace(',', '', $request->get('price')[$i]));
                    if (empty($itemStock->average_cost)) {
                        $itemStock->average_cost = $price;
                    }else{
                        $itemStock->average_cost = (($itemStock->stock * $itemStock->average_cost) + ($request->get('qtyAdjustment')[$i] * $price)) / ($itemStock->stock + $request->get('qtyAdjustment')[$i]);
                    }
                    $itemStock->stock += $request->get('qtyAdjustment')[$i];

                    // insert journal
                    $journalHeader           = new JournalHeader();
                    $journalHeader->category = JournalHeader::ADJUSTMENT;
                    $journalHeader->status   = JournalHeader::OPEN;

                    $now                    = new \DateTime();
                    $period                 = new \DateTime($now->format('Y-m-1'));
                    $journalHeader->period  = $period;

                    $journalHeader->description = $model->adjustment_number;
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
                    $modelItem     = MasterItem::find($request->get('itemId')[$i]);
                    $modelCategory = $modelItem->category;
                    $account       = $modelCategory->coa;
                    $accountCode   = $account->coa_code;

                    $accountCombination = AccountCombinationService::getCombination($accountCode);

                    $journalLine->journal_header_id      = $journalHeader->journal_header_id;
                    $journalLine->account_combination_id = $accountCombination->account_combination_id;
                    $journalLine->debet                  = $request->get('qtyAdjustment')[$i] * $price;
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
                    $journalLine->journal_header_id      = $journalHeader->journal_header_id;
                    $journalLine->account_combination_id = $line->account_comb_id;
                    $journalLine->debet                  = 0;
                    $journalLine->credit                 = $request->get('qtyAdjustment')[$i] * $price;
                    $journalLine->description            = 'Inventory Adjustment';

                    $journalLine->created_date = $now;
                    $journalLine->created_by = \Auth::user()->id;

                    try {
                        $journalLine->save();
                    } catch (\Exception $e) {
                        return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                    }

                }
                $itemStock->save();
            }
            try {
                    $model->save();
                    $modelOR->save();
                } catch (\Exception $e) {
                    return redirect(self::URL.'/edit/'.$model->adjustment_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }
        }
        
        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('inventory/menu.adjustment-stock').' '.$model->adjustment_number])
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

    public function cancelAdj(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'cancel'])) {
            abort(403);
        }

        $model = AdjustmentStockHeader::find($request->get('id'));
        if ($model === null) {
            abort(404);
        }

        $model->status = AdjustmentStockHeader::CANCELED;
        $model->description = $model->description .'. Canceled reason : '.$request->get('reason');   
        $model->last_updated_date = new \DateTime();
        $model->last_updated_by   = \Auth::user()->id;
        $model->save();

        $userNotif = NotificationService::getUserNotification([Role::BRANCH_MANAGER]);
        foreach ($userNotif as $user) {
            $notif             = new Notification();
            $notif->branch_id  = \Session::get('currentBranch')->branch_id;
            $notif->url        = self::URL.'/edit/'.$model->adjustment_header_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->category   = 'Canceled Adjustment Stock';
            $notif->message    = 'Canceled Adjustment Stock '.$model->adjustment_number. '. ' . $request->get('reason', '');
            $notif->save();
        }

        $request->session()->flash(
            'successMessage',
            trans('purchasing/fields.canceled-message', ['variable' => trans('inventory/menu.move-order').' '.$model->adjustment_number])
            );

        return redirect(self::URL);
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

    protected function getStock($itemId, $whId){
        return \DB::table('inv.mst_stock_item')
                    ->selectRaw('sum(stock) as stock')
                    ->where('item_id', '=', $itemId)
                    ->where('wh_id', '=', $whId)
                    ->first();
    }

    protected function getAdjustmentExist($headerId, $itemId, $whId){

        return \DB::table('inv.trans_adjustment_line')
                    ->selectRaw('sum(qty_adjustment) as qty_adjustment')
                    ->join('inv.trans_adjustment_header', 'trans_adjustment_header.adjustment_header_id', '=', 'trans_adjustment_line.adjustment_header_id')
                    ->where('trans_adjustment_header.adjustment_header_id', '!=', $headerId)
                    ->where('item_id', '=', $itemId)
                    ->where('wh_id', '=', $whId)
                    ->where('trans_adjustment_header.status', '=', AdjustmentStockHeader::INCOMPLETE)
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

    protected function getAdjustmentStockNumber(AdjustmentStockHeader $model)
    {
        $branch      = MasterBranch::find(\Session::get('currentBranch')->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('inv.trans_adjustment_header')
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->count();

        return 'ADJ.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }

    protected function getOptionsStatus()
    {
        return [
            AdjustmentStockHeader::INCOMPLETE,
            AdjustmentStockHeader::COMPLETE,
        ];
    }

    protected function getOptionsType()
    {
        return [
            AdjustmentStockHeader::ADJUSTMENT_MIN,
            AdjustmentStockHeader::ADJUSTMENT_PLUS,
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

    protected function getOptionsAllItemStock(){
        $query = \DB::table('inv.mst_item')
                    ->select('mst_item.item_id', 'mst_item.item_code', 'mst_item.description', 'mst_uom.uom_id', 'mst_uom.uom_code')
                    ->join('inv.mst_uom', 'mst_uom.uom_id', '=', 'mst_item.uom_id')
                    ->join('inv.mst_category', 'mst_category.category_id', '=', 'mst_item.category_id')
                    ->whereIn('mst_category.category_code', MasterCategory::STOCK)
                    ->where('mst_item.active', '=', 'Y')
                    ->distinct()
                    ->get();

        $arrayOption = [];
        foreach ($query as $item) {
            $item->average_cost = $this->getAvarageCostItem($item->item_id);
            $arrayOption [] = $item;
        }
        return $arrayOption;
    }

    protected function getAvarageCostItem($itemId){

        $stocks = \DB::table('inv.mst_stock_item')
                    ->join('inv.mst_warehouse', 'mst_warehouse.wh_id', '=', 'mst_stock_item.wh_id')
                    ->where('mst_stock_item.item_id', '=', $itemId)
                    ->where('mst_warehouse.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->get();

        $count  = 0;
        $amount = 0;
        foreach ($stocks as $stock) {
            $amount += $stock->average_cost; 
            $count++; 
        }
        if ($count == 0) {
            return 0;
        }

        return $amount/$count;
    }

    protected function getOptionsCoa(){
        return \DB::table('gl.mst_coa')
                    ->where('segment_name', '=', MasterCoa::ACCOUNT)
                    ->where('active', '=', 'Y')
                    ->get();
    }
}
