<?php

namespace App\Modules\Operational\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Master\MasterCommodity;

class MasterCommodityController extends Controller
{
    const RESOURCE = 'Operational\Master\MasterCommodity';
    const URL      = 'operational/master/master-commodity';

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
        $query   = $this->getQuery($request);

        return view('operational::master.master-commodity.index', [
            'models'   => $query->paginate(10),
            'filters'  => $filters,
            'resource' => self::RESOURCE,
            'optionCategory' => $this->getCategory(),
            'url'      => self::URL,
        ]);
    }

    protected function getQuery(Request $request)
    {
        $filters = $request->session()->get('filters');
        $query   = \DB::table('op.mst_commodity');

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('mst_commodity.active', '=', 'Y');
        } else {
            $query->where('mst_commodity.active', '=', 'N');
        }

        if (!empty($filters['category'])) {
            $query->where('mst_commodity.show', '=', $filters['category']);
        } 

        if (!empty($filters['commodity'])) {
            $query->where('commodity_name', 'ilike', '%'.$filters['commodity'].'%');
        }

        return $query;
    }

    public function printExcel(Request $request)
    {
        $query   = $this->getQuery($request);
        $filters = \Session::get('filters');

        \Excel::create(trans('operational/menu.commodity'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.commodity'));
                });

                $sheet->cells('A3:C3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('operational/fields.commodity'),
                    trans('operational/fields.show-on-manifest'),
                    trans('shared/common.active'),
                ]);

                $currentRow = 4;
                foreach($query->get() as $model) {
                    $data = [
                        $model->commodity_name,
                        $model->show == 'Y' ? 'V' : 'X',
                        $model->active == 'Y' ? 'V' : 'X',
                    ];

                    $sheet->row($currentRow++, $data);
                }

                $lastDataRow = $currentRow;
                $currentRow = $lastDataRow + 1;
                if (!empty($filters['commodity'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.commodity'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['commodity'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['category'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.category'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['category'] == 'Y' ? 'Show' : 'Hidden', 'C', $currentRow);
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

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model         = new MasterCommodity();
        $model->active = 'Y';

        return view('operational::master.master-commodity.add', [
            'title' => trans('shared/common.add'),
            'model' => $model,
            'url'   => self::URL,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterCommodity::where('commodity_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        return view('operational::master.master-commodity.add', [
            'title' => trans('shared/common.edit'),
            'model' => $model,
            'url'   => self::URL,
        ]);
    }

    public function save(Request $request)
    {
        $id    = intval($request->get('id'));
        $model = !empty($id) ? MasterCommodity::where('commodity_id', '=', $id)->first() : new MasterCommodity();

        $this->validate($request, [
            'commodity'  => 'required',
        ]);

        $model->commodity_name = $request->get('commodity');
        $model->active = !empty($request->get('status')) ? 'Y' : 'N';
        $model->show = !empty($request->get('show')) ? 'Y' : 'N';
        $now = new \DateTime();

        if (empty($id)) {
            $model->created_date = $now;
            $model->created_by = \Auth::user()->id;
        }else{
            $model->last_updated_date = $now;
            $model->last_updated_by = \Auth::user()->id;
        }

        try {
            $model->save();            
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.commodity').' '.$model->commodity_name])
        );

        return redirect(self::URL);
    }

    protected function getCategory(){
        return [
            'All'    => '', 
            'Show'   => 'Y', 
            'Hidden' => 'N', 
        ];
    }
}
