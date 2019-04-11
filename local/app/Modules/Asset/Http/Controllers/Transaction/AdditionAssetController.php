<?php

namespace App\Modules\Asset\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\DummyHeader;
use App\Modules\Asset\Model\Transaction\AdditionAsset;
use App\Modules\Asset\Model\Transaction\AssigmentAsset;
use App\Modules\Asset\Model\Transaction\RetirementAsset;
use App\Modules\Asset\Model\Transaction\DepreciationAsset;
use App\Modules\Asset\Model\Master\AssetCategory;
use App\Modules\Inventory\Model\Master\MasterCategory;
use App\Modules\Inventory\Model\Master\MasterItem;
use App\Modules\Inventory\Model\Transaction\ReceiptLine;
use App\Modules\Generalledger\Model\Master\SettingJournal;
use App\Modules\Generalledger\Model\Master\MasterAccountCombination;
use App\Modules\Generalledger\Service\Master\AccountCombinationService;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Asset\Model\Master\MasterAsset;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Service\Penomoran;

class AdditionAssetController extends Controller
{
    const RESOURCE = 'Asset\Transaction\AdditionAsset';
    const URL      = 'asset/transaction/addition-asset';
    protected $now;
    protected $branchName;

    public function __construct()
    {
        $this->middleware('auth');
        $this->now = new \DateTime();
    }

    public function index(Request $request)
    {
        \Session::get('currentBranch')->branch_code_numeric;
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
        $query = $this->getQuery($request, $filters);

        return view('asset::transaction.addition-asset.index', [
            'models'         => $query->paginate(10),
            'filters'        => $filters,
            'optionCategory' => \DB::table('ast.asset_category')
                                ->where('active', '=', 'Y')
                                ->get(),
            'optionStatus'   => \DB::table('ast.asset_status')->get(),
            'optionType'     => $this->optionType(),
            'optionBranch'   => MasterBranch::get(),
            'resource'       => self::RESOURCE,
            'url'            => self::URL
        ]);
    }

