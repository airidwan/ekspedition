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
use App\Modules\Operational\Model\Transaction\ResiStock;
use App\Modules\Operational\Service\Master\RouteService;
use App\Modules\Operational\Service\Master\TruckService;
use App\Modules\Operational\Service\Master\DriverService;
use App\Modules\Operational\Service\Transaction\ResiService;
use App\Modules\Operational\Service\Transaction\HistoryResiService;

class ShipmentManifestController extends Controller
{
    const RESOURCE = 'Operational\Transaction\ShipmentManifest';
    const URL      = 'operational/transaction/shipment-manifest';

    protected $now;

    public function __construct()
    {
        $this->middleware('auth');
        $this->now = new \DateTime();
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
        $query   = \DB::table('op.trans_manifest_header')
                    ->leftJoin('op.mst_driver AS driver', 'trans_manifest_header.driver_id', '=', 'driver.driver_id')
                    ->leftJoin('op.mst_driver AS driver_assistant', 'trans_manifest_header.driver_assistant_id', '=', 'driver_assistant.driver_id')
                    ->leftJoin('op.mst_truck', 'trans_manifest_header.truck_id', '=', 'mst_truck.truck_id')
                    ->where('trans_manifest_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where('status', '=', ManifestHeader::APPROVED)
                    ->orderBy('trans_manifest_header.created_date', 'desc');

        if (!empty($filters['manifestNnumber'])) {
            $query->where('manifest_number', 'ilike', '%'.$filters['manifestNnumber'].'%');
        }

        if (!empty($filters['driver'])) {
            $query->where('driver.driver_name', 'ilike', '%'.$filters['driver'].'%');
        }

        if (!empty($filters['driverAssistant'])) {
            $query->where('driver_assistant.driver_name', 'ilike', '%'.$filters['driverAssistant'].'%');
        }

        if (!empty($filters['nopolTruck'])) {
            $query->where('mst_truck.policeNumber', 'ilike', '%'.$filters['nopolTruck'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $query->where('trans_manifest_header.created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $query->where('trans_manifest_header.created_date', '>=', $date->format('Y-m-d 23:59:59'));
        }

        return view('operational::transaction.shipment-manifest.index', [
            'models' => $query->paginate(10),
            'filters' => $filters,
            'resource' => self::RESOURCE,
            'url' => self::URL,
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

        return view('operational::transaction.shipment-manifest.add', [
            'title' => trans('shared/common.edit'),
            'model' => $model,
            'url' => self::URL,
            'resource' => self::RESOURCE,
        ]);
    }

    public function save(Request $request)
    {
        \DB::beginTransaction();
        $id = intval($request->get('id'));
        $model = !empty($id) ? ManifestHeader::find($id) : new ManifestHeader();

        if (!$model->isApproved()) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Manifest is not approved']);
        }

        if ($model->money_trip === null) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Money Trip is required']);
        }

        $model->status = ManifestHeader::OTR;
        $model->last_updated_date = $this->now;
        $model->last_updated_by   = \Auth::user()->id;
        $model->shipment_date     = $this->now;

        foreach ($model->line as $line) {
            $modelStock = ResiStock::where('resi_header_id', '=', $line->resi_header_id)->where('branch_id', '=',  $model->branch_id)->first();
            $modelStock->coly = $modelStock->coly - $line->coly_sent;
            if ($modelStock->coly <= 0 ) {
                $modelStock->delete();
            } else {
                $modelStock->save();
            }
        }

        try {
            $model->save();
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);

        }

        /** notifikasi **/
        $cityStartName = !empty($model->route->cityStart) ? $model->route->cityStart->city_name : '';
        foreach ($this->getBranchTujuanManifest($model) as $branch) {
            NotificationService::createSpesificBranchNotification(
                'Manifest Shipped',
                'Manifest ' . $model->manifest_number . ' shipped from '.$cityStartName,
                ArriveManifestController::URL.'/edit/'.$model->manifest_header_id,
                [Role::OPERATIONAL_ADMIN],
                $branch->branch_id
            );
        }

        $this->saveHistoryResi($model);
        \DB::commit();

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.shipment-manifest').' '.$model->manifest_number])
        );

        return redirect(self::URL);
    }

    protected function getBranchTujuanManifest(ManifestHeader $model)
    {
        $cityEnd = !empty($model->route) ? $model->route->cityEnd : null;
        if ($cityEnd === null) {
            return;
        }

        return \DB::table('op.mst_branch')->where('active', '=', 'Y')->where('city_id', '=', $cityEnd->city_id)->get();
    }

    protected function saveHistoryResi(ManifestHeader $model)
    {
        $manifest = ManifestHeader::find($model->manifest_header_id);
        foreach ($manifest->line as $line) {
            HistoryResiService::saveHistory(
                $line->resi_header_id,
                'Manifest Shipped',
                'Manifest Number: '.$manifest->manifest_number.', Coly Sent: '.$line->coly_sent
            );
        }
    }
}
