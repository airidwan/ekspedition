<?php

namespace App\Modules\Payable\Http\Controllers\Transaction;

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
use App\Modules\Payable\Model\Transaction\InvoiceHeader;

class InjectManifestDriverAndAssistantSalaryController extends Controller
{
    const RESOURCE = 'Payable\Transaction\InjectManifestDriverAndAssistantSalary';
    const URL      = 'payable/transaction/inject-manifest-driver-and-assistant-salary';

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

        $filters      = $request->session()->get('filters');
        $sqlInvoiceAp = 'SELECT manifest_id FROM ap.invoice_line JOIN ap.invoice_header ON invoice_line.header_id = invoice_header.header_id WHERE '.
                        'invoice_header.type_id = \'' . InvoiceHeader::DRIVER_SALARY . '\' AND manifest_id IS NOT NULL';
        $query        = \DB::table('op.trans_manifest_header')
                            ->leftJoin('op.mst_driver AS driver', 'trans_manifest_header.driver_id', '=', 'driver.driver_id')
                            ->leftJoin('op.mst_driver AS driver_assistant', 'trans_manifest_header.driver_assistant_id', '=', 'driver_assistant.driver_id')
                            ->leftJoin('op.mst_truck', 'trans_manifest_header.truck_id', '=', 'mst_truck.truck_id')
                            ->where('trans_manifest_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->whereIn('status', [ManifestHeader::ARRIVED, ManifestHeader::CLOSED_WARNING, ManifestHeader::CLOSED])
                            ->whereRaw('trans_manifest_header.manifest_header_id NOT IN (' . $sqlInvoiceAp . ')')
                            ->orderBy('trans_manifest_header.created_date', 'desc');

        if (!empty($filters['manifestNumber'])) {
            $query->where('manifest_number', 'ilike', '%'.$filters['manifestNumber'].'%');
        }

        if (!empty($filters['driver'])) {
            $query->where(function ($query) use ($filters) {
                        $query->orWhere('driver.driver_name', 'ilike', '%'.$filters['driver'].'%')
                              ->orWhere('driver.driver_code', 'ilike', '%'.$filters['driver'].'%')
                              ->orWhere('driver_assistant.driver_name', 'ilike', '%'.$filters['driver'].'%')
                              ->orWhere('driver_assistant.driver_code', 'ilike', '%'.$filters['driver'].'%');
                    });
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

        if (!empty($filters['route'])) {
            $query->where('trans_manifest_header.route_id', '=', $filters['route']);
        }

        return view('payable::transaction.inject-manifest-driver-and-assistant-salary.index', [
            'models' => $query->paginate(10),
            'filters' => $filters,
            'resource' => self::RESOURCE,
            'url' => self::URL,
            'optionRoute' => $this->getOptionRoute()
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = ManifestHeader::where('manifest_header_id', '=', $id)->first();
        if ($model === null || !in_array($model->status, [ManifestHeader::OTR, ManifestHeader::ARRIVED, ManifestHeader::CLOSED_WARNING, ManifestHeader::CLOSED])) {
            abort(404);
        }

        if ($request->user()->cannot('accessBranch', $model->branch_id)) {
            abort(403);
        }

        return view('payable::transaction.inject-manifest-driver-and-assistant-salary.add', [
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

        if (!empty($model->driver) && $model->driver->isTripEmployee()) {
            $model->driver_salary = str_replace(',', '', $request->get('driverSalary'));
        }

        if (!empty($model->driverAssistant) && $model->driverAssistant->isTripEmployee()) {
            $model->driver_assistant_salary = str_replace(',', '', $request->get('driverAssistantSalary'));
        }

        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('payable/menu.inject-manifest-driver-and-assistant-salary').' Manifest '.$model->manifest_number])
        );

        return redirect(self::URL);
    }

    protected function getOptionRoute()
    {
        $query = \DB::table('op.mst_route')->where('active', '=', 'Y')->orderBy('route_code', 'asc');

        return $query->get();
    }
}
