<?php

namespace App\Modules\Operational\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Master\MasterCity;

class MasterCityController extends Controller
{
    const RESOURCE = 'Operational\Master\MasterCity';
    const URL      = 'operational/master/master-city';
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
        
        return view('operational::master.master-city.index', [
            'models'     => $query->paginate(10),
            'filters'    => $filters,
            'resource'   => self::RESOURCE,
            'url'        => self::URL,
            'optionProvince' => \DB::table('op.mst_province')->orderBy('province_name')->get(),
            ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model         = new MasterCity();
        $model->active = 'Y';

        return view('operational::master.master-city.add', [
            'title'      => trans('shared/common.add'),
            'model'      => $model,
            'url'        => self::URL,
            'optionProvince' => \DB::table('op.mst_province')->orderBy('province_name')->get(),
            ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterCity::where('city_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        return view('operational::master.master-city.add', [
            'title'      => trans('shared/common.edit'),
            'model'      => $model,
            'url'        => self::URL,
            'optionProvince' => \DB::table('op.mst_province')->orderBy('province_name')->get(),
            ]);
    }

    public function save(Request $request)
    {
        $id    = intval($request->get('id'));
        $model = !empty($id) ? MasterCity::where('city_id', '=', $id)->first() : new MasterCity();

        $this->validate($request, [
            'code'  => 'required|max:4|unique:operational.mst_city,city_code,'.$id.',city_id',
            'cityName'  => 'required|max:40',
            ]);

        $status='Y';
        if ($request->get('status')!="Y") {
            $status="N";
        }

        $opr = empty($id) ? 'I' : 'U';

        $model->city_name = $request->get('cityName');
        $model->province  = $request->get('province');
        $model->city_code = $request->get('code');
        $model->active    = $status;
        $now = new \DateTime();

        if ($opr == 'I') {
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
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.city').' '.$model->city_name])
            );

        return redirect(self::URL);
    }

    protected function getQuery(Request $request, $filters){
        $query   = \DB::table('op.v_mst_city');

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('v_mst_city.active', '=', 'Y');
        } else {
            $query->where('v_mst_city.active', '=', 'N');
        }
        if (!empty($filters['code'])) {
            $query->where('city_code', 'ilike', '%'.$filters['code'].'%');
        }

        if (!empty($filters['city'])) {
            $query->where('city_name', 'ilike', '%'.$filters['city'].'%');
        }

        if (!empty($filters['province'])) {
            $query->where('province', 'ilike', '%'.$filters['province'].'%');
        }

        return $query;
    }

    public function printExcelIndex(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters)->get();

        \Excel::create(trans('operational/menu.master-city'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.master-city'));
                });

                $sheet->cells('A3:E3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('shared/common.code'),
                    trans('shared/common.city'),
                    trans('operational/fields.province'),
                    trans('shared/common.active'),
                ]);
                foreach($query as $index => $model) {
                    $data = [
                        $index + 1,
                        $model->city_code,
                        $model->city_name,
                        $model->province,
                        $model->active == 'Y' ? 'v' : 'x',
                    ];
                    $sheet->row($index + 4, $data);
                }

                $currentRow = count($query) + 5;
                if (!empty($filters['code'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.code'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['code'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['city'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.city'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['city'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['province'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.province'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['province'], 'C', $currentRow);
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
