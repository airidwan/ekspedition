<?php

namespace App\Modules\AccountReceivables\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ReceivablesController extends Controller
{
    const RESOURCE = 'Accountreceivables\Transaction\Receivables';
    const URL = 'accountreceivables/transaction/receivables';

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }
        return view('accountreceivables::transaction.receivables.index',
            [ 
                'resource'   => self::RESOURCE,
                'url'        => self::URL,
            ]);
    }

    public function add(Request $request)
    {

        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        return view('accountreceivables::master.master-cara-bayar.add',
            [ 
                'title'      => trans('shared/common.add'),
                'resource'   => self::RESOURCE,
                'url'        => self::URL,

            ]);
    }
}
