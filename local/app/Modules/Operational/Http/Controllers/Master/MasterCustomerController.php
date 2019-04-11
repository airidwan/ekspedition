<?php

namespace App\Modules\Operational\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Master\MasterCustomer;
use App\Modules\Operational\Model\Master\MasterCity;
use App\Modules\Operational\Model\Master\DetailCustomerBranch;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Service\Penomoran;

class MasterCustomerController extends Controller
{
    const RESOURCE = 'Operational\Master\MasterCustomer';
    const URL      = 'operational\master\master-customer';
    const DESC     = 'CUSTOMER';

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

        return view('operational::master.master-customer.index', [
            'models'      => $query->paginate(10),
            'filters'    => $filters,
            'resource'   => self::RESOURCE,
            'url'        => self::URL,
            'optionCity' => \DB::table('op.v_mst_city')->get()
        ]);
    }

    protected function getQuery(Request $request)
    {
        $filters = $request->session()->get('filters');
        $query   = \DB::table('op.v_mst_customer')
                    ->select('v_mst_customer.*')
                    ->join('op.dt_customer_branch', 'v_mst_customer.customer_id', '=', 'dt_customer_branch.customer_id')
                    ->where('dt_customer_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where('dt_customer_branch.active', '=', 'Y')
                    ->orderBy('v_mst_customer.customer_code')
                    ->distinct();

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('v_mst_customer.active', '=', 'Y');
        } else {
            $query->where('v_mst_customer.active', '=', 'N');
        }

        if (!empty($filters['code'])) {
            $query->where('customer_code', 'ilike', '%'.$filters['code'].'%');
        }

        if (!empty($filters['name'])) {
            $query->where('customer_name', 'ilike', '%'.$filters['name'].'%');
        }

        if (!empty($filters['phone'])) {
            $query->where('phone_number', 'ilike', '%'.$filters['phone'].'%');
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

        \Excel::create(trans('operational/menu.customer'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.customer'));
                });

                $sheet->cells('A3:K3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.code'),
                    trans('shared/common.name'),
                    trans('shared/common.address'),
                    trans('shared/common.city'),
                    trans('shared/common.phone'),
                    trans('shared/common.notes'),
                    trans('operational/fields.contact-person'),
                    trans('operational/fields.phone-cp'),
                    trans('shared/common.category'),
                    trans('operational/fields.sub-account'),
                    trans('shared/common.active'),
                ]);

                $currentRow = 4;
                foreach($query->get() as $model) {
                    $data = [
                        $model->customer_code,
                        $model->customer_name,
                        $model->address,
                        $model->city_name,
                        $model->phone_number,
                        $model->description,
                        $model->contact_person,
                        $model->contact_phone,
                        $model->customer_category,
                        $model->subaccount_code,
                        $model->active == 'Y' ? 'V' : 'X'
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
                if (!empty($filters['phone'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.phone'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['phone'], 'C', $currentRow);
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

        $model         = new MasterCustomer();
        $model->active = 'Y';

        return view('operational::master.master-customer.add', [
            'title'            => trans('shared/common.add'),
            'model'            => $model,
            'url'              => self::URL,
            'optionCity'       => \DB::table('op.v_mst_city')->get(),
            'optionCategory'   => \DB::table('adm.v_mst_lookup_values')->where('lookup_type', 'CUST_CATEGORY')->get(),
            'optionBranch'     => \DB::table('op.mst_branch')->where('active', '=','Y')->orderBy('branch_name')->get(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterCustomer::where('customer_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        if (!$this->checkAccessBranch($model)) {
            abort(403);
        }

        return view('operational::master.master-customer.add', [
            'title'            => trans('shared/common.edit'),
            'model'            => $model,
            'url'              => self::URL,
            'optionCity'       => \DB::table('op.v_mst_city')->get(),
            'optionCategory'   => \DB::table('adm.v_mst_lookup_values')->where('lookup_type', 'CUST_CATEGORY')->get(),
            'optionBranch'     => \DB::table('op.mst_branch')->where('active', '=','Y')->orderBy('branch_name')->get(),
        ]);
    }

    public function save(Request $request)
    {
        $id    = intval($request->get('id'));
        $model = !empty($id) ? MasterCustomer::where('customer_id', '=', $id)->first() : new MasterCustomer();

        $this->validate($request, [
            'name'  => 'required|max:55',
            'city'  => 'required|max:55',
            'category'  => 'required',
            ]);

        if (empty($request->get('branchDetail'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Branch detail on the Activation tab can not be empty']);
        }

        $status='Y';
        if ($request->get('status')!="Y") {
            $status="N";
        }

        $opr = empty($model->customer_id) ? 'I' : 'U';

        $model = !empty($id) ? MasterCustomer::find($id) : new MasterCustomer();
        $model->customer_name    = str_replace("'", "`", $request->get('name'));
        $model->address          = str_replace("'", "`", $request->get('address'));
        $model->city_id          = $request->get('city');
        $model->phone_number     = str_replace("'", "`", $request->get('phone'));
        $model->contact_person   = str_replace("'", "`", $request->get('contactPerson'));
        $model->contact_phone    = str_replace("'", "`", $request->get('contactPhone'));
        $model->category         = $request->get('category');
        $model->description      = str_replace("'", "`", $request->get('description'));
        $model->active           = $status;
        $now = new \DateTime();

        if ($opr == 'I') {
            $model->branch_id_insert = $request->session()->get('currentBranch')->branch_id ;
            $count                = \DB::table('op.mst_customer')->where('branch_id_insert', '=', \Session::get('currentBranch')->branch_id)->count();
            $customerCode         = 'C.'.\Session::get('currentBranch')->branch_code.'.'.Penomoran::getStringNomor($count+1, 4);
            $model->customer_code = $customerCode;
            
            $count = \DB::table('gl.mst_coa')->where('segment_name','=',MasterCoa::SUB_ACCOUNT)->count();
            $codeSub = Penomoran::getStringNomor($count+1, 5);
            $model->subaccount_code  = $codeSub;

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

        $model->customerBranch()->delete();
        foreach ($request->get('branchDetail') as $branch) {
            $customerBranch = new DetailCustomerBranch();
            $customerBranch->customer_id = $model->customer_id;
            $customerBranch->branch_id = $branch;
            $customerBranch->active = 'Y';
            if ($opr == 'I') {
                $customerBranch->created_date = $now;
                $customerBranch->created_by = \Auth::user()->id;
            }else{
                $customerBranch->last_updated_date = $now;
                $customerBranch->last_updated_by = \Auth::user()->id;
            }

            try {
                $customerBranch->save();
            } catch (\Exception $e) {
                return redirect(self::URL.'/edit/'.$model->customer_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }
        }

        $modelCoa = !empty($id) ? MasterCoa::where('segment_name', '=', MasterCoa::SUB_ACCOUNT)->where('coa_code','=',$request->get('subAccountCode'))->first() : new MasterCoa();

        if($modelCoa->coa_code != MasterCoa::NONAME_SUB_ACCOUNT ){
            $modelCoa->description = $request->get('name').' ('.self::DESC.')';
        }
        
        $modelCoa->segment_name = MasterCoa::SUB_ACCOUNT;

        if ($opr == 'I') {
            $modelCoa->coa_code = $codeSub;
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
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.customer').' '.$model->customer_name])
            );

        return redirect(self::URL);
    }

    protected function checkAccessBranch(MasterCustomer $model)
    {
        $canAccessBranch = false;
        foreach ($model->customerBranch as $customerBranch) {
            $branch = $customerBranch->branch;
            if ($branch !== null && \Auth::user()->can('accessBranch', $branch->branch_id)) {
                $canAccessBranch = true;
            }
        }

        return $canAccessBranch;
    }
}
