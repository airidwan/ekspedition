<?php

namespace App\Modules\Operational\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Transaction\ReceiptDocumentTransferHeader;
use App\Modules\Operational\Model\Transaction\ReceiptDocumentTransferLine;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Model\Transaction\DocumentTransferHeader;
use App\Modules\Operational\Model\Transaction\DocumentTransferLine;
use App\Service\NotificationService;
use App\Service\Penomoran;
use App\Notification;
use App\Role;

class ReceiptDocumentTransferController extends Controller
{
    const RESOURCE = 'Operational\Transaction\ReceiptDocumentTransfer';
    const URL      = 'operational/transaction/receipt-document-transfer';

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
            $query   = \DB::table('op.trans_receipt_document_transfer_header')
                            ->select('trans_receipt_document_transfer_header.*', 'trans_document_transfer_header.document_transfer_number')
                            ->join('op.trans_receipt_document_transfer_line', 'trans_receipt_document_transfer_line.receipt_document_transfer_header_id', '=', 'trans_receipt_document_transfer_header.receipt_document_transfer_header_id')
                            ->leftJoin('op.trans_document_transfer_header', 'trans_document_transfer_header.document_transfer_header_id', '=', 'trans_receipt_document_transfer_header.document_transfer_header_id')
                            ->where('trans_receipt_document_transfer_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->orderBy('trans_receipt_document_transfer_header.receipt_document_transfer_number', 'desc')
                            ->distinct();
        }else{
            $query   = \DB::table('op.trans_receipt_document_transfer_line')
                            ->select(
                                'trans_receipt_document_transfer_line.*', 
                                'trans_document_transfer_header.receipt_document_transfer_number', 
                                'trans_receipt_document_transfer_header.created_date', 
                                'trans_document_transfer_header.document_transfer_number',
                                'trans_resi_header.resi_number',
                                'trans_resi_header.item_name',
                                'trans_resi_header.sender_name',
                                'trans_resi_header.receiver_name',
                                'trans_resi_header.description'
                                )
                            ->join('op.trans_receipt_document_transfer_header', 'trans_receipt_document_transfer_header.receipt_document_transfer_header_id', '=', 'trans_receipt_document_transfer_header.receipt_document_transfer_header_id')
                            ->leftJoin('op.trans_document_transfer_line', 'trans_document_transfer_line.document_transfer_line_id', '=', 'trans_receipt_document_transfer_line.document_transfer_line_id')
                            ->leftJoin('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_document_transfer_line.resi_header_id')
                            ->where('trans_receipt_document_transfer_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                            ->orderBy('trans_receipt_document_transfer_header.receipt_document_transfer_number', 'desc')
                            ->distinct();
        }

        if (!empty($filters['documentTransferNumber'])) {
            $query->where('trans_document_transfer_header.document_transfer_number', 'ilike', '%'.$filters['documentTransferNumber'].'%');
        }

        if (!empty($filters['description']) && $filters['jenis'] == 'headers') {
            $query->where('trans_receipt_document_transfer_header.description', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['receiptNumber'])) {
            $query->where('trans_receipt_document_transfer_header.receipt_document_transfer_number', 'ilike', '%'.$filters['receiptNumber'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = new \DateTime($filters['dateFrom']);
            $query->where('trans_receipt_document_transfer_header.created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = new \DateTime($filters['dateTo']);
            $query->where('trans_receipt_document_transfer_header.created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        return view('operational::transaction.receipt-document-transfer.index', [
            'models'          => $query->paginate(10),
            'filters'         => $filters,
            'resource'        => self::RESOURCE,
            'url'             => self::URL,
            ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new ReceiptDocumentTransferLine();

        return view('operational::transaction.receipt-document-transfer.add', [
            'title'            => trans('shared/common.add'),
            'model'            => $model,
            'url'              => self::URL,
            'resource'         => self::RESOURCE,
            'optionDocumentTransfer'=> \DB::table('op.trans_document_transfer_header')
                                    ->select('trans_document_transfer_header.*', 'mst_driver.driver_name', 'mst_truck.truck_code', 'mst_truck.police_number')
                                    ->join('op.trans_document_transfer_line', 'trans_document_transfer_line.document_transfer_header_id', '=', 'trans_document_transfer_header.document_transfer_header_id')
                                    ->leftJoin('op.mst_driver', 'mst_driver.driver_id', '=', 'trans_document_transfer_header.driver_id')
                                    ->leftJoin('op.mst_truck', 'mst_truck.truck_id', '=', 'trans_document_transfer_header.truck_id')
                                    ->join('op.mst_city','mst_city.city_id', '=', 'trans_document_transfer_header.to_city_id')
                                    ->whereNull('trans_document_transfer_line.receipt_branch_id')                                    
                                    ->where('mst_city.city_id', '=', \Session::get('currentBranch')->city_id)                                    
                                    ->where('trans_document_transfer_header.status', '=', DocumentTransferHeader::INPROCESS)
                                    ->distinct()
                                    ->get(),
            ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = ReceiptDocumentTransferHeader::find($id);

        if ($model === null) {
            abort(404);
        }
        return view('operational::transaction.receipt-document-transfer.add', [
            'title'            => trans('shared/common.edit'),
            'model'            => $model,
            'url'              => self::URL,
            'resource'         => self::RESOURCE,
            'optionDocumentTransfer' => [],
            ]);
    }

    public function save(Request $request)
    {
        // var_dump($request->all());exit();
        $id    = intval($request->get('id'));

        $this->validate($request, [
            'documentTransferHeaderId' => 'required',
            ]);

        if (empty($request->get('documentTransferLineId'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'You must choose minimal of one item']);
        }
        $modelDocumentTransfer = DocumentTransferHeader::where('document_transfer_header_id', '=', $request->get('documentTransferHeaderId'))->first();

        $opr   = empty($id) ? 'I' : 'U';

        $now   = new \DateTime();
        $model = new ReceiptDocumentTransferHeader();
        $model->receipt_document_transfer_number = $this->getDocumentTransferNumber($model);
        $model->branch_id                        = \Session::get('currentBranch')->branch_id;
        $model->document_transfer_header_id      = $modelDocumentTransfer->document_transfer_header_id;
        $model->description                      = $request->get('description');
        $model->created_date                     = $now;
        $model->created_by                       = \Auth::user()->id;
        $model->save();

        foreach ($request->get('documentTransferLineId') as $documentTransferLineId) {
            $line = new ReceiptDocumentTransferLine();
            $line->document_transfer_line_id           = $documentTransferLineId;
            $line->receipt_document_transfer_header_id = $model->receipt_document_transfer_header_id;

            if ($opr == 'I') {
                $line->created_date = $now;
                $line->created_by   = \Auth::user()->id;
            }else{
                $line->last_updated_date = $now;
                $line->last_updated_by   = \Auth::user()->id;
            }

            try {
                $line->save();
            } catch (\Exception $e) {
                return redirect(self::URL)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            $documentTransferLine                    = DocumentTransferLine::where('document_transfer_line_id', '=', $documentTransferLineId)->first();
            $documentTransferLine->receipt_branch_id = $model->branch_id;
            $documentTransferLine->save();
        }

        if ($this->checkDocumentTransferComplete($modelDocumentTransfer->document_transfer_header_id) ) {
            $modelDocumentTransfer->status = DocumentTransferHeader::COMPLETE;
            $modelDocumentTransfer->save();
        }

        $userNotif = NotificationService::getUserNotification([Role::WAREHOUSE_ADMIN]);
        foreach ($userNotif as $user) {
            $notif             = new Notification();
            $notif->branch_id  = $modelDocumentTransfer->branch_id;
            $notif->category   = 'Branch Transfer Delivered';
            $notif->message    = 'Branch Transfer '.$modelDocumentTransfer->document_transfer_number.' delivered to '.\Session::get('currentBranch')->branch_code;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->save();
        }

        $userNotif = NotificationService::getUserNotification([Role::FINANCE_ADMIN]);
        foreach ($userNotif as $user) {
            $notif             = new Notification();
            $notif->branch_id  = \Session::get('currentBranch')->branch_id;
            $notif->category   = 'Receipt Document Transfer';
            $notif->message    = 'Receipting Document Transfer from '.$modelDocumentTransfer->document_transfer_number;
            $notif->url        = self::URL.'/edit/'.$model->receipt_document_transfer_header_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->save();
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.receipt-document-transfer').' '.$request->get('documentTransferNumber')])
            );
        return redirect(self::URL);
    }

    protected function getDocumentTransferNumber(ReceiptDocumentTransferHeader $model)
    {
        $branch      = MasterBranch::find(\Session::get('currentBranch')->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('op.trans_receipt_document_transfer_header')
                        ->where('branch_id', '=', $branch->branch_id)
                        ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                        ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                        ->count();

        return 'RDT.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }

    function checkDocumentTransferComplete($headerId){
        if (\DB::table('op.trans_document_transfer_header')
                ->join('op.trans_document_transfer_line', 'trans_document_transfer_line.document_transfer_header_id', '=', 'trans_document_transfer_header.document_transfer_header_id')
                ->where('trans_document_transfer_header.document_transfer_header_id', '=', $headerId)
                ->whereNull('trans_document_transfer_line.receipt_branch_id')
                ->where('trans_document_transfer_header.status', '=', DocumentTransferHeader::INPROCESS)
                ->count() <= 0) {
            return true;
        }
        return false;
    }
}
