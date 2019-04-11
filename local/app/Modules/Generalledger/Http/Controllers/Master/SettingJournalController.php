<?php

namespace App\Modules\Generalledger\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Generalledger\Model\Master\SettingJournal;

class SettingJournalController extends Controller
{
    const RESOURCE = 'Generalledger\Master\SettingJournal';
    const URL      = 'general-ledger/master/setting-journal';

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
        $query   = \DB::table('gl.mst_setting_journal')
                        ->select('mst_setting_journal.*', 'mst_coa.coa_code', 'mst_coa.description as coa_description')
                        ->leftJoin('gl.mst_coa', 'mst_setting_journal.coa_id', '=', 'mst_coa.coa_id')
                        ->orderBy('mst_setting_journal.setting_name', 'asc');

        if (!empty($filters['settingName'])) {
            $query->where('mst_setting_journal.setting_name', 'ilike', '%'.$filters['settingName'].'%');
        }

        if (!empty($filters['segmentName'])) {
            $query->where('mst_setting_journal.segment_name', '=', $filters['segmentName']);
        }

        if (!empty($filters['coa'])) {
            $query->where(function($query) use ($filters) {
                $query->where('mst_coa.coa_code', 'ilike', '%'.$filters['coa'].'%')
                        ->orWhere('mst_coa.description', 'ilike', '%'.$filters['coa'].'%');
            });
        } 

        if (!empty($filters['description'])) {
            $query->where('mst_setting_journal.description', 'ilike', '%'.$filters['description'].'%');
        }

        return view('generalledger::master.setting-journal.index', [
            'models'   => $query->paginate(10),
            'filters'  => $filters,
            'resource' => self::RESOURCE,
            'optionSegmentName' => $this->getOptionSegmentName(),
            'url'      => self::URL,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = SettingJournal::where('setting_journal_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        return view('generalledger::master.setting-journal.add', [
            'title' => trans('shared/common.edit'),
            'model' => $model,
            'url'   => self::URL,
        ]);
    }

    public function save(Request $request)
    {
        $id    = intval($request->get('id'));
        $model = SettingJournal::find($id);

        $this->validate($request, [
            'coaId'  => 'required',
        ]);

        $model->coa_id = $request->get('coaId');
        $model->description = $request->get('description');
        $model->last_updated_date = new \DateTime();
        $model->last_updated_by = \Auth::user()->id;

        try {
            $model->save();            
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('general-ledger/menu.setting-journal').' '.$model->setting_name])
        );

        return redirect(self::URL);
    }

    protected function getOptionSegmentName(){
        return [ SettingJournal::ACCOUNT, SettingJournal::SUB_ACCOUNT, SettingJournal::FUTURE ];
    }

    public function getJsonCoa(Request $request)
    {
        $search = $request->get('search');
        $segmentName = $request->get('segmentName');
        $query = \DB::table('gl.mst_coa')
                    ->where('mst_coa.active', '=', 'Y')
                    ->where('mst_coa.segment_name', '=', $segmentName)
                    ->where(function($query) use ($search) {
                        $query->where('mst_coa.coa_code', 'ilike', '%'.$search.'%')
                                ->orWhere('mst_coa.description', 'ilike', '%'.$search.'%');
                    })
                    ->orderBy('mst_coa.coa_code', 'asc')
                    ->take(10);

        return response()->json($query->get());
    }
}
