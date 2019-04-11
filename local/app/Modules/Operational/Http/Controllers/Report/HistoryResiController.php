<?php

namespace App\Modules\Operational\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Transaction\HistoryTransaction;

class HistoryResiController extends Controller
{
    const RESOURCE = 'Operational\Report\HistoryResi';
    const URL      = 'operational/report/history-resi';

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
        $query   = \DB::table('op.history_transaction')
                        ->select('history_transaction.*', 'users.full_name as user_full_name', 'roles.name as role_name', 'mst_branch.branch_name')
                        ->join('op.trans_resi_header', 'history_transaction.transaction_id', '=', 'trans_resi_header.resi_header_id')
                        ->leftJoin('adm.users', 'history_transaction.user_id', '=', 'users.id')
                        ->leftJoin('adm.roles', 'history_transaction.role_id', '=', 'roles.id')
                        ->leftJoin('op.mst_branch', 'history_transaction.branch_id', '=', 'mst_branch.branch_id')
                        ->where('history_transaction.type', '=', HistoryTransaction::RESI)
                        // ->where('trans_resi_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->orderBy('transaction_date', 'desc');

        if (!empty($filters['resiNumber'])) {
            $query->where('history_transaction.transaction_number', 'ilike', '%'.$filters['resiNumber'].'%');
        }

        if (!empty($filters['transactionName'])) {
            $query->where('history_transaction.transaction_name', 'ilike', '%'.$filters['transactionName'].'%');
        }

        if (!empty($filters['description'])) {
            $query->where('history_transaction.description', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $query->where('history_transaction.created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $query->where('history_transaction.created_date', '<=', $date->format('Y-m-d 23:59:59'));
        }

        return view('operational::report.history-resi.index', [
            'models'   => $query->paginate(10),
            'filters'  => $filters,
            'resource' => self::RESOURCE,
            'url'      => self::URL,
        ]);
    }
}
