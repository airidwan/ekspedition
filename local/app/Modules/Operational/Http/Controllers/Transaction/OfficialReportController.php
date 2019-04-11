<?php

namespace App\Modules\Operational\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Master\DetailOfficialReportToBranch;
use App\Modules\Operational\Model\Master\DetailOfficialReportToRole;
use App\Modules\Operational\Model\Transaction\OfficialReport;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Http\Controllers\Transaction\OfficialReportController;
use App\Modules\Operational\Http\Controllers\Transaction\ApproveOfficialReportController;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Service\Transaction\ResiService;
use App\Service\Penomoran;
use App\Service\NotificationService;
use App\Notification;
use App\Service\TimezoneDateConverter;
use App\Role;

class OfficialReportController extends Controller
{
    const RESOURCE = 'Operational\Transaction\OfficialReport';
    const URL      = 'operational/transaction/official-report';

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
                    ->select('trans_official_report.*', 'user.full_name as created_name', 'response.full_name as response_name', 'trans_resi_header.resi_header_id', 'trans_resi_header.resi_number')
                    ->leftJoin('adm.users as user', 'user.id', '=', 'trans_official_report.created_by')
                    ->leftJoin('adm.users as response', 'response.id', '=', 'trans_official_report.respon_by')
                    ->leftJoin('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_official_report.resi_header_id')
                    ->orderBy('created_date', 'desc');

        if (!empty($filters['officialReportNumber'])) {
            $query->where('official_report_number', 'ilike', '%'.$filters['officialReportNumber'].'%');
        }

        if (!empty($filters['personName'])) {
            $query->where('person_name', 'ilike', '%'.$filters['personName'].'%');
        }

        if (!empty($filters['resiNumber'])) {
            $query->where('trans_resi_header.resi_number', 'ilike', '%'.$filters['resiNumber'].'%');
        }

        if (!empty($filters['description'])) {
            $query->where('trans_official_report.description', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['category'])) {
            $query->where('category', '=', $filters['category']);
        }

