<?php

namespace App\Modules\Generalledger\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Generalledger\Model\Master\MasterBank;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Modules\Generalledger\Model\Master\DetailBankBranch;

class MasterBankController extends Controller
{
    const RESOURCE = 'Generalledger\Master\MasterBank';
    const URL      = 'general-ledger/master/master-bank';
    protected $now;

    public function __construct()
    {
        $this->now = new \DateTime();
        $this->middleware('auth');
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

        return view('generalledger::master.master-bank.index', [
            'models' => $query->paginate(10),
            'filters' => $filters,
            'resource' => self::RESOURCE,
            'url' => self::URL,
        ]);
    }

    public function getQuery(Request $request, $filters){
        $query = \DB::table('gl.v_mst_bank')->orderBy('created_date', 'desc');

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('active', '=', 'Y');
        } else {
            $query->where('active', '=', 'N');
        }

        if (!empty($filters['bankName'])) {
            $query->where('bank_name', 'ilike', '%'.$filters['bankName'].'%');
        }

        if (!empty($filters['accountName'])) {
            $query->where('account_name', 'ilike', '%'.$filters['accountName'].'%');        
        }

        if (!empty($filters['accountNumber'])) {
            $query->where('account_number', 'ilike', '%'.$filters['accountNumber'].'%');        
        }

        if (!empty($filters['npwp'])) {
            $query->where('npwp', 'ilike', '%'.$filters['npwp'].'%');        
        }

        if (!empty($filters['coaDescription'])) {
            $query->where('coa_bank_description', 'ilike', '%'.$filters['coaDescription'].'%');        
        }

        if (!empty($filters['coaCode'])) {
            $query->where('coa_bank_code', 'ilike', '%'.$filters['coaCode'].'%');        
        }
        return $query;
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new MasterBank();
        $model->active = 'Y';

        return view('generalledger::master.master-bank.add', [
            'title'                => trans('shared/common.add'),
            'model'                => $model,
            'optionCoa'            => $this->optionCoa(),
            'optionsType'          => [MasterBank::CASH_IN, MasterBank::CASH_OUT, MasterBank::BANK],
            'optionsBranch'        => $this->getOptionsBranch(),
            'url'                  => self::URL,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterBank::where('bank_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        return view('generalledger::master.master-bank.add', [
            'title'                => trans('shared/common.edit'),
            'model'                => $model,
            'optionCoa'            => $this->optionCoa(),
            'optionsType'          => [MasterBank::CASH_IN, MasterBank::CASH_OUT, MasterBank::BANK],
            'optionsBranch'        => $this->getOptionsBranch(),
            'url'                  => self::URL,
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? MasterBank::where('bank_id', '=', $id)->first() : new MasterBank();
        
        $this->validate($request, [
            'coaBankId'     => 'required',
            'bankName'      => 'required|max:50',
            'accountName'   => 'required|max:150',
            'accountNumber' => 'required|max:150',
            'npwp'          => 'required|max:150',
            'address'       => 'required|max:250',
            'type'          => 'required',
        ], [
            'branchId.required_if' => 'This field is required',
        ]);

        if (empty($request->get('branchDetail'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Branch detail on the Activation tab can not be empty']);
        }

        if ($request->get('type') != MasterBank::BANK && count($request->get('branchDetail')) > 1) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'You can choose only one branch']);
        }

        $model->bank_name       = $request->get('bankName');
        $model->account_name    = $request->get('accountName');
        $model->account_number  = $request->get('accountNumber');
        $model->npwp            = $request->get('npwp');
        $model->coa_bank_id     = $request->get('coaBankId');
        $model->bank_address    = $request->get('address');
        $model->description     = $request->get('description');
        $model->active          = !empty($request->get('status')) ? 'Y' : 'N';
        $model->type            = $request->get('type');

        $now = new \DateTime();
        if (empty($id)) {
            $model->branch_id_insert = $request->session()->get('currentBranch')->branch_id ;
            $model->created_date = $now;
            $model->created_by   = \Auth::user()->id;
        } else {
            $model->last_updated_date = $now;
            $model->last_updated_by   = \Auth::user()->id;
        }

        try {
            $model->save();            
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $model->bankBranch()->delete();
        foreach ($request->get('branchDetail') as $branch) {
            $bankBranch = new DetailBankBranch();
            $bankBranch->bank_id = $model->bank_id;
            $bankBranch->branch_id = $branch;
            $bankBranch->active = 'Y';
            if (empty($id)) {
                $bankBranch->created_date = $now;
                $bankBranch->created_by = \Auth::user()->id;
            }else{
                $bankBranch->last_updated_date = $now;
                $bankBranch->last_updated_by = \Auth::user()->id;
            }

            try {
                $bankBranch->save();
            } catch (\Exception $e) {
                return redirect(self::URL.'/edit/'.$model->bank_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('general-ledger/menu.master-bank').' '.$model->coa_code])
            );

        return redirect(self::URL);
    }

    public function printExcel(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters)->get();

        \Excel::create(trans('general-ledger/menu.master-bank'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('general-ledger/menu.master-bank'));
                });

                $sheet->cells('A3:J3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('shared/common.type'),
                    trans('general-ledger/fields.account-name'),
                    trans('general-ledger/fields.account-number'),
                    trans('general-ledger/fields.npwp'),
                    trans('general-ledger/fields.coa-bank'),
                    trans('general-ledger/fields.coa-description'),
                    trans('shared/common.address'),
                    trans('shared/common.description'),
                    trans('shared/common.active'),
                ]);
                foreach($query as $index => $model) {
                    $data = [
                        $index + 1,
                        $model->type,
                        $model->account_name,
                        $model->account_number,
                        $model->npwp,
                        $model->coa_bank_code,
                        $model->coa_bank_description,
                        $model->bank_address,
                        $model->description,
                        $model->active == 'Y' ? 'v' : 'x',
                    ];
                    $sheet->row($index + 4, $data);
                }

                $currentRow = count($query) + 5;
                if (!empty($filters['bankName'])) {
                    $this->addLabelDescriptionCell($sheet, trans('general-ledger/fields.bank-name'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['bankName'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['accountName'])) {
                    $this->addLabelDescriptionCell($sheet, trans('general-ledger/fields.account-name'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['accountName'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['accountNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('general-ledger/fields.account-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['accountNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['npwp'])) {
                    $this->addLabelDescriptionCell($sheet, trans('general-ledger/fields.npwp'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['npwp'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['coaCode'])) {
                    $this->addLabelDescriptionCell($sheet, trans('general-ledger/fields.coa-code'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['coaCode'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['coaDescription'])) {
                    $this->addLabelDescriptionCell($sheet, trans('general-ledger/fields.coa-description'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['coaDescription'], 'C', $currentRow);
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

    protected function optionCoa(){
        return \DB::table('gl.mst_coa')
                    ->where('active', '=', 'Y')
                    ->where('segment_name', '=', MasterCoa::ACCOUNT)
                    ->orderBy('coa_code')->get();
    }

    protected function getOptionsBranch()
    {
        return \DB::table('op.mst_branch')->where('active', '=', 'Y')->orderBy('branch_name', 'asc')->get();
    }
}
