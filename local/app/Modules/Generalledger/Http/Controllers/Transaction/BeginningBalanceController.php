<?php

namespace App\Modules\Generalledger\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Generalledger\Model\Transaction\BeginningBalance;

class BeginningBalanceController extends Controller
{
    const RESOURCE = 'Generalledger\Transaction\BeginningBalance';
    const URL      = 'general-ledger/transaction/beginning-balance';

    public function __construct()
    {
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

        return view('generalledger::transaction.beginning-balance.index', [
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

        $model = new BeginningBalance();
        $model->active = 'Y';

        return view('generalledger::transaction.beginning-balance.add', [
            'title'      => trans('shared/common.add'),
            'model'      => $model,
            'optionBank' => $this->getBank(),
            'url'        => self::URL,
            ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = BeginningBalance::where('beginning_balance_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        return view('generalledger::transaction.beginning-balance.add', [
            'title'         => trans('shared/common.edit'),
            'optionBank'    => $this->getBank(),
            'model'         => $model,
            'url'           => self::URL,
            ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? BeginningBalance::where('beginning_balance_id', '=', $id)->first() : new BeginningBalance();

        $this->validate($request, [
            'bankId'            => 'required',
            'beginningBalance'  => 'required',
            ]);

        $model->bank_id           = $request->get('bankId');
        $model->beginning_balance = str_replace(',', '', $request->get('beginningBalance'));
        $now = new \DateTime();

        $balanceDate         = !empty($request->get('balanceDate')) ? new \DateTime($request->get('balanceDate')) : null;
        $model->balance_date = !empty($balanceDate) ? $balanceDate->format('Y-m-d H:i:s') : null;
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
            trans('shared/common.saved-message', ['variable' => trans('general-ledger/menu.beginning-balance').' '.$model->coa_code])
            );

        return redirect(self::URL);
    }

    protected function getQuery(Request $request, $filters){
        $query = \DB::table('gl.trans_beginning_balance')
                    ->select('trans_beginning_balance.*', 'mst_bank.bank_name', 'mst_bank.account_name')
                    ->join('gl.mst_bank', 'mst_bank.bank_id', '=', 'trans_beginning_balance.bank_id')
                    ->join('gl.dt_bank_branch', 'dt_bank_branch.bank_id', '=', 'mst_bank.bank_id')
                    ->where('dt_bank_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('trans_beginning_balance.balance_date','desc');

        if (!empty($filters['bankName'])) {
            $query->where('mst_bank.bank_name', '=', '%'.$filters['bankName'].'%');
        }

        if (!empty($filters['accountName'])) {
            $query->where('mst_bank.account_name', '=', '%'.$filters['accountName'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $query->where('trans_beginning_balance.created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $query->where('trans_beginning_balance.created_date', '<=', $date->format('Y-m-d 23:59:59'));
        }
        return $query;
    }

    protected function getBank(){
        return \DB::table('gl.mst_bank')
                    ->select('mst_bank.*')
                    ->join('gl.dt_bank_branch', 'dt_bank_branch.bank_id', '=', 'mst_bank.bank_id')
                    ->where('dt_bank_branch.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->where('mst_bank.active', '=', 'Y')
                    ->orderBy('bank_name', 'asc')
                    ->get();
    }

    public function printPdf(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters);

        $header = view('print.header-pdf', ['title' => trans('general-ledger/menu.beginning-balance')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('generalledger::transaction.beginning-balance.print-pdf', [
            'models'  => $query->get(),
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('general-ledger/menu.beginning-balance'));
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('P', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output(trans('general-ledger/menu.beginning-balance').'.pdf');
        \PDF::reset();
    }
}