        if (!empty($filters['status'])) {
            $query->where('trans_official_report.status', '=', $filters['status']);
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = TimezoneDateConverter::getServerDateTime($filters['dateFrom']);
            $query->where('datetime', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = TimezoneDateConverter::getServerDateTime($filters['dateTo']);
            $query->where('datetime', '<=', $date->format('Y-m-d 23:59:59'));
        }

        return view('operational::transaction.official-report.index', [
            'models'   => $query->paginate(10),
            'filters'  => $filters,
            'resource' => self::RESOURCE,
            'url'      => self::URL,
            'optionCategory' => $this->getOptionCategory(),
            'optionStatus' => $this->getOptionStatus(),
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model         = new OfficialReport();
        $model->status = OfficialReport::OPEN;
        $model->branch_id = \Session::get('currentBranch')->branch_id;
        $model->created_by = \Auth::user()->id;
        $model->to_role_id = Role::OPERATIONAL_ADMIN;
        return view('operational::transaction.official-report.add', [
            'title' => trans('shared/common.add'),
            'model' => $model,
            'url'   => self::URL,
            'optionCategory' => $this->getOptionCategory(),
            'optionStatus'   => $this->getOptionStatus(),
            'optionRole'     => $this->getOptionRole(),
            'optionBranch'   => $this->getOptionBranch(),
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
        return view('operational::transaction.official-report.add', [
            'title' => trans('shared/common.edit'),
            'model' => $model,
            'url'   => self::URL,
            'optionCategory' => $this->getOptionCategory(),
            'optionStatus' => $this->getOptionStatus(),
            'optionRole'     => $this->getOptionRole(),
            'optionBranch'   => $this->getOptionBranch(),
        ]);
    }

    public function save(Request $request)
    {
        $id    = intval($request->get('id'));
        $opr   = empty($id) ? 'I' : 'U';
        $model = !empty($id) ? OfficialReport::where('official_report_id', '=', $id)->first() : new OfficialReport();

        $this->validate($request, [
            'personName'   => 'required',
            'description'  => 'required',
        ]);

        if((empty($request->get('toRole')) || empty($request->get('toBranch'))) && empty($id)){
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'To Role or to Branch can\'t be empty' ]);
        }

        if(($request->get('category') == OfficialReport::ADJUSTMENT || $request->get('category') == OfficialReport::RESI_CORRECTION) && ($request->get('status') == OfficialReport::CLOSED || $request->get('status') == OfficialReport::APPROVED)){
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'For Official Report Adjustment type, can\'t change status to \'Closed/Approved\' manually' ]);
        }

        if(!empty($request->get('resiId'))){
            $model->resi_header_id = $request->get('resiId');
        }else{
            $model->resi_header_id = null;
        }

        if (!empty($request->get('status'))) {
            $model->status = $request->get('status');
        }
        
        $model->person_name = $request->get('personName');
        $model->description = $request->get('description');
        $now = new \DateTime();

        if (empty($id)) {
            $model->category     = $request->get('category');
            $model->status       = OfficialReport::OPEN;
            $model->branch_id    = \Session::get('currentBranch')->branch_id;

            $timeString = $request->get('date').' '.$request->get('hour').':'.$request->get('minute');
            $time       = !empty($timeString) ? TimezoneDateConverter::getServerDateTime($timeString) : null;

            $model->datetime    = !empty($time) ? $time->format('Y-m-d H:i:s'):null;
            $model->created_date = $now;
            $model->created_by   = \Auth::user()->id;
            // $model->to_branch_id = intval($request->get('branch'));
            // $model->to_role_id   = intval($request->get('role'));
        }else{
            $model->last_updated_date = $now;
            $model->last_updated_by   = \Auth::user()->id;
        }

        if (!empty($request->get('respon')) && $model->created_by != \Auth::user()->id) {
            $model->respon      = $request->get('respon');
            $model->respon_date = $now;
            $model->respon_by   = \Auth::user()->id;

            NotificationService::createSpesificBranchNotification(
                'Official Report Responsed',
                'Offical Report Responsed' . $model->official_report_number . '. '.$model->respon,
                self::URL.'/edit/'.$model->official_report_id,
                [Role::BRANCH_MANAGER, Role::OPERATIONAL_ADMIN],
                $model->branch_id
            );
        }

        if (empty($model->official_report_number)) {
            $model->official_report_number = $this->getOfficialReportNumber($model);
        }

        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }
        if (empty($id)) {
            $model->toBranch()->delete();
            $model->toRole()->delete();

            foreach ($request->get('toBranch') as $branch) {
                $toBranch                     = new DetailOfficialReportToBranch();
                $toBranch->official_report_id = $model->official_report_id;
                $toBranch->branch_id          = $branch;

                if (empty($id)) {
                    $toBranch->created_date = $now;
                    $toBranch->created_by   = \Auth::user()->id;
                } else {
                    $toBranch->last_updated_date = $now;
                    $toBranch->last_updated_by   = \Auth::user()->id;
                }

                $toBranch->save();
            }

            foreach ($request->get('toRole') as $role) {
                $toRole                     = new DetailOfficialReportToRole();
                $toRole->official_report_id = $model->official_report_id;
                $toRole->role_id            = $role;

                if (empty($id)) {
                    $toRole->created_date = $now;
                    $toRole->created_by   = \Auth::user()->id;
                } else {
                    $toRole->last_updated_date = $now;
                    $toRole->last_updated_by   = \Auth::user()->id;
                }
                $toRole->save();
            }
        }

