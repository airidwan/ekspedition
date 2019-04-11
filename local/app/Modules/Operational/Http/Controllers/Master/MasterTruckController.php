<?php

namespace App\Modules\Operational\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\MasterLookupValues;
use App\Modules\Operational\Model\Master\MasterTruck;
use App\Modules\Operational\Model\Master\DetailTruckBranch;
use App\Modules\Operational\Model\Master\DetailTruckRent;
use App\Modules\Asset\Service\Transaction\AssetService;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Service\Penomoran;


class MasterTruckController extends Controller
{
    const RESOURCE = 'Operational\Master\MasterTruck';
    const URL = 'operational/master/master-truck';
    const DESC = 'TRUCK';

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

        return view('operational::master.master-truck.index', [
            'models'         => $query->paginate(10),
            'filters'        => $filters,
            'optionCategory' => \DB::table('adm.v_mst_lookup_values')
                                ->where('lookup_type', '=', MasterLookupValues::KATEGORI_KENDARAAN)
                                ->get(),
            'resource'       => self::RESOURCE,
            'url'            => self::URL
        ]);
    }

    protected function getQuery(Request $request)
    {
        $filters = $request->session()->get('filters');
        $query   = \DB::table('op.v_mst_truck')
                        ->select('v_mst_truck.*')
                        ->join('op.dt_truck_branch', 'v_mst_truck.truck_id', '=', 'dt_truck_branch.truck_id')
                        ->where('dt_truck_branch.branch_id','=', \Session::get('currentBranch')->branch_id)
                        ->orderBy('v_mst_truck.truck_code')
                        ->distinct();

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('v_mst_truck.active', '=', 'Y');
        } else {
            $query->where('v_mst_truck.active', '=', 'N');
        }

        if (!empty($filters['code'])) {
            $query->where('truck_code', 'ilike', '%'.$filters['code'].'%');
        }

        if (!empty($filters['ownerName'])) {
            $query->where('owner_name', 'ilike', '%'.$filters['ownerName'].'%');
        }

        if (!empty($filters['category'])) {
            $query->where('category', 'ilike', '%'.$filters['category'].'%');
        }

        return $query;
    }

    public function printExcel(Request $request)
    {
        $query   = $this->getQuery($request);
        $filters = \Session::get('filters');

        \Excel::create(trans('operational/menu.truck'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.truck'));
                });

                $sheet->cells('A3:Q3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.code'),
                    trans('operational/fields.nama-pemilik'),
                    trans('operational/fields.price-per-unit'),
                    trans('operational/fields.police-number'),
                    trans('operational/fields.brand'),
                    trans('operational/fields.type'),
                    trans('shared/common.category'),
                    trans('operational/fields.nomor-rangka'),
                    trans('operational/fields.nomor-mesin'),
                    trans('operational/fields.tahun-buat'),
                    trans('operational/fields.dimensi-bak-plt'),
                    trans('operational/fields.tanggal-stnk'),
                    trans('operational/fields.tanggal-kir'),
                    trans('operational/fields.berat-max'),
                    trans('operational/fields.ground-clearance'),
                    trans('shared/common.keterangan'),
                    trans('operational/fields.sub-account'),
                ]);

                $currentRow = 4;
                foreach($query->get() as $model) {
                    $stnkDate = !empty($model->stnk_date) ? new \DateTime($model->stnk_date) : null;
                    $kirDate = !empty($model->kir_date) ? new \DateTime($model->kir_date) : null;
                    $data = [
                        $model->truck_code,
                        $model->owner_name,
                        number_format($model->truck_price) ,
                        $model->police_number,
                        $model->vehicle_merk,
                        $model->vehicle_type,
                        $model->vehicle_category,
                        $model->chassis_number,
                        $model->machine_number,
                        $model->production_year,
                        number_format($model->long_tube, 2).' x '.number_format($model->width_tube, 2).' x '.number_format($model->height_tube, 2).' m',
                        !empty($stnkDate) ? $stnkDate->format('d-M-Y') : '',
                        !empty($kirDate) ? $kirDate->format('d-M-Y') : '',
                        number_format($model->weight_max),
                        number_format($model->ground_clearance, 2),
                        $model->description,
                        $model->subaccount_code,
                    ];

                    $sheet->row($currentRow++, $data);
                }

                $lastDataRow = $currentRow;
                $currentRow = $lastDataRow + 1;
                if (!empty($filters['code'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.code'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['code'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['ownerName'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.nama-pemilik'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['ownerName'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['category'])) {
                    $category = MasterLookupValues::where('lookup_type', MasterLookupValues::KATEGORI_KENDARAAN)->where('lookup_code', $filters['category'])->first();
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.category'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $category->meaning, 'C', $currentRow);
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

        $model         = new MasterTruck();
        $modelRent     = new DetailTruckRent();
        $model->active = 'Y';

        return view('operational::master.master-truck.add', [
            'title'          => trans('shared/common.add'),
            'model'          => $model,
            'modelRent'      => $modelRent,
            'url'            => self::URL,
            'optionCity'     => \DB::table('op.v_mst_city')->get(),
            'optionBrand'    => \DB::table('adm.v_mst_lookup_values')->where('lookup_type', MasterLookupValues::MERK_KENDARAAN)->get(),
            'optionType'     => \DB::table('adm.v_mst_lookup_values')->where('lookup_type', MasterLookupValues::TIPE_KENDARAAN)->get(),
            'optionCategory' => \DB::table('adm.v_mst_lookup_values')->where('lookup_type', MasterLookupValues::KATEGORI_KENDARAAN)->get(),
            'optionBranch'   => \DB::table('op.mst_branch')->where('active', '=','Y')->orderBy('branch_name')->get(),
            'optionYears'    => $this->getOptionYears(),
            'optionAsset'    => AssetService::getExistAssetKendaraan(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterTruck::where('truck_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        if (!$this->checkAccessBranch($model)) {
            abort(403);
        }

        $modelRent = DetailTruckRent::where('truck_id', '=', $id)->first();
        if ($modelRent === null) {
            $modelRent = new DetailTruckRent();
        }

        return view('operational::master.master-truck.add', [
            'title'          => trans('shared/common.edit'),
            'model'          => $model,
            'modelRent'      => $modelRent,
            'url'            => self::URL,
            'optionCity'     => \DB::table('op.v_mst_city')->get(),
            'optionBrand'    => \DB::table('adm.v_mst_lookup_values')->where('lookup_type', MasterLookupValues::MERK_KENDARAAN)->get(),
            'optionType'     => \DB::table('adm.v_mst_lookup_values')->where('lookup_type', MasterLookupValues::TIPE_KENDARAAN)->get(),
            'optionCategory' => \DB::table('adm.v_mst_lookup_values')->where('lookup_type', MasterLookupValues::KATEGORI_KENDARAAN)->get(),
            'optionBranch'   => \DB::table('op.mst_branch')->where('active', '=','Y')->orderBy('branch_name')->get(),
            'optionYears'    => $this->getOptionYears(),
            'optionAsset'    => AssetService::getExistAssetKendaraan(),
        ]);
    }

    public function save(Request $request)
    {
        $id    = intval($request->get('id'));
        $model = !empty($id) ? MasterTruck::where('truck_id', '=', $id)->first() : new MasterTruck();

        $this->validate($request, [
            'category'     => 'required',
            'brand'        => 'required',
            'type'         => 'required',
            'ownerName'    => 'required|max:55',
            'price'        => 'required|max:55',
            'policeNumber' => 'required|max:55',
        ]);

        if ($request->get('category') == "ASSET") {
            $this->validate($request, [
                'assetNumber'  => 'required',
            ]);
        }

        $modelRent=null;
        if ($request->get('category') == "SEWA_BULANAN") {
            $this->validate($request, [
                'contractLength'  => 'required|max:4',
                'contractNumber'  => 'required',
                'dueDate'         => 'required|max:55',
                'rateMonth'       => 'required|max:55',
                'discountMonth'    => 'required|max:55',
            ]);
        }

        if (empty($request->get('branchDetail'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Branch detail on the Activation tab can not be empty']);
        }

        $status='Y';
        if ($request->get('status')!="Y") {
            $status="N";
        }
        $stnkDate    = !empty($request->get('stnkDate')) ? new \DateTime($request->get('stnkDate')) : null;
        $kirDate     = !empty($request->get('kirDate')) ? new \DateTime($request->get('kirDate')) : null;
        $tglStnk     = !empty($stnkDate) ? $stnkDate->format('Y-m-d H:i:s'):null;
        $tglKir      = !empty($kirDate) ? $kirDate->format('Y-m-d H:i:s') :null;

        $opr = empty($model->truck_id) ? 'I' : 'U';

        $modelTruck = !empty($id) ? MasterTruck::find($id) : new MasterTruck();
        if ($request->get('category') == "ASSET" && !empty($request->get('assetId'))) {
            $modelTruck->asset_id     = $request->get('assetId');
        }
        $modelTruck->owner_name       = $request->get('ownerName');
        $modelTruck->truck_price      = str_replace(',', '',$request->get('price'));
        $modelTruck->police_number    = $request->get('policeNumber');
        $modelTruck->brand            = $request->get('brand');
        $modelTruck->type             = $request->get('type');
        $modelTruck->production_year  = intval($request->get('productionYear'));
        $modelTruck->chassis_number   = $request->get('chassisNumber');
        $modelTruck->machine_number   = $request->get('machineNumber');
        $modelTruck->long_tube        = floatval(str_replace(',', '',$request->get('longTube')));
        $modelTruck->width_tube       = floatval(str_replace(',', '',$request->get('widthTube')));
        $modelTruck->height_tube      = floatval(str_replace(',', '',$request->get('heightTube')));
        $modelTruck->stnk_date        = $tglStnk;
        $modelTruck->kir_date         = $tglKir;
        $modelTruck->weight_max       = intval(str_replace(',', '',$request->get('maxWeight')));
        $modelTruck->ground_clearance = floatval(str_replace(',', '',$request->get('groundClearance')));
        $modelTruck->category         = $request->get('category');
        $modelTruck->description      = $request->get('description');
        $modelTruck->pic              = $request->get('pic');
        $modelTruck->active           = $status;
        $now                          = new \DateTime();

         if ($opr == 'I') {
            $count                  = \DB::table('op.mst_truck')->where('branch_id', '=', \Session::get('currentBranch')->branch_id)->count();
            $truckCode              = 'VC.'.\Session::get('currentBranch')->branch_code.'.'.Penomoran::getStringNomor($count+1, 4);
            $modelTruck->truck_code = $truckCode;
            $modelTruck->branch_id  = \Session::get('currentBranch')->branch_id;

            $count = \DB::table('gl.mst_coa')->where('segment_name','=',MasterCoa::SUB_ACCOUNT)->count();
            $subAccountCode = Penomoran::getStringNomor($count+1, 5);
            $modelTruck->subaccount_code = $subAccountCode;

            $modelTruck->created_date  = $now;
            $modelTruck->created_by    = \Auth::user()->id;
        }else{
            $modelTruck->last_updated_date = $now;
            $modelTruck->last_updated_by   = \Auth::user()->id;
        }

        try {
            $modelTruck->save();            
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }


        $modelTruck->truckBranch()->delete();
        foreach ($request->get('branchDetail') as $branch) {
            $truckBranch = new DetailTruckBranch();
            $truckBranch->truck_id = $modelTruck->truck_id;
            $truckBranch->branch_id = $branch;
            $truckBranch->active = 'Y';
            if ($opr == 'I') {
                $truckBranch->created_date = $now;
                $truckBranch->created_by = \Auth::user()->id;
            }else{
                $truckBranch->last_updated_date = $now;
                $truckBranch->last_updated_by = \Auth::user()->id;
            }
            try {
                $truckBranch->save();
            } catch (\Exception $e) {
                return redirect(self::URL.'/edit/'.$modelTruck->truck_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }
        }

        if ($request->get('category') == "SEWA_BULANAN") {
            $modelRent = !empty($id) ? DetailTruckRent::where('truck_id', '=', $id)->first() : new DetailTruckRent();
            $oprRent   = empty($modelRent->truck_id) ? 'I' : 'U';

            $dueDate    = !empty($request->get('dueDate')) ? new \DateTime($request->get('dueDate')) : null;

            $modelRent->truck_id        = $modelTruck->truck_id;
            $modelRent->contract_number = $request->get('contractNumber');
            $modelRent->contract_length = floatval(str_replace(',', '',$request->get('contractLength')));
            $modelRent->due_date        = $dueDate->format('Y-m-d H:i:s');
            $modelRent->rate_per_month  = floatval(str_replace(',', '',$request->get('rateMonth')));
            $modelRent->rate_discount_per_month  = floatval(str_replace(',', '',$request->get('discountMonth')));

            if ($oprRent == 'I') {
                $modelRent->created_date = $now;
                $modelRent->created_by = \Auth::user()->id;
            }else{
                $modelRent->last_updated_date = $now;
                $modelRent->last_updated_by = \Auth::user()->id;
            }
            try {
                $modelRent->save();
            } catch (\Exception $e) {
                return redirect(self::URL.'/edit/'.$modelTruck->truck_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

        }

        $modelCoa = !empty($id) ? MasterCoa::where('segment_name', '=', MasterCoa::SUB_ACCOUNT)->where('coa_code','=',$request->get('subAccountCode'))->first() : new MasterCoa();

        $modelCoa->active = $status;
        $modelCoa->description = $request->get('policeNumber').' ('.self::DESC.')';
        $modelCoa->segment_name = MasterCoa::SUB_ACCOUNT;

        if ($opr == 'I') {
            $count = \DB::table('gl.mst_coa')->where('segment_name','=',MasterCoa::SUB_ACCOUNT)->count();
            $modelCoa->coa_code     = $subAccountCode;
            $modelCoa->created_date = $now;
            $modelCoa->created_by   = \Auth::user()->id;
        }else{
            $modelCoa->last_updated_date = $now;
            $modelCoa->last_updated_by   = \Auth::user()->id;
        }

        try {
                $modelCoa->save();
            } catch (\Exception $e) {
                return redirect(self::URL.'/edit/'.$modelTruck->truck_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.truck').' '.$modelTruck->truck_code])
        );

        return redirect(self::URL);
    }

    function optionSubAccount(){
        return \DB::table('gl.mst_coa')
                ->where('segment_name','=','Sub Account')
                ->where('active', '=', 'Y')
                ->orderBy('coa_code')->get();
    }

    protected function getOptionYears()
    {
        $now = new \DateTime();
        $options = [];
        for($i = $now->format('Y'); $i >= $now->format('Y') - 50; $i--) {
            $options[] = $i;
        }

        return $options;
    }

    protected function checkAccessBranch(MasterTruck $model)
    {
        $canAccessBranch = false;
        foreach ($model->truckBranch as $truckBranch) {
            $branch = $truckBranch->branch;
            if ($branch !== null && \Auth::user()->can('accessBranch', $branch->branch_id)) {
                $canAccessBranch = true;
            }
        }

        return $canAccessBranch;
    }
}
