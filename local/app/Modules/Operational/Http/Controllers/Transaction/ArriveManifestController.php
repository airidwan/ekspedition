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

class ArriveManifestController extends Controller
{
    const RESOURCE = 'Operational\Transaction\ArriveManifest';
    const URL      = 'operational/transaction/arrive-manifest';

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
                    ->leftJoin('op.mst_route', 'mst_route.route_id', '=', 'trans_manifest_header.route_id')
                    ->where('mst_route.city_end_id', '=', \Session::get('currentBranch')->city_id)
                    ->where('trans_manifest_header.branch_id', '<>', \Session::get('currentBranch')->branch_id)
                    ->where('status', '=', ManifestHeader::OTR)
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

        if (!empty($filters['status'])) {
            $query->where('status', '=', $filters['status']);
        }

        return view('operational::transaction.arrive-manifest.index', [
            'models' => $query->paginate(10),
            'filters' => $filters,
            'resource' => self::RESOURCE,
            'url' => self::URL,
            'optionStatus' => [
                ManifestHeader::OTR,
                ManifestHeader::ARRIVED,
                ManifestHeader::CLOSED,
                ManifestHeader::CLOSED_WARNING,
            ]
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = ManifestHeader::where('manifest_header_id', '=', $id)->first();
        if ($model === null || !in_array($model->status, [ManifestHeader::OTR, ManifestHeader::ARRIVED, ManifestHeader::CLOSED, ManifestHeader::CLOSED_WARNING])) {
            abort(404);
        }

        return view('operational::transaction.arrive-manifest.add', [
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

        if (!$model->isOtr()) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Manifest is not shipped']);
        }

        $model->status = ManifestHeader::ARRIVED;
        $model->last_updated_date = $this->now;
        $model->last_updated_by   = \Auth::user()->id;
        $model->arrive_date       = $this->now;
        $model->arrive_branch_id  = \Session::get('currentBranch')->branch_id;

        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        /** notifikasi **/
        NotificationService::createSpesificBranchNotification(
            'Manifest Arrived',
            'Manifest ' . $model->manifest_number . ' is arrived at '.\Session::get('currentBranch')->branch_name,
            ManifestController::URL.'/edit/'.$model->manifest_header_id,
            [Role::OPERATIONAL_ADMIN],
            $model->branch_id
        );

        $this->saveHistoryResi($model);

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.arrive-manifest').' '.$model->manifest_number])
        );

        return redirect(self::URL);
    }

    public function printPdfChecklist(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $filters = \Session::get('filters');
        $model= ManifestHeader::find($id);

        if ($model === null || !in_array($model->status, [ManifestHeader::OTR, ManifestHeader::ARRIVED, ManifestHeader::CLOSED, ManifestHeader::CLOSED_WARNING])) {
            abort(404);
        }

        $header = view('print.header-pdf', ['title' => trans('operational/menu.manifest-checklist')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('operational::transaction.arrive-manifest.print-pdf-checklist', [
            'model'  => $model,
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('operational/menu.manifest-checklist'));
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('P', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output(trans('operational/menu.manifest-checklist').' '.$model->manifest_number.'.pdf');
        \PDF::reset();
    }

    protected function saveHistoryResi(ManifestHeader $model)
    {
        $manifest = ManifestHeader::find($model->manifest_header_id);
        foreach ($manifest->line as $line) {
            HistoryResiService::saveHistory(
                $line->resi_header_id,
                'Manifest Arrived',
                'Manifest Number: '.$manifest->manifest_number.' arrived at '.\Session::get('currentBranch')->branch_name
            );
        }
    }

    public function printExcelChecklist(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $filters = \Session::get('filters');
        $model   = ManifestHeader::find($id);

        if ($model === null || !in_array($model->status, [ManifestHeader::OTR, ManifestHeader::ARRIVED, ManifestHeader::CLOSED, ManifestHeader::CLOSED_WARNING])) {
            abort(404);
        }

        \Excel::create(trans('operational/menu.manifest'), function($excel) use ($model, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($model, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.arrive-manifest'));
                });

                $currentRow = 3;

                $this->addLabelDescriptionCell($sheet, trans('operational/fields.manifest-number'), 'A', $currentRow);
                $this->addValueDescriptionCell($sheet,  $model->manifest_number, 'B', $currentRow);

                $this->addLabelDescriptionCell($sheet, trans('operational/fields.driver'), 'D', $currentRow);
                $this->addValueDescriptionCell($sheet,  $model->driver->driver_name, 'E', $currentRow);

                $this->addLabelDescriptionCell($sheet, trans('operational/fields.owner-name'), 'G', $currentRow);
                $this->addValueDescriptionCell($sheet,  $model->truck->owner_name, 'H', $currentRow);
                $currentRow++;

                $this->addLabelDescriptionCell($sheet, trans('operational/fields.route'), 'A', $currentRow);
                $this->addValueDescriptionCell($sheet,  !empty($model->route) ? $model->route->route_code : '', 'B', $currentRow);
                
                $this->addLabelDescriptionCell($sheet, trans('operational/fields.driver-assistant'), 'D', $currentRow);
                $this->addValueDescriptionCell($sheet,  !empty($model->driverAssistant) ? $model->driverAssistant->driver_name : '', 'E', $currentRow);

                $this->addLabelDescriptionCell($sheet, trans('operational/fields.police-number'), 'G', $currentRow);
                $this->addValueDescriptionCell($sheet,  $model->truck->police_number, 'H', $currentRow);
                $currentRow++;
                
                $this->addLabelDescriptionCell($sheet, trans('shared/common.date'), 'A', $currentRow);
                $date = new \DateTime($model->created_date);
                $this->addValueDescriptionCell($sheet,  $date->format('d-m-Y'), 'B', $currentRow);

                $this->addLabelDescriptionCell($sheet, trans('shared/common.description'), 'D', $currentRow);
                $this->addValueDescriptionCell($sheet,  $model->description, 'E', $currentRow);
                $currentRow++;
                
                $currentRow++;
                $sheet->cells('A'.$currentRow.':L'.$currentRow, function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row($currentRow, [
                    trans('shared/common.num'),
                    trans('operational/fields.resi-number'),
                    trans('operational/fields.item-name'),
                    trans('operational/fields.sender'),
                    trans('operational/fields.receiver'),
                    trans('operational/fields.total-coly'),
                    trans('operational/fields.coly-send'),
                    trans('operational/fields.weight'),
                    trans('operational/fields.volume'),
                    trans('operational/fields.kota-tujuan'),
                    trans('operational/fields.check'),
                    trans('operational/fields.description'),
                ]);
                $currentRow++;
                $num = 1;
                foreach($model->line as $line) {
                    $data = [
                        $num++,
                        $line->resi !== null ? $line->resi->resi_number : '',
                        $line->resi !== null ? $line->resi->item_name : '',
                        $line->resi !== null ? $line->resi->sender_name : '',
                        $line->resi !== null ? $line->resi->receiver_name : '',
                        $line->resi !== null ? $line->resi->totalColy() : '',
                        $line->coly_sent,
                        $line->resi !== null ? $line->resi->totalWeightAll() : '',
                        $line->resi !== null ? $line->resi->totalVolumeAll() : '',
                        $line->resi->route->cityEnd !== null ? $line->resi->route->cityEnd->city_name : '',
                    ];

                    $sheet->row($currentRow++, $data);
                }

                $currentRow = $currentRow + 1;

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
