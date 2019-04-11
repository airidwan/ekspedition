<?php

namespace App\Modules\Operational\Http\Controllers\Report;

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

class ManifestArrivedController extends Controller
{
    const RESOURCE = 'Operational\Report\ManifestArrived';
    const URL      = 'operational/report/manifest-arrived';
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
        $query   = $this->getQuery($request, $filters);

        return view('operational::report.manifest-arrived.index', [
            'models'        => $query->paginate(10),
            'filters'       => $filters,
            'resource'      => self::RESOURCE,
            'url'           => self::URL,
            'optionStatus'  => [
                                ManifestHeader::ARRIVED,
                                ManifestHeader::CLOSED,
                                ManifestHeader::CLOSED_WARNING,
                                ]
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $model = ManifestHeader::where('manifest_header_id', '=', $id)->first();
        if ($model === null || !in_array($model->status, [ManifestHeader::ARRIVED, ManifestHeader::CLOSED, ManifestHeader::CLOSED_WARNING])) {
            abort(404);
        }

        if ($request->user()->cannot('accessBranch', $model->arrive_branch_id)) {
            abort(403);
        }

        return view('operational::report.manifest-arrived.add', [
            'title'     => trans('shared/common.edit'),
            'model'     => $model,
            'url'       => self::URL,
            'resource'  => self::RESOURCE,
        ]);
    }

    public function getQuery(Request $request, $filters){
        $query   = \DB::table('op.trans_manifest_header')
                    ->leftJoin('op.mst_driver AS driver', 'trans_manifest_header.driver_id', '=', 'driver.driver_id')
                    ->leftJoin('op.mst_driver AS driver_assistant', 'trans_manifest_header.driver_assistant_id', '=', 'driver_assistant.driver_id')
                    ->leftJoin('op.mst_truck', 'trans_manifest_header.truck_id', '=', 'mst_truck.truck_id')
                    ->where('trans_manifest_header.arrive_branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where(function($query){
                        $query->where('trans_manifest_header.status', '=', ManifestHeader::ARRIVED)
                              ->orWhere('trans_manifest_header.status', '=', ManifestHeader::CLOSED)
                              ->orWhere('trans_manifest_header.status', '=', ManifestHeader::CLOSED_WARNING);
                    })
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
            $query->where('trans_manifest_header.arrived_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $query->where('trans_manifest_header.arrived_date', '<=', $date->format('Y-m-d 23:59:59'));
        }

        if (!empty($filters['status'])) {
            $query->where('trans_manifest_header.status', '=', $filters['status']);
        }

        return $query;
    }

    public function printExcelIndex(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters);

        \Excel::create(trans('operational/menu.manifest-arrived'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.manifest-arrived'));
                });

                $sheet->cells('A3:L3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('operational/fields.manifest-number'),
                    trans('operational/fields.date'),
                    trans('operational/fields.route'),
                    trans('operational/fields.kota-asal'),
                    trans('operational/fields.kota-tujuan'),
                    trans('operational/fields.nopol-truck'),
                    trans('operational/fields.truck-owner'),
                    trans('operational/fields.driver'),
                    trans('operational/fields.driver-assistant'),
                    trans('operational/fields.description'),
                    trans('operational/fields.arrived-date'),
                    trans('shared/common.status'),
                ]);

                $currentRow = 4;
                foreach($query->get() as $model) {
                    $model = ManifestHeader::find($model->manifest_header_id);
                    $date = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                    $arrivedDate = !empty($model->arrive_date) ? new \DateTime($model->arrive_date) : null;
                    $route = $model->route;
                    $startCity = $route !== null ? $route->cityStart : null;
                    $endCity = $route !== null ? $route->cityEnd : null;
                    $policeNumber = $model->truck !== null ? $model->truck->police_number : '';
                    $truckCategory = $model->truck !== null ? $model->truck->getCategory() : '';
                    $truckOwner = $model->truck !== null ? $model->truck->owner_name : '';
                    $truckPO = $model->po !== null ? ' - '.$model->po->po_number : '';

                    $data = [
                        $model->manifest_number,
                        $date !== null ? $date->format('d-m-Y') : '',
                        $route !== null ? $route->route_code : '',
                        $startCity !== null ? $startCity->city_name : '',
                        $endCity !== null ? $endCity->city_name : '',
                        $policeNumber.' - '.$truckCategory,
                        $truckOwner.$truckPO,
                        $model->driver !== null ? $model->driver->driver_name : '',
                        $model->driverAssistant !== null ? $model->driverAssistant->driver_name : '',
                        $model->description,
                        $arrivedDate !== null ? $arrivedDate->format('d-m-Y') : '',
                        $model->status,
                    ];

                    $sheet->row($currentRow++, $data);
                }

                $lastDataRow = $currentRow;
                $currentRow = $lastDataRow + 1;
                if (!empty($filters['manifestNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.manifest-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['manifestNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['driver'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.driver'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['driver'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['driverAssistant'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.driver-assistant'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['driverAssistant'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['nopolTruck'])) { 
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.nopol-truck'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['nopolTruck'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['arriveDate'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.arrived-date'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['arriveDate'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['dateFrom'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.date-from'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['dateFrom'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['dateTo'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.date-to'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['dateTo'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['status'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.status'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['status'], 'C', $currentRow);
                    $currentRow++;
                }

                $currentRow = $lastDataRow + 1;
                $this->addLabelDescriptionCell($sheet, trans('shared/common.date'), 'E', $currentRow);
                $this->addValueDescriptionCell($sheet, $this->now->format('d-m-Y'), 'F', $currentRow);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.user'), 'E', $currentRow + 1);
                $this->addValueDescriptionCell($sheet, \Auth::user()->full_name, 'F', $currentRow + 1);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.branch'), 'E', $currentRow + 2);
                $this->addValueDescriptionCell($sheet, \Session::get('currentBranch')->branch_name, 'F', $currentRow + 2);
            });

        })->export('xlsx');
    }

    protected function addLabelDescriptionCell($sheet, $value, $column, $row)
    {
        $sheet->cell($column.$row, function($cell) use($value) {
            $cell->setFont(['bold' => true]);
            $cell->setValue($value);
        });
    }

    protected function addValueDescriptionCell($sheet, $value, $column, $row)
    {
        $sheet->cell($column.$row, function($cell) use($value) {
            $cell->setValue($value);
        });
    }
}