        if (($model->category == OfficialReport::ADJUSTMENT || $model->category == OfficialReport::RESI_CORRECTION) && $opr == 'I') {
             /** notifikasi approve ke kacab **/
            NotificationService::createNotification(
                'Official Report Request Approved',
                'Person name '.$model->person_name.'. Offical Report Request Approval ' . $model->official_report_number . '. '.$model->description,
                ApproveOfficialReportController::URL.'/edit/'.$model->official_report_id,
                [Role::BRANCH_MANAGER]
            );
        }
        if($opr == 'I'){
            foreach ($request->get('toBranch') as $branch) {
                foreach ($request->get('toRole') as $role) {
                    NotificationService::createSpesificBranchNotification(
                        'Official Report Created',
                        'Person name '.$model->person_name.'. Offical Report ' . $model->official_report_number . '. '.$model->description,
                        OfficialReportController::URL.'/edit/'.$model->official_report_id,
                        [$role],
                        $branch
                    );
                }
                /** notifikasi ke kacab cabang tujuan **/
                NotificationService::createSpesificBranchNotification(
                    'Official Report Created',
                    'Person name '.$model->person_name.'. Offical Report ' . $model->official_report_number . '. '.$model->description,
                    OfficialReportController::URL.'/edit/'.$model->official_report_id,
                    [Role::BRANCH_MANAGER],
                    $branch
                );
            }

            /** notifikasi ke kacab cabang pembuat**/
            if ($model->category == OfficialReport::UMUM || $model->category == OfficialReport::INVOICE_REQUEST) {
                NotificationService::createNotification(
                    'Official Report Created',
                    'Person name '.$model->person_name.'. Offical Report ' . $model->official_report_number . '. '.$model->description,
                    OfficialReportController::URL.'/edit/'.$model->official_report_id,
                    [Role::BRANCH_MANAGER]
                );
            }
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.official-report').' '.$model->official_report_number])
        );

        return redirect(self::URL);
    }

    public function getJsonResi(Request $request)
    {
        $search = $request->get('search');
        $query = ResiService::getQueryResiAllBranch();

        $query->where(function ($query) use ($search) {
                    $query->where('trans_resi_header.resi_number', 'ilike', '%'.$search.'%')
                          ->orWhere('mst_customer.customer_name', 'ilike', '%'.$search.'%')
                          ->orWhere('mst_customer.address', 'ilike', '%'.$search.'%')
                          ->orWhere('trans_resi_header.sender_name', 'ilike', '%'.$search.'%')
                          ->orWhere('trans_resi_header.receiver_name', 'ilike', '%'.$search.'%')
                          ->orWhere('trans_resi_header.item_name', 'ilike', '%'.$search.'%');
                })
                ->take(10);
        $arrayResi = [];
        foreach ($query->get() as $model) {
            $resi                    = TransactionResiHeader::find($model->resi_header_id);
            $model->total_coly       = $resi->totalColy();
            $model->customer_name    = !empty($model->customer_name) ? $model->customer_name : '';
            $model->customer_address = !empty($model->customer_address) ? $model->customer_address : '';
            $arrayResi[]             = $model;
        }

        return response()->json($arrayResi);
    }

    protected function getOfficialReportNumber(OfficialReport $model)
    {
        $branch      = MasterBranch::find($model->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('op.trans_official_report')
                            ->where('branch_id', '=', $model->branch_id)
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->count();

        return 'OR.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }

    public function getOptionCategory()
    {
        return [
            OfficialReport::UMUM,
            OfficialReport::ADJUSTMENT,
            OfficialReport::RESI_CORRECTION,
            OfficialReport::INVOICE_REQUEST,
        ];
    }

    public function getOptionStatus()
    {
        return [
            OfficialReport::OPEN,
            OfficialReport::CLOSED,
            OfficialReport::APPROVED,
        ];
    }

    public function getOptionRole()
    {
        return \DB::table('adm.roles')->where('active', '=', 'Y')->where('id', '<>', Role::BRANCH_MANAGER)->orderBy('name', 'asc')->get();
    }

    public function getOptionBranch()
    {
        return \DB::table('op.mst_branch')->where('active', '=', 'Y')->orderBy('branch_name', 'asc')->get();
    }
}
