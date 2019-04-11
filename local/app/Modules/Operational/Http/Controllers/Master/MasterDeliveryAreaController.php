<?php

namespace App\Modules\Operational\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Master\MasterDeliveryArea;
use App\Modules\Operational\Model\Master\DetailDeliveryAreaBranch;
use App\Service\Penomoran;

class MasterDeliveryAreaController extends Controller
{
    const RESOURCE = 'Operational\Master\MasterDeliveryArea';
    const URL      = 'operational\master\master-delivery-area';
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
        $query   = $this->getQuery($request, $filters);

        return view('operational::master.master-delivery-area.index', [
            'models'      => $query->paginate(10),
            'filters'    => $filters,
            'resource'   => self::RESOURCE,
            'url'        => self::URL,
            'optionCity' => \DB::table('op.v_mst_city')->get()
        ]);
    }

    public function getQuery(Request $request, $filters){
        $query   = \DB::table('op.mst_delivery_area')
                    ->select('mst_delivery_area.*')
                    ->join('op.dt_delivery_area_branch', 'mst_delivery_area.delivery_area_id', '=', 'dt_delivery_area_branch.delivery_area_id')
                    ->where('dt_delivery_area_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('mst_delivery_area.created_date', 'desc')
                    ->distinct();

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('mst_delivery_area.active', '=', 'Y');
        } else {
            $query->where('mst_delivery_area.active', '=', 'N');
        }

        if (!empty($filters['name'])) {
            $query->where('delivery_area_name', 'ilike', '%'.$filters['name'].'%');
        }

        if (!empty($filters['description'])) {
            $query->where('description', 'ilike', '%'.$filters['description'].'%');
        }

        return $query;
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model         = new MasterDeliveryArea();
        $model->active = 'Y';

        return view('operational::master.master-delivery-area.add', [
            'title'        => trans('shared/common.add'),
            'model'        => $model,
            'url'          => self::URL,
            'optionBranch' => \DB::table('op.mst_branch')->where('active', '=','Y')->orderBy('branch_name')->get(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterDeliveryArea::where('delivery_area_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        if (!$this->checkAccessBranch($model)) {
            abort(403);
        }

        return view('operational::master.master-delivery-area.add', [
            'title'        => trans('shared/common.edit'),
            'model'        => $model,
            'url'          => self::URL,
            'optionBranch' => \DB::table('op.mst_branch')->where('active', '=','Y')->orderBy('branch_name')->get(),
        ]);
    }

    public function save(Request $request)
    {
        $id    = intval($request->get('id'));
        $model = !empty($id) ? MasterDeliveryArea::where('delivery_area_id', '=', $id)->first() : new MasterDeliveryArea();

        $this->validate($request, [
            'name'         => 'required|max:55',
            'description'  => 'required|max:500',
            ]);

        if (empty($request->get('branchDetail'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Branch detail on the Activation tab can not be empty']);
        }

        $status='Y';
        if ($request->get('status')!="Y") {
            $status="N";
        }

        $opr = empty($model->delivery_area_id) ? 'I' : 'U';

        $model = !empty($id) ? MasterDeliveryArea::find($id) : new MasterDeliveryArea();
        $model->delivery_area_name = $request->get('name');
        $model->description        = $request->get('description');
        $model->active             = $status;
        $now = new \DateTime();

        if ($opr == 'I') {
            $model->created_date  = $now;
            $model->created_by    = \Auth::user()->id;
        }else{
            $model->last_updated_date = $now;
            $model->last_updated_by   = \Auth::user()->id;
        }

        try {
            $model->save();            
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $model->deliveryAreaBranch()->delete();
        foreach ($request->get('branchDetail') as $branch) {
            $deliveryAreaBranch = new DetailDeliveryAreaBranch();
            $deliveryAreaBranch->delivery_area_id = $model->delivery_area_id;
            $deliveryAreaBranch->branch_id = $branch;
            $deliveryAreaBranch->active = 'Y';
            if ($opr == 'I') {
                $deliveryAreaBranch->created_date = $now;
                $deliveryAreaBranch->created_by = \Auth::user()->id;
            }else{
                $deliveryAreaBranch->last_updated_date = $now;
                $deliveryAreaBranch->last_updated_by = \Auth::user()->id;
            }

            try {
                $deliveryAreaBranch->save();
            } catch (\Exception $e) {
                return redirect(self::URL.'/edit/'.$model->delivery_area_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.delivery-area').' '.$model->delivery_area_name])
            );

        return redirect(self::URL);
    }

    public function printExcelIndex(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters)->get();

        \Excel::create(trans('operational/menu.delivery-area').' '.\Session::get('currentBranch')->branch_name, function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.delivery-area'));
                });

                $sheet->cells('A3:D3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('operational/fields.area-name'),
                    trans('shared/common.description'),
                    trans('shared/common.active'),
                ]);
                foreach($query as $index => $model) {
                    $data = [
                        $index + 1,
                        $model->delivery_area_name,
                        $model->description,
                        $model->active == 'Y' ? 'v' : 'x',
                    ];
                    $sheet->row($index + 4, $data);
                }

                $currentRow = count($query) + 5;
                if (!empty($filters['name'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.area-name'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['name'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['description'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.description'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['description'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['status'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.status'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['status'], 'C', $currentRow);
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

    protected function checkAccessBranch(MasterDeliveryArea $model)
    {
        $canAccessBranch = false;
        foreach ($model->deliveryAreaBranch as $deliveryAreaBranch) {
            $branch = $deliveryAreaBranch->branch;
            if ($branch !== null && \Auth::user()->can('accessBranch', $branch->branch_id)) {
                $canAccessBranch = true;
            }
        }

        return $canAccessBranch;
    }
}
