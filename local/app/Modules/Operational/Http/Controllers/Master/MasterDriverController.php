<?php

namespace App\Modules\Operational\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Master\MasterDriver;
use App\Modules\Operational\Model\Master\DetailDriverBranch;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Service\Penomoran;

class MasterDriverController extends Controller
{
    const RESOURCE = 'Operational\Master\MasterDriver';
    const URL      = 'operational/master/master-driver';
    const DESC     = 'DRIVER/ASSISTAN';

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
        $query   = $this->getQuery($request);

        return view('operational::master.master-driver.index', [
            'models'         => $query->paginate(10),
            'filters'        => $filters,
            'resource'       => self::RESOURCE,
            'url'            => self::URL,
            'optionPosition' => \DB::table('adm.v_mst_lookup_values')->where('lookup_type', 'DRIVER_CATEGORY')->orderBy('meaning','desc')->get(),
            'optionType'     => $this->getType(),
        ]);
    }

    protected function getQuery(Request $request)
    {
        $filters = $request->session()->get('filters');
        $query   = \DB::table('op.v_mst_driver')
                        ->select('v_mst_driver.*')
                        ->join('op.dt_driver_branch', 'v_mst_driver.driver_id', '=', 'dt_driver_branch.driver_id')
                        ->where('dt_driver_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->orderBy('v_mst_driver.driver_code')
                        ->distinct();

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('v_mst_driver.active', '=', 'Y');
        } else {
            $query->where('v_mst_driver.active', '=', 'N');
        }

        if (!empty($filters['code'])) {
            $query->where('driver_code', 'ilike', '%'.$filters['code'].'%');
        }

        if (!empty($filters['name'])) {
            $query->where('driver_name', 'ilike', '%'.$filters['name'].'%');
        }

        if (!empty($filters['position'])) {
            $query->where('position', '=', $filters['position']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', '=', $filters['type']);
        }

        return $query;
    }

    public function printExcel(Request $request)
    {
        $query   = $this->getQuery($request);
        $filters = \Session::get('filters');

        \Excel::create(trans('operational/menu.driver'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.driver'));
                });

                $sheet->cells('A3:N3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.code'),
                    trans('shared/common.name'),
                    trans('operational/fields.nickname'),
                    trans('operational/fields.no-ktp'),
                    trans('shared/common.alamat'),
                    trans('shared/common.kota'),
                    trans('shared/common.telepon'),
                    trans('operational/fields.position'),
                    trans('shared/common.type'),
                    trans('operational/fields.tanggal-masuk'),
                    trans('operational/fields.tanggal-keluar'),
                    trans('shared/common.status'),
                    trans('shared/common.keterangan'),
                    trans('operational/fields.sub-account'),
                ]);

                $currentRow = 4;
                foreach($query->get() as $model) {
                    $joinDate    = !empty($model->join_date) ? new \DateTime($model->join_date) : null;
                    $resignDate  = !empty($model->resign_date) ? new \DateTime($model->resign_date) : null;

                    $data = [
                        $model->driver_code,
                        $model->driver_name,
                        $model->driver_nickname,
                        $model->identity_number,
                        $model->address,
                        $model->city_name,
                        $model->phone_number,
                        $model->driver_category,
                        $model->type,
                        !empty($joinDate) ? $joinDate->format('d-M-Y') : '',
                        !empty($resignDate) ? $resignDate->format('d-M-Y') : '',
                        $model->merried_status,
                        $model->description,
                        $model->subaccount_code,
                    ];

                    $sheet->row($currentRow++, $data);
                }

                $lastDataRow = $currentRow;
                $currentRow = $lastDataRow + 1;
                if (!empty($filters['code'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.code'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['code'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['name'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.name'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['name'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['position'])) {
                    $position = \App\MasterLookupValues::where('lookup_type', 'DRIVER_CATEGORY')->where('lookup_code', $filters['position'])->first();
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.position'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $position->meaning, 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['type'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.type'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['type'], 'C', $currentRow);
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

        $model         = new MasterDriver();
        $model->active = 'Y';

        return view('operational::master.master-driver.add', [
            'title'            => trans('shared/common.add'),
            'model'            => $model,
            'url'              => self::URL,
            'optionCity'       => \DB::table('op.v_mst_city')->get(),
            'optionPosition'   => \DB::table('adm.v_mst_lookup_values')->where('lookup_type', 'DRIVER_CATEGORY')->orderBy('meaning','desc')->get(),
            'optionBranch'     => \DB::table('op.mst_branch')->where('active', '=', 'Y')->orderBy('branch_name')->get(),
            'optionType'       => $this->getType(),
        ]);
    }

     public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterDriver::where('driver_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        if (!$this->checkAccessBranch($model)) {
            abort(403);
        }

        return view('operational::master.master-driver.add', [
            'title'            => trans('shared/common.edit'),
            'model'            => $model,
            'url'              => self::URL,
            'optionCity'       => \DB::table('op.v_mst_city')->get(),
            'optionPosition'   => \DB::table('adm.v_mst_lookup_values')->where('lookup_type', 'DRIVER_CATEGORY')->orderBy('meaning','desc')->get(),
            'optionBranch'     => \DB::table('op.mst_branch')->where('active', '=', 'Y')->orderBy('branch_name')->get(),
            'optionType'       => $this->getType(),
        ]);
    }

    public function save(Request $request)
    {
        $id    = intval($request->get('id'));
        $model = !empty($id) ? MasterDriver::where('driver_id', '=', $id)->first() : new MasterDriver();

        $this->validate($request, [
            'name'           => 'required|max:55',
            'position'       => 'required',
            'type'           => 'required',
            'identityNumber' => 'required|max:55',
            'city'           => 'required|max:55',
        ]);

        if (empty($request->get('branchDetail'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Branch detail on the Activation tab can not be empty']);
        }

        $status='Y';
        if ($request->get('status')!="Y") {
            $status="N";
        }

        $joinDate      = !empty($request->get('joinDate')) ? new \DateTime($request->get('joinDate')) : null;
        $resignDate    = !empty($request->get('resignDate')) ? new \DateTime($request->get('resignDate')) : null;
        $joinDate      = !empty($joinDate) ? $joinDate->format('Y-m-d H:i:s'):null;
        $resignDate    = !empty($resignDate) ? $resignDate->format('Y-m-d H:i:s') :null;
        $now = new \DateTime();

        $opr = empty($model->driver_id) ? 'I' : 'U';

        $model = !empty($id) ? MasterDriver::find($id) : new MasterDriver();
        $model->driver_name      = str_replace("'", "`", $request->get('name'));
        $model->driver_nickname  = str_replace("'", "`", $request->get('nickname'));
        $model->identity_number  = str_replace("'", "`", $request->get('identityNumber'));
        $model->address          = str_replace("'", "`", $request->get('address'));
        $model->city_id          = str_replace("'", "`", $request->get('city'));
        $model->phone_number     = str_replace("'", "`", $request->get('phone'));
        $model->driver_name      = str_replace("'", "`", $request->get('name'));
        $model->position         = $request->get('position');
        $model->type             = $request->get('type');
        $model->merried_status   = $request->get('merriedStatus');
        $model->description      = str_replace("'", "`", $request->get('description'));
        $model->active           = $status;
        if (!empty($request->get('resignDate'))) {
            $model->active = 'N';
        }
        $model->join_date        = $joinDate;
        $model->resign_date      = $resignDate;
        if ($opr == 'I') {
            $count                = \DB::table('op.mst_driver')->where('branch_id_insert', '=', \Session::get('currentBranch')->branch_id)->count();
            $driverCode           = 'DRA.'.\Session::get('currentBranch')->branch_code.'.'.Penomoran::getStringNomor($count+1, 4);
            $model->driver_code   = $driverCode;

            $count = \DB::table('gl.mst_coa')->where('segment_name','=',MasterCoa::SUB_ACCOUNT)->count();
            $subAccountCode = Penomoran::getStringNomor($count+1, 5);
            $model->subaccount_code = $subAccountCode;
            $model->branch_id_insert = $request->session()->get('currentBranch')->branch_id;

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

        $model->driverBranch()->delete();
        foreach ($request->get('branchDetail') as $branch) {
            $driverBranch = new DetailDriverBranch();
            $driverBranch->driver_id = $model->driver_id;
            $driverBranch->branch_id = $branch;
            $driverBranch->active = 'Y';
            if ($opr == 'I') {
                $driverBranch->created_date = $now;
                $driverBranch->created_by = \Auth::user()->id;
            }else{
                $driverBranch->last_updated_date = $now;
                $driverBranch->last_updated_by = \Auth::user()->id;
            }
            try {
                $driverBranch->save();
            } catch (\Exception $e) {
                return redirect(self::URL.'/edit/'.$model->driver_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }
        }

        $modelCoa = !empty($id) ? MasterCoa::where('segment_name', '=', MasterCoa::SUB_ACCOUNT)->where('coa_code','=',$model->subaccount_code)->first() : new MasterCoa();
        $modelCoa->description = $request->get('name').' ('.self::DESC.')';
        $modelCoa->segment_name = MasterCoa::SUB_ACCOUNT;
        

        if ($opr == 'I') {
            $modelCoa->coa_code = $subAccountCode;
            $modelCoa->created_date = $now;
            $modelCoa->created_by = \Auth::user()->id;
        }else{
            $modelCoa->last_updated_date = $now;
            $modelCoa->last_updated_by = \Auth::user()->id;
        }
        $modelCoa->active = $status;
        try {
                $modelCoa->save();
            } catch (\Exception $e) {
                return redirect(self::URL.'/edit/'.$model->driver_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }
      
        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.driver').' '.$model->driver_name])
        );

        return redirect(self::URL);
    }

    protected function getType(){
        return [
            MasterDriver::MONTHLY_EMPLOYEE,
            MasterDriver::TRIP_EMPLOYEE,
            MasterDriver::NON_EMPLOYEE,
        ];
    }

    protected function checkAccessBranch(MasterDriver $model)
    {
        $canAccessBranch = false;
        foreach ($model->driverBranch as $driverBranch) {
            $branch = $driverBranch->branch;
            if ($branch !== null && \Auth::user()->can('accessBranch', $branch->branch_id)) {
                $canAccessBranch = true;
            }
        }

        return $canAccessBranch;
    }
}
