<?php

namespace App\Modules\Operational\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\DummyHeader;
use App\MasterLookupValues;
use App\Modules\Operational\Model\Master\DetailTruckBranch;
use App\Modules\Operational\Model\Master\DetailTruckRent;
use App\Modules\Asset\Service\Transaction\AssetService;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Service\Penomoran;


class MasterTruckTypeController extends Controller
{
    const RESOURCE = 'Operational\Master\MasterTruckType';
    const URL      = 'operational/master/master-truck-type';
    const DESC     = 'TRUCK';

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
        $query   = \DB::table('adm.mst_lookup_values')
                        ->select('mst_lookup_values.*')
                        ->where('mst_lookup_values.lookup_type', '=', MasterLookupValues::TIPE_KENDARAAN)
                        ->orderBy('mst_lookup_values.lookup_code')
                        ->distinct();

        if (!empty($filters['code'])) {
            $query->where('mst_lookup_values.lookup_code', 'ilike', '%'.$filters['code'].'%');
        }

        if (!empty($filters['brandName'])) {
            $query->where('mst_lookup_values.meaning', 'ilike', '%'.$filters['brandName'].'%');
        }

        if (!empty($filters['description'])) {
            $query->where('mst_lookup_values.description', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('mst_lookup_values.active', '=', 'Y');
        } else {
            $query->where('mst_lookup_values.active', '=', 'N');
        }

        return view('operational::master.master-truck-type.index', [
            'models'         => $query->paginate(10),
            'filters'        => $filters,
            'resource'       => self::RESOURCE,
            'url'            => self::URL
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model         = new MasterLookupValues();
        $model->active = 'Y';

        return view('operational::master.master-truck-type.add', [
            'title'          => trans('shared/common.add'),
            'model'          => $model,
            'url'            => self::URL,
            'resource'       => self::RESOURCE,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterLookupValues::where('id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        return view('operational::master.master-truck-type.add', [
            'title'          => trans('shared/common.edit'),
            'model'          => $model,
            'url'            => self::URL,
            'resource'       => self::RESOURCE,
        ]);
    }

    public function save(Request $request)
    {
        $id    = intval($request->get('id'));
        $model = !empty($id) ? MasterLookupValues::where('id', '=', $id)->first() : new MasterLookupValues();

        $this->validate($request, [
            'brandName'     => 'required',
            'description'   => 'required',
        ]);

        $now    = new \DateTime();
        $model->lookup_type = MasterLookupValues::TIPE_KENDARAAN;
        $model->meaning     = $request->get('brandName');
        $model->description = $request->get('description');
        $model->active      = $request->get('status') != 'Y' ? 'N' : 'Y';

        if (empty($id)) {
            $count                   = \DB::table('adm.mst_lookup_values')->where('lookup_type', '=', MasterLookupValues::TIPE_KENDARAAN)->count();
            $model->lookup_code      = 'VCT.'.Penomoran::getStringNomor($count+1, 3);
            $model->creation_date      = $now;
        }

        try {
                $model->save();
            } catch (\Exception $e) {
                return redirect(self::URL.'/add')->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.truck-type').' '.$model->lookup_code])
        );

        return redirect(self::URL);
    }
}
