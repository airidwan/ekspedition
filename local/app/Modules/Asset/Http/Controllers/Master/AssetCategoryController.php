<?php

namespace App\Modules\Asset\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Asset\Model\Master\AssetCategory;
use App\Modules\Generalledger\Model\Master\MasterCoa;

class AssetCategoryController extends Controller
{
    const RESOURCE = 'Asset\Master\AssetCategory';
    const URL = 'asset/master/asset-category';

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
        $query = \DB::table('ast.v_asset_category');

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('active', '=', 'Y');
        } else {
            $query->where('active', '=', 'N');
        }

        if (!empty($filters['category'])) {
            $query->where('category_name', 'ilike', '%'.$filters['category'].'%');
        }

        return view('asset::master.asset-category.index', [
            'models'        => $query->paginate(10),
            'filters'       => $filters,
            'resource'      => self::RESOURCE,
            'url'           => self::URL,
            'optionsCoa'    => $this->getOptionsCoa(),
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new AssetCategory();
        $model->active = 'Y';

        return view('asset::master.asset-category.add', [
            'title'             => trans('shared/common.add'),
            'model'             => $model,
            'url'               => self::URL,
            'optionsCoa'        => $this->getOptionsCoa(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = AssetCategory::find($id);
        if ($model === null) {
            abort(404);
        }

        return view('asset::master.asset-category.add', [
            'title'             => trans('shared/common.edit'),
            'model'             => $model,
            'url'               => self::URL,
            'optionsCoa'        => $this->getOptionsCoa(),
        ]);
    }

    public function save(Request $request)
    {
        // var_dump($request->all());exit();
        $id = intval($request->get('id'));
        $model = !empty($id) ? AssetCategory::find($id) : new AssetCategory();

        $this->validate($request, [
            'category'     => 'required|max:100|unique:asset.asset_category,category_name,'.$id.',asset_category_id',
            'clearing'     => 'required',
            'depreciation' => 'required',
            'acumulated'   => 'required',
        ]);

        $model->category_name       = $request->get('category');
        $model->clearing_coa_id     = $request->get('clearing');
        $model->depreciation_coa_id = $request->get('depreciation');
        $model->acumulated_coa_id   = $request->get('acumulated');
        $model->active              = !empty($request->get('status')) ? 'Y' : 'N';

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
            trans('shared/common.saved-message', ['variable' => trans('asset/menu.asset-category').' '.$model->category_name])
        );

        return redirect(self::URL);
    }

    protected function getOptionsCoa()
    {
        return \DB::table('gl.mst_coa')
                    ->where('active', '=', 'Y')
                    ->where('segment_name', '=', MasterCoa::ACCOUNT)
                    ->orderBy('coa_code', 'asc')
                    ->get();
    }
}
