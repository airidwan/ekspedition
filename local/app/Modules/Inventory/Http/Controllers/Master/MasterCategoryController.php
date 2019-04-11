<?php

namespace App\Modules\Inventory\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Inventory\Model\Master\MasterCategory;
use App\Modules\Generalledger\Model\Master\MasterCoa;

class MasterCategoryController extends Controller
{
    const RESOURCE = 'Inventory\Master\MasterCategory';
    const URL = 'inventory/master/master-category';

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
        $query = \DB::table('inv.mst_category')
                    ->select('mst_category.*', 'mst_coa.coa_code', 'mst_coa.description as coa_description')
                    ->leftJoin('gl.mst_coa','mst_coa.coa_id', '=', 'mst_category.coa_id')
                    ->orderBy('mst_category.category_code', 'asc');


        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('mst_category.active', '=', 'Y');
        } else {
            $query->where('mst_category.active', '=', 'N');
        }

        if (!empty($filters['coaCode'])) {
            $query->where('mst_coa.coa_code', 'ilike', '%'.$filters['coaCode'].'%');
        }

        if (!empty($filters['coaDesc'])) {
            $query->where('mst_coa.description', 'ilike', '%'.$filters['coaDesc'].'%');
        }

        if (!empty($filters['categoryCode'])) {
            $query->where('mst_category.category_code', 'ilike', '%'.$filters['categoryCode'].'%');
        }

        if (!empty($filters['description'])) {
            $query->where('mst_category.description', 'ilike', '%'.$filters['description'].'%');
        }

        return view('inventory::master.master-category.index', [
            'models' => $query->paginate(10),
            'filters' => $filters,
            'resource' => self::RESOURCE,
            'url' => self::URL,
            'optionsCoa' => $this->getOptionsCoa(),
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new MasterCategory();
        $model->active = 'Y';

        return view('inventory::master.master-category.add', [
            'title'          => trans('shared/common.add'),
            'model'          => $model,
            'url'            => self::URL,
            'optionsCoa'     => $this->getOptionsCoa(),
            'optionCategory' => $this->getCategoryCode(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterCategory::where('category_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        return view('inventory::master.master-category.add', [
            'title'          => trans('shared/common.edit'),
            'model'          => $model,
            'url'            => self::URL,
            'optionsCoa'     => $this->getOptionsCoa(),
            'optionCategory' => $this->getCategoryCode(),
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? MasterCategory::where('category_id', '=', $id)->first() : new MasterCategory();

        $this->validate($request, [
            'categoryCode' => 'required|max:100|unique:inventory.mst_category,category_code,'.$id.',category_id',
            'description'  => 'required|max:255',
            'coa'          => 'required',
        ]);

        $model->category_code   = $request->get('categoryCode');
        $model->description     = $request->get('description');
        $model->coa_id          = $request->get('coa');
        $model->active          = !empty($request->get('status')) ? 'Y' : 'N';

        $now = new \DateTime();
        if (empty($id)) {
            $model->created_date = $now;
            $model->created_by   = \Auth::user()->id;
        } else {
            $model->last_updated_date = $now;
            $model->last_updated_by   = \Auth::user()->id;
        }

        $model->save();

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('inventory/menu.master-category').' '.$model->category_code])
        );

        return redirect(self::URL);
    }

    protected function getCategoryCode(){
        return [
            MasterCategory::SP,
            MasterCategory::AST,
            MasterCategory::CSM,
            MasterCategory::JS,
            MasterCategory::JSK,
            MasterCategory::JTK,
            MasterCategory::JOK,
            MasterCategory::JPK,
            MasterCategory::JPN,
            MasterCategory::JTS,
            MasterCategory::JBM,
            MasterCategory::JKK,
            MasterCategory::JP,
            MasterCategory::JBB,
            MasterCategory::JAS,
            MasterCategory::JAB,
            MasterCategory::JTP,
        ];
    }

    protected function getOptionsCoa()
    {
        return \DB::table('gl.mst_coa')
                    ->where('segment_name', '=', MasterCoa::ACCOUNT)
                    ->where('active', '=', 'Y')
                    ->orderBy('coa_code', 'asc')
                    ->get();
    }
}
