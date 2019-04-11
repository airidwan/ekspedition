<?php

namespace App\Modules\Asset\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\DummyHeader;
use App\Modules\Asset\Model\Transaction\ServiceAsset;
use App\Modules\Asset\Model\Transaction\AssigmentAsset;
use App\Modules\Asset\Model\Transaction\AdditionAsset;
use App\Modules\Asset\Model\Transaction\RetirementAsset;
use App\Modules\Asset\Model\Transaction\DepreciationAsset;
use App\Modules\Asset\Model\Master\MasterAsset;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Service\Penomoran;

class ServiceAssetController extends Controller
{
    const RESOURCE = 'Asset\Transaction\ServiceAsset';
    const URL      = 'asset/transaction/service-asset';
    protected $now;

    public function __construct()
    {
        $this->middleware('auth');
        $this->now = new \DateTime();
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
        $query = $this->getQuery($request, $filters);

        return view('asset::transaction.service-asset.index', [
            'models'         => $query->paginate(10),
            'filters'        => $filters,
            'resource'       => self::RESOURCE,
            'url'            => self::URL
        ]);
    }

    protected function getQuery(Request $request, $filters){
        $query   = \DB::table('ast.v_service_asset')
                    ->where('v_service_asset.branch_id','=', \Session::get('currentBranch')->branch_id)
                    ->where('v_service_asset.service_type', '=', ServiceAsset::ASSET);
        if (!empty($filters['status'])) {
            $query->where('finished', '=', $filters['status']);
        }

        if (!empty($filters['serviceNumber'])) {
            $query->where('service_number', 'ilike', '%'.$filters['serviceNumber'].'%');
        }

        if (!empty($filters['assetNumber'])) {
            $query->where('asset_number', 'ilike', '%'.$filters['assetNumber'].'%');
        }

        if (!empty($filters['poNumber'])) {
            $query->where('po_number', 'ilike', '%'.$filters['poNumber'].'%');
        }

        if (!empty($filters['item'])) {
            $query->where('item_description', 'ilike', '%'.$filters['item'].'%');
        }

        return $query;
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model        = new ServiceAsset();

        return view('asset::transaction.service-asset.add', [
            'title'            => trans('shared/common.add'),
            'model'            => $model,
            'addAsset'         => '',
            'item'             => '',
            'receipt'          => '',
            'poHeader'         => '',
            'category'         => '',
            'assigment'        => '',
            'url'              => self::URL,
            'resource'         => self::RESOURCE,
            'optionAsset'    => $this->getAsset(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = ServiceAsset::where('service_asset_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        $asset = $model->addAsset;

        if ($request->user()->cannot('accessBranch', $asset->branch_id)) {
            abort(403);
        }

        $addAsset        = $model->addAsset()->first();

        $item         = !empty($addAsset) ? $addAsset->item : null;
        $receipt      = !empty($addAsset) ? $addAsset->receipt : null;
        $poHeader     = !empty($receipt) ? $receipt->po : null ; 
        $assigment    = !empty($addAsset) ? $addAsset->assigment : null ; 
        $category     = !empty($addAsset) ? $addAsset->category : null ; 

        return view('asset::transaction.service-asset.add', [
            'title'            => trans('shared/common.edit'),
            'model'            => $model,
            'addAsset'         => $addAsset,
            'item'             => $item,
            'receipt'          => $receipt,
            'poHeader'         => $poHeader,
            'category'         => $category, 
            'assigment'        => $assigment,
            'url'              => self::URL,
            'resource'         => self::RESOURCE,
            'optionAsset'    => $this->getAsset(),
        ]);
    }

    public function save(Request $request)
    {
        $id    = intval($request->get('id'));

        $this->validate($request, [
            'assetId'             => 'required',
            'assetNumber'             => 'required',
        ]);

        $serviceDate    = !empty($request->get('serviceDate')) ? new \DateTime($request->get('serviceDate')) : null;
        $finishDate    = !empty($request->get('finishDate')) ? new \DateTime($request->get('finishDate')) : null;

        $opr = empty($id) ? 'I' : 'U';

        $model               = !empty($id) ? ServiceAsset::find($id) : new ServiceAsset();

        $model->asset_id     = intval($request->get('assetId'));
        $model->note         = $request->get('note');
        $model->service_date = !empty($serviceDate) ? $serviceDate->format('Y-m-d H:i:s'):null;
        $now                         = new \DateTime();
        
        $asset = AdditionAsset::find($model->asset_id);
        
        if ($opr == 'I') {
            $asset->status_id      = AdditionAsset::ONSERVICE;
            $model->service_type   = ServiceAsset::ASSET;
            $model->finished       = false;
            $model->service_number = $this->getServiceNumber($model);
            $model->created_date   = $now;
            $model->created_by     = \Auth::user()->id;
        }else{
            $model->last_updated_date = $now;
            $model->last_updated_by   = \Auth::user()->id;
        }
        
        if ($request->get('btn-finish') !== null || !empty($finishDate)) {
            $model->finish_date  = !empty($finishDate) ? $finishDate->format('Y-m-d H:i:s'): $now;
            $model->finished     = true;
            $assigment = $asset->assigment;
            if (!empty($assigment->employee_name)) {
                $asset->status_id = AdditionAsset::ACTIVE;
            }else{
                $asset->status_id = AdditionAsset::NONACTIVE;
            }
        }


        try {
            $model->save();            
            $asset->save();            
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('asset/menu.service-asset').' '.$model->service_number])
        );

        return redirect(self::URL);
    }

    public function printExcelIndex(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters)->get();

        \Excel::create(trans('asset/menu.service-asset').' '.\Session::get('currentBranch')->branch_code, function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('asset/menu.service-asset'));
                });

