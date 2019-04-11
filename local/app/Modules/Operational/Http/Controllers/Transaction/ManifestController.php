<?php

namespace App\Modules\Operational\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Role;
use App\MasterLookupValues;
use App\Notification;
use App\Service\Penomoran;
use App\Service\NotificationService;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Model\Master\MasterDriver;
use App\Modules\Operational\Model\Master\MasterTruck;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
use App\Modules\Operational\Model\Transaction\ManifestLine;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Transaction\ResiStock;
use App\Modules\Operational\Service\Master\RouteService;
use App\Modules\Operational\Service\Master\TruckService;
use App\Modules\Operational\Service\Master\DriverService;
use App\Modules\Operational\Service\Transaction\ResiService;
use App\Modules\Purchasing\Service\Transaction\PurchaseOrderService;

class ManifestController extends Controller
{
    const RESOURCE = 'Operational\Transaction\Manifest';
    const URL      = 'operational/transaction/manifest';

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

        return view('operational::transaction.manifest.index', [
            'models' => $query->paginate(10),
            'filters' => $filters,
            'resource' => self::RESOURCE,
            'url' => self::URL,
            'optionRoute' => RouteService::getActiveRoute(),
            'truckCategory' => \DB::table('adm.mst_lookup_values')->where('lookup_type', '=', MasterLookupValues::KATEGORI_KENDARAAN)->get(),
            'optionStatus' => [
                ManifestHeader::OPEN,
                ManifestHeader::REQUEST_APPROVE,
                ManifestHeader::APPROVED,
                ManifestHeader::OTR,
                ManifestHeader::ARRIVED,
                ManifestHeader::CLOSED_WARNING,
                ManifestHeader::CLOSED,
                ManifestHeader::RETURNED,
                ManifestHeader::RETURNED_CLOSED,
                ManifestHeader::RETURNED_CLOSED_WARNING,
            ]
        ]);
    }

    protected function getQuery(Request $request, $filters){
        $query   = \DB::table('op.trans_manifest_header')
                    ->select('trans_manifest_header.*')
                    ->leftJoin('op.trans_manifest_line', 'trans_manifest_header.manifest_header_id', '=', 'trans_manifest_line.manifest_header_id')
                    ->leftJoin('op.trans_resi_header', 'trans_manifest_line.resi_header_id', '=', 'trans_resi_header.resi_header_id')
                    ->leftJoin('op.mst_driver AS driver', 'trans_manifest_header.driver_id', '=', 'driver.driver_id')
                    ->leftJoin('op.mst_driver AS driver_assistant', 'trans_manifest_header.driver_assistant_id', '=', 'driver_assistant.driver_id')
                    ->leftJoin('op.mst_truck', 'trans_manifest_header.truck_id', '=', 'mst_truck.truck_id')
                    ->where('trans_manifest_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('trans_manifest_header.created_date', 'desc')
                    ->groupBy('trans_manifest_header.manifest_header_id')
                    ->distinct();

        if (!empty($filters['manifestNumber'])) {
            $query->where('manifest_number', 'ilike', '%'.$filters['manifestNumber'].'%');
        }

        if (!empty($filters['route'])) {
            $query->where('trans_manifest_header.route_id', '=', $filters['route']);
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

        if (!empty($filters['resiNumber'])) {
            $query->where('trans_resi_header.resi_number', 'ilike', '%'.$filters['resiNumber'].'%');
        }

        if (!empty($filters['truckCategory'])) {
            $query->where('mst_truck.category', 'ilike', '%'.$filters['truckCategory'].'%');
        }

        if (!empty($filters['status'])) {
            $query->where('trans_manifest_header.status', '=', $filters['status']);
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $query->where('trans_manifest_header.created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $query->where('trans_manifest_header.created_date', '<=', $date->format('Y-m-d 23:59:59'));
        }

        if (!empty($filters['status'])) {
            $query->where('trans_manifest_header.status', '=', $filters['status']);
        }
        return $query;
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new ManifestHeader();
        $model->status = ManifestHeader::OPEN;

        return view('operational::transaction.manifest.add', [
            'title' => trans('shared/common.add'),
            'model' => $model,
            'url' => self::URL,
            'resource' => self::RESOURCE,
            'optionRoute' => RouteService::getActiveRoute(),
            'optionTruck' => TruckService::getAllActiveTruckNonService(),
            'optionDriver' => DriverService::getActiveDriverAsistant(),
            'optionAssistant' => DriverService::getActiveDriverAsistant(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = ManifestHeader::where('manifest_header_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        if ($request->user()->cannot('accessBranch', $model->branch_id)) {
            abort(403);
        }

        $data = [
            'title' => trans('shared/common.edit'),
            'model' => $model,
            'url' => self::URL,
            'resource' => self::RESOURCE,
            'optionRoute' => RouteService::getActiveRoute(),
            'optionTruck' => TruckService::getAllActiveTruckNonService(),
            'optionDriver' => DriverService::getActiveDriverAsistant(),
            'optionAssistant' => DriverService::getActiveDriverAsistant(),
        ];

        if ($request->user()->can('access', [self::RESOURCE, 'update'])) {
            return view('operational::transaction.manifest.add', $data);
        } else {
            return view('operational::transaction.manifest.detail', $data);
        }

    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? ManifestHeader::find($id) : new ManifestHeader();

        $this->validate($request, [
            'routeId' => 'required',
            'truckId' => 'required',
            'driverId' => 'required',
        ]);

        if (empty($request->get('lineId')) && $request->get('btn-request-approve') !== null) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'You must insert minimal 1 line']);
        }
        
        if (!empty($request->get('lineId'))) {
            $resiKelebihanColy = $this->checkLineKelebihanColy($request, $id);
            if (!empty($resiKelebihanColy)) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => implode(', ', $resiKelebihanColy)]);
            }
        }

        if (empty($model->status)) {
            $model->status = ManifestHeader::OPEN;
        }

        if ($model->isOpen()) {
            if ($request->get('truckCategory') == MasterTruck::SEWA_TRIP && !empty($request->get('poHeaderId'))) {
                $model->po_header_id    = $request->get('poHeaderId');
            }else{
                $model->po_header_id    = null;
            }
            $model->route_id = $request->get('routeId');
            $model->truck_id = $request->get('truckId');
            $model->driver_id = $request->get('driverId');

            $driverAssistantId = !empty($request->get('driverAssistantId')) ? $request->get('driverAssistantId') : null;
            $model->driver_assistant_id = $driverAssistantId;
            $model->description = $request->get('description');
            $model->branch_id = \Session::get('currentBranch')->branch_id;
            $model->canceled_inprocess_date = null;
            $model->canceled_inprocess_by = null;
            $model->canceled_inprocess_note = null;

            if ($model->isOpen()) {
                $driver = MasterDriver::find(intval($model->driver_id));
                $salary = $driver !== null && $driver->isTripEmployee() ? $this->getDriverSalary($model->route_id, $model->truck_id, MasterDriver::DRIVER) : 0;
                $model->driver_salary = $salary;
            }

            if ($model->isOpen()) {
                $driverAssistant = MasterDriver::find(intval($model->driver_assistant_id));
                $salary = $driverAssistant !== null && $driverAssistant->isTripEmployee() ? $this->getDriverSalary($model->route_id, $model->truck_id, MasterDriver::ASSISTANT) : 0;
                $model->driver_assistant_salary = $salary;
            }

            if (empty($id)) {
                $model->created_date = $this->now;
                $model->created_by = \Auth::user()->id;
            } else {
                $model->last_updated_date = $this->now;
                $model->last_updated_by = \Auth::user()->id;
            }

            if (empty($model->manifest_number)) {
                $model->manifest_number = $this->getManifestNumber($model);
            }

            try {
                $model->save();
            } catch (\Exception $e) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }
        }

        if ($model->isOpen() || $model->isRequestApprove() || $model->isApproved()) {
            $model->description = $request->get('description');
            $model->save();

            $model->line()->delete();
            $lines = [];
            for ($i=0; $i < count($request->get('lineId')); $i++) {
                $resiId = $request->get('resiId')[$i];
                $colySent = intval(str_replace(',', '', $request->get('colySent')[$i]));

                if (isset($lines[$resiId])) {
                    $lines[$resiId]['colySent'] += $colySent;
                    continue;
                }

                $lines[$resiId] = [
                    'resiId' => $resiId,
                    'colySent' => $colySent,
                ];
            }

            foreach ($lines as $arrLine) {
                $line =  new ManifestLine();
                $line->manifest_header_id = $model->manifest_header_id;
                $line->resi_header_id = $arrLine['resiId'];
                $line->coly_sent = $arrLine['colySent'];
                $line->quantity_remain = $line->coly_sent;

                if (empty($id)) {
                    $line->created_date = $this->now;
                    $line->created_by = \Auth::user()->id;
                }else{
                    $line->last_updated_date = $this->now;
                    $line->last_updated_by = \Auth::user()->id;
                }

                try {
                    $line->save();
                } catch (\Exception $e) {
                    return redirect(self::URL . '/edit/' . $model->manifest_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }
            }
        }

        if ($request->get('btn-request-approve') !== null && $model->isOpen()) {
            $model->status = ManifestHeader::REQUEST_APPROVE;
            $model->last_updated_date = $this->now;
            $model->last_updated_by = \Auth::user()->id;
            $model->approved_note = null;

            try {
                $model->save();
            } catch (\Exception $e) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            /** notifikasi request approve **/
            NotificationService::createNotification(
                'Manifest Request for Approval',
                'Manifest '.$model->manifest_number.' - '.$request->get('approvedNotes'),
                ApproveManifestController::URL.'/edit/'.$model->manifest_header_id,
                [Role::WAREHOUSE_MANAGER]
            );
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.manifest').' '.$model->manifest_number])
        );

        return redirect(self::URL);
    }

    public function close(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'close'])) {
            abort(403);
        }

        $model = ManifestHeader::find($request->get('id'));
        if ($model === null || !$model->isArrived()) {
            abort(404);
        }
       
        $model->status = ManifestHeader::CLOSED_WARNING;
        $model->description .= ' (Close warning reason is '. $request->get('reason', '').')';
        $model->save();

        $userNotif = NotificationService::getUserNotification([Role::BRANCH_MANAGER]);
        foreach ($userNotif as $user) {
            $notif             = new Notification();
            $notif->branch_id  = \Session::get('currentBranch')->branch_id;
            $notif->category   = 'Manifest Force Close';
            $notif->message    = 'Manifest Force Close '.$model->manifest_number. '. ' . $request->get('reason', '');
            $notif->url        = self::URL.'/edit/'.$model->manifest_header_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->save();
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.close-message', ['variable' => trans('operational/menu.manifest').' '.$model->manifest_number])
        );

        return redirect(self::URL);
    }

    public function printPdfIndex(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters);

        $header = view('print.header-pdf', ['title' => trans('operational/menu.manifest')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('operational::transaction.manifest.print-pdf-index', [
            'models'  => $query->get(),
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('operational/menu.manifest'));
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('P', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output(trans('operational/menu.manifest').'.pdf');
        \PDF::reset();
    }

    public function printExcelIndex(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters);

        \Excel::create(trans('operational/menu.manifest'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.manifest'));
                });

                $sheet->cells('A3:K3', function($cells) {
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
                    trans('shared/common.status'),
                ]);

                $currentRow = 4;
                foreach($query->get() as $model) {
                    $model = ManifestHeader::find($model->manifest_header_id);
                    $date = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
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
                if (!empty($filters['resiNumber'])) { 
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.resi-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['resiNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['truckCategory'])) {
                    $truckCategory = \DB::table('adm.mst_lookup_values')->where('lookup_code', '=', $filters['truckCategory'])->first();
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.truck-category'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $truckCategory->meaning, 'C', $currentRow);
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

    public function printPdfDetail(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $filters = \Session::get('filters');
        $model= ManifestHeader::find($id);

        if ($model === null) {
            abort(404);
        }

        $header = view('print.header-pdf', ['title' => trans('operational/menu.manifest')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('operational::transaction.manifest.print-pdf-detail', [
            'model'  => $model,
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('operational/menu.manifest'));
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('P', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output($model->manifest_number.'.pdf');
        \PDF::reset();
    }

    public function printPdfDetailB(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $filters = \Session::get('filters');
        $model= ManifestHeader::find($id);

        if ($model === null) {
            abort(404);
        }

        $header = view('print.header-pdf', ['title' => trans('operational/menu.manifest')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('operational::transaction.manifest.print-pdf-detail-b', [
            'model'  => $model,
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('operational/menu.manifest'));
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('P', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output($model->manifest_number.'.pdf');
        \PDF::reset();
    }

    public function printPdfReport(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }           
        $filters = \Session::get('filters');
        $model= ManifestHeader::find($id);

        if ($model === null) {
            abort(404);
        }

        $header = view('print.header-pdf', ['title' => trans('operational/menu.manifest-report')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('operational::transaction.manifest.print-pdf-report', [
            'model'  => $model,
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('operational/menu.manifest-report'));
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('P', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output(trans('operational/menu.manifest-report').' '.$model->manifest_number.'.pdf');
        \PDF::reset();
    }

    protected function checkLineKelebihanColy(Request $request, $manifestId)
    {
        $errorMessages = [];

        foreach ($this->distinctResiId($request) as $resiId) {
            $resi = TransactionResiHeader::find($resiId);

            $colySent = $this->calculateColySent($request, $resiId);
            $resiStock = $this->countResiStock($resiId);
            $resiManifestLain = $this->countResiManifestLain($resiId, $manifestId);

            if ($colySent > $resiStock - $resiManifestLain) {
                $errorMessages[] = 'Resi Number ' . $resi->resi_number . ' exceed max ' . ($resiStock - $resiManifestLain) . ' coly';
            }
        }

        return $errorMessages;
    }

    protected function distinctResiId(Request $request)
    {
        $distinct = [];
        foreach ($request->get('resiId') as $resiId) {
            if (!in_array($resiId, $distinct)) {
                $distinct[] = $resiId;
            }
        }

        return $distinct;
    }

    protected function calculateColySent(Request $request, $resiId)
    {
        $colySent = 0;
        $index = 0;
        foreach ($request->get('resiId') as $resiIdPost) {
            if ($resiIdPost == $resiId) {
                $colySent += $request->get('colySent')[$index];
            }

            $index++;
        }

        return $colySent;
    }

    protected function countResiStock($resiId)
    {
        $resiStock = \DB::table('op.mst_stock_resi')
                        ->where('resi_header_id', '=', $resiId)
                        ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->first();

        return $resiStock !== null ? $resiStock->coly : 0;
    }

    protected function countResiManifestLain($resiId, $manifestId)
    {
        $colySent = \DB::table('op.trans_manifest_line')
                    ->selectRaw('SUM(trans_manifest_line.coly_sent) as sum')
                    ->join('op.trans_manifest_header', 'trans_manifest_line.manifest_header_id', '=', 'trans_manifest_header.manifest_header_id')
                    ->where('trans_manifest_line.resi_header_id', '=', $resiId)
                    ->where('trans_manifest_line.manifest_header_id', '<>', $manifestId)
                    ->where('trans_manifest_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->whereIn('trans_manifest_header.status', [ManifestHeader::OPEN, ManifestHeader::REQUEST_APPROVE, ManifestHeader::APPROVED])
                    ->first();

        return $colySent->sum;
    }

    protected function getManifestNumber(ManifestHeader $model)
    {
        $branch      = MasterBranch::find($model->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('op.trans_manifest_header')
                            ->where('branch_id', '=', $model->branch_id)
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->count();

        return 'MF.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }

    public function getJsonResi(Request $request){
        $term = $request->get('term');
        $query   = \DB::table('op.trans_resi_header')
                        ->select('trans_resi_header.*', 'mst_stock_resi.coly as coly_wh')
                        ->join('op.mst_stock_resi', 'trans_resi_header.resi_header_id', '=', 'mst_stock_resi.resi_header_id')
                        ->where('mst_stock_resi.branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->where('trans_resi_header.status', '<>', TransactionResiHeader::CANCELED)
                        ->orderBy('resi_number', 'asc');

        $query->where('trans_resi_header.resi_number', 'ilike', '%'.$term.'%');

        $data = [];
        foreach ($query->take(10)->get() as $stdResi) {
            $resi = TransactionResiHeader::find($stdResi->resi_header_id);

            $stdResi->route_code = $resi->route !== null ? $resi->route->route_code : '';
            $stdResi->total_coly = $resi->totalColy();
            $stdResi->receiver   = $resi->customerReceiver !== null ? $resi->customerReceiver->customer_name : $resi->receiver_name;

            $data[] = $stdResi;
        }

        return response()->json($data);
    }

    public function getJsonPo(Request $request)
    {
        $search = $request->get('search');
        $query = PurchaseOrderService::getQueryPurchaseOrderTruckRent();

        $query->where(function ($query) use ($search) {
                    $query->where('v_po_headers.po_number', 'ilike', '%'.$search.'%')
                          ->orWhere('v_po_headers.description', 'ilike', '%'.$search.'%')
                          ->orWhere('v_po_headers.kode_vendor', 'ilike', '%'.$search.'%')
                          ->orWhere('v_po_headers.nama_vendor', 'ilike', '%'.$search.'%');
                })
                ->take(10);
        return response()->json($query->get());
    }

    protected function getDriverSalary($routeId, $truckId, $position)
    {
        $truck = MasterTruck::find($truckId);
        if ($truck === null) {
            return 0;
        }

        $driverSalary = \DB::table('op.mst_driver_salary')
                        ->where('route_id', '=', $routeId)
                        ->where('driver_position', '=', $position)
                        ->where('vehicle_type', '=', $truck->type)
                        ->first();

        return $driverSalary !== null ? intval($driverSalary->salary) : 0;
    }

    public function getDriverAndAssistantSalary(Request $request)
    {
        $routeId = $request->get('routeId');
        $truckId = $request->get('truckId');

        return response()->json(
            [
                'driver_salary' => $this->getDriverSalary($routeId, $truckId, MasterDriver::DRIVER),
                'driver_assistant_salary' => $this->getDriverSalary($routeId, $truckId, MasterDriver::ASSISTANT),
            ]
        );
    }
}
