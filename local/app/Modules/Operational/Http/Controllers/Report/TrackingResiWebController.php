<?php

namespace App\Modules\Operational\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Service\Master\TrackingResiService;

class TrackingResiWebController extends Controller
{
    public function tracking(Request $request)
    {
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

        return view('operational::report.tracking-resi.tracking', [
            'data'     => $data,
            'history'  => $history,
            'filters'  => $filters
        ]);
    }

    public function getTracking(Request $request)
    {
        $filters = $data = $history = [];
        if ($request->isMethod('post')) {
            $filters = $request->all();
            $resiNumber = $request->get('resiNumber', '');
            $resiNumberLength = strlen($resiNumber);
            $minimumLength = 7;

            if ($resiNumberLength < $minimumLength) {
                return response()->json([
                    'status'   => 'failed',
                    'message'  => 'Minimal nomor resi '.$minimumLength.' karakter',
                ], 200);
            }

            $resi = !empty($resiNumber) ? TransactionResiHeader::where('resi_number', 'ilike', '%'.$resiNumber)->orderBy('resi_header_id', 'desc')->first() : null;
            if ($resi === null) {
                return response()->json([
                    'status'   => 'failed',
                    'message'  => 'Resi tidak ditemukan',
                ], 200);
            }

            $data    = TrackingResiService::tracking($resi);
            $history = TrackingResiService::simpleHistory($resi);
        }

        return response()->json([
            'status'   => 'success',
            'message'  => 'Success',
            'resi'     => $resi,
            'data'     => $data,
            'history'  => $history,
        ], 200);
    }
}
