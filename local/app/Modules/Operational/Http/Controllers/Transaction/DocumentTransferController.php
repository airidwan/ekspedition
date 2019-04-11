<?php

namespace App\Modules\Operational\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Transaction\DocumentTransferHeader;
use App\Modules\Operational\Model\Transaction\DocumentTransferLine;
use App\Modules\Operational\Service\Master\DriverService;
use App\Modules\Operational\Service\Transaction\ResiService;
use App\Modules\Operational\Service\Master\TruckService;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Service\Penomoran;
use App\Notification;
use App\Service\NotificationService;
use App\Role;

class DocumentTransferController extends Controller
{
    const RESOURCE = 'Operational\Transaction\DocumentTransfer';
    const URL      = 'operational/transaction/document-transfer';

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
        } elseif (empty($request->get('page'))) {
            $request->session()->forget('filters');
        }

        $filters = $request->session()->get('filters');
        if (empty($filters['jenis']) || $filters['jenis'] == 'headers') {
            $query = \DB::table('op.trans_document_transfer_header')
                    ->select(
                        'trans_document_transfer_header.*', 
                        'mst_driver.driver_code', 
                        'mst_driver.driver_name', 
                        'mst_truck.truck_code', 
                        'mst_city.city_name'
                        )
                    ->leftJoin('op.trans_document_transfer_line', 'trans_document_transfer_line.document_transfer_header_id', '=', 'trans_document_transfer_header.document_transfer_header_id')
                    ->leftJoin('op.mst_city', 'mst_city.city_id', '=', 'trans_document_transfer_header.to_city_id')
                    ->leftJoin('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_document_transfer_line.resi_header_id')
                    ->leftJoin('op.mst_driver', 'mst_driver.driver_id', '=', 'trans_document_transfer_header.driver_id')
                    ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_document_transfer_header.truck_id')
                    ->where('trans_document_transfer_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('trans_document_transfer_header.created_date', 'desc')
                    ->groupBy(
                        'trans_document_transfer_header.document_transfer_header_id', 
                        'mst_driver.driver_code', 
                        'mst_driver.driver_name', 
                        'mst_truck.truck_code', 
                        'mst_city.city_name'
                        )
                    ->distinct();
        }else{
            $query = \DB::table('op.trans_document_transfer_line')
                    ->select(
                        'trans_document_transfer_line.*',
                        'trans_document_transfer_header.*',
                        'mst_city.city_name',
                        'trans_resi_header.resi_number',
                        'trans_resi_header.item_name',
                        'trans_resi_header.sender_name',
                        'trans_resi_header.receiver_name',
                        'trans_resi_header.description as description_resi',
                        'mst_branch.branch_code as branch_to_code'
                        )
                    ->leftJoin('op.trans_document_transfer_header', 'trans_document_transfer_header.document_transfer_header_id', '=', 'trans_document_transfer_line.document_transfer_header_id')
                    ->leftJoin('op.mst_city', 'mst_city.city_id', '=', 'trans_document_transfer_header.to_city_id')
                    ->leftJoin('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_document_transfer_line.resi_header_id')
                    ->leftJoin('op.mst_branch', 'mst_branch.branch_id', '=', 'trans_resi_header.branch_id')
                    ->leftJoin('op.mst_driver', 'mst_driver.driver_id', '=', 'trans_document_transfer_header.driver_id')
                    ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_document_transfer_header.truck_id')
                    ->where('trans_document_transfer_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('trans_document_transfer_header.created_date', 'desc')
                    ->distinct();
        }

        if (!empty($filters['documentTransferNumber'])) {
            $query->where('trans_document_transfer_header.document_transfer_number', 'ilike', '%'.$filters['documentTransferNumber'].'%');
        }

        if (!empty($filters['description'])) {
            $query->where('trans_document_transfer_header.description', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['driverCode'])) {
            $query->where('mst_driver.driver_code', 'ilike', '%'.$filters['driverCode'].'%');
        }

        if (!empty($filters['truckCode'])) {
            $query->where('mst_truck.truck_code', 'ilike', '%'.$filters['truckCode'].'%');
        }

        if (!empty($filters['resiNumber'])) {
            $query->where('trans_resi_header.resi_number', 'ilike', '%'.$filters['resiNumber'].'%');
        }

        if (!empty($filters['itemName'])) {
            $query->where('trans_resi_header.item_name', 'ilike', '%'.$filters['itemName'].'%');
        }

        if (!empty($filters['status'])) {
            $query->where('trans_document_transfer_header.status', '=', $filters['status']);
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = new \DateTime($filters['dateFrom']);
            $query->where('trans_document_transfer_header.created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = new \DateTime($filters['dateTo']);
            $query->where('trans_document_transfer_header.created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }
        return view('operational::transaction.document-transfer.index', [
            'models'          => $query->paginate(10),
            'filters'         => $filters,
            'optionStatus'    => $this->getOptionsStatus(),
            'optionRoute'     => $this->getOptionRoute(),
            'optionToBranch'  => \DB::table('op.mst_branch')->where('branch_id', '!=', \Session::get('currentBranch')->branch_id)->get(),
            'resource'        => self::RESOURCE,
            'url'             => self::URL,
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new DocumentTransferHeader();
        $model->status = DocumentTransferHeader::INCOMPLETE;

        return view('operational::transaction.document-transfer.add', [
            'title'           => trans('shared/common.add'),
            'model'           => $model,
            'optionRoute'     => $this->getOptionRoute(),
            'optionStatus'    => $this->getOptionsStatus(),
            // 'optionResi'      => ResiService::getApprovedResiAllBranch(),
            'optionTruck'     => TruckService::getActiveTruck(),
            'optionDriver'    => DriverService::getActiveDriverAsistant(),
            'resource'        => self::RESOURCE,
            'url'             => self::URL,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = DocumentTransferHeader::where('document_transfer_header_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        return view('operational::transaction.document-transfer.add', [
            'title'           => trans('shared/common.edit'),
            'model'           => $model,
            'optionRoute'     => $this->getOptionRoute(),
            'optionStatus'    => $this->getOptionsStatus(),
            // 'optionResi'      => ResiService::getApprovedResiAllBranch(),
            'optionTruck'     => TruckService::getActiveTruck(),
            'optionDriver'    => DriverService::getActiveDriverAsistant(),
            'resource'        => self::RESOURCE,
            'url'             => self::URL,
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? DocumentTransferHeader::where('document_transfer_header_id', '=', $id)->first() : new DocumentTransferHeader();

        $this->validate($request, [
            'description' => 'required',
            'driverName'  => 'required',
            'toCity'      => 'required',
        ]);

        if (empty($request->get('resiHeaderId'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Lines are required']);
        }

        if (empty($model->branch_id)) {
            $model->branch_id = $request->session()->get('currentBranch')->branch_id;
        }

        $model->description = $request->get('description');
        $model->driver_id   = intval($request->get('driverId'));
        $model->to_city_id  = intval($request->get('toCity'));
        if(!empty($request->get('truckId'))){
            $model->truck_id    = $request->get('truckId');
        }

        $now = new \DateTime();
        if (empty($id)) {
            $model->status                     = DocumentTransferHeader::INCOMPLETE;
            $model->document_transfer_number   = $this->DocumentTransferNumber($model);
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

        $resiHeaderIds = $request->get('resiHeaderId');
        $lines = $model->lines()->delete();

        for ($i = 0; $i < count($request->get('resiHeaderId')); $i++) {
            $line = new DocumentTransferLine();
            $line->document_transfer_header_id = $model->document_transfer_header_id;
            $line->resi_header_id= $request->get('resiHeaderId')[$i];
            $line->created_date  = new \DateTime();
            $line->created_by    = \Auth::user()->id;

            try {
                $line->save();
            } catch (\Exception $e) {
                return redirect(self::URL.'/edit/'.$model->document_transfer_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }
        }

        if ($request->get('btn-transact') !== null) {
            $model->status = DocumentTransferHeader::INPROCESS;
            try {
                $model->save();
            } catch (\Exception $e) {
                return redirect(self::URL.'/edit/'.$model->document_transfer_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            $this->notifCabangTujuan($model);
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.document-transfer').' '.$model->document_transfer_number])
        );

        return redirect(self::URL);
    }

    public function close(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'close'])) {
            abort(403);
        }

        $model = DocumentTransferHeader::find($request->get('id'));
        if ($model === null || !$model->isInprocess()) {
            abort(404);
        }
       
        $model->status       = DocumentTransferHeader::CLOSED_WARNING;
        $model->description .= ' (Close warning reason is '. $request->get('reasonClose', '').')';
        $model->save();

        $userNotif = NotificationService::getUserNotification([Role::BRANCH_MANAGER, Role::WAREHOUSE_MANAGER]);
        foreach ($userNotif as $user) {
            $notif             = new Notification();
            $notif->branch_id  = \Session::get('currentBranch')->branch_id;
            $notif->category   = 'Document Transfer Force Close';
            $notif->message    = 'Document Transfer Force Close '.$model->document_transfer_number. '. ' . $request->get('reasonClose', '');
            // $notif->url        = self::URL.'/edit/'.$model->document_transfer_header_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->save();
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.close-message', ['variable' => trans('operational/menu.document-transfer').' '.$model->document_transfer_number])
        );

        return redirect(self::URL);
    }

    protected function notifCabangTujuan(DocumentTransferHeader $model)
    {
        $branchs = [];
        $documentTransfer = DocumentTransferHeader::find($model->document_transfer_header_id);
        if ($documentTransfer === null) {
            return;
        }

        foreach ($documentTransfer->lines as $line) {
            if ($line->resi === null) {
                return;
            }

            $branchs[$line->resi->branch_id] = $line->resi->branch_id;
        }

        foreach ($branchs as $branchId) {
            NotificationService::createSpesificBranchNotification(
                'Document Transfer',
                'Document Transfer ' . $model->document_transfer_number . ' shipped from ' . \Session::get('currentBranch')->branch_name,
                null,
                [Role::OPERATIONAL_ADMIN],
                $branchId
            );
        }
    }

    public function getJsonResi(Request $request)
    {
        $search   = $request->get('search');
        $toCity   = $request->get('toCity');

        $query = \DB::table('op.trans_resi_header')
                        ->select('trans_resi_header.*', 'mst_branch.branch_code')
                        ->leftJoin('op.mst_branch', 'mst_branch.branch_id', '=', 'trans_resi_header.branch_id')
                        ->where('trans_resi_header.status', '=', TransactionResiHeader::APPROVED)
                        ->where(function ($query) use ($search) {
                            $query->where('trans_resi_header.resi_number', 'ilike', '%'.$search.'%')
                              ->orWhere('trans_resi_header.item_name', 'ilike', '%'.$search.'%')
                              ->orWhere('trans_resi_header.sender_name', 'ilike', '%'.$search.'%')
                              ->orWhere('trans_resi_header.receiver_name', 'ilike', '%'.$search.'%')
                              ->orWhere('trans_resi_header.description', 'ilike', '%'.$search.'%');
                        })
                        ->orderBy('trans_resi_header.created_date', 'desc')
                        ->take(10);
        // if (!empty($toCity)) {
        //     $query->where('mst_branch.city_id', '=', $toCity);
        // }

        return response()->json($query->get());
    }

    protected function getOptionRoute(){
        return \DB::table('op.mst_route')->where('city_start_id', '=', \Session::get('currentBranch')->city_id)->get();
    }

    public function printPdfDetail(Request $request, $id)
    {
        $filters = \Session::get('filters');
        $model   = DocumentTransferHeader::find($id);

        $header = view('print.header-pdf', ['title' => trans('operational/menu.document-transfer')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('operational::transaction.document-transfer.print-pdf-detail', [
            'model'  => $model,
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle('Document Transfer');
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A5');
        \PDF::writeHTML($html);
        \PDF::Output($model->document_transfer_number.'.pdf');
        \PDF::reset();
    }

    public function cancel(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'cancel'])) {
            abort(403);
        }

        $model = DocumentTransferHeader::find($request->get('id'));
        if ($model === null) {
            abort(404);
        }

        $model->status = DocumentTransferHeader::CANCELED;
        $model->description = $model->description .'. Canceled reason : '.$request->get('reason');
        $model->last_updated_date = new \DateTime();
        $model->last_updated_by   = \Auth::user()->id;
        $model->save();

        $request->session()->flash(
            'successMessage',
            trans('purchasing/fields.canceled-message', ['variable' => trans('operational/menu.document-transfer').' '.$model->document_transfer_number])
            );

        return redirect(self::URL);
    }

    protected function DocumentTransferNumber(DocumentTransferHeader $model)
    {
        $branch      = MasterBranch::find(\Session::get('currentBranch')->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('op.trans_document_transfer_header')
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->where('branch_id', '=', $branch->branch_id)
                            ->count();

        return 'DT.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }

    protected function getOptionsStatus()
    {
        return [
            DocumentTransferHeader::INCOMPLETE,
            DocumentTransferHeader::INPROCESS,
            DocumentTransferHeader::COMPLETE,
            DocumentTransferHeader::CANCELED,
        ];
    }
}
