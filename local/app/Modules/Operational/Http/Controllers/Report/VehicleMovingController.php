<?php

namespace App\Modules\Operational\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterLookupValues;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Transaction\HistoryTransaction;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
use App\Service\TimezoneDateConverter;

class VehicleMovingController extends Controller
{
    const RESOURCE = 'Operational\Report\VehicleMoving';
    const URL      = 'operational/report/vehicle-moving';

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
        
        return view('operational::report.vehicle-moving.index', [
            'models'   => $query,
            'filters'  => $filters,
            'truckCategory' => \DB::table('adm.mst_lookup_values')->where('lookup_type', '=', MasterLookupValues::KATEGORI_KENDARAAN)->get(),
            'resource' => self::RESOURCE,
            'url'      => self::URL,
            'optionStatus' => [
                ManifestHeader::OPEN,
                ManifestHeader::REQUEST_APPROVE,
                ManifestHeader::APPROVED,
                ManifestHeader::OTR,
                ManifestHeader::CLOSED_WARNING,
                ManifestHeader::CLOSED,
                ManifestHeader::RETURNED,
                ManifestHeader::RETURNED_CLOSED,
                ManifestHeader::RETURNED_CLOSED_WARNING,
            ]
        ]);
    }

    public function printPdfIndex(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters);

        $header = view('print.header-pdf', ['title' => trans('operational/menu.vehicle-moving')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('operational::report.vehicle-moving.print-pdf-index', [
            'models'  => $query,
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('operational/menu.vehicle-moving').' - '.\Session::get('currentBranch')->branch_code);
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('P', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output(trans('operational/menu.vehicle-moving').' '.\Session::get('currentBranch')->branch_code.'.pdf');
        \PDF::reset();
    }

    public function printExcelIndex(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters);

        \Excel::create(trans('operational/menu.vehicle-moving'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.vehicle-moving'));
                });

                $sheet->cells('A3:K3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('operational/fields.police-number'),
                    trans('operational/fields.truck-code'),
                    trans('shared/common.category'),
                    trans('operational/fields.moving-type'),
                    trans('shared/common.date'),
                    trans('shared/common.time'),
                    trans('operational/fields.manifest-number'),
                    trans('operational/fields.driver'),
                    trans('operational/fields.assistant'),
                    trans('shared/common.status')
                ]);

                foreach($query as $index => $model) {
                    $date = !empty($model->date) ? TimezoneDateConverter::getClientDateTime($model->date) : null;
                    $truck = \DB::table('adm.mst_lookup_values')->select('meaning')->where('lookup_code', '=', $model->category)->first();

                    $data = [
                        $index + 1,
                        $model->police_number,
                        $model->truck_code,
                        !empty($truck) ? $truck->meaning : '',
                        $model->in_out,
                        $date->format('d-m-Y'),
                        $date->format('H:i'),
                        $model->manifest_number,
                        $model->driver_name,
                        $model->assistant_name,
                        $model->status,
                    ];
                    $sheet->row($index + 4, $data);
                }

                $currentRow = count($query) + 5;
                if (!empty($filters['truckCode'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.truck-code'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['truckCode'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['policeNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.nopol-truck'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['policeNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['manifestNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.manifest-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['manifestNumber'], 'C', $currentRow);
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
                if (!empty($filters['truckCategory'])) {
                    $truck = \DB::table('adm.mst_lookup_values')->select('meaning')->where('lookup_code', '=', $filters['truckCategory'])->first();
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.truck-category'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $truck->meaning, 'C', $currentRow);
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

                $currentRow = count($query) + 5;
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

    protected function getQuery(Request $request, $filters){
        if(empty($filters['truckCode']) && empty($filters['policeNumber']) && empty($filters['driver']) && empty($filters['driverAssistant']) && empty($filters['manifestNumber']) && empty($filters['truckCategory']) && empty($filters['status']) && empty($filters['dateFrom']) && empty($filters['dateTo'])){
            return [];
        }

        $query   = \DB::table('op.v_vehicle_moving')
                        ->where(function($query){
                            $query->where('city_start_id', '=', \Session::get('currentBranch')->city_id)
                                  ->orWhere('city_end_id', '=', \Session::get('currentBranch')->city_id);
                        })
                        ->distinct()
                        ->orderBy('date', 'desc');

        if (!empty($filters['truckCode'])) {
            $query->where('truck_code', 'ilike', '%'.$filters['truckCode'].'%');
        }

        if (!empty($filters['policeNumber'])) {
            $query->where('police_number', 'ilike', '%'.$filters['policeNumber'].'%');
        }

        if (!empty($filters['driver'])) {
            $query->where('driver_name', 'ilike', '%'.$filters['driver'].'%');
        }

        if (!empty($filters['driverAssistant'])) {
            $query->where('assistant_name', 'ilike', '%'.$filters['driverAssistant'].'%');
        }

        if (!empty($filters['manifestNumber'])) {
            $query->where('manifest_number', 'ilike', '%'.$filters['manifestNumber'].'%');
        }

        if (!empty($filters['truckCategory'])) {
            $query->where('category', 'ilike', '%'.$filters['truckCategory'].'%');
        }
        if (!empty($filters['status'])) {
            $query->where('status', 'ilike', '%'.$filters['status'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $query->where('date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $query->where('date', '<=', $date->format('Y-m-d 23:59:59'));
        }

        $arrModels    = [];
        $arrManifestStart = [];
        $arrManifestEnd   = [];
        foreach ($query->get() as $model) {
            if ($model->city_end_id == \Session::get('currentBranch')->city_id && !in_array($model->manifest_header_id, $arrManifestStart)) {
                $model->in_out   = "Manifest In";
                $arrManifestStart [] = $model->manifest_header_id;
            }elseif ($model->city_start_id == \Session::get('currentBranch')->city_id && !in_array($model->manifest_header_id, $arrManifestEnd)) {
                $model->in_out = "Manifest Out";
                $arrManifestEnd [] = $model->manifest_header_id;
            }else{
                continue;
            }
            $arrModels [] = $model;
        }
        return $arrModels;
    }
}
