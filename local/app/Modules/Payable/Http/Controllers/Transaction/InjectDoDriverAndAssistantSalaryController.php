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
use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader;
use App\Modules\Operational\Model\Transaction\DeliveryOrderLine;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Transaction\ResiStock;
use App\Modules\Operational\Service\Master\RouteService;
use App\Modules\Operational\Service\Master\TruckService;
use App\Modules\Operational\Service\Master\DriverService;
use App\Modules\Operational\Service\Master\DeliveryAreaService;
use App\Modules\Operational\Service\Transaction\ResiService;
use App\Modules\Payable\Model\Transaction\InvoiceHeader;

class InjectDoDriverAndAssistantSalaryController extends Controller
{
    const RESOURCE = 'Payable\Transaction\InjectDoDriverAndAssistantSalary';
    const URL      = 'payable/transaction/inject-do-driver-and-assistant-salary';

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
        $sqlInvoiceAp = 'SELECT invoice_line.do_header_id FROM ap.invoice_line JOIN ap.invoice_header ON invoice_line.header_id = invoice_header.header_id WHERE '.
                        'invoice_header.type_id = \'' . InvoiceHeader::DRIVER_SALARY . '\' AND invoice_line.do_header_id IS NOT NULL';
        $query        = \DB::table('op.trans_delivery_order_header')
                            ->leftJoin('op.mst_driver AS driver', 'trans_delivery_order_header.driver_id', '=', 'driver.driver_id')
                            ->leftJoin('op.mst_driver AS driver_assistant', 'trans_delivery_order_header.assistant_id', '=', 'driver_assistant.driver_id')
                            ->leftJoin('op.mst_truck', 'trans_delivery_order_header.truck_id', '=', 'mst_truck.truck_id')
                            ->where('trans_delivery_order_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->whereIn('status', [DeliveryOrderHeader::CLOSED])
                            ->whereRaw('trans_delivery_order_header.delivery_order_header_id NOT IN (' . $sqlInvoiceAp . ')')
                            ->orderBy('trans_delivery_order_header.created_date', 'desc');

        if (!empty($filters['deliveryOrderNumber'])) {
            $query->where('delivery_order_number', 'ilike', '%'.$filters['deliveryOrderNumber'].'%');
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
            $query->where('trans_delivery_order_header.created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $query->where('trans_delivery_order_header.created_date', '>=', $date->format('Y-m-d 23:59:59'));
        }

        if (!empty($filters['deliveryArea'])) {
            $query->where('trans_delivery_order_header.delivery_area_id', '=', $filters['deliveryArea']);
        }

        return view('payable::transaction.inject-do-driver-and-assistant-salary.index', [
            'models'             => $query->paginate(10),
            'filters'            => $filters,
            'resource'           => self::RESOURCE,
            'url'                => self::URL,
            'optionDeliveryArea' => DeliveryAreaService::getActiveDeliveryArea(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = DeliveryOrderHeader::where('delivery_order_header_id', '=', $id)->first();
        if ($model === null || !in_array($model->status, [DeliveryOrderHeader::CLOSED])) {
            abort(404);
        }

        if ($request->user()->cannot('accessBranch', $model->branch_id)) {
            abort(403);
        }

        return view('payable::transaction.inject-do-driver-and-assistant-salary.add', [
            'title' => trans('shared/common.edit'),
            'model' => $model,
            'url' => self::URL,
            'resource' => self::RESOURCE,
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? DeliveryOrderHeader::find($id) : new DeliveryOrderHeader();

        if (!empty($model->driver) && $model->driver->isTripEmployee()) {
            $model->driver_salary = intval(str_replace(',', '', $request->get('driverSalary')));
        }

        if (!empty($model->assistant) && $model->assistant->isTripEmployee()) {
            $model->driver_assistant_salary = intval(str_replace(',', '', $request->get('driverAssistantSalary')));
        }

        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('payable/menu.inject-do-driver-and-assistant-salary').' DO '.$model->delivery_order_number])
        );

        return redirect(self::URL);
    }
}
