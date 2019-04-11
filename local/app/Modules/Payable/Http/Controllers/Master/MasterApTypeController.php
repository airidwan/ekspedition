<?php

namespace App\Modules\Payable\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Payable\Model\Master\MasterApType;
use App\Modules\Generalledger\Model\Master\MasterCoa;


class MasterApTypeController extends Controller
{
    const RESOURCE = 'Payable\Master\MasterApType';
    const URL = 'payable\master\master-ap-type';

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
        $query   = \DB::table('ap.v_mst_ap_type')->select('v_mst_ap_type.*');

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('v_mst_ap_type.active', '=', 'Y');
        } else {
            $query->where('v_mst_ap_type.active', '=', 'N');
        }
        
        if (!empty($filters['typeName'])) {
            $query->where('type_name', 'ilike', '%'.$filters['typeName'].'%');
        }

        return view('payable::master.master-ap-type.index', [
            'models'    => $query->paginate(10),
            'filters'    => $filters,
            'resource'   => self::RESOURCE,
            'url'        => self::URL,
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model         = new MasterApType();
        $model->active = 'Y';

        return view('payable::master.master-ap-type.add', [
            'title'             => trans('shared/common.add'),
            'model'             => $model,
            'url'               => self::URL,
            'coaCDesc'          => '',
            'coaDDesc'          => '',
            'optionsCoa'        => $this->optionAccount(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterApType::where('type_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        $coaDescC = $model->getCoaDescriptionC();
        $coaDescD = $model->getCoaDescriptionD();

        return view('payable::master.master-ap-type.add', [
            'title'             => trans('shared/common.add'),
            'model'             => $model,
            'url'               => self::URL,
            'coaCDesc'          => $coaDescC,
            'coaDDesc'          => $coaDescD,
            'optionsCoa'        => $this->optionAccount(),
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? MasterApType::where('type_id', '=', $id)->first() : new MasterApType();

        $this->validate($request, [
            'typeName' => 'required|max:50|unique:payable.mst_ap_type,type_name,'.$id.',type_id',
            'coaC' => 'required',
            'coaD' => 'required',
        ]);

        $model->type_name = $request->get('typeName');
        $model->coa_id_c = $request->get('coaC');
        $model->coa_id_d = $request->get('coaD');
        $model->active = !empty($request->get('status')) ? 'Y' : 'N';

        $now = new \DateTime();
        if (empty($id)) {
            $model->created_date = $now;
            $model->created_by = \Auth::user()->id;
        } else {
            $model->last_updated_date = $now;
            $model->last_updated_by = \Auth::user()->id;
        }

        try {
            $model->save();            
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('purchasing/menu.master-type-po').' '.$model->type_name])
        );

        return redirect(self::URL);
    }

    public function delete(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'delete'])) {
            abort(403);
        }
    }

    function optionSubAccount(){
        return \DB::table('gl.mst_coa')
                ->where('segment_name','=', MasterCoa::SUB_ACCOUNT)
                ->where('active', '=', 'Y')
                ->orderBy('coa_code')->get();
    }

    function optionAccount(){
        return \DB::table('gl.mst_coa')
                ->where('segment_name','=', MasterCoa::ACCOUNT)
                ->where('active', '=', 'Y')
                ->orderBy('coa_code')->get();
    }
}
