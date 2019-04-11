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
use App\Modules\Operational\Service\Master\TruckService;
use App\Service\Penomoran;

class ServiceTruckMonthlyController extends Controller
{
    const RESOURCE = 'Asset\Transaction\ServiceTruckMonthly';
    const URL      = 'asset/transaction/service-truck-monthly';
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

        return view('asset::transaction.service-truck-monthly.index', [
            'models'         => $query->paginate(10),
            'filters'        => $filters,
            'resource'       => self::RESOURCE,
            'url'            => self::URL
        ]);
    }

    protected function getQuery(Request $request, $filters){
        $query   = \DB::table('ast.service_asset')
                    ->select('service_asset.*','mst_truck.police_number', 'mst_truck.owner_name')
                    ->join('op.mst_truck', 'mst_truck.truck_id', '=', 'service_asset.truck_id')
                    ->where('service_asset.branch_id','=', \Session::get('currentBranch')->branch_id)
                    ->where('service_asset.service_type', '=', ServiceAsset::TRUCK_MONTHLY);

        if (!empty($filters['status'])) {
            $query->where('finished', '=', $filters['status']);
        }

        if (!empty($filters['serviceNumber'])) {
            $query->where('service_number', 'ilike', '%'.$filters['serviceNumber'].'%');
        }

        if (!empty($filters['policeNumber'])) {
            $query->where('mst_truck.police_number', 'ilike', '%'.$filters['policeNumber'].'%');
        }

        if (!empty($filters['ownnerName'])) {
            $query->where('mst_truck.owner_name', 'ilike', '%'.$filters['owner_name'].'%');
        }

        return $query;
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model        = new ServiceAsset();

        return view('asset::transaction.service-truck-monthly.add', [
            'title'            => trans('shared/common.add'),
            'model'            => $model,
            'url'              => self::URL,
            'resource'         => self::RESOURCE,
            'optionTruck'      => TruckService::getActiveTruckMonthly(),
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

        return view('asset::transaction.service-truck-monthly.add', [
            'title'            => trans('shared/common.edit'),
            'model'            => $model,
            'url'              => self::URL,
            'resource'         => self::RESOURCE,
            'optionTruck'      => TruckService::getActiveTruckMonthly(),
        ]);
    }

    public function save(Request $request)
    {
        $id    = intval($request->get('id'));

        $this->validate($request, [
            'truckId'             => 'required',
        ]);

        $serviceDate    = !empty($request->get('serviceDate')) ? new \DateTime($request->get('serviceDate')) : null;
        $finishDate    = !empty($request->get('finishDate')) ? new \DateTime($request->get('finishDate')) : null;

        $opr = empty($id) ? 'I' : 'U';

        $model               = !empty($id) ? ServiceAsset::find($id) : new ServiceAsset();


        $model->truck_id     = intval($request->get('truckId'));
        $model->service_type = ServiceAsset::TRUCK_MONTHLY;
        $model->note         = $request->get('note');
        $model->service_date = !empty($serviceDate) ? $serviceDate->format('Y-m-d H:i:s'):null;
        $now                         = new \DateTime();
        
        if ($request->get('btn-finish') !== null || !empty($finishDate)) {
            $model->finish_date  = !empty($finishDate) ? $finishDate->format('Y-m-d H:i:s'): $now;
            $model->finished     = true;
        }

        if ($opr == 'I') {
            $model->finished       = false;
            $model->service_number = $this->getServiceNumber($model);
            $model->branch_id      = \Session::get('currentBranch')->branch_id;
            $model->created_date   = $now;
            $model->created_by     = \Auth::user()->id;
        }else{
            $model->last_updated_date = $now;
            $model->last_updated_by   = \Auth::user()->id;
        }

        try {
            $model->save();            
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('asset/menu.service-truck-monthly').' '.$model->service_number])
        );

        return redirect(self::URL);
    }

    public function printExcelIndex(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters)->get();

        \Excel::create(trans('asset/menu.service-truck-monthly').' '.\Session::get('currentBranch')->branch_code, function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('asset/menu.service-truck-monthly'));
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
}
