<?php

namespace App\Modules\Inventory\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Inventory\Model\Master\MasterWarehouse;

class MasterWarehouseController extends Controller
{
    const RESOURCE = 'Inventory\Master\MasterWarehouse';
    const URL = 'inventory/master/master-warehouse';

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
        $query = \DB::table('inv.v_mst_warehouse');

        if (!empty($filters['whCode'])) {
            $query->where('wh_code', 'ilike', '%'.$filters['whCode'].'%');
        }

        if (!empty($filters['description'])) {
            $query->where('description', 'ilike', '%'.$filters['description'].'%');
        }


        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('active', '=', 'Y');
        } else {
            $query->where('active', '=', 'N');
        }

        $query->where('branch_id', '=', \Session::get('currentBranch')->branch_id);

        return view('inventory::master.master-warehouse.index', [
            'models' => $query->paginate(10),
            'filters' => $filters,
            'resource' => self::RESOURCE,
            'url' => self::URL,
            'optionsBranch' => $this->getOptionsBranch(),
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new MasterWarehouse();
        $model->active = 'Y';

        return view('inventory::master.master-warehouse.add', [
            'title' => trans('shared/common.add'),
            'model' => $model,
            'url' => self::URL,
            'optionsBranch' => $this->getOptionsBranch(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterWarehouse::where('wh_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        if ($request->user()->cannot('accessBranch', $model->branch_id)) {
            abort(403);
        }

        return view('inventory::master.master-warehouse.add', [
            'title' => trans('shared/common.edit'),
            'model' => $model,
            'url' => self::URL,
            'optionsBranch' => $this->getOptionsBranch(),
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? MasterWarehouse::where('wh_id', '=', $id)->first() : new MasterWarehouse();

        $this->validate($request, [
            'whCode' => 'required|max:100|unique:inventory.mst_warehouse,wh_code,'.$id.',wh_id',
            'description' => 'required|max:255',
        ]);

        $model->wh_code = $request->get('whCode');
        $model->description = $request->get('description');
        $model->branch_id = \Session::get('currentBranch')->branch_id;
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
            trans('shared/common.saved-message', ['variable' => trans('inventory/menu.master-warehouse').' '.$model->wh_code])
        );

        return redirect(self::URL);
    }

    protected function getOptionsBranch()
    {
        return \DB::table('op.mst_branch')
                    ->where('active', '=', 'Y')
                    ->orderBy('branch_name', 'asc')
                    ->get();
    }
}
