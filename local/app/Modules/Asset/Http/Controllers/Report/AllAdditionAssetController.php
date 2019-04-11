<?php

namespace App\Modules\Asset\Http\Controllers\Report;

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

class AllAdditionAssetController extends Controller
{
    const RESOURCE = 'Asset\Report\AllAdditionAsset';
    const URL      = 'asset/report/all-addition-asset';
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

        return view('asset::report.all-addition-asset.index', [
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

        if(!empty($filters['branch'])){
            $query->where('branch_id', '=', $filters['branch']);
        }

        return $query;
    }

    public function printExcelIndex(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters)->get();
        $modelBranch = MasterBranch::find($filters['branch']);
        $this->branchName = !empty($modelBranch->branch_name) ? $modelBranch->branch_name : 'All Branch';

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

    function optionType(){
        return [
            AdditionAsset::EXIST,
            AdditionAsset::PO
        ];
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
}
