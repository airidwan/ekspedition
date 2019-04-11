<?php

namespace App\Modules\Inventory\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Inventory\Model\Transaction\ReturnHeader;
use App\Modules\Inventory\Model\Transaction\ReturnLine;
use App\Modules\Inventory\Model\Transaction\ReceiptHeader;
use App\Modules\Inventory\Model\Transaction\ReceiptLine;
use App\Modules\Inventory\Model\Master\MasterItem;
use App\Modules\Inventory\Model\Master\MasterCategory;
use App\Modules\Inventory\Model\Master\MasterStock;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Asset\Model\Master\MasterAsset;
use App\Modules\Purchasing\Model\Transaction\PurchaseOrderHeader;
use App\Modules\Purchasing\Model\Transaction\PurchaseOrderLine;
use App\Modules\Payable\Model\Transaction\InvoiceHeader;
use App\Modules\Generalledger\Service\Master\AccountCombinationService;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Service\NotificationService;
use App\Service\Penomoran;
use App\Notification;
use App\Role;

class ReturnPurchaseOrderController extends Controller
{
    const RESOURCE = 'Inventory\Transaction\ReturnPurchaseOrder';
    const URL      = 'inventory/transaction/return-po';

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
            $query   = \DB::table('inv.trans_return_header')
                            ->select(
                                'trans_return_header.return_id',
                                'trans_return_header.return_number',
                                'trans_return_header.return_date',
                                'trans_receipt_header.receipt_number',
                                'trans_receipt_header.receipt_date',
                                'po_headers.po_number',
                                'po_headers.po_date'
                                )
                            ->leftJoin('inv.trans_receipt_header', 'trans_receipt_header.receipt_id', '=', 'trans_return_header.receipt_id')
                            ->leftJoin('inv.trans_return_line', 'trans_return_line.return_id', '=', 'trans_return_header.return_id')
                            ->leftJoin('inv.trans_receipt_line', 'trans_receipt_line.receipt_line_id', '=', 'trans_return_line.receipt_line_id')
                            ->leftJoin('po.po_lines', 'po_lines.line_id', '=', 'trans_receipt_line.po_line_id')
                            ->leftJoin('po.po_headers', 'po_headers.header_id', '=', 'trans_receipt_header.po_header_id')
                            ->leftJoin('inv.mst_item', 'mst_item.item_id', '=', 'po_lines.item_id')
                            ->where('trans_return_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->distinct()
                            ->orderBy('return_number', 'desc');
        }else{
            $query   = \DB::table('inv.trans_return_line')
                            ->select(
                                'trans_return_header.return_id',
                                'trans_return_header.return_number',
                                'trans_return_header.return_date',
                                'trans_return_line.return_quantity',
                                'trans_return_line.note',
                                'trans_return_line.created_date',
                                'po_headers.po_number',
                                'po_headers.po_date',
                                'mst_warehouse.wh_code',
                                'trans_receipt_header.receipt_number',
                                'trans_receipt_header.receipt_date',
                                'mst_item.item_code',
                                'mst_item.description as item_description',
                                'mst_uom.uom_code'
                                )
                            ->leftJoin('inv.trans_return_header', 'trans_return_header.return_id', '=', 'trans_return_line.return_id')
                            ->leftJoin('inv.trans_receipt_header', 'trans_receipt_header.receipt_id', '=', 'trans_return_header.receipt_id')
                            ->leftJoin('inv.trans_receipt_line', 'trans_receipt_line.receipt_line_id', '=', 'trans_return_line.receipt_line_id')
                            ->leftJoin('po.po_lines', 'po_lines.line_id', '=', 'trans_receipt_line.po_line_id')
                            ->leftJoin('po.po_headers', 'po_headers.header_id', '=', 'trans_receipt_header.po_header_id')
                            ->leftJoin('inv.mst_warehouse', 'mst_warehouse.wh_id', '=', 'trans_receipt_line.wh_id')
                            ->leftJoin('inv.mst_item', 'mst_item.item_id', '=', 'po_lines.item_id')
                            ->leftJoin('inv.mst_uom', 'mst_uom.uom_id', '=', 'mst_item.uom_id')
                            ->where('trans_return_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->orderBy('return_number', 'desc');
        }

        if (!empty($filters['poNumber'])) {
            $query->where('po_headers.po_number', 'ilike', '%'.$filters['poNumber'].'%');
        }

        if (!empty($filters['returnNumber'])) {
            $query->where('trans_return_header.return_number', 'ilike', '%'.$filters['returnNumber'].'%');
        }

        if (!empty($filters['receiptNumber'])) {
            $query->where('trans_receipt_header.receipt_number', 'ilike', '%'.$filters['receiptNumber'].'%');
        }

        if (!empty($filters['itemCode'])) {
            $query->where('mst_item.item_code', 'ilike', '%'.$filters['itemCode'].'%');
        }

        if (!empty($filters['itemName'])) {
            $query->where('mst_item.description', 'ilike', '%'.$filters['itemName'].'%');
        }

        if (!empty($filters['warehouse'])) {
            $query->where('trans_receipt_line.wh_id', '=', $filters['warehouse']);
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = new \DateTime($filters['dateFrom']);
            $query->where('trans_return_header.return_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = new \DateTime($filters['dateTo']);
            $query->where('trans_return_header.return_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        return view('inventory::transaction.return.index', [
            'models'     => $query->paginate(10),
            'filters'    => $filters,
            'optionWarehouse' => \DB::table('inv.v_mst_warehouse')->where('branch_id', '=', \Session::get('currentBranch')->branch_id)->get(),
            'resource'   => self::RESOURCE,
            'url'        => self::URL,
            ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new ReturnLine();

        $optionReceipt = \DB::table('inv.v_trans_receipt_header')
                                ->select('v_trans_receipt_header.*', 'invoice_line.po_header_id', 'invoice_line.line_id', 'invoice_header.header_id', 'invoice_header.status')
                                ->join('inv.v_trans_receipt_line', 'v_trans_receipt_line.receipt_id', '=', 'v_trans_receipt_header.receipt_id')
                                ->leftJoin('ap.invoice_line', 'invoice_line.po_header_id', '=', 'v_trans_receipt_header.po_header_id')
                                ->leftJoin('ap.invoice_header', 'invoice_header.header_id', '=', 'invoice_line.header_id')
                                ->where(function($query){
                                    $query->whereNull('invoice_line.line_id')
                                        ->orWhereRaw('invoice_line.po_header_id not in (select invoice_line.po_header_id from ap.invoice_line join ap.invoice_header on invoice_header.header_id = invoice_line.header_id where invoice_header.status <> \''.InvoiceHeader::CANCELED.'\' and po_header_id is not null)');
                                })
                                ->whereRaw('v_trans_receipt_line.receipt_quantity > v_trans_receipt_line.return_quantity')
                                ->where('v_trans_receipt_line.category_code', '<>', MasterCategory::JS)
                                ->where('v_trans_receipt_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                                ->orderBy('v_trans_receipt_header.receipt_number', 'desc')
                                ->distinct()
                                ->get();

        return view('inventory::transaction.return.add', [
            'title'            => trans('shared/common.add'),
            'model'            => $model,
            'url'              => self::URL,
            'optionReceipt'    => $optionReceipt,
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

        $model = ReturnHeader::find($id);

        if ($model === null) {
            abort(404);
        }
        return view('inventory::transaction.return.add', [
            'title'            => trans('shared/common.edit'),
            'model'            => $model,
            'url'              => self::URL,
            'optionReceipt'    => [],
            'optionWarehouse'  => [],
            ]);
    }

    public function save(Request $request)
    {
        $this->validate($request, [
            'receiptNumber' => 'required',
            ]);

        if (empty($request->get('receiptLineId'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'You must choose minimal of one item']);
        }

        foreach ($request->get('receiptLineId') as $receiptLineId) {
            $itemId           = 'itemId-'.$receiptLineId;
            $returnQuantity   = 'returnQuantity-'.$receiptLineId;
            $categoryCode     = 'categoryCode-'.$receiptLineId;
            $modelReceiptLine = ReceiptLine::find($receiptLineId);
            $message = '';
            if (in_array($request->get($categoryCode), MasterCategory::STOCK)) {
                $stockItem  = MasterStock::where('item_id', '=', $request->get($itemId))->where('wh_id', '=', $modelReceiptLine->wh_id)->first();
                $item = $stockItem->item;
                if ($stockItem->stock < $request->get($returnQuantity)) {
                    $message = $message.' '.$item->item_code. ' exist on '.$stockItem->stock.'. ';
                }
            }else if($request->get($categoryCode) == MasterCategory::AST) {
                $asset = MasterAsset::where('receipt_id', '=', $request->get('receiptId'))->where('item_id', '=', $request->get($itemId))->where('wh_id', '=', $modelReceiptLine->wh_id)->count();
                $item = MasterItem::find($request->get($itemId));
                if ($asset < $request->get($returnQuantity)) {
                    $message = $message.' '.$item->item_code. ' exist on '.$asset.'. ';
                }
            }
        }
        if ($message) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Item Exceed! '.$message]);
        }
        $now = new \DateTime();
        
        $modelHeader   = new ReturnHeader();
        $modelHeader->branch_id      = \Session::get('currentBranch')->branch_id;
        $modelHeader->return_number = $this->getReturnNumber($modelHeader);
        $modelHeader->receipt_id    = $request->get('receiptId');
        $modelHeader->return_date   = $now;
        $modelHeader->save();

        foreach ($request->get('receiptLineId') as $receiptLineId) {
            $returnQuantity  = 'returnQuantity-'.$receiptLineId;
            $categoryCode    = 'categoryCode-'.$receiptLineId;
            $itemId          = 'itemId-'.$receiptLineId;
            $note            = 'note-'.$receiptLineId;

            $model = new ReturnLine();
            $model->return_id        = $modelHeader->return_id;
            $model->receipt_line_id  = $receiptLineId;
            $model->item_id          = $request->get($itemId);
            $model->return_quantity  = $request->get($returnQuantity);
            $model->note             = $request->get($note);
            $model->created_date     = $now;
            $model->created_by       = \Auth::user()->id;

            $modelReceiptLine        = ReceiptLine::find($receiptLineId);

            if (in_array($request->get($categoryCode), MasterCategory::STOCK)) {
                $stockItem           = MasterStock::where('item_id', '=', $model->item_id)->where('wh_id', '=', $modelReceiptLine->wh_id)->first();
                $stockItem->stock    = $stockItem->stock - $model->return_quantity;
                $stockItem->save();
            }else if($request->get($categoryCode) == MasterCategory::AST) {
                 for ($i=0; $i < $model->return_quantity; $i++) { 
                    $asset = MasterAsset::where('receipt_id', '=', $modelHeader->receipt_id)->where('item_id', '=', $model->item_id)->where('wh_id', '=', $modelReceiptLine->wh_id)->first();
                    $asset->delete();
                }
            }

            $poLine = PurchaseOrderLine::where('line_id', '=', $modelReceiptLine->po_line_id)->first(); 
            $quantityRemain = $poLine->quantity_remain;
            $poLine->quantity_remain = $quantityRemain + intval($request->get($returnQuantity));
            $poLine->save();

            try {
                $model->save();
            } catch (\Exception $e) {
                return redirect(self::URL)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            $modelReceiptLine->return_quantity = $modelReceiptLine->return_quantity + intval($request->get($returnQuantity));
            $modelReceiptLine->save();

            // insert journal header
            $modelPO = $poLine->purchaseOrderHeader;
            $journalHeader           = new JournalHeader();
            $journalHeader->category = JournalHeader::RETURN_PO;
            $journalHeader->status   = JournalHeader::OPEN;

            $now                    = new \DateTime();
            $period                 = new \DateTime($now->format('Y-m-1'));
            $journalHeader->period  = $period;

            $journalHeader->description = $modelPO->po_number;
            $journalHeader->branch_id   = \Session::get('currentBranch')->branch_id;

            $journalHeader->journal_date   = $now;
            $journalHeader->created_date   = $now;
            $journalHeader->created_by     = \Auth::user()->id;
            $journalHeader->journal_number = $this->getJournalNumber($journalHeader);

            try {
                $journalHeader->save();
            } catch (\Exception $e) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            // insert journal line debit
            $journalLine    = new JournalLine();
            $modelTypePo    = $modelPO->type;
            $account        = $modelTypePo->coa;
            $accountCode    = $account->coa_code;
            $subAccount     = $modelPO->vendor;
            $subAccountCode = $subAccount->subaccount_code;

            $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

            $journalLine->journal_header_id      = $journalHeader->journal_header_id;
            $journalLine->account_combination_id = $accountCombination->account_combination_id;
            $journalLine->debet                  = intval($poLine->unit_price) * intval($request->get($returnQuantity));
            $journalLine->credit                  = 0;
            $journalLine->description            = $modelTypePo->type_name;

            $journalLine->created_date = $now;
            $journalLine->created_by = \Auth::user()->id;

            try {
                $journalLine->save();
            } catch (\Exception $e) {
                return redirect(self::URL . '/edit/' . $journalHeader->journal_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            // insert journal line credit
            $journalLine    = new JournalLine();
            $modelItem      = MasterItem::find($poLine->item_id);
            $modelCategory  = $modelItem->category;
            $account        = $modelCategory->coa;
            $accountCode    = $account->coa_code;

            $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

            $journalLine->journal_header_id      = $journalHeader->journal_header_id;
            $journalLine->account_combination_id = $accountCombination->account_combination_id;
            $journalLine->debet                  = 0;
            $journalLine->credit                 = intval($poLine->unit_price) * intval($request->get($returnQuantity));
            $journalLine->description            = $modelItem->description;

            $journalLine->created_date = $now;
            $journalLine->created_by = \Auth::user()->id;


            try {
                $journalLine->save();
            } catch (\Exception $e) {
                return redirect(self::URL . '/edit/' . $journalHeader->journal_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }
        }
        $modelReceiptHeader = $modelReceiptLine->header()->first();

        $modelPO = PurchaseOrderHeader::where('header_id', '=', $modelReceiptHeader->po_header_id)->first();
        $modelPO->status = PurchaseOrderHeader::APPROVED;
        $modelPO->save();

        $userNotif = NotificationService::getUserNotification([Role::FINANCE_ADMIN]);

        foreach ($userNotif as $user) {
            $notif             = new Notification();
            $notif->branch_id  = \Session::get('currentBranch')->branch_id;
            $notif->category   = 'Return Purchase Order';
            $notif->message    = 'Return Purchase Order '.$modelPO->po_number;
            $notif->url        = self::URL.'/edit/'.$modelHeader->return_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->save();
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('inventory/menu.return').' '.$request->get('poNumber')])
            );
        return redirect(self::URL);
    }

    protected function getReturnNumber(ReturnHeader $model){
        $branch      = MasterBranch::find($model->branch_id);
        $createdDate = $model->return_date instanceof \DateTime ? $model->return_date : new \DateTime($model->return_date);
        $count       = \DB::table('inv.trans_return_header')
                            ->where('return_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('return_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->where('branch_id', '=', $branch->branch_id)
                            ->count();

        return 'RT.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
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
