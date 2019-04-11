<?php

namespace App\Modules\Inventory\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Inventory\Model\Master\MasterUom;

class MasterUomController extends Controller
{
    const RESOURCE = 'Inventory\Master\MasterUom';
    const URL = 'inventory/master/master-uom';

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
        $query = \DB::table('inv.mst_uom')->orderBy('uom_code');

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('active', '=', 'Y');
        } else {
            $query->where('active', '=', 'N');
        }

        if (!empty($filters['uomCode'])) {
            $query->where('uom_code', 'ilike', '%'.$filters['uomCode'].'%');
        }

        if (!empty($filters['description'])) {
            $query->where('description', 'ilike', '%'.$filters['description'].'%');
        }

        return view('inventory::master.master-uom.index', [
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

        $model = new MasterUom();
        $model->active = 'Y';

        return view('inventory::master.master-uom.add', [
            'title' => trans('shared/common.add'),
            'model' => $model,
            'url' => self::URL,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterUom::where('uom_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        return view('inventory::master.master-uom.add', [
            'title' => trans('shared/common.edit'),
            'model' => $model,
            'url' => self::URL,
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? MasterUom::where('uom_id', '=', $id)->first() : new MasterUom();

        $this->validate($request, [
            'uomCode' => 'required|max:100|unique:inventory.mst_uom,uom_code,'.$id.',uom_id',
        ]);

        $model->uom_code = $request->get('uomCode');
        $model->description = $request->get('description');
        $model->active = !empty($request->get('status')) ? 'Y' : 'N';

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
            trans('shared/common.saved-message', ['variable' => trans('inventory/menu.master-uom').' '.$model->uom_code])
        );

        return redirect(self::URL);
    }
}
