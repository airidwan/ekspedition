<?php

namespace App\Modules\Generalledger\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Generalledger\Model\Master\MasterAccountCombination;

class MasterCoaCombinationController extends Controller
{
    const RESOURCE = 'Generalledger\Master\MasterCoaCombination';
    const URL      = 'general-ledger/master/master-coa-combination';

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
        $query = \DB::table('gl.v_mst_account_combination_edit')->orderBy('combination_code');

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('active', '=', 'Y');
        } else {
            $query->where('active', '=', 'N');
        }

        if (!empty($filters['companyFrom']) && $filters['companyFrom'] != 'ALL') {
            $query->where('segment_1_code', '>=', $filters['companyFrom']);
        }

        if (!empty($filters['companyTo']) && $filters['companyTo'] != 'ALL') {
            $query->where('segment_1_code', '<=', $filters['companyTo']);
        }

        if (!empty($filters['costCenterFrom']) && $filters['costCenterFrom'] != 'ALL') {
            $query->where('segment_2_code', '>=', $filters['costCenterFrom']);
        }

        if (!empty($filters['costCenterTo']) && $filters['costCenterTo'] != 'ALL') {
            $query->where('segment_2_code', '<=', $filters['costCenterTo']);
        }

        if (!empty($filters['accountFrom']) && $filters['accountFrom'] != 'ALL') {
            $query->where('segment_3_code', '>=', $filters['accountFrom']);
        }

        if (!empty($filters['accountTo']) && $filters['accountTo'] != 'ALL') {
            $query->where('segment_3_code', '<=', $filters['accountTo']);
        }

        if (!empty($filters['subAccountFrom']) && $filters['subAccountFrom'] != 'ALL') {
            $query->where('segment_4_code', '>=', $filters['subAccountFrom']);
        }

        if (!empty($filters['subAccountTo']) && $filters['subAccountTo'] != 'ALL') {
            $query->where('segment_4_code', '<=', $filters['subAccountTo']);
        }

        if (!empty($filters['futureFrom']) && $filters['futureFrom'] != 'ALL') {
            $query->where('segment_5_code', '>=', $filters['futureFrom']);
        }

        if (!empty($filters['futureTo']) && $filters['futureTo'] != 'ALL') {
            $query->where('segment_5_code', '<=', $filters['futureTo']);
        }

        return view('generalledger::master.master-coa-combination.index', [
            'models'           => $query->paginate(10),
            'optionCompany'    => $this->optionCompany(),
            'optionCostCenter' => $this->optionCostCenter(),
            'optionAccount'    => $this->optionAccount(),
            'optionSubAccount' => $this->optionSubAccount(),
            'optionFuture'     => $this->optionFuture(),
            'filters'          => $filters,
            'resource'         => self::RESOURCE,
            'url'              => self::URL,
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new MasterAccountCombination();
        $model->active = 'Y';

        return view('generalledger::master.master-coa-combination.add', [
            'title' => trans('shared/common.add'),
            'optionCompany'    => $this->optionCompany(),
            'optionCostCenter' => $this->optionCostCenter(),
            'optionAccount'    => $this->optionAccount(),
            'optionSubAccount' => $this->optionSubAccount(),
            'optionFuture'     => $this->optionFuture(),
            'model'            => $model,
            'url'              => self::URL,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterAccountCombination::where('account_combination_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        return view('generalledger::master.master-coa-combination.add', [
            'title'            => trans('shared/common.edit'),
            'optionCompany'    => $this->optionCompany(),
            'optionCostCenter' => $this->optionCostCenter(),
            'optionAccount'    => $this->optionAccount(),
            'optionSubAccount' => $this->optionSubAccount(),
            'optionFuture'     => $this->optionFuture(),
            'model'            => $model,
            'url'              => self::URL,
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        // var_dump($id); exit();
        $model = !empty($id) ? MasterAccountCombination::where('account_combination_id', '=', $id)->first() : new MasterAccountCombination();

        $company    = explode(':', $request->get('company'));
        $costCenter = explode(':', $request->get('costCenter'));
        $account    = explode(':', $request->get('account'));
        $subAccount = explode(':', $request->get('subAccount'));
        $future     = explode(':', $request->get('future'));

        $combinationCode = $company[0]
                           .'.'.$costCenter[0]
                           .'.'.$account[0]
                           .'.'.$subAccount[0]
                           .'.'.$future[0];

        if ($this->isSameCombination($combinationCode, $id)) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Combination already exists']);
        }

        $model->segment_1        = $company[1];
        $model->segment_2        = $costCenter[1];
        $model->segment_3        = $account[1];
        $model->segment_4        = $subAccount[1];
        $model->segment_5        = $future[1];
        // $model->description      = $company[1].'.'.
        //                            $costCenter[1].'.'.
        //                            $account[1].'.'.
        //                            $subAccount[1].'.'.
        //                            $future[1];
        // $model->combination_code = $combinationCode;
        $model->active           = !empty($request->get('status')) ? 'Y' : 'N';
        $now                     = new \DateTime();


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
            trans('shared/common.saved-message', ['variable' => trans('general-ledger/menu.master-coa-combination').' '.$combinationCode])
        );

        return redirect(self::URL);
    }

    protected function optionCompany(){
        return \DB::table('gl.mst_coa')
                ->where('segment_name','=','Company')
                ->where('active', '=', 'Y')
                ->orderBy('coa_code')->get();
    }

    protected function optionCostCenter(){
        return \DB::table('gl.mst_coa')
                ->where('segment_name','=','Cost Center')
                ->where('active', '=', 'Y')
                ->orderBy('coa_code')->get();
    }

    protected function optionAccount(){
        return \DB::table('gl.mst_coa')
                ->where('segment_name','=','Account')
                ->where('active', '=', 'Y')
                ->orderBy('coa_code')->get();
    }

    protected function optionSubAccount(){
        return \DB::table('gl.mst_coa')
                ->where('segment_name','=','Sub Account')
                ->where('active', '=', 'Y')
                ->orderBy('coa_code')->get();
    }

    protected function optionFuture(){
        return \DB::table('gl.mst_coa')
                ->where('segment_name','=','Future 1')
                ->where('active', '=', 'Y')
                ->orderBy('coa_code')->get();
    }

    protected function isSameCombination($codeInput, $id){
        $query = \DB::table('gl.v_mst_account_combination_edit')
                ->select('combination_code','account_combination_id')->get();
        foreach ($query as $row) {
            if (($row->combination_code == $codeInput) && ($row->account_combination_id != $id)) {
                    return true;
                }    
        }
        return false;
    } 
}