                $sheet->cells('A3:K3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('asset/fields.service-number'),
                    trans('asset/fields.asset-number'),
                    trans('purchasing/fields.po-number'),
                    trans('inventory/fields.item-description'),
                    trans('shared/common.category'),
                    trans('asset/fields.employee'),
                    trans('asset/fields.service-date'),
                    trans('asset/fields.finish-date'),
                    trans('shared/common.note'),
                    trans('shared/common.active'),
                ]);
                foreach($query as $index => $model) {
                    $serviceDate = !empty($model->service_date) ? new \DateTime($model->service_date) : null;
                    $finishDate = !empty($model->finish_date) ? new \DateTime($model->finish_date) : null;

                    $data = [
                        $index + 1,
                        $model->service_number,
                        $model->asset_number,
                        $model->po_number,
                        $model->item_description,
                        $model->category_name,
                        $model->employee_name,
                        !empty($serviceDate) ? $serviceDate->format('d-m-Y') : '',
                        !empty($finishDate) ? $finishDate->format('d-m-Y') : '',
                        $model->note,
                        $model->finished ? 'v' : 'x',
                    ];
                    $sheet->row($index + 4, $data);
                }

                $currentRow = count($query) + 5;
                if (!empty($filters['serviceNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('asset/fields.service-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['serviceNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['assetNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('asset/fields.asset-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['assetNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['poNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('purchasing/fields.po-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['poNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['item'])) {
                    $this->addLabelDescriptionCell($sheet, trans('inventory/fields.item-description'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['item'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['status'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.status'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['status'] == 'Y' ? 'v' : 'x', 'C', $currentRow);
                    $currentRow++;
                }

                $currentRow = count($query) + 5;
                $this->addLabelDescriptionCell($sheet, trans('shared/common.date'), 'E', $currentRow);
                $this->addValueDescriptionCell($sheet, $this->now->format('d-m-Y'), 'F', $currentRow);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.user'), 'E', $currentRow + 1);
                $this->addValueDescriptionCell($sheet, \Auth::user()->full_name, 'F', $currentRow + 1);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.branch'), 'E', $currentRow + 2);
                $this->addValueDescriptionCell($sheet, \Session::get('currentBranch')->branch_name, 'F', $currentRow + 2);
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

    protected function getServiceNumber(ServiceAsset $model)
    {
        $branch      = MasterBranch::find(\Session::get('currentBranch')->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('ast.service_asset')
                            ->join('ast.addition_asset', 'addition_asset.asset_id', '=', 'service_asset.asset_id')
                            ->where('service_asset.created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('service_asset.created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->where('addition_asset.branch_id', '=', $branch->branch_id)
                            ->count();

        return 'SRV.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }

    protected function getItem(){
        return \DB::table('ast.v_mask_add_asset_lov')->where('branch_id', '=', \Session::get('currentBranch')->branch_id)->get();
    }

    protected function getAsset(){
        return \DB::table('ast.v_addition_asset')
                ->where('status_id', '<>', AdditionAsset::RETIREMENT)
                ->where('status_id', '<>', AdditionAsset::ONSERVICE)
                ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)->get();
    }

}
