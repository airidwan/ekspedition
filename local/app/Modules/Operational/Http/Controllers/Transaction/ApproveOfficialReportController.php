<?php

namespace App\Modules\Operational\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Transaction\OfficialReport;
use App\Modules\Operational\Http\Controllers\Transaction\OfficialReportController;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Service\Penomoran;
use App\Service\NotificationService;
use App\Notification;
use App\Service\TimezoneDateConverter;
use App\Role;

class ApproveOfficialReportController extends Controller
{
    const RESOURCE = 'Operational\Transaction\ApproveOfficialReport';
    const URL      = 'operational/transaction/approve-official-report';

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
        $query   = \DB::table('op.trans_official_report')
                    ->leftJoin('adm.users', 'users.id', '=', 'trans_official_report.respon_by')
                    ->where('trans_official_report.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('created_date', 'desc');

        if (!empty($filters['officialReportNumber'])) {
            $query->where('official_report_number', 'ilike', '%'.$filters['officialReportNumber'].'%');
        }

        if (!empty($filters['personName'])) {
            $query->where('person_name', 'ilike', '%'.$filters['personName'].'%');
        }

        if (!empty($filters['description'])) {
            $query->where('description', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = TimezoneDateConverter::getServerDateTime($filters['dateFrom']);
            $query->where('datetime', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = TimezoneDateConverter::getServerDateTime($filters['dateTo']);
            $query->where('datetime', '<=', $date->format('Y-m-d 23:59:59'));
        }
        $query->where(function($query){
                    $query->where('category', '=', OfficialReport::ADJUSTMENT)
                          ->orWhere('category', '=', OfficialReport::RESI_CORRECTION);
                })
                ->where('status', '=', OfficialReport::OPEN);
        return view('operational::transaction.approve-official-report.index', [
            'models'   => $query->paginate(10),
            'filters'  => $filters,
            'resource' => self::RESOURCE,
            'url'      => self::URL,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = OfficialReport::where('official_report_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        return view('operational::transaction.approve-official-report.add', [
            'title' => trans('shared/common.edit'),
            'model' => $model,
            'url'   => self::URL,
            'resource' => self::RESOURCE,
        ]);
    }

    public function save(Request $request)
    {
        $id    = intval($request->get('id'));
        $model = OfficialReport::where('official_report_id', '=', $id)->first();

        $this->validate($request, [
            'noteApproved'  => 'required',
        ]);

        $model->status = OfficialReport::APPROVED;
        
        $now = new \DateTime();
        $model->last_updated_date = $now;
        $model->last_updated_by = \Auth::user()->id;

        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        /** notifikasi ke admin **/
        NotificationService::createNotification(
            'Official Report Request Approved',
            'Offical Report Request' . $model->official_report_number . '. '.$model->description,
            OfficialReportController::URL.'/edit/'.$model->official_report_id,
            [Role::WAREHOUSE_ADMIN]
        );

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.official-report').' '.$model->official_report_number])
        );

        return redirect(self::URL);
    }
}
