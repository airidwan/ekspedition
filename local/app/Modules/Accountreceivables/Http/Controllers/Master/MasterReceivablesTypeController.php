<?php

namespace App\Modules\Accountreceivables\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class MasterReceivablesTypeController extends Controller
{
    const RESOURCE = 'Accountreceivables\Master\MasterReceivablesType';
    const URL = 'accountreceivables-master-master-receivables-type';

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        return view('accountreceivables::master.master-receivables-type.index',
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

        return view('accountreceivables::master.master-receivables-type.add',
            [ 
                'resource'   => self::RESOURCE,
                'title'      => trans('shared/common.add'),
                'url'        => self::URL,

            ]);
    }
}
