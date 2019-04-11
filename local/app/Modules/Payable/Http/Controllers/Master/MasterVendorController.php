<?php

namespace App\Modules\Payable\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Payable\Model\Master\MasterVendor;
use App\Modules\Operational\Model\Master\MasterCity;
use App\Modules\Payable\Model\Master\DetailVendorBranch;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Modules\Operational\Service\Master\CityService;
use App\Service\Penomoran;


class MasterVendorController extends Controller
{
    const RESOURCE = 'Payable\Master\MasterVendor';
    const URL = 'payable\master\master-vendor';
    const DESC = 'VENDOR';
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

        return view('payable::master.master-vendor.index', [
            'vendors'    => $query->paginate(10),
            'filters'    => $filters,
            'resource'   => self::RESOURCE,
            'optionKategori' => \DB::table('adm.v_mst_lookup_values')
                                ->where('lookup_type', 'VENDOR_CATEGORY')
                                ->get(),
            'url'        => self::URL,
            'optionKota' => CityService::getAllCity(),
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model         = new MasterVendor();
        $model->active = 'Y';

        return view('payable::master.master-vendor.add', [
            'title'             => trans('shared/common.add'),
            'model'             => $model,
            'url'               => self::URL,
            'optionSubAccount'  => $this->optionSubAccount(),
            'optionKota'        => CityService::getAllCity(),
            'optionKategori'    => \DB::table('adm.v_mst_lookup_values')->where('lookup_type', 'VENDOR_CATEGORY')->get(),
            'daftarCabang'      => \DB::table('op.mst_branch')->where('active', '=', 'Y')->orderBy('branch_name')->get(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterVendor::where('vendor_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        if (!$this->checkAccessBranch($model)) {
            abort(403);
        }

        return view('payable::master.master-vendor.add', [
            'title'            => trans('shared/common.edit'),
            'model'            => $model,
            'url'              => self::URL,
            'optionSubAccount' => $this->optionSubAccount(),
            'optionKota'       => CityService::getAllCity(),
            'optionKategori'   => \DB::table('adm.v_mst_lookup_values')->where('lookup_type', 'VENDOR_CATEGORY')->get(),
            'daftarCabang'     => \DB::table('op.mst_branch')->where('active', '=', 'Y')->orderBy('branch_name')->get(),
        ]);
    }

    public function save(Request $request)
    {
        $id    = intval($request->get('id'));
        $model = !empty($id) ? MasterVendor::where('vendor_id', '=', $id)->first() : new MasterVendor();

        $this->validate($request, [
            'nama'  => 'required|max:55',
            'kota'  => 'required|max:55',
        ]);

        if (empty($request->get('detailCabang'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Branch detail on the Activation tab can not be empty']);
        }

        $status='Y';
        if ($request->get('status')!="Y") {
            $status="N";
        }

        $model = !empty($id) ? MasterVendor::find($id) : new MasterVendor();
        $opr = empty($model->vendor_id) ? 'I' : 'U';
        
        $now = new \DateTime();
        $model->vendor_name      = $request->get('nama');
        $model->address          = $request->get('alamat');
        $model->city_id          = $request->get('kota');
        $model->phone_number     = $request->get('telp');
        $model->contact_person   = $request->get('contactPerson');
        $model->contact_phone    = $request->get('telpContactPerson');
        $model->category         = $request->get('kategori');
        $model->description      = $request->get('keterangan');
        $model->active           = $status;

         if ($opr == 'I') {
            $count   = \DB::table('gl.mst_coa')->where('segment_name','=',MasterCoa::SUB_ACCOUNT)->count();
            $kodeSub = Penomoran::getStringNomor($count+1, 5);
            $model->branch_id_insert = $request->session()->get('currentBranch')->branch_id;
            $model->subaccount_code  = $kodeSub;

            $countVendor = \DB::table('ap.mst_vendor')->where('branch_id_insert', '=', \Session::get('currentBranch')->branch_id)->count();
            $vendorCode = 'VND.'.\Session::get('currentBranch')->branch_code.'.'.Penomoran::getStringNomor($countVendor+1, 4);
            $model->vendor_code = $vendorCode;
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

        $model->vendorBranch()->delete();
        foreach ($request->get('detailCabang') as $branch) {
            $vendorBranch = new DetailVendorBranch();
            $vendorBranch->vendor_id = $model->vendor_id;
            $vendorBranch->branch_id = $branch;
            if ($opr == 'I') {
                $vendorBranch->created_date = $now;
                $vendorBranch->created_by = \Auth::user()->id;
            }else{
                $vendorBranch->last_updated_date = $now;
                $vendorBranch->last_updated_by = \Auth::user()->id;
            }
            try {
                $vendorBranch->save();
            } catch (\Exception $e) {
                return redirect(self::URL.'/edit/'.$model->vendor_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }
        }
        $modelCoa = $opr=='U' ? MasterCoa::where('segment_name', '=', MasterCoa::SUB_ACCOUNT)->where('coa_code','=',$request->get('kodeSub'))->first() : new MasterCoa();

        $modelCoa->description = $request->get('nama').' ('.self::DESC.')';
        $modelCoa->segment_name = MasterCoa::SUB_ACCOUNT;
        $now = new \DateTime();

        if ($opr == 'I') {
            $modelCoa->coa_code = $kodeSub;
            $modelCoa->created_date = $now;
            $modelCoa->created_by = \Auth::user()->id;
        }else{
            $modelCoa->last_updated_date = $now;
            $modelCoa->last_updated_by = \Auth::user()->id;
        }
        $modelCoa->active = $status;

        try {
                $modelCoa->save();
            } catch (\Exception $e) {
                return redirect(self::URL.'/edit/'.$model->vendor_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

         $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('payable/menu.vendor-supplier').' '.$model->vendor_name])
            );
            return redirect(self::URL);
    }

    protected function getQuery(Request $request, $filters){
        $query   = \DB::table('ap.v_mst_vendor')
                        ->select('v_mst_vendor.*')
                        ->leftJoin('ap.v_dt_vendor_branch', 'v_mst_vendor.vendor_id', '=', 'v_dt_vendor_branch.vendor_id')
                        ->orderBy('v_mst_vendor.created_date','desc')
                        ->distinct();

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('v_mst_vendor.active', '=', 'Y');
        } else {
            $query->where('v_mst_vendor.active', '=', 'N');
        }
        
        if (!empty($filters['kategori'])) {
            $query->where('category', 'ilike', '%'.$filters['kategori'].'%');
        }

        if (!empty($filters['nama'])) {
            $query->where('vendor_name', 'ilike', '%'.$filters['nama'].'%');
        }

        if (!empty($filters['kota'])) {
            $query->where('city_id', '=', $filters['kota']);
        }

        $query->where('v_dt_vendor_branch.branch_id', '=', $request->session()->get('currentBranch')->branch_id)
                ->where('v_dt_vendor_branch.active', '=', 'Y');

    return $query;                
    }

    public function printExcelIndex(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters)->get();

        \Excel::create(trans('payable/menu.vendor-supplier'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('payable/menu.vendor-supplier'));
                });

                $sheet->cells('A3:L3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('shared/common.kode'),
                    trans('shared/common.nama'),
                    trans('shared/common.address'),
                    trans('shared/common.city'),
                    trans('shared/common.telepon'),
                    trans('shared/common.description'),
                    trans('shared/common.cp'),
                    trans('shared/common.contact-cp'),
                    trans('shared/common.category'),
                    trans('shared/common.kode-sub'),
                    trans('shared/common.active'),
                ]);
                foreach($query as $index => $model) {
                    $date   = !empty($model->date) ? new \DateTime($model->date) : null;
                    $active = $model->active == 'Y' ? 'Y' : 'N'; 
                    $data = [
                        $index + 1,
                        $model->vendor_code,
                        $model->vendor_name,
                        $model->address,
                        $model->city_name,
                        $model->phone_number,
                        $model->description,
                        $model->contact_person,
                        $model->contact_phone,
                        $model->meaning,
                        $model->subaccount_code,
                        $active,
                    ];
                    $sheet->row($index + 4, $data);
                }

                $currentRow = count($query) + 5;
                if (!empty($filters['nama'])) {
                    $this->addLabelDescriptionCell($sheet, trans('payable/fields.vendor-name'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['nama'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['kategori'])) {
                    $modelKategori = \DB::table('adm.mst_lookup_values')->where('lookup_code', '=', $filters['kategori'])->first();
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.kategori'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $modelKategori->meaning, 'C', $currentRow);
                    $currentRow++;
                }

                if (!empty($filters['kota'])) {
                    $modelKota = MasterCity::find($filters['kota']);
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.kota'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $modelKota->city_name, 'C', $currentRow);
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

    function optionSubAccount(){
        return \DB::table('gl.mst_coa')
                ->where('segment_name','=','Sub Account')
                ->where('active', '=', 'Y')
                ->orderBy('coa_code')->get();
    }

    protected function checkAccessBranch(MasterVendor $model)
    {
        $canAccessBranch = false;
        foreach ($model->vendorBranch as $vendorBranch) {
            $branch = $vendorBranch->branch;
            if ($branch !== null && \Auth::user()->can('accessBranch', $branch->branch_id)) {
                $canAccessBranch = true;
            }
        }

        return $canAccessBranch;
    }
}
