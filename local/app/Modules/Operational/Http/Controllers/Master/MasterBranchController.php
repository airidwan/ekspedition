<?php

namespace App\Modules\Operational\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Model\Master\MasterCity;
use App\Modules\Operational\Model\Master\MasterOrganization;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Service\Penomoran;
use App\MasterLookupValues;

class MasterBranchController extends Controller
{
    const RESOURCE = 'Operational\Master\MasterBranch';
    const URL      = 'operational/master/master-branch';

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
        $query = $this->getQuery($request);

        return view('operational::master.master-branch.index', [
            'models'     => $query->paginate(10),
            'filters'    => $filters,
            'resource'   => self::RESOURCE,
            'url'        => self::URL,
            'optionCity' => \DB::table('op.v_mst_city')->where('active', '=', 'Y')->get()
        ]);
    }

    protected function getQuery(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = \DB::table('op.v_mst_branch')->orderBy('branch_code_numeric', 'desc');

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('active', '=', 'Y');
        } else {
            $query->where('active', '=', 'N');
        }

        if (!empty($filters['code'])) {
            $query->where('branch_code', 'ilike', '%'.$filters['code'].'%');
        }

        if (!empty($filters['name'])) {
            $query->where('branch_name', 'ilike', '%'.$filters['name'].'%');
        }

        if (!empty($filters['city'])) {
            $query->where('city_id', '=', $filters['city']);
        }

        return $query;
    }

    public function printExcel(Request $request)
    {
        $query   = $this->getQuery($request);
        $filters = \Session::get('filters');

        \Excel::create(trans('operational/menu.branch'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.branch'));
                });

                $sheet->cells('A3:M3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('operational/fields.id-number'),
                    trans('operational/fields.code'),
                    trans('shared/common.name'),
                    trans('shared/common.address'),
                    trans('shared/common.phone'),
                    trans('operational/fields.branch-manager'),
                    trans('shared/common.city'),
                    trans('operational/fields.cost-center'),
                    trans('operational/fields.main-branch'),
                    trans('shared/common.active'),
                ]);

                $currentRow = 4;
                foreach($query->get() as $model) {
                    $modelBranch = MasterBranch::find($model->branch_id);

                    $data = [
                        $model->branch_code_numeric,
                        $model->branch_code,
                        $model->branch_name,
                        $model->address,
                        $model->phone_number,
                        $model->manager_name,
                        $model->city_name,
                        $model->cost_center_code,
                        $modelBranch->main_branch ? 'V' : 'X',
                        $model->active == 'Y' ? 'V' : 'X',
                    ];

                    $sheet->row($currentRow++, $data);
                }

                $lastDataRow = $currentRow;
                $currentRow = $lastDataRow + 1;
                if (!empty($filters['code'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.code'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['code'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['name'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.name'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['name'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['city'])) {
                    $city = MasterCity::find($filters['city']);
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.city'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $city->city_name, 'C', $currentRow);
                    $currentRow++;
                }

                $currentRow = $lastDataRow + 1;
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

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model         = new MasterBranch();
        $model->active = 'Y';

        return view('operational::master.master-branch.add', [
            'title'            => trans('shared/common.add'),
            'model'            => $model,
            'url'              => self::URL,
            'optionUser'       => \DB::table('adm.users')->get(),
            'optionCity'       => \DB::table('op.v_mst_city')->where('active', '=', 'Y')->get(),
            ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterBranch::where('branch_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        return view('operational::master.master-branch.add', [
            'title'            => trans('shared/common.edit'),
            'model'            => $model,
            'url'              => self::URL,
            'optionUser'       => \DB::table('adm.users')->get(),
            'optionCity'       => \DB::table('op.v_mst_city')->where('active', '=', 'Y')->get(),
            ]);
    }

    public function save(Request $request)
    {
        $id    = intval($request->get('id'));
        $model = !empty($id) ? MasterBranch::where('branch_id', '=', $id)->first() : new MasterBranch();
        $this->validate($request, [
            'code' => 'required|max:25|unique:operational.mst_branch,branch_code,'.$id.',branch_id',
            'name'  => 'required|max:55',
            'city'  => 'required|max:55',
        ]);

        if(empty($request->get('mainBranch'))) {
            $currentMainBranchs = $this->getCurrentMainBranchs($request->get('city'), $id);
            if ($currentMainBranchs->count() == 0) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Main Branch for this city is not exist']);
            }
        }

        $status='Y';
        if ($request->get('status')!="Y") {
            $status="N";
        }

        $modelCoa = !empty($id) ?
                        MasterCoa::where('segment_name', '=', MasterCoa::COST_CENTER)->where('coa_code','=',$request->get('costCenterCode'))->first() :
                        new MasterCoa();

        $costCenterCode = $request->get('costCenterCode', '');

        $modelCoa->description = $request->get('name','');
        $modelCoa->segment_name = MasterCoa::COST_CENTER;
        $now = new \DateTime();

        $last  = \DB::table('op.mst_branch')->count();

        if (empty($id)) {
            
            $costCenterCode = Penomoran::getStringNomor($last+1, 3);
            $codeNumber     = Penomoran::getStringNomor($last+1, 2);
            
            $modelCoa->coa_code = $costCenterCode;
            $modelCoa->created_date = $now;
            $modelCoa->created_by = \Auth::user()->id;
        }else{
            $modelCoa->last_updated_date = $now;
            $modelCoa->last_updated_by = \Auth::user()->id;
        }
        
        $modelCoa->active = $status;
        $modelCoa->save();

        $model->branch_code         = $request->get('code');
        $model->branch_name         = $request->get('name');
        $model->address             = $request->get('address');
        $model->phone_number        = $request->get('phone');
        $model->city_id             = $request->get('city');
        $model->branch_manager      = $request->get('branchManager');
        $model->cost_center_code    = $costCenterCode;
        $model->org_id              = MasterCoa::COMPANY_CODE;
        $model->main_branch         = !empty($request->get('mainBranch'));
        $model->active              = $status;

        if (empty($id)) {
            $model->branch_code_numeric = $codeNumber;
            $model->created_date = $now;
            $model->created_by   = \Auth::user()->id;
        }else{
            $model->last_updated_date = $now;
            $model->last_updated_by   = \Auth::user()->id;
        }
        $model->save();

        if ($model->main_branch) {
            foreach ($this->getCurrentMainBranchs($request->get('city'), $id) as $mainBranch) {
                $mainBranch->main_branch = false;
                $mainBranch->save();
            }
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.branch').' '.$model->branch_name])
        );

        return redirect(self::URL);
    }

    protected function getCurrentMainBranchs($cityId, $id)
    {
        return MasterBranch::where('city_id', '=', $cityId)->where('branch_id', '<>', $id)->where('main_branch', '=', true)->get();
    }

    protected function optionCostCenter(){
        return \DB::table('gl.mst_coa')
        ->where('segment_name','=','Cost Center')
        ->where('active', '=', 'Y')
        ->orderBy('coa_code')->get();
    }
}
