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

class MoneyTripManifestController extends Controller
{
    const RESOURCE = 'Operational\Transaction\MoneyTripManifest';
    const URL      = 'operational/transaction/money-trip-manifest';

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
        $query   = \DB::table('op.trans_manifest_header')
                    ->leftJoin('op.mst_driver AS driver', 'trans_manifest_header.driver_id', '=', 'driver.driver_id')
                    ->leftJoin('op.mst_driver AS driver_assistant', 'trans_manifest_header.driver_assistant_id', '=', 'driver_assistant.driver_id')
                    ->leftJoin('op.mst_truck', 'trans_manifest_header.truck_id', '=', 'mst_truck.truck_id')
                    ->whereNull('canceled_inprocess_date')
                    ->where('trans_manifest_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where('status', '=', ManifestHeader::APPROVED)
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

        return view('operational::transaction.money-trip-manifest.index', [
            'models' => $query->paginate(10),
            'filters' => $filters,
            'resource' => self::RESOURCE,
            'url' => self::URL
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = ManifestHeader::where('manifest_header_id', '=', $id)->where('status', '=', ManifestHeader::APPROVED)->first();
        if ($model === null) {
            abort(404);
        }

        if ($request->user()->cannot('accessBranch', $model->branch_id)) {
            abort(403);
        }

        return view('operational::transaction.money-trip-manifest.add', [
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

        if ($model->truck !== null && !$model->truck->isSewaTrip()) {
            $this->validate($request, [
                'moneyTrip' => 'required',
                'moneyTripNotes' => 'required',
            ]);
        }

        if ($model->truck !== null && !$model->truck->isSewaTrip()) {
            $model->money_trip = str_replace(',', '', $request->get('moneyTrip'));
            $model->money_trip_note = $request->get('moneyTripNotes');
        } else {
            $model->money_trip = 0;
            $model->money_trip_note = null;
        }

        $now = new \DateTime();
        $model->last_updated_date = $now;
        $model->last_updated_by = \Auth::user()->id;

        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        NotificationService::createNotification(
            'Money Trip Manifest Updated',
            'Manifest ' . $model->manifest_number . '. Money trip: '.number_format($model->money_trip).'. Note: '.$model->money_trip_note.'. Manifest ready to ship.',
            ShipmentManifestController::URL.'/edit/'.$model->manifest_header_id,
            [Role::OPERATIONAL_ADMIN]
        );

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.money-trip-manifest').' '.$model->manifest_number])
        );

        return redirect(self::URL);
    }
}
