<?php

namespace App\Modules\Operational\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Notification;
use App\Role;
use App\Service\Penomoran;
use App\Service\NotificationService;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
use App\Modules\Operational\Model\Transaction\ManifestLine;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Service\Master\RouteService;
use App\Modules\Operational\Service\Master\TruckService;
use App\Modules\Operational\Service\Master\DriverService;
use App\Modules\Operational\Service\Transaction\ResiService;

class ApproveManifestController extends Controller
{
    const RESOURCE = 'Operational\Transaction\ApproveManifest';
    const URL      = 'operational/transaction/approve-manifest';

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
        $status  = !empty($filters['status']) ? $filters['status'] : ManifestHeader::REQUEST_APPROVE;
        $query   = \DB::table('op.trans_manifest_header')
                    ->leftJoin('op.mst_driver AS driver', 'trans_manifest_header.driver_id', '=', 'driver.driver_id')
                    ->leftJoin('op.mst_driver AS driver_assistant', 'trans_manifest_header.driver_assistant_id', '=', 'driver_assistant.driver_id')
                    ->leftJoin('op.mst_truck', 'trans_manifest_header.truck_id', '=', 'mst_truck.truck_id')
                    ->where('trans_manifest_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where('status', '=', $status)
                    ->orderBy('trans_manifest_header.created_date', 'desc');

        if (!empty($filters['manifestNumber'])) {
            $query->where('manifest_number', 'ilike', '%'.$filters['manifestNumber'].'%');
        }

        if (!empty($filters['driver'])) {
            $query->where('driver.driver_name', 'ilike', '%'.$filters['driver'].'%');
        }

        if (!empty($filters['driverAssistant'])) {
            $query->where('driver_assistant.driver_name', 'ilike', '%'.$filters['driverAssistant'].'%');
        }

        if (!empty($filters['nopolTruck'])) {
            $query->where('mst_truck.police_number', 'ilike', '%'.$filters['nopolTruck'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $query->where('trans_manifest_header.created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $query->where('trans_manifest_header.created_date', '>=', $date->format('Y-m-d 23:59:59'));
        }

        return view('operational::transaction.approve-manifest.index', [
            'models' => $query->paginate(10),
            'filters' => $filters,
            'resource' => self::RESOURCE,
            'url' => self::URL,
            'optionStatus' => [
                ManifestHeader::REQUEST_APPROVE,
                ManifestHeader::APPROVED
            ]
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = ManifestHeader::where('manifest_header_id', '=', $id)->first();
        if ($model === null || !in_array($model->status, [ManifestHeader::REQUEST_APPROVE, ManifestHeader::APPROVED])) {
            abort(404);
        }

        if ($request->user()->cannot('accessBranch', $model->branch_id)) {
            abort(403);
        }

        return view('operational::transaction.approve-manifest.add', [
            'title' => trans('shared/common.edit'),
            'model' => $model,
            'url' => self::URL,
            'resource' => self::RESOURCE,
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? ManifestHeader::find($id) : new ManifestHeader();

        $this->validate($request, [
            'approvedNote' => 'required',
        ]);

        if ($request->get('btn-approve') !== null) {
            $model->approved = true;
            $model->status = ManifestHeader::APPROVED;

        } elseif ($request->get('btn-reject') !== null) {
            $model->approved = false;
            $model->status = ManifestHeader::OPEN;
        }

        $model->approved_note = $request->get('approvedNote');
        $model->approved_by = \Auth::user()->id;
        $model->approved_date = new \DateTime();

        if ($model->truck === null || $model->truck->isSewaTrip()) {
            $model->money_trip = 0;
        }

        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(self::URL.'/edit/'.$model->manifest_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $notifCategory = $request->get('btn-approve') !== null ? 'Manifest Approved' : 'Manifest Rejected';
        NotificationService::createNotification(
            $notifCategory,
            'Manifest ' . $model->manifest_number . ' - ' . $model->approved_note,
            ManifestController::URL.'/edit/'.$model->manifest_header_id,
            [Role::WAREHOUSE_ADMIN]
        );

        if ($model->approved) {
            /** notifikasi request approve **/
            NotificationService::createNotification(
                'Manifest Request Money Trip',
                'Manifest '.$model->manifest_number,
                MoneyTripManifestController::URL.'/edit/'.$model->manifest_header_id,
                [Role::FINANCE_ADMIN]
            );
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.approve-manifest').' '.$model->manifest_number])
        );

        return redirect(self::URL);
    }
}
