<?php

namespace App\Modules\Inventory\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Inventory\Model\Transaction\ReceiptHeader;
use App\Modules\Inventory\Model\Transaction\ReceiptLine;
use App\Modules\Inventory\Model\Master\MasterItem;
use App\Modules\Inventory\Model\Master\MasterCategory;
use App\Modules\Inventory\Model\Master\MasterStock;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Asset\Model\Master\MasterAsset;
use App\Modules\Purchasing\Model\Transaction\PurchaseOrderHeader;
use App\Modules\Purchasing\Model\Transaction\PurchaseOrderLine;
use App\Modules\Purchasing\Service\Transaction\PurchaseOrderService;
use App\Modules\Generalledger\Service\Master\AccountCombinationService;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Service\NotificationService;
use App\Service\Penomoran;
use App\Notification;
use App\Role;

class ReceiptPurchaseOrderController extends Controller
{
    const RESOURCE = 'Inventory\Transaction\ReceiptPurchaseOrder';
    const URL      = 'inventory/transaction/receipt-po';

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
            $query   = \DB::table('inv.trans_receipt_header')
                            ->select(
                                'trans_receipt_header.receipt_id',
                                'trans_receipt_header.receipt_number',
                                'trans_receipt_header.receipt_date',
                                'po_headers.po_number',
                                'po_headers.description as description_po'
                                )
                            ->leftJoin('po.po_headers', 'po_headers.header_id', '=', 'trans_receipt_header.po_header_id')
                            ->leftJoin('inv.trans_receipt_line', 'trans_receipt_line.receipt_id', '=', 'trans_receipt_header.receipt_id')
                            ->leftJoin('po.po_lines', 'po_lines.line_id', '=', 'trans_receipt_line.po_line_id')
                            ->leftJoin('inv.mst_item', 'mst_item.item_id', '=', 'po_lines.item_id')
                            ->where('trans_receipt_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->distinct()
                            ->orderBy('receipt_number', 'desc');
        }else{
            $query   = \DB::table('inv.trans_receipt_line')
                            ->select(
                                'trans_receipt_header.receipt_id',
                                'trans_receipt_header.receipt_number',
                                'trans_receipt_header.receipt_date',
                                'trans_receipt_line.receipt_quantity',
                                'trans_receipt_line.created_date',
                                'mst_warehouse.wh_code',
                                'po_headers.po_number',
                                'po_headers.description as description_po',
                                'mst_item.item_code',
                                'mst_item.description as description_item',
                                'mst_uom.uom_code'
                                )
                            ->leftJoin('inv.trans_receipt_header', 'trans_receipt_header.receipt_id', '=', 'trans_receipt_line.receipt_id')
                            ->leftJoin('po.po_headers', 'po_headers.header_id', '=', 'trans_receipt_header.po_header_id')
                            ->leftJoin('po.po_lines', 'po_lines.line_id', '=', 'trans_receipt_line.po_line_id')
                            ->leftJoin('inv.mst_warehouse', 'mst_warehouse.wh_id', '=', 'trans_receipt_line.wh_id')
                            ->leftJoin('inv.mst_item', 'mst_item.item_id', '=', 'po_lines.item_id')
                            ->leftJoin('inv.mst_uom', 'mst_uom.uom_id', '=', 'mst_item.uom_id')
                            ->where('trans_receipt_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->orderBy('receipt_number', 'desc');
        }

        if (!empty($filters['poNumber'])) {
            $query->where('po_headers.po_number', 'ilike', '%'.$filters['poNumber'].'%');
        }

        if (!empty($filters['description'])) {
            $query->where('po_headers.description', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['itemName'])) {
            $query->where('mst_item.description', 'ilike', '%'.$filters['itemName'].'%');
        }

        if (!empty($filters['itemCode'])) {
            $query->where('mst_item.item_code', 'ilike', '%'.$filters['itemCode'].'%');
        }

        if (!empty($filters['warehouse'])) {
            $query->where('trans_receipt_line.wh_id', '=', $filters['warehouse']);
        }

        if (!empty($filters['receiptNumber'])) {
            $query->where('trans_receipt_header.receipt_number', 'ilike', '%'.$filters['receiptNumber'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = new \DateTime($filters['dateFrom']);
            $query->where('trans_receipt_header.receipt_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = new \DateTime($filters['dateTo']);
            $query->where('trans_receipt_header.receipt_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        return view('inventory::transaction.receipt.index', [
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

        $model = new ReceiptLine();

        return view('inventory::transaction.receipt.add', [
            'title'            => trans('shared/common.add'),
            'model'            => $model,
            'url'              => self::URL,
            'optionPO'         => \DB::table('po.po_headers')
                                    ->select('po_headers.*', 'mst_vendor.vendor_name', 'mst_vendor.vendor_code', 'mst_po_type.type_name')
                                    ->leftJoin('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'po_headers.supplier_id')
                                    ->leftJoin('po.mst_po_type', 'mst_po_type.type_id', '=', 'po_headers.type_id')
                                    ->join('po.po_lines', 'po_lines.header_id', '=', 'po_headers.header_id')
                                    ->where('po_lines.quantity_remain', '>', 0)
                                    ->where('po_headers.branch_id', '=', \Session::get('currentBranch')->branch_id)
                                    ->where('po_headers.status', '=', PurchaseOrderHeader::APPROVED)
                                    ->orderBy('po_headers.created_date', 'desc')
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

        $model = ReceiptHeader::find($id);

        if ($model === null) {
            abort(404);
        }
        return view('inventory::transaction.receipt.add', [
            'title'            => trans('shared/common.edit'),
            'model'            => $model,
            'url'              => self::URL,
            'optionPO'         => [],
            'optionWarehouse'  => [],
            ]);
    }

    public function save(Request $request)
    {
        // var_dump($request->all());exit();
        $id    = intval($request->get('id'));

        $this->validate($request, [
            'poNumber' => 'required',
            ]);

        if (empty($request->get('poLineId'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'You must choose minimal of one item']);
        }

        foreach ($request->get('poLineId') as $poLineId) {
            
            $purchaseQuantity = intval($this->getPurchaseQuantity($poLineId)->quantity_need);
            $receiptExist     = intval($this->getReceiptQuantity($poLineId)->receipt_quantity);
            $returnExist      = intval($this->getReturnQuantity($poLineId)->return_quantity);

            $receiptQuantity = $request->get('receiptQuantity-'.$poLineId);

            if ($receiptQuantity > ($purchaseQuantity - $receiptExist + $returnExist)) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Receipt quantity exceed! Remain on '. number_format($purchaseQuantity - $receiptExist + $returnExist)]);
            }
        }

        $modelPO = PurchaseOrderHeader::where('header_id', '=', $request->get('poHeaderId'))->first();

        $opr = empty($id) ? 'I' : 'U';

        $count         = \DB::table('inv.trans_receipt_header')->where('branch_id', '=', \Session::get('currentBranch')->branch_id)->count();
        $receiptNumber = 'RPO.'.\Session::get('currentBranch')->branch_code.'.'.Penomoran::getStringNomor($count+1, 4);
        $now           = new \DateTime();
        
        $modelHeader   = new ReceiptHeader();
        $modelHeader->receipt_number = $receiptNumber;
        $modelHeader->branch_id      = \Session::get('currentBranch')->branch_id;
        $modelHeader->receipt_date   = $now;
        $modelHeader->po_header_id   = $modelPO->header_id;
        $modelHeader->save();

        foreach ($request->get('poLineId') as $poLineId) {
            $whId            = 'whId-'.$poLineId;
            $receiptQuantity = 'receiptQuantity-'.$poLineId;
            $categoryCode    = 'categoryCode-'.$poLineId;
            $quantity        = 'quantity-'.$poLineId;
            $unitPrice       = 'unitPrice-'.$poLineId;

            $model = new ReceiptLine();
            $model->po_line_id       = $poLineId;
            $model->receipt_id       = $modelHeader->receipt_id;
            $model->wh_id            = $request->get($whId);
            $model->receipt_quantity = intval($request->get($receiptQuantity));

            if ($opr == 'I') {
                $model->created_date = $now;
                $model->created_by   = \Auth::user()->id;
            }else{
                $model->last_updated_date = $now;
                $model->last_updated_by   = \Auth::user()->id;
            }

            try {
                $model->save();
            } catch (\Exception $e) {
                return redirect(self::URL)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            $poLine = PurchaseOrderLine::where('line_id', '=', $poLineId)->first(); 
            if (in_array($request->get($categoryCode), MasterCategory::STOCK)) {
                $check     = $this->checkExistStock($poLine->item_id, $request->get($whId));
                $stockItem = $check ? MasterStock::where('item_id', '=', $poLine->item_id)->where('wh_id', '=', $request->get($whId))->first() : new MasterStock();
                $stockItem->wh_id = $request->get($whId);
                $stockItem->item_id = $poLine->item_id;
                if (empty($stockItem->average_cost)) {
                    $stockItem->average_cost = intval($request->get($unitPrice));
                }else{
                    $stockItem->average_cost = (($stockItem->average_cost * $stockItem->stock) + (intval($request->get($unitPrice)) * intval($request->get($receiptQuantity)))) / ($stockItem->stock + intval($request->get($receiptQuantity))) ; 
                }

                if ($check) {
                    $stockItem->stock = $stockItem->stock + $model->receipt_quantity;
                }else{
                    $stockItem->stock = $model->receipt_quantity;
                }
                $stockItem->save();

            }else if($request->get($categoryCode) == MasterCategory::AST) {
                for ($i=0; $i < $model->receipt_quantity; $i++) { 
                    $asset                  = new MasterAsset();
                    $asset->receipt_id      = $modelHeader->receipt_id;
                    $asset->receipt_line_id = $model->receipt_line_id;
                    $asset->branch_id       = \Session::get('currentBranch')->branch_id;
                    $asset->wh_id           = $request->get($whId);
                    $asset->item_id         = $poLine->item_id;
                    $asset->po_cost         = $poLine->unit_price;
                    $asset->created_date    = $now;
                    $asset->created_by      = \Auth::user()->id;
                    $asset->save();
                }
            }

            $quantityRemain = $poLine->quantity_remain;
            $poLine->quantity_remain = $quantityRemain - intval($request->get($receiptQuantity));
            $poLine->save();

            if ($this->checkPoClosed($modelPO->header_id)) {
                $modelPO->status = PurchaseOrderHeader::CLOSED;
                $modelPO->save();
            }

            // insert journal header
            $journalHeader           = new JournalHeader();
            $journalHeader->category = JournalHeader::RECEIPT_PO;
            $journalHeader->status   = JournalHeader::OPEN;

            $now                    = new \DateTime();
            $period                 = new \DateTime($now->format('Y-m-1'));
            $journalHeader->period  = $period;

            $journalHeader->description = $modelPO->po_number;
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

            $journalLine    = new JournalLine();
            $modelItem      = MasterItem::find($poLine->item_id);
            $modelCategory  = $modelItem->category;
            $account        = $modelCategory->coa;
            $accountCode    = $account->coa_code;
            $subAccount     = $modelPO->vendor;
            $subAccountCode = $subAccount->subaccount_code;

            $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

            $journalLine->journal_header_id      = $journalHeader->journal_header_id;
            $journalLine->account_combination_id = $accountCombination->account_combination_id;
            $journalLine->debet                  = intval($request->get($unitPrice)) * intval($request->get($receiptQuantity));
            $journalLine->credit                 = 0;
            $journalLine->description            = $modelItem->description;

            $journalLine->created_date = $now;
            $journalLine->created_by = \Auth::user()->id;

            try {
                $journalLine->save();
            } catch (\Exception $e) {
                return redirect(self::URL . '/edit/' . $journalHeader->journal_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            // insert journal line credit
            $journalLine    = new JournalLine();
            $modelTypePo    = $modelPO->type;
            $account        = $modelTypePo->coa;
            $accountCode    = $account->coa_code;

            $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

            $journalLine->journal_header_id      = $journalHeader->journal_header_id;
            $journalLine->account_combination_id = $accountCombination->account_combination_id;
            $journalLine->credit                 = intval($request->get($unitPrice)) * intval($request->get($receiptQuantity));
            $journalLine->debet                  = 0;
            $journalLine->description            = $modelTypePo->type_name;

            $journalLine->created_date = $now;
            $journalLine->created_by = \Auth::user()->id;

            try {
                $journalLine->save();
            } catch (\Exception $e) {
                return redirect(self::URL . '/edit/' . $journalHeader->journal_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }
        }

        $userNotif = NotificationService::getUserNotification([Role::FINANCE_ADMIN]);

        foreach ($userNotif as $user) {
            $notif             = new Notification();
            $notif->branch_id  = \Session::get('currentBranch')->branch_id;
            $notif->category   = 'Receipt Purchase Order';
            $notif->message    = 'Receipting order from '.$modelPO->po_number;
            $notif->url        = self::URL.'/edit/'.$modelHeader->receipt_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->save();
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('inventory/menu.receipt-po').' '.$request->get('poNumber')])
            );
        return redirect(self::URL);
    }

    function getPurchaseQuantity($poLineId){
        return \DB::table('po.po_lines')
                    ->select('quantity_need')
                    ->where('line_id', '=', $poLineId)
                    ->first();
    }

    function getReceiptQuantity($poLineId){
        return \DB::table('inv.trans_receipt_line')
                    ->selectRaw('sum(receipt_quantity) as receipt_quantity')
                    ->where('trans_receipt_line.po_line_id', '=', $poLineId)
                    ->first();
    }

    function getReturnQuantity($poLineId){
        return \DB::table('inv.trans_return_line')
                    ->selectRaw('sum(trans_return_line.return_quantity) as return_quantity')
                    ->join('inv.trans_return_header', 'trans_return_header.return_id', '=', 'trans_return_line.return_id')
                    ->join('inv.trans_receipt_header', 'trans_receipt_header.receipt_id', '=', 'trans_return_header.receipt_id')
                    ->join('inv.trans_receipt_line', 'trans_receipt_line.receipt_id', '=', 'trans_receipt_header.receipt_id')
                    ->where('trans_receipt_line.po_line_id', '=', $poLineId)
                    ->first();
    }

    function checkPoClosed($headerId){
        if (\DB::table('po.v_po_headers')
                ->join('po.v_po_lines', 'v_po_lines.header_id', '=', 'v_po_headers.header_id')
                ->where('v_po_headers.header_id', '=', $headerId)
                ->where('v_po_lines.quantity_remain', '<>', 0)
                ->where('v_po_headers.branch_id', '=', \Session::get('currentBranch')->branch_id)
                ->where('v_po_headers.status', '=', PurchaseOrderHeader::APPROVED)
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
}
