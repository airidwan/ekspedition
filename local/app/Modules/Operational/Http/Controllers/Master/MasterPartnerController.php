<?php

namespace App\Modules\Operational\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Master\MasterPartner;
use App\Modules\Operational\Model\Master\DetailPartnerBranch;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Service\Penomoran;

class MasterPartnerController extends Controller
{
    const RESOURCE = 'Operational\Master\MasterPartner';
    const URL      = 'operational\master\master-partner';
    const DESC     = 'PARTNER';

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
        $query   = \DB::table('op.mst_partner')
                    ->select('mst_partner.*', 'mst_city.*')
                    ->join('op.mst_city', 'mst_city.city_id', '=', 'mst_partner.city_id')
                    ->join('op.dt_partner_branch', 'mst_partner.partner_id', '=', 'dt_partner_branch.partner_id')
                    ->where('dt_partner_branch.active', '=', 'Y')
                    ->where('dt_partner_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('mst_partner.partner_code')
                    ->distinct();

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('mst_partner.active', '=', 'Y');
        } else {
            $query->where('mst_partner.active', '=', 'N');
        }

        if (!empty($filters['code'])) {
            $query->where('partner_code', 'ilike', '%'.$filters['code'].'%');
        }

        if (!empty($filters['name'])) {
            $query->where('partner_name', 'ilike', '%'.$filters['name'].'%');
        }

        if (!empty($filters['city'])) {
            $query->where('city_id', '=', $filters['city']);
        }

        return view('operational::master.master-partner.index', [
            'models'      => $query->paginate(10),
            'filters'    => $filters,
            'resource'   => self::RESOURCE,
            'url'        => self::URL,
            'optionCity' => \DB::table('op.v_mst_city')->get()
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model         = new MasterPartner();
        $model->active = 'Y';

        return view('operational::master.master-partner.add', [
            'title'            => trans('shared/common.add'),
            'model'            => $model,
            'url'              => self::URL,
            'optionCity'       => \DB::table('op.v_mst_city')->get(),
            'optionBranch'     => \DB::table('op.mst_branch')->where('active', '=','Y')->orderBy('branch_name')->get(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterPartner::where('partner_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        if (!$this->checkAccessBranch($model)) {
            abort(403);
        }

        return view('operational::master.master-partner.add', [
            'title'            => trans('shared/common.edit'),
            'model'            => $model,
            'url'              => self::URL,
            'optionCity'       => \DB::table('op.v_mst_city')->get(),
            'optionBranch'     => \DB::table('op.mst_branch')->where('active', '=','Y')->orderBy('branch_name')->get(),
        ]);
    }

    public function save(Request $request)
    {
        $id    = intval($request->get('id'));
        $model = !empty($id) ? MasterPartner::where('partner_id', '=', $id)->first() : new MasterPartner();

        $this->validate($request, [
            'name'  => 'required|max:55',
            'city'  => 'required|max:55',
            ]);

        if (empty($request->get('branchDetail'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Branch detail on the Activation tab can not be empty']);
        }

        $status='Y';
        if ($request->get('status')!="Y") {
            $status="N";
        }

        $opr = empty($model->partner_id) ? 'I' : 'U';

        $model = !empty($id) ? MasterPartner::find($id) : new MasterPartner();
        $model->partner_name    = $request->get('name');
        $model->address          = $request->get('address');
        $model->city_id          = $request->get('city');
        $model->phone_number     = $request->get('phone');
        $model->contact_person   = $request->get('contactPerson');
        $model->contact_phone    = $request->get('contactPhone');
        $model->description      = $request->get('description');
        $model->branch_id_insert = $request->session()->get('currentBranch')->branch_id ;
        $model->active           = $status;
        $now = new \DateTime();

        if ($opr == 'I') {
            $count                = \DB::table('op.mst_partner')->where('branch_id_insert', '=', \Session::get('currentBranch')->branch_id)->count();
            $partnerCode         = 'P.'.\Session::get('currentBranch')->branch_code.'.'.Penomoran::getStringNomor($count+1, 4);
            $model->partner_code = $partnerCode;
            
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

        $model->partnerBranch()->delete();
        foreach ($request->get('branchDetail') as $branch) {
            $partnerBranch = new DetailPartnerBranch();
            $partnerBranch->partner_id = $model->partner_id;
            $partnerBranch->branch_id = $branch;
            $partnerBranch->active = 'Y';
            if ($opr == 'I') {
                $partnerBranch->created_date = $now;
                $partnerBranch->created_by = \Auth::user()->id;
            }else{
                $partnerBranch->last_updated_date = $now;
                $partnerBranch->last_updated_by = \Auth::user()->id;
            }

            try {
                $partnerBranch->save();
            } catch (\Exception $e) {
                return redirect(self::URL.'/edit/'.$model->partner_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }
        }

        $modelCoa = !empty($id) ? MasterCoa::where('segment_name', '=', MasterCoa::SUB_ACCOUNT)->where('coa_code','=',$request->get('subAccountCode'))->first() : new MasterCoa();

        $modelCoa->description = $request->get('name').' ('.self::DESC.')';
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
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.partner').' '.$model->partner_name])
            );

        return redirect(self::URL);
    }

    protected function checkAccessBranch(MasterPartner $model)
    {
        $canAccessBranch = false;
        foreach ($model->partnerBranch as $partnerBranch) {
            $branch = $partnerBranch->branch;
            if ($branch !== null && \Auth::user()->can('accessBranch', $branch->branch_id)) {
                $canAccessBranch = true;
            }
        }

        return $canAccessBranch;
    }
}
