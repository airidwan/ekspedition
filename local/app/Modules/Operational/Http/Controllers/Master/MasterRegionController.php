<?php

namespace App\Modules\Operational\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Master\MasterRegion;
use App\Modules\Operational\Model\Master\DetailRegionCity;
use App\Modules\Operational\Model\Master\MasterOrganization;

class MasterRegionController extends Controller
{
    const RESOURCE = 'Operational\Master\MasterRegion';
    const URL      = 'operational/master/master-region';

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

        return view('operational::master.master-region.index', [
            'models'     => $query->paginate(10),
            'filters'    => $filters,
            'resource'   => self::RESOURCE,
            'url'        => self::URL,
            'optionKota' => \DB::table('op.mst_city')->get()
        ]);
    }

    protected function getQuery(Request $request)
    {
        $filters = $request->session()->get('filters');
        $query   = \DB::table('op.mst_region')->orderBy('region_code', 'asc');

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('active', '=', 'Y');
        } else {
            $query->where('active', '=', 'N');
        }

        if (!empty($filters['kode'])) {
            $query->where('region_code', 'ilike', '%'.$filters['kode'].'%');
        }

        if (!empty($filters['nama'])) {
            $query->where('region_name', 'ilike', '%'.$filters['nama'].'%');
        }

        return $query;
    }

    public function printExcel(Request $request)
    {
        $query   = $this->getQuery($request);
        $filters = \Session::get('filters');

        \Excel::create(trans('operational/menu.region'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.region'));
                });

                $sheet->cells('A3:C3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.kode'),
                    trans('shared/common.nama'),
                    trans('operational/fields.detail-kota'),
                ]);

                $currentRow = 4;
                foreach($query->get() as $model) {
                    $region = \App\Modules\Operational\Model\Master\MasterRegion::find($model->region_id);
                    $cityNames = [];
                    foreach ($region->regionCity()->get() as $regionCity) {
                        $city = $regionCity->city()->first();
                        if ($city !== null) {
                            $cityNames[] = $city->city_name;
                        }
                    }

                    $data = [
                        $model->region_code,
                        $model->region_name,
                        implode(', ', $cityNames),
                    ];

                    $sheet->row($currentRow++, $data);
                }

                $lastDataRow = $currentRow;
                $currentRow = $lastDataRow + 1;
                if (!empty($filters['kode'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.kode'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['kode'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['nama'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.nama'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['nama'], 'C', $currentRow);
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

        $model         = new MasterRegion();
        $model->active = 'Y';

        return view('operational::master.master-region.add', [
            'title'      => trans('shared/common.add'),
            'model'      => $model,
            'url'        => self::URL,
            'optionKota' => $this->getCityAdd(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }
        $idGlobal=$id;

        $model = MasterRegion::find($id);
        if ($model === null) {
            abort(404);
        }

        return view('operational::master.master-region.add', [
            'title'      => trans('shared/common.edit'),
            'model'      => $model,
            'url'        => self::URL,
            'optionKota' => $this->getCityEdit($id),
        ]);
    }

    public function save(Request $request)
    {
        $id    = intval($request->get('id'));
        $model = !empty($id) ? MasterRegion::find($id) : new MasterRegion();

        $this->validate($request, [
            'kode' => 'required|max:25|unique:operational.mst_region,region_code,'.$id.',region_id',
            'nama' => 'required|max:55',
        ]);

        if (empty($request->get('detailKota'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(
                ['errorMessage' => 'City detail can not be empty']
            );
        }

        $org = MasterOrganization::first() !== null ? MasterOrganization::first()->no_organisasi : 0;

        $model->region_code = $request->get('kode', '');
        $model->region_name = $request->get('nama', '');
        $model->org_id = $org;
        $model->active = !empty($request->get('status')) ? 'Y' : 'N';

        $now = new \DateTime();
        if (empty($id)) {
            $model->created_date = $now;
            $model->created_by = \Auth::user()->id;
        } else {
            $model->last_updated_date = $now;
            $model->last_updated_by = \Auth::user()->id;
        }

        $model->save();
        $model->regionCity()->delete();

        foreach ($request->get('detailKota') as $city) {
            $check = $this->cityUnique($city, $model->region_id);
            if (!empty($check)) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(
                    ['errorMessage' => $check.' city is alraedy exists in another region']
                );
            }
            $detailCity = new DetailRegionCity();
            $detailCity->region_id = $model->region_id;
            $detailCity->city_id = $city;

            if (empty($id)) {
                $detailCity->created_date = $now;
                $detailCity->created_by = \Auth::user()->id;
            } else {
                $detailCity->last_updated_date = $now;
                $detailCity->last_updated_by = \Auth::user()->id;
            }

            $detailCity->save();
        }

        $request->session()->flash('successMessage', 'Master Wilayah ' . $model->nama_wilayah . ' berhasil disimpan');
        return redirect(self::URL);
    }

    function cityUnique($city , $id){
        $query = \DB::table('op.dt_region_city')->select('region_id', 'city_id')->get();
        foreach ($query as $data) {
            if (($data->city_id == $city) && ($data->region_id != $id) ) {
                $cityName = \DB::table('op.mst_city')->select('city_name')->where('city_id', '=', $city)->first();
                return $cityName->city_name;            
            }
        }
        return;
    }

    public static function getCityAdd()
    {
        return \DB::table('op.v_mst_city')
                    ->select('v_mst_city.city_id','v_mst_city.city_name','v_mst_city.city_code')
                    ->leftJoin('op.dt_region_city', 'dt_region_city.city_id' , '=', 'v_mst_city.city_id')
                    ->where('dt_region_city.city_id', '=', null)
                    ->where('active', '=', 'Y')
                    ->orderBy('city_name')->get();

    }

    public function getCityEdit($id)
    {
        return  \DB::table('op.v_mst_city')
                    ->select('v_mst_city.city_id','v_mst_city.city_name','v_mst_city.city_code')
                    ->leftJoin('op.dt_region_city', 'dt_region_city.city_id' , '=', 'v_mst_city.city_id')
                    ->where(function ($query) use ($id) {
                      $query->where('dt_region_city.city_id', '=', null)
                            ->orWhere('dt_region_city.region_id','=',$id);
                    })
                    ->where('active', '=', 'Y')
                    ->orderBy('city_name')->get();
    }
}