    protected function getQuery(Request $request, $filters){
        $query   = \DB::table('ast.v_addition_asset');

        if (!empty($filters['assetNumber'])) {
            $query->where('asset_number', 'ilike', '%'.$filters['assetNumber'].'%');
        }

        if (!empty($filters['itemCode'])) {
            $query->where('item_code', 'ilike', '%'.$filters['itemCode'].'%');
        }

        if (!empty($filters['itemDescription'])) {
            $query->where('item_description', 'ilike', '%'.$filters['itemDescription'].'%');
        }

        if (!empty($filters['policeNumber'])) {
            $query->where('police_number', 'ilike', '%'.$filters['policeNumber'].'%');
        }

        if (!empty($filters['employee'])) {
            $query->where('employee_name', 'ilike', '%'.$filters['employee'].'%');
        }
        
        if (!empty($filters['category'])) {
            $query->where('asset_category_id', $filters['category']);
        }

        if (!empty($filters['status'])) {
            $query->where('status_id', '=', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', '=', $filters['type']);
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = new \DateTime($filters['dateFrom']);
            $query->where('asset_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = new \DateTime($filters['dateTo']);
            $query->where('asset_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        $query->where('branch_id', '=', \Session::get('currentBranch')->branch_id);
        return $query;
    }

    public function printPdfIndex(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters);

        $header = view('print.header-pdf', ['title' => trans('asset/menu.addition-asset')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('asset::transaction.addition-asset.print-pdf-index', [
            'models'  => $query->get(),
            'filters' => $filters,
        ])->render();

        $this->branchName = \Session::get('currentBranch')->branch_name;

        \PDF::SetTitle(trans('asset/menu.addition-asset').' '.$this->branchName);
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('P', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output(trans('asset/menu.addition-asset').' '.$this->branchName.'.pdf');
        \PDF::reset();
    }

    public function printExcelIndex(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters)->get();

        $this->branchName = \Session::get('currentBranch')->branch_name;
        \Excel::create(trans('asset/menu.addition-asset').' '.$this->branchName, function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('asset/menu.addition-asset'));
                });

                $sheet->cells('A3:J3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('asset/fields.asset-number'),
                    trans('shared/common.type'),
                    trans('shared/common.category'),
                    trans('inventory/fields.item-code'),
                    trans('inventory/fields.item-description'),
                    trans('operational/fields.police-number'),
                    trans('asset/fields.employee'),
                    trans('shared/common.date'),
                    trans('shared/common.status'),
                ]);
                foreach($query as $index => $model) {
                    $date = !empty($model->asset_date) ? new \DateTime($model->asset_date) : null;

                    $data = [
                        $index + 1,
                        $model->asset_number,
                        $model->type,
                        $model->category_name,
                        $model->item_code,
                        $model->item_description,
                        $model->police_number,
                        $model->employee_name,
                        !empty($date) ? $date->format('d-m-Y') : '',
                        $model->status,
                    ];
                    $sheet->row($index + 4, $data);
                }

                $currentRow = count($query) + 5;
                if (!empty($filters['assetNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('asset/fields.asset-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['assetNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['itemCode'])) {
                    $this->addLabelDescriptionCell($sheet, trans('inventory/fields.item-code'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['itemCode'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['itemDescription'])) {
                    $this->addLabelDescriptionCell($sheet, trans('inventory/fields.item-description'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['itemDescription'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['employee'])) {
                    $this->addLabelDescriptionCell($sheet, trans('asset/fields.employee'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['employee'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['category'])) {
                    $category = AssetCategory::find($filters['category']);
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.category'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  !empty($category) ? $category->category_name : '', 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['type'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.type'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['type'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['status'])) {
                    $status = \DB::table('ast.asset_status')->where('asset_status_id', '=', $filters['status'])->first();
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.status'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  !empty($status) ? $status->status : '', 'C', $currentRow);
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

                $currentRow = count($query) + 5;
                $this->addLabelDescriptionCell($sheet, trans('shared/common.date'), 'E', $currentRow);
                $this->addValueDescriptionCell($sheet, $this->now->format('d-m-Y'), 'F', $currentRow);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.user'), 'E', $currentRow + 1);
                $this->addValueDescriptionCell($sheet, \Auth::user()->full_name, 'F', $currentRow + 1);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.branch'), 'E', $currentRow + 2);
                $this->addValueDescriptionCell($sheet, $this->branchName, 'F', $currentRow + 2);
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

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model        = new AdditionAsset();
        $item         = $model->item; 
        $retirement   = $model->retirement; 
        $depreciation = $model->depreciation; 
        $assigment    = $model->assigment; 

        return view('asset::transaction.addition-asset.add', [
            'title'            => trans('shared/common.add'),
            'model'            => $model,
            'item'             => '',
            'receipt'          => '',
            'receiptLine'      => '',
            'poHeader'         => '',
            'poLine'           => '',
            'retirement'       => '',
            'depreciation'     => '',
            'assigment'        => '',
            'url'              => self::URL,
            'resource'         => self::RESOURCE,
            'optionStatus'     => \DB::table('ast.asset_status')->get(),
            'optionCategory'   => \DB::table('ast.asset_category')->where('active', '=', 'Y')->get(),
            'optionItem'       => $this->getItem(),
            'optionMasterItem' => $this->getMasterItem(),
            'optionType'       => $this->optionType(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = AdditionAsset::where('asset_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }
        if ($request->user()->cannot('accessBranch', $model->branch_id) && \Session::get('currentBranch')->branch_code_numeric != MasterBranch::KODE_NUMERIC_HO) {
           abort(403);
        }

        $item         = $model->item;
        $retirement   = $model->retirement; 
        $depreciation = $model->depreciation; 
        $assigment    = $model->assigment; 
        $receipt      = $model->receipt;
        $receiptLine  = $model->receiptLine;
        $poHeader     = !empty($receipt) ? $receipt->po : null ; 
        $poLine       = !empty($poHeader) ? $poHeader->purchaseOrderLines()->where('item_id','=',$item->item_id)->first() : null ; 

        $data = [
            'title'            => trans('shared/common.edit'),
            'model'            => $model,
            'item'             => $item,
            'receipt'          => $receipt,
            'receiptLine'      => $receiptLine,
            'poHeader'         => $poHeader,
            'poLine'           => $poLine,
            'retirement'       => $retirement,
            'depreciation'     => $depreciation,
            'assigment'        => $assigment,
            'url'              => self::URL,
            'resource'         => self::RESOURCE,
            'optionStatus'     => \DB::table('ast.asset_status')->get(),
            'optionCategory'   => \DB::table('ast.asset_category')->where('active', '=', 'Y')->get(),
            'optionItem'       => $this->getItem(),
            'optionMasterItem' => $this->getMasterItem(),
            'optionType'       => $this->optionType(),
        ];

        if ($request->user()->can('access', [self::RESOURCE, 'update'])) {
            return view('asset::transaction.addition-asset.add', $data);
        } else {
            return view('asset::transaction.addition-asset.detail', $data);
        }
    }

    public function save(Request $request)
    {
        $id       = intval($request->get('id'));
        $modelAdd = !empty($id) ? AdditionAsset::where('asset_id', '=', $id)->first() : new AdditionAsset();

        $this->validate($request, [
            'itemCode'             => 'required',
            'poCost'               => 'required',
        ]);

        if (empty($id)) {
            $this->validate($request, [
                'type'                 => 'required',
                'category'             => 'required',
            ]);
        }

        if ((empty($request->get('assigmentEmployee')) || empty($request->get('assigmentLocation'))) && empty($id)) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Assigment is required']);
        }

        if (empty($request->get('depreciationLifeYear'))) {
            // return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Depreciation is required']);
        }

        $assetDate    = !empty($request->get('assetDate')) ? new \DateTime($request->get('assetDate')) : null;

        $opr = empty($modelAdd->asset_id) ? 'I' : 'U';

        $modelAdd                    = !empty($id) ? AdditionAsset::find($id) : new AdditionAsset();

        if (!empty($request->get('type'))) {
            $modelAdd->type          = $request->get('type');
        }

        if (!empty($request->get('category'))) {
            $modelAdd->asset_category_id = $request->get('category');
        }

        $modelAdd->branch_id = \Session::get('currentBranch')->branch_id;
        $modelAdd->item_id           = intval($request->get('itemId'));
        $modelAdd->serial_number     = $request->get('serialNumber');
        $modelAdd->lease_number      = $request->get('leaseNumber');
        $modelAdd->police_number     = $request->get('policeNumber');

        if (!empty($request->get('poCost'))) {
            $modelAdd->po_cost           = str_replace(',', '',$request->get('poCost'));
        }
        if (!empty($request->get('receiptId'))) {
            $modelAdd->receipt_id        = intval($request->get('receiptId'));
        }
        if (!empty($request->get('receiptLineId'))) {
            $modelAdd->receipt_line_id   = intval($request->get('receiptLineId'));
        }

        if (!empty($request->get('assigmentEmployee'))) {
            $modelAdd->status_id     = AdditionAsset::ACTIVE;
        }else{
            $modelAdd->status_id     = AdditionAsset::NONACTIVE;
        }
        
        $retirementDate          = !empty($request->get('retirementDate')) ? new \DateTime($request->get('retirementDate')) : null;
        if (!empty($request->get('retirementDate'))) {
            $modelAdd->inactive_date = !empty($retirementDate) ? $retirementDate->format('Y-m-d H:i:s'):null;
            $modelAdd->status_id     = AdditionAsset::RETIREMENT;
        }
       
        $now                         = new \DateTime();

        if ($opr == 'I') {
            $modelAdd->asset_date        = !empty($assetDate) ? $assetDate->format('Y-m-d H:i:s'):null;
            $modelAdd->asset_number   = $this->getAssetNumber($modelAdd);
            $modelAdd->created_date   = $now;
            $modelAdd->created_by     = \Auth::user()->id;

            if ($modelAdd->type == AdditionAsset::PO) {
                $modelMask                = MasterAsset::where('receipt_line_id', '=', $modelAdd->receipt_line_id)->first();
                $modelMask->delete();

                $modelReceiptLine  = ReceiptLine::find($modelAdd->receipt_line_id);
                $modelReceiptLine->return_quantity = $modelReceiptLine->return_quantity + 1; 
                $modelReceiptLine->save();
            }

        }else{
            $modelAdd->last_updated_date = $now;
            $modelAdd->last_updated_by   = \Auth::user()->id;
        }

        try {
            $modelAdd->save();            
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $modelAss = !empty($id) ? AssigmentAsset::where('asset_id', '=', $id)->first() : new AssigmentAsset();
        $modelAss->asset_id      = $modelAdd->asset_id;
        $modelAss->employee_name = $request->get('assigmentEmployee');
        $modelAss->location      = $request->get('assigmentLocation');

        if ($opr == 'I') {
            $modelAss->created_date = $now;
            $modelAss->created_by   = \Auth::user()->id;
        }else{
            $modelAss->last_updated_date = $now;
            $modelAss->last_updated_by   = \Auth::user()->id;
        }

        try {
            $modelAss->save();
        } catch (\Exception $e) {
            return redirect(self::URL.'/edit/'.$modelAdd->asset_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $modelDep = !empty($id) ? DepreciationAsset::where('asset_id', '=', $id)->first() : new DepreciationAsset();
        $modelDep->asset_id      = $modelAdd->asset_id;
        $modelDep->life_year     = intval(str_replace(',', '',$request->get('depreciationLifeYear')));
        $modelDep->cost_year     = intval(str_replace(',', '',$request->get('depreciationCostYear')));
        $modelDep->cost_month    = intval(str_replace(',', '',$request->get('depreciationCostMonth')));

        if ($opr == 'I') {
            $modelDep->created_date = $now;
            $modelDep->created_by   = \Auth::user()->id;
        }else{
            $modelDep->last_updated_date = $now;
            $modelDep->last_updated_by   = \Auth::user()->id;
        }

        try {
            $modelDep->save();
        } catch (\Exception $e) {
            return redirect(self::URL.'/edit/'.$modelAdd->asset_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $modelRet = !empty($id) ? RetirementAsset::where('asset_id', '=', $id)->first() : new RetirementAsset();
        $modelRet->asset_id      = $modelAdd->asset_id;
        if (!empty($request->get('retirementDate'))) {
            $modelRet->retirement_date = !empty($retirementDate) ? $retirementDate->format('Y-m-d H:i:s'):null;
        }
        $modelRet->retirement_type = intval($request->get('retirementType'));
        $modelRet->current_cost    = intval(str_replace(',', '',$request->get('currentCost')));
        $modelRet->retirement_cost = intval(str_replace(',', '',$request->get('retirementCost')));
        $modelRet->description     = $request->get('retirementDescription');

        if ($opr == 'I') {
            $modelRet->created_date = $now;
            $modelRet->created_by   = \Auth::user()->id;
        }else{
            $modelRet->last_updated_date = $now;
            $modelRet->last_updated_by   = \Auth::user()->id;
        }

        try {
            $modelRet->save();
        } catch (\Exception $e) {
            return redirect(self::URL.'/edit/'.$modelAdd->asset_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        if ($opr == 'I') {
            // insert journal
            $journalHeader           = new JournalHeader();
            $journalHeader->category = JournalHeader::ADDITION_ASSET;
            $journalHeader->status   = JournalHeader::OPEN;

            $now                    = new \DateTime();
            $period                 = new \DateTime($now->format('Y-m-1'));
            $journalHeader->period  = $period;

            $journalHeader->description = $modelAdd->asset_number;
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
            $modelAssetType   = $modelAdd->category;
            $account          = $modelAssetType->clearing;
            $accountCode      = $account->coa_code;

            $defaultJournalAccount = SettingJournal::where('setting_name', '=', SettingJournal::DEFAULT_SUB_ACCOUNT)->first();
            $subAccount        = $defaultJournalAccount->coa;
            $subAccountCode    = $subAccount->coa_code;

            $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

            $journalLine->journal_header_id      = $journalHeader->journal_header_id;
            $journalLine->account_combination_id = $accountCombination->account_combination_id;
            $journalLine->debet                  = $modelAdd->po_cost;
            $journalLine->credit                 = 0;
            $journalLine->description            = 'Account Category Asset';

            $journalLine->created_date = $now;
            $journalLine->created_by = \Auth::user()->id;

            try {
                $journalLine->save();
            } catch (\Exception $e) {
                return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            // insert journal line credit
            $journalLine   = new JournalLine();
            $modelItem     = MasterItem::find($modelAdd->item_id);
            $modelCategory = $modelItem->category;
            $account       = $modelCategory->coa;
            $accountCode   = $account->coa_code;

            $accountCombination = AccountCombinationService::getCombination($accountCode, $subAccountCode);

            $journalLine->journal_header_id      = $journalHeader->journal_header_id;
            $journalLine->account_combination_id = $accountCombination->account_combination_id;
            $journalLine->debet                  = 0;
            $journalLine->credit                 = $modelAdd->po_cost;
            $journalLine->description            = 'Account Item Category';

            $journalLine->created_date = $now;
            $journalLine->created_by = \Auth::user()->id;

            try {
                $journalLine->save();
            } catch (\Exception $e) {
                return redirect(self::URL . '/edit/' . $model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }
        }
        
        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('asset/menu.addition-asset').' '.$modelAdd->asset_number])
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

    function optionType(){
        return [
            AdditionAsset::EXIST,
            AdditionAsset::PO
        ];
    }

    function optionSubAccount(){
        return \DB::table('gl.mst_coa')
                ->where('segment_name','=','Sub Account')
                ->where('active', '=', 'Y')
                ->orderBy('coa_code')->get();
    }

    protected function getOptionYears()
    {
        $now = new \DateTime();
        $options = [];
        for($i = $now->format('Y'); $i >= $now->format('Y') - 50; $i--) {
            $options[] = $i;
        }

        return $options;
    }

    protected function checkAccessBranch(AdditionAsset $model)
    {
        $canAccessBranch = false;
        foreach ($model->truckBranch as $truckBranch) {
            $branch = $truckBranch->branch;
            if ($branch !== null && \Auth::user()->can('accessBranch', $branch->branch_id)) {
                $canAccessBranch = true;
            }
        }

        return $canAccessBranch;
    }

    protected function getAssetNumber(AdditionAsset $model)
    {
        $branch      = MasterBranch::find($model->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('ast.addition_asset')
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->where('branch_id', '=', $branch->branch_id)
                            ->count();

        return 'AST.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }

    protected function getItem(){
        return \DB::table('ast.v_mask_add_asset_lov')->where('branch_id', '=', \Session::get('currentBranch')->branch_id)->get();
    }

    protected function getMasterItem(){
        return \DB::table('inv.mst_item')
                ->select('mst_item.*')
                ->leftJoin('inv.mst_category', 'mst_category.category_id', '=', 'mst_item.category_id')
                ->where('mst_category.category_code', '=', MasterCategory::AST)
                ->orderBy('item_code')->distinct()->get();
    }

}
