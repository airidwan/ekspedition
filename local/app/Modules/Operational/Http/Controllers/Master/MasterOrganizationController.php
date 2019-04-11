<?php

namespace App\Modules\Operational\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Master\MasterOrganization;
use App\Modules\Operational\Model\Master\MasterCity;
use App\Modules\Generalledger\Model\Master\MasterCoa;

class MasterOrganizationController extends Controller
{
    const RESOURCE = 'Operational\Master\MasterOrganization';
    const URL      = 'operational/master/master-organization';

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }
        $model = MasterOrganization::first() !== null ? MasterOrganization::first() : new MasterOrganization();

        return view(
            'operational::master.master-organization.index',
            ['model' => $model, 'resource' => self::RESOURCE, 'url' => self::URL, 'optionCity' => MasterCity::orderBy('city_name')->where('active', '=', 'Y')->get()]
            );
    }

    public function save(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $this->validate($request, [
            'code'         => 'required|max:5',
            'name'         => 'required|max:55',
            'phone'        => 'required|max:55',
            ]);

        $modelCoa = MasterCoa::where('segment_name', '=',MasterCoa::COMPANY)->where('coa_code','=',MasterCoa::COMPANY_CODE)->first();
        $modelCoa->coa_code          = MasterCoa::COMPANY_CODE;
        $modelCoa->segment_name      = MasterCoa::COMPANY;
        $modelCoa->description       = $request->get('name');
        $modelCoa->last_updated_date = new \DateTime();
        $modelCoa->last_updated_by   = \Auth::user()->id;
        $modelCoa->active            = 'Y';
        $modelCoa->save();

        $id       = $request->get('id');
        $model    = !empty($id) ? MasterOrganization::find($id) : new MasterOrganization();

        $model->org_code      = $request->get('code','');
        $model->org_name      = $request->get('name','');
        $model->address       = $request->get('address','');
        $model->director_name = $request->get('director','');
        $model->phone_number  = $request->get('phone','');
        $model->city_id       = $request->get('city',0);
        $model->company       = MasterCoa::COMPANY_CODE;
        $now = new \DateTime();

        if (empty($id)) {
            $model->created_date = $now;
            $model->created_by = \Auth::user()->id;
        } else {
            $model->last_updated_date = $now;
            $model->last_updated_by  = \Auth::user()->id;
        }
        $model->save();

        $request->session()->flash('successMessage', trans('shared/common.saved-message', ['variable' => trans('operational/menu.organization')]));
        return redirect(self::URL);
    }
}
