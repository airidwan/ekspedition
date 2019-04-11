<?php

namespace App\Modules\Accountreceivables\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Accountreceivables\Model\Master\MasterCekGiro;

class MasterCekGiroController extends Controller
{
    const RESOURCE = 'Accountreceivables\Master\MasterCekGiro';
    const URL      = 'accountreceivables/master/master-cek-giro';

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
        $query = \DB::table('ar.v_mst_cek_giro')->orderBy('cek_giro_id','desc');

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('active', '=', 'Y');
        } else {
            $query->where('active', '=', 'N');
        }

        if (!empty($filters['number']) && $filters['number'] != 'ALL') {
            $query->where('cg_number', '=', $filters['number']);
        }

        if (!empty($filters['type']) && $filters['type'] != 'ALL') {
            $query->where('cg_type', '=', $filters['type']);
        }

        if (!empty($filters['customer']) && $filters['customer'] != 'ALL') {
            $query->where('customer_id', '=', $filters['customer']);
        }

         if (!empty($filters['startDate'])) {
            $startDate = new \DateTime($filters['startDate']);
            $query->where('cg_date', '>=', $startDate->format('Y-m-d'));
        }

        if (!empty($filters['dueDate'])) {
            $dueDate = new \DateTime($filters['dueDate']);
            $query->where('cg_due_date', '<=', $dueDate->format('Y-m-d'));
        }

        return view('accountreceivables::master.master-cek-giro.index', [
            'models'         => $query->paginate(10),
            'optionNumber'   => $this->optionNumber(),
            'optionCustomer' => $this->optionCustomer($request->session()->get('currentBranch')->branch_id),
            'filters'        => $filters,
            'resource'       => self::RESOURCE,
            'url'            => self::URL,
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new MasterCekGiro();
        $model->active = 'Y';

        return view('accountreceivables::master.master-cek-giro.add', [
            'title' => trans('shared/common.add'),
            'optionCustomer' => $this->optionCustomer($request->session()->get('currentBranch')->branch_id),
            'model' => $model,
            'url' => self::URL,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterCekGiro::where('cek_giro_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        return view('accountreceivables::master.master-cek-giro.add', [
            'title' => trans('shared/common.edit'),
            'optionCustomer' => $this->optionCustomer($request->session()->get('currentBranch')->branch_id),
            'model' => $model,
            'url' => self::URL,
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? MasterCekGiro::where('cek_giro_id', '=', $id)->first() : new MasterCekGiro();

        $customer    = explode(':', $request->get('customer'));
        $this->validate($request, [
            'type' => 'required',
            'number' => 'required|max:25|unique:ar.mst_cek_giro,cg_number,'.$id.',cek_giro_id,customer_id,'.$customer[0],
        ]);

        $startDate  = !empty($request->get('startDate')) ? new \DateTime($request->get('startDate')) : null;
        $startDate  = !empty($startDate) ? "'".$startDate->format('Y-m-d H:i:s')."'" : null;
        $dueDate    = !empty($request->get('dueDate')) ? new \DateTime($request->get('dueDate')) : null;
        $dueDate    = !empty($dueDate) ? "'".$dueDate->format('Y-m-d H:i:s')."'" : null;

        $model->cg_number    = $request->get('number');
        $model->customer_id  = $customer[0];
        $model->bank_name    = $request->get('bankName');
        $model->cg_type      = $request->get('type');
        $model->cg_date      = $startDate;
        $model->cg_due_date  = $dueDate;
        $model->active       = !empty($request->get('status')) ? 'Y' : 'N';
        $now = new \DateTime();

        if (empty($id)) {
            $model->created_date = $now;
            $model->created_by = \Auth::user()->id;
        } else {
            $model->last_updated_date = $now;
            $model->last_updated_by = \Auth::user()->id;
        }

        $model->save();

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('accountreceivables/menu.master-cek-giro').' '.$customer[1].' - '.$model->cg_number])
        );

        return redirect(self::URL);
    }

    public function delete(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'delete'])) {
            abort(403);
        }

        $model = MasterCekGiro::where('cek_giro_id', '=', $request->get('id'))->first();
        if ($model === null) {
            abort(404);
        }

        $model->active = 'N';
        $model->last_updated_date = new \DateTime();
        $model->last_updated_by = \Auth::user()->id;
        $model->save();

        $request->session()->flash(
            'successMessage',
            trans('shared/common.deleted-message', ['variable' => trans('accountreceivables/menu.master-cek-giro').' '.$model->coa_code])
        );

        return redirect(self::URL);
    }

    function optionNumber(){
        return \DB::table('ar.mst_cek_giro')
                ->select('cg_number')
                ->where('active','=','Y')
                ->orderBy('cek_giro_id','desc')->get();
    }

    function optionCustomer($branchId){
        return \DB::table('op.v_mst_customer')
                    ->select('v_mst_customer.customer_id', 'v_mst_customer.customer_name')
                    // ->orderBy('v_mst_customer.customer_id', 'desc')
                    ->leftJoin('op.dt_customer_branch', 'v_mst_customer.customer_id', '=', 'dt_customer_branch.customer_id')
                    ->distinct()
                    ->where('dt_customer_branch.branch_id', '=', $branchId)
                    ->where('v_mst_customer.active', '=', 'Y')->get();
    }
}
