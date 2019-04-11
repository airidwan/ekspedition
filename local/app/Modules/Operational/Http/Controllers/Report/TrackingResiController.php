<?php

namespace App\Modules\Operational\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Service\Master\TrackingResiService;

class TrackingResiController extends Controller
{
    const RESOURCE = 'Operational\Report\TrackingResi';
    const URL      = 'operational/report/tracking-resi';

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $filters = $data = $history = [];
        if ($request->isMethod('post')) {
            $filters = $request->all();
            $resiNumber = $request->get('resiNumber', '');
            $resiNumberLength = strlen($resiNumber);
            $minimumLength = 7;

            if ($resiNumberLength < $minimumLength) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Minimum resi number length is '.$minimumLength.' character']);
            }

            $resi = !empty($resiNumber) ? TransactionResiHeader::where('resi_number', 'ilike', '%'.$resiNumber)->orderBy('resi_header_id', 'desc')->first() : null;
            if ($resi === null) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Resi not found']);
            }

            $data    = TrackingResiService::tracking($resi);
            $history = TrackingResiService::history($resi);
        }

        return view('operational::report.tracking-resi.index', [
            'data'     => $data,
            'history'  => $history,
            'filters'  => $filters,
            'resource' => self::RESOURCE,
            'url'      => self::URL,
        ]);
    }
}
