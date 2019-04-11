<?php

namespace App\Modules\AccountReceivables\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Accountreceivables\Model\Transaction\ReceiptArHeader;
use App\Modules\Accountreceivables\Model\Transaction\ReceiptArLine;
use App\Modules\Accountreceivables\Model\Transaction\InvoiceArHeader;
use App\Modules\Accountreceivables\Model\Transaction\InvoiceArLine;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Accountreceivables\Model\Master\MasterCekGiro;
use App\Modules\Operational\Service\Master\CustomerService;
use App\Role;
use App\Service\Penomoran;
use App\Service\NotificationService;

class ApproveReceiptController extends Controller
{
    const RESOURCE = 'Accountreceivables\Transaction\ApproveReceipt';
    const URL = 'accountreceivables/transaction/approve-receipt';

    protected $now;

    public function __construct()
    {
        $this->middleware('auth');
        $this->now = new \DateTime();
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
        $query   = \DB::table('ar.receipt_ar_header')
                        ->select('receipt_ar_header.*')
                        ->join('ar.inv_ar_header', 'receipt_ar_header.inv_ar_header_id', '=', 'inv_ar_header.inv_ar_header_id')
                        ->leftJoin('op.mst_customer', 'inv_ar_header.customer_id', '=', 'mst_customer.customer_id')
                        ->where('receipt_ar_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->where('receipt_ar_header.status', '=', ReceiptArHeader::INPROCESS)
                        ->orderBy('receipt_ar_header.created_date', 'desc')
                        ->distinct();

        if (!empty($filters['receiptNumber'])) {
            $query->where('receipt_number', 'ilike', '%'.$filters['receiptNumber'].'%');
        }

        if (!empty($filters['invoiceNumber'])) {
            $query->where('inv_ar_number', 'ilike', '%'.$filters['invoiceNumber'].'%');
        }

        if (!empty($filters['customer'])) {
            $query->where('mst_customer.customer_name', 'ilike', '%'.$filters['customer'].'%');
        }

        if (!empty($filters['billTo'])) {
            $query->where('bill_to', 'ilike', '%'.$filters['billTo'].'%');
        }

        if (!empty($filters['receiptMethod'])) {
            $query->where('receipt_ar_header.receipt_method', '=', $filters['receiptMethod']);
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $query->where('receipt_ar_header.created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $query->where('receipt_ar_header.created_date', '<=', $date->format('Y-m-d 23:59:59'));
        }

        return view('accountreceivables::transaction.approve-receipt.index', [
            'models' => $query->paginate(10),
            'filters' => $filters,
            'resource' => self::RESOURCE,
            'url' => self::URL,
            'optionReceiptMethod' => $this->getOptionReceiptMethod(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = ReceiptArHeader::where('receipt_ar_header_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        if ($request->user()->cannot('accessBranch', $model->branch_id)) {
            abort(403);
        }

        return view('accountreceivables::transaction.approve-receipt.add', [
            'title' => trans('shared/common.add'),
            'model' => $model,
            'url' => self::URL,
            'resource' => self::RESOURCE,
            'optionReceiptMethod' => $this->getOptionReceiptMethod(),
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = ReceiptArHeader::find($id);

        $this->validate($request, [
            'approveNote' => 'required',
        ]);

        if ($request->get('btn-approve') !== null) {
            $model->approved = true;
            $model->approved_note = 'Approved : '.$request->get('approveNote');
            $model->status = ReceiptArHeader::APPROVED;
            $model->req_approve_note = null;

        } elseif ($request->get('btn-reject') !== null) {
            $model->approved = false;
            $model->approved_note = 'Rejected : '.$request->get('approveNote');
            $model->status = ReceiptArHeader::OPEN;
            $model->req_approve_note = null;
        }

        $model->approved_by = \Auth::user()->id;
        $model->approved_date = new \DateTime(); 

        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(self::URL.'/edit/'.$model->receipt_ar_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $notifCategory = $request->get('btn-approve') !== null ? 'Invoice Approved' : 'Invoice Rejected';
        NotificationService::createNotification(
            $notifCategory,
            'Invoice ' . $model->inv_ar_number . ' - ' . $model->approved_note,
            ReceiptController::URL.'/edit/'.$model->receipt_ar_header_id,
            [Role::FINANCE_ADMIN]
        );

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('accountreceivables/menu.approve-receipt').' '.$model->inv_ar_number])
        );

        return redirect(self::URL);
    }

    protected function getOptionReceiptMethod()
    {
        return [ReceiptArHeader::CASH, ReceiptArHeader::TRANSFER, ReceiptArHeader::CEK_GIRO];
    }
}
