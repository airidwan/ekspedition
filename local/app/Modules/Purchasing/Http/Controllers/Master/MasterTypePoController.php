<?php

namespace App\Modules\Purchasing\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operasional\Model\Master\MasterCabang;
use App\Modules\Purchasing\Model\Master\MasterTypePo;
use App\Modules\Generalledger\Model\Master\MasterCoa;

class MasterTypePoController extends Controller
{
    const RESOURCE = 'Purchasing\Master\MasterTypePo';
    const URL = 'purchasing/master/master-type-po';

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
        } elseif (empty($request->get('page'))) {
            $request->session()->forget('filters');
        }

        $filters = $request->session()->get('filters');
        $query = \DB::table('po.mst_po_type')
                    ->select('mst_po_type.type_id', 'mst_po_type.type_name', 'mst_po_type.active', 'mst_coa.coa_code', 'mst_coa.description')
                    ->leftJoin('gl.mst_coa', 'mst_coa.coa_id', '=', 'mst_po_type.coa_id')
                    ->where('mst_po_type.active', '=', 'Y')
                    ->orderBy('mst_po_type.type_name', 'asc');

        if (!empty($filters['typeName'])) {
            $query->where('type_name', 'ilike', '%'.$filters['typeName'].'%');
        }

        if (!empty($filters['coaCode'])) {
            $query->where('mst_coa.coa_code', 'ilike', '%'.$filters['coaCode'].'%');
        }

        if (!empty($filters['coaDesc'])) {
            $query->where('mst_coa.description', 'ilike', '%'.$filters['coaDesc'].'%');
        }

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('mst_po_type.active', '=', 'Y');
        } else {
            $query->where('mst_po_type.active', '=', 'N');
        }

        return view('purchasing::master.master-type-po.index', [
            'models' => $query->paginate(10),
            'filters' => $filters,
            'resource' => self::RESOURCE,
            'url' => self::URL,
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new MasterTypePo();
        $model->active = 'Y';

        return view('purchasing::master.master-type-po.add', [
            'title' => trans('shared/common.add'),
            'model' => $model,
            'coaDesc'   => '',
            'url' => self::URL,
            'optionsCoa' => \DB::table('gl.mst_coa')->where('active', '=', 'Y')->where('segment_name', '=', MasterCoa::ACCOUNT)->get(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterTypePo::where('type_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        return view('purchasing::master.master-type-po.add', [
            'title'     => trans('shared/common.add'),
            'model'     => $model,
            'url'       => self::URL,
            'optionsCoa' => \DB::table('gl.mst_coa')->where('active', '=', 'Y')->where('segment_name', '=', MasterCoa::ACCOUNT)->get(),
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? MasterTypePo::where('type_id', '=', $id)->first() : new MasterTypePo();

        $this->validate($request, [
            'typeName'  => 'required|max:100|unique:purchasing.mst_po_type,type_name,'.$id.',type_id',
            'coa'       => 'required',
        ]);

        $model->type_name   = $request->get('typeName');
        $model->coa_id  = $request->get('coa');
        $model->active      = !empty($request->get('status')) ? 'Y' : 'N';

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
            trans('shared/common.saved-message', ['variable' => trans('purchasing/menu.master-type-po').' '.$model->type_name])
        );

        return redirect(self::URL);
    }
}
