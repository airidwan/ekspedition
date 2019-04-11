<?php

namespace App\Modules\Generalledger\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Generalledger\Model\Master\MasterCoa;

class MasterCoaController extends Controller
{
    const RESOURCE = 'Generalledger\Master\MasterCoa';
    const URL      = 'general-ledger/master/master-coa';
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

        return view('generalledger::master.master-coa.index', [
            'models' => $query->paginate(10),
            'filters' => $filters,
            'resource' => self::RESOURCE,
            'url' => self::URL,
            ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new MasterCoa();
        $model->active = 'Y';

        return view('generalledger::master.master-coa.add', [
            'title' => trans('shared/common.add'),
            'model' => $model,
            'url' => self::URL,
            ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterCoa::where('coa_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        return view('generalledger::master.master-coa.add', [
            'title' => trans('shared/common.edit'),
            'model' => $model,
            'url' => self::URL,
            ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? MasterCoa::where('coa_id', '=', $id)->first() : new MasterCoa();

        if ($request->get('segmentName') == MasterCoa::ACCOUNT) {
            $this->validate($request, [
                'coaCode' => 'required|max:8|unique:gl.mst_coa,coa_code,'.$id.',coa_id,segment_name,Account',
                ]);
        }else if($request->get('segmentName') == 'Future 1'){
            $this->validate($request, [
                'coaCode' => 'required|max:5|unique:gl.mst_coa,coa_code,'.$id.',coa_id,segment_name,Future 1',
                ]);
        }
        if ($request->get('segmentName') == MasterCoa::ACCOUNT) {
            $model->identifier = $request->get('identifier');
        }

        $model->segment_name = $request->get('segmentName');
        $model->description  = $request->get('description');
        $model->active       = !empty($request->get('status')) ? 'Y' : 'N';
        $now = new \DateTime();

        $model->coa_code     = $request->get('coaCode');

        if (empty($id)) {
            $model->created_date = $now;
            $model->created_by = \Auth::user()->id;
        } else {
            $model->last_updated_date = $now;
            $model->last_updated_by = \Auth::user()->id;
        }

        $model->save();

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('general-ledger/menu.master-coa').' '.$model->coa_code])
            );

        return redirect(self::URL);
    }

    protected function getQuery(Request $request, $filters){
        $query = \DB::table('gl.mst_coa')->orderBy('coa_code');

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('active', '=', 'Y');
        } else {
            $query->where('active', '=', 'N');
        }

        if (!empty($filters['segmentName'])) {
            $query->where('segment_name', '=', $filters['segmentName']);
        }

        if (!empty($filters['coaCode'])) {
            $query->where('coa_code', '=', $filters['coaCode']);
        }

        if (!empty($filters['identifier']) && $filters['segmentName'] == MasterCoa::ACCOUNT) {
            $query->where('identifier', '=', $filters['identifier']);
        }

        if (!empty($filters['description'])) {
            $query->where('description', 'ilike', '%'.$filters['description'].'%');
        }
        return $query;
    }

    public function printPdf(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters);

        $header = view('print.header-pdf', ['title' => trans('general-ledger/menu.master-coa')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('generalledger::master.master-coa.print-pdf', [
            'models'  => $query->get(),
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('general-ledger/menu.master-coa'));
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('P', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output(trans('general-ledger/menu.master-coa').'.pdf');
        \PDF::reset();
    }

    public function printExcel(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters)->get();

        \Excel::create(trans('general-ledger/menu.master-coa'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('general-ledger/menu.master-coa'));
                });

                $sheet->cells('A3:F3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('general-ledger/fields.segment-name'),
                    trans('general-ledger/fields.coa-code'),
                    trans('shared/common.description'),
                    trans('general-ledger/fields.identifier'),
                    trans('shared/common.active'),
                ]);
                foreach($query as $index => $model) {
                    $identifier = '';
                    if ($model->identifier == '1') {
                        $identifier = 'Asset';
                    } else if ($model->identifier == '2'){
                        $identifier = 'Liability';
                    } else if ($model->identifier == '3'){
                        $identifier = 'Equitas';
                    } else if ($model->identifier == '4'){
                        $identifier = 'Revenue';
                    } else if ($model->identifier == '5'){
                        $identifier = 'Ekspense';
                    }
                    $data = [
                        $index + 1,
                        $model->segment_name,
                        $model->coa_code,
                        $model->description,
                        $identifier,
                        $model->active == 'Y' ? 'v' : 'x',
                    ];
                    $sheet->row($index + 4, $data);
                }

                $currentRow = count($query) + 5;
                if (!empty($filters['segmentName'])) {
                    $this->addLabelDescriptionCell($sheet, trans('general-ledger/fields.segment-name'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['segmentName'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['coaCode'])) {
                    $this->addLabelDescriptionCell($sheet, trans('general-ledger/fields.coa-code'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['coaCode'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['description'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.description'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['description'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['identifier'])) {
                    $identifier = '';
                    if ($filters['identifier'] == '1') {
                        $identifier = 'Asset';
                    } else if ($filters['identifier'] == '2'){
                        $identifier = 'Liability';
                    } else if ($filters['identifier'] == '3'){
                        $identifier = 'Equitas';
                    } else if ($filters['identifier'] == '4'){
                        $identifier = 'Revenue';
                    } else if ($filters['identifier'] == '5'){
                        $identifier = 'Ekspense';
                    }
                    $this->addLabelDescriptionCell($sheet, trans('general-ledger/fields.identifier'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $identifier, 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['category'])) {
                    $category = AssetCategory::find($filters['category']);
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.category'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  !empty($category) ? $category->category_name : '', 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['status'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.status'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['status'], 'C', $currentRow);
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
}
