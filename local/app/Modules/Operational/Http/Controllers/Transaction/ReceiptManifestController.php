<?php

namespace App\Modules\Operational\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
use App\Modules\Operational\Model\Transaction\ReceiptManifestHeader;
use App\Modules\Operational\Model\Transaction\ReceiptManifestLine;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Transaction\ManifestLine;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Model\Transaction\ResiStock;
use App\Service\Penomoran;
use App\Notification;
use App\Role;
use App\Service\NotificationService;
use App\Service\TimezoneDateConverter;
use App\Modules\Operational\Service\Transaction\HistoryResiService;

class ReceiptManifestController extends Controller
{
    const RESOURCE = 'Operational\Transaction\ReceiptManifest';
    const URL      = 'operational/transaction/receipt-manifest';

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

        if (empty($filters['jenis']) || $filters['jenis'] == 'headers') {
            $query = \DB::table('op.trans_manifest_receipt_header')
                            ->select(
                                'trans_manifest_receipt_header.manifest_receipt_header_id',
                                'trans_manifest_receipt_header.manifest_receipt_number',
                                'trans_manifest_receipt_header.created_date',
                                'trans_manifest_receipt_header.note',
                                'trans_manifest_header.manifest_number'
                                )
                            ->leftJoin('op.trans_manifest_header', 'trans_manifest_header.manifest_header_id', '=', 'trans_manifest_receipt_header.manifest_header_id')
                            ->leftJoin('op.trans_manifest_receipt_line', 'trans_manifest_receipt_line.manifest_receipt_header_id', '=', 'trans_manifest_receipt_header.manifest_receipt_header_id')
                            ->leftJoin('op.trans_manifest_line', 'trans_manifest_line.manifest_line_id', '=', 'trans_manifest_receipt_line.manifest_line_id')
                            ->leftJoin('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_manifest_line.resi_header_id')
                            ->where('trans_manifest_receipt_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->groupBy(
                                'trans_manifest_receipt_header.manifest_receipt_header_id',
                                'trans_manifest_receipt_header.manifest_receipt_number',
                                'trans_manifest_receipt_header.created_date',
                                'trans_manifest_receipt_header.note',
                                'trans_manifest_header.manifest_number'
                                )
                            ->orderBy('trans_manifest_receipt_header.created_date', 'desc')
                            ->distinct();
        }else{
            $query = \DB::table('op.trans_manifest_receipt_line')
                            ->select(
                                'trans_manifest_receipt_header.*',
                                'trans_manifest_receipt_line.*',
                                'trans_manifest_header.manifest_number',
                                'trans_manifest_line.coly_sent',
                                'trans_resi_header.resi_header_id',
                                'trans_resi_header.resi_number'
                                )
                            ->leftJoin('op.trans_manifest_receipt_header', 'trans_manifest_receipt_header.manifest_receipt_header_id', '=', 'trans_manifest_receipt_line.manifest_receipt_header_id')
                            ->leftJoin('op.trans_manifest_header', 'trans_manifest_header.manifest_header_id', '=', 'trans_manifest_receipt_header.manifest_header_id')
                            ->leftJoin('op.trans_manifest_line', 'trans_manifest_line.manifest_line_id', '=', 'trans_manifest_receipt_line.manifest_line_id')
                            ->leftJoin('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_manifest_line.resi_header_id')
                            ->where('trans_manifest_receipt_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->orderBy('trans_manifest_receipt_header.created_date', 'desc');
        }

        if (!empty($filters['receiptManifestNumber'])) {
            $query->where('manifest_receipt_number', 'ilike', '%'.$filters['receiptManifestNumber'].'%');
        }

        if (!empty($filters['resiNumber'])) {
            $query->where('resi_number', 'ilike', '%'.$filters['resiNumber'].'%');
        }

        if (!empty($filters['manifestNumber'])) {
            $query->where('manifest_number', 'ilike', '%'.$filters['manifestNumber'].'%');
        }

        if (!empty($filters['note'])) {
            $query->where('note', 'ilike', '%'.$filters['note'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = TimezoneDateConverter::getServerDateTime($filters['dateFrom']);
            $query->where('trans_manifest_receipt_header.created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = TimezoneDateConverter::getServerDateTime($filters['dateTo']);
            $query->where('trans_manifest_receipt_header.created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        return view('operational::transaction.receipt-manifest.index', [
            'models'          => $query->paginate(10),
            'filters'         => $filters,
            'optionWarehouse' => \DB::table('inv.v_mst_warehouse')->where('branch_id', '=', \Session::get('currentBranch')->branch_id)->get(),
            'resource'        => self::RESOURCE,
            'url'             => self::URL,
            ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new ReceiptManifestLine();

        return view('operational::transaction.receipt-manifest.add', [
            'title'            => trans('shared/common.add'),
            'model'            => $model,
            'url'              => self::URL,
            'optionManifest'   => \DB::table('op.trans_manifest_header')
                                    ->select('trans_manifest_header.*', 'v_mst_route.route_code', 'v_mst_route.city_start_name', 'v_mst_route.city_end_name', 'v_mst_driver.driver_name', 'mst_truck.police_number')
                                    ->join('op.trans_manifest_line', 'trans_manifest_line.manifest_header_id', '=', 'trans_manifest_header.manifest_header_id')
                                    ->join('op.v_mst_route', 'v_mst_route.route_id', '=', 'trans_manifest_header.route_id')
                                    ->join('op.v_mst_driver', 'v_mst_driver.driver_id', '=', 'trans_manifest_header.driver_id')
                                    ->leftJoin('op.mst_truck', 'trans_manifest_header.truck_id', '=', 'mst_truck.truck_id')
                                    ->where('trans_manifest_line.quantity_remain', '>', 0)
                                    ->where('v_mst_route.city_end_id', '=', \Session::get('currentBranch')->city_id)
                                    ->where(function ($query) {
                                          $query->where('trans_manifest_header.status', '=', ManifestHeader::ARRIVED);
                                      })
                                    ->distinct()
                                    ->get(),
            'optionWarehouse'  => \DB::table('inv.v_mst_warehouse')
                                    ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                                    ->get(),
            ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = ReceiptManifestHeader::find($id);

        if ($model === null) {
            abort(404);
        }
        return view('operational::transaction.receipt-manifest.add', [
            'title'            => trans('shared/common.edit'),
            'model'            => $model,
            'url'              => self::URL,
            'optionManifest'   => [],
            'optionWarehouse'  => \DB::table('inv.v_mst_warehouse')
                                    ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                                    ->get(),
            ]);
    }

    public function save(Request $request)
    {
        $id    = intval($request->get('id'));

        $this->validate($request, [
            'manifestHeaderId' => 'required',
            'note'             => 'required_if:btn-close-warning,closed',
        ], [
            'note.required_if' => 'Reason is required',
        ]);

        $this->validate($request, [
            'manifestHeaderId' => 'required',
            ]);

        if (empty($request->get('manifestLineId'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'You must choose minimal of one item']);
        }

        $stringMessage = null;
        foreach ($request->get('manifestLineId') as $manifestLineId) {
            $manifestLine = ManifestLine::find($manifestLineId);
            if($manifestLine->quantity_remain < $request->get('receiptQuantity-'.$manifestLineId)){
                $resi = TransactionResiHeader::find($manifestLine->resi_header_id);
                $stringMessage .= 'Resi '.$resi->resi_number.' remain '.$manifestLine->quantity_remain.' coly. ';
            }
        }

        if(!empty($stringMessage)){
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Receipt quantity exceed! '.$stringMessage. 'There is another user receipted this manifest, please refresh your browser.']);
        }

        $modelManifestHeader = ManifestHeader::where('manifest_header_id', '=', $request->get('manifestHeaderId'))->first();
        $opr = empty($id) ? 'I' : 'U';

        $now = new \DateTime();
        $modelHeader   = new ReceiptManifestHeader();
        $modelHeader->branch_id          = \Session::get('currentBranch')->branch_id;
        $modelHeader->manifest_header_id = intval($modelManifestHeader->manifest_header_id);
        $modelHeader->note               = $request->get('note');

        if ($opr == 'I') {
            $modelHeader->manifest_receipt_number = $this->getManifestReceiptNumber($modelHeader);
            $modelHeader->created_date      = $now;
            $modelHeader->created_by        = \Auth::user()->id;
        }else{
            $modelHeader->last_updated_date = $now;
            $modelHeader->last_updated_by   = \Auth::user()->id;
        }
        $modelHeader->save();
        foreach ($request->get('manifestLineId') as $manifestLineId) {
            $receiptQuantity = 'receiptQuantity-'.$manifestLineId;
            $quantity        = 'quantity-'.$manifestLineId;
            $resiId          = 'resiId-'.$manifestLineId;
            $descriptionLine = 'descriptionLine-'.$manifestLineId;
            $modelLine = new ReceiptManifestLine();
            $modelLine->manifest_line_id            = intval($manifestLineId);
            $modelLine->manifest_receipt_header_id  = intval($modelHeader->manifest_receipt_header_id);
            $modelLine->coly_receipt                = intval($request->get($receiptQuantity));
            $modelLine->description                 = $request->get($descriptionLine);

            if ($opr == 'I') {
                $modelLine->created_date = $now;
                $modelLine->created_by   = \Auth::user()->id;
            }else{
                $modelLine->last_updated_date = $now;
                $modelLine->last_updated_by   = \Auth::user()->id;
            }

            try {
                $modelLine->save();
            } catch (\Exception $e) {
                return redirect(self::URL)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            $manifestLine = ManifestLine::where('manifest_line_id', '=', $manifestLineId)->first();
            $check     = $this->checkExistStock($manifestLine->resi_header_id, $modelHeader->branch_id);
            $stockResi = $check ? ResiStock::where('resi_header_id', '=', $manifestLine->resi_header_id)->where('branch_id', '=', $modelHeader->branch_id)->first() : new ResiStock();
            $stockResi->branch_id      = $modelHeader->branch_id;
            $stockResi->resi_header_id = $manifestLine->resi_header_id;

            if ($check) {
                $stockResi->coly = $stockResi->coly + $modelLine->coly_receipt;
                $stockResi->last_updated_date = $now;
                $stockResi->last_updated_by   = \Auth::user()->id;
                $modelLine->created_date = $now;
                $modelLine->created_by   = \Auth::user()->id;
            }else{
                $stockResi->coly = $modelLine->coly_receipt;
                $stockResi->created_date = $now;
                $stockResi->created_by   = \Auth::user()->id;
                $stockResi->last_updated_date = $now;
                $stockResi->last_updated_by   = \Auth::user()->id;
                $modelLine->last_updated_date = $now;
                $modelLine->last_updated_by   = \Auth::user()->id;
            }
            $stockResi->save();

            $quantityRemain = $manifestLine->quantity_remain;
            $manifestLine->quantity_remain = $quantityRemain - intval($request->get($receiptQuantity));
            $manifestLine->save();
        }

        $process = 'Receipt Manifest';
        if ($this->checkManifestClosed($modelManifestHeader->manifest_header_id)) {
            $process = 'Close Receipt Manifest';
            $modelManifestHeader->status = ManifestHeader::CLOSED;
        }

        $modelManifestHeader->save();

        $this->saveHistoryResi($modelHeader, $process);

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.receipt-manifest').' '.$modelManifestHeader->manifest_number])
            );
        return redirect(self::URL);
    }

    protected function saveHistoryResi(ReceiptManifestHeader $model, $process)
    {
        $receiptManifest = ReceiptManifestHeader::find($model->manifest_receipt_header_id);
        foreach ($receiptManifest->lines as $line) {
            $resiId = $line->manifestLine !== null ? $line->manifestLine->resi_header_id : null;
            HistoryResiService::saveHistory(
                $resiId,
                $process,
                'Receipt Manifest Number: '.$receiptManifest->manifest_receipt_number.'. Coly Receipt: '.$line->coly_receipt
            );
        }
    }

    public function printPdfDetail(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $filters = \Session::get('filters');
        $model   = ReceiptManifestHeader::find($id);
        
        if ($model === null) {
            abort(404);
        }

        $header = view('print.header-pdf', ['title' => trans('operational/menu.receipt-manifest')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('operational::transaction.receipt-manifest.print-pdf-detail', [
            'model'  => $model,
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('operational/menu.receipt-manifest'));
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('P', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output(trans('operational/menu.receipt-manifest').' '.$model->manifest_number.'.pdf');
        \PDF::reset();
    }

    protected function getManifestReceiptNumber(ReceiptManifestHeader $model)
    {
        $branch      = MasterBranch::find(\Session::get('currentBranch')->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('op.trans_manifest_receipt_header')
                            ->where('branch_id', '=', $model->branch_id)
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->count();
        return 'RMF.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }

    function checkManifestClosed($headerId){
        if (\DB::table('op.trans_manifest_header')
                ->join('op.trans_manifest_line', 'trans_manifest_line.manifest_header_id', '=', 'trans_manifest_header.manifest_header_id')
                ->where('trans_manifest_header.manifest_header_id', '=', $headerId)
                ->where('trans_manifest_line.quantity_remain', '>', 0)
                ->where(function ($query) {
                      $query->where('trans_manifest_header.status', '=', ManifestHeader::ARRIVED)
                            ->orWhere('trans_manifest_header.status', '=', ManifestHeader::CLOSED_WARNING);
                  })
                ->count() <= 0) {
            return true;
        }
        return false;
    }

    function checkExistStock($resiId, $branchId){
        if (\DB::table('op.mst_stock_resi')
                ->where('mst_stock_resi.resi_header_id', '=', $resiId)
                ->where('mst_stock_resi.branch_id', '=', $branchId)
                ->count() > 0) {
            return true;
        }
        return false;
    }
}
