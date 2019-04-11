<?php

namespace App\Modules\Accountreceivables\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Accountreceivables\Model\Transaction\BatchInvoiceHeader;
use App\Modules\Accountreceivables\Model\Transaction\BatchInvoiceLine;
use App\Modules\Accountreceivables\Model\Transaction\Invoice;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Service\Master\CustomerService;
use App\Role;
use App\Service\Penomoran;
use App\Service\NotificationService;
use App\Modules\Operational\Service\Transaction\HistoryResiService;

class BatchInvoiceController extends Controller
{
    const RESOURCE = 'Accountreceivables\Transaction\BatchInvoice';
    const URL = 'accountreceivables/transaction/batch-invoice';

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
        $query   = \DB::table('ar.batch_invoice_header')
                        ->select('batch_invoice_header.*')
                        ->leftJoin('op.mst_customer', 'batch_invoice_header.customer_id', '=', 'mst_customer.customer_id')
                        ->where('batch_invoice_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->orderBy('batch_invoice_header.created_date', 'desc')
                        ->distinct();

        if (!empty($filters['batchInvoiceNumber'])) {
            $query->where('batch_invoice_number', 'ilike', '%'.$filters['batchInvoiceNumber'].'%');
        }

        if (!empty($filters['customer'])) {
            $query->where('mst_customer.customer_name', 'ilike', '%'.$filters['customer'].'%');
        }

        if (!empty($filters['billTo'])) {
            $query->where('bill_to', 'ilike', '%'.$filters['billTo'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $query->where('batch_invoice_header.created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $query->where('batch_invoice_header.created_date', '<=', $date->format('Y-m-d 23:59:59'));
        }

        if (!empty($filters['status'])) {
            $query->where('status', '=', $filters['status']);
        }

        return view('accountreceivables::transaction.batch-invoice.index', [
            'models' => $query->paginate(10),
            'filters' => $filters,
            'resource' => self::RESOURCE,
            'url' => self::URL,
            'optionStatus' => [BatchInvoiceHeader::OPEN, BatchInvoiceHeader::CLOSED, BatchInvoiceHeader::CANCELED],
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model         = new BatchInvoiceHeader();
        $model->status = BatchInvoiceHeader::OPEN;

        return view('accountreceivables::transaction.batch-invoice.add', [
            'title' => trans('shared/common.add'),
            'model' => $model,
            'url' => self::URL,
            'resource' => self::RESOURCE,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = BatchInvoiceHeader::where('batch_invoice_header_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        if ($request->user()->cannot('accessBranch', $model->branch_id)) {
            abort(403);
        }

        return view('accountreceivables::transaction.batch-invoice.add', [
            'title' => trans('shared/common.add'),
            'model' => $model,
            'url' => self::URL,
            'resource' => self::RESOURCE,
        ]);
    }

    public function printPdf(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $model = BatchInvoiceHeader::find($id);
        if ($model === null) {
            abort(404);
        }

        $header = view('print.header-pdf', ['title' => trans('accountreceivables/menu.batch-invoice')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });

        $html = view('accountreceivables::transaction.batch-invoice.print-pdf', [
            'model'  => $model,
        ])->render();

        \PDF::SetTitle(trans('accountreceivables/menu.batch-invoice'));
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('P', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output($model->invoice_number.'.pdf');
        \PDF::reset();
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? BatchInvoiceHeader::find($id) : new BatchInvoiceHeader();

        $this->validate($request, [
            'billTo' => 'required',
            'billToAddress' => 'required',
            'billToPhone' => 'required',
        ]);

        if (empty($request->get('invoiceHeaderId'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'You must insert minimal 1 line']);
        }

        if (!empty($request->get('discountPersen')) && empty($request->get('requestApproveNote'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Request approve note is required']);

            foreach($model->lines as $line) {
                if ($line->invoice !== null && $line->invoice->isInprocess()) {
                    return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Invoice '.$line->invoice->invoice_number.' is still inprocess']);
                }
            }
        }

        if (!empty($request->get('customerId'))) {
            $model->customer_id = $request->get('customerId');
        }

        $model->bill_to = $request->get('billTo');
        $model->bill_to_address = $request->get('billToAddress');
        $model->bill_to_phone = $request->get('billToPhone');
        $model->description = $request->get('description');
        $model->branch_id = \Session::get('currentBranch')->branch_id;

        if (empty($id)) {
            $model->created_date = $this->now;
            $model->created_by = \Auth::user()->id;
        } else {
            $model->last_updated_date = $this->now;
            $model->last_updated_by = \Auth::user()->id;
        }

        if (empty($model->status)) {
            $model->status = BatchInvoiceHeader::OPEN;
        }

        if (empty($model->batch_invoice_number)) {
            $model->batch_invoice_number = $this->getBatchInvoiceNumber($model);
        }

        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $model->lines()->delete();
        for ($i=0; $i < count($request->get('invoiceHeaderId')); $i++) {
            $line =  new BatchInvoiceLine();
            $line->batch_invoice_header_id = $model->batch_invoice_header_id;
            $line->invoice_id = intval($request->get('invoiceHeaderId')[$i]);

            if (empty($id)) {
                $line->created_date = $this->now;
                $line->created_by = \Auth::user()->id;
            }else{
                $line->last_updated_date = $this->now;
                $line->last_updated_by = \Auth::user()->id;
            }

            try {
                $line->save();
            } catch (\Exception $e) {
                return redirect(self::URL . '/edit/' . $model->batch_invoice_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }
        }

        if (!empty($request->get('discountPersen'))) {
            $discountPersen = str_replace(',', '', $request->get('discountPersen'));

            $model->status = BatchInvoiceHeader::INPROCESS;
            $model->discount_persen = $discountPersen;
            $model->request_approve_note = $request->get('requestApproveNote');

            try {
                $model->save();
            } catch (\Exception $e) {
                return redirect(self::URL . '/edit/' . $model->batch_invoice_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            $error = $this->addDiscountBatchInvoiceLine($model, $discountPersen, $request->get('requestApproveNote'));
            if (!empty($error)) {
                return redirect(self::URL . '/edit/' . $model->batch_invoice_header_id)->withInput($request->all())->withErrors(['errorMessage' => $error]);
            }

            NotificationService::createNotification(
                'Request Approve Batch Invoice',
                'Batch Invoice ' . $model->batch_invoice_number . ' - ' . $request->get('requestApproveNote'),
                ApproveBatchInvoiceController::URL.'/edit/'.$model->batch_invoice_header_id,
                [Role::BRANCH_MANAGER]
            );
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('accountreceivables/menu.batch-invoice').' '.$model->batch_invoice_number])
        );

        return redirect(self::URL);
    }

    protected function addDiscountBatchInvoiceLine($model, $discountPersen, $requestApproveNote)
    {
        $batchInvoice = BatchInvoiceHeader::find($model->batch_invoice_header_id);
        foreach($batchInvoice->lines as $line) {
            $invoice = $line->invoice;
            if ($invoice === null || !$invoice->canAddDiscount()) {
                continue;
            }

            $discount = ceil($discountPersen / 10000 * $invoice->amount) * 100; 
            $invoice->status = Invoice::INPROCESS_BATCH;
            if ($invoice->current_discount == 1) {
                $invoice->discount_1 = $discount;
                $invoice->discount_persen_1 = $discountPersen;
            } elseif ($invoice->current_discount == 2) {
                $invoice->discount_2 = $discount;
                $invoice->discount_persen_2 = $discountPersen;
            } elseif ($invoice->current_discount == 3) {
                $invoice->discount_3 = $discount;
                $invoice->discount_persen_3 = $discountPersen;
            }

            try {
                $invoice->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }

            HistoryResiService::saveHistory(
                $invoice->resi_header_id,
                'Request Discount Batch Invoice',
                'Batch Invoice '.$model->batch_invoice_number.'. Discount: '.$discountPersen.' % = '.number_format($discount).'. Note: '.$requestApproveNote
            );
        }
    }

    public function cancel(Request $request)
    {
        $id    = intval($request->get('id'));
        $model = BatchInvoiceHeader::find($id);
        if ($model === null || !$model->isOpen()) {
            abort(404);
        }

        $model->status = Invoice::CANCELED;
        $model->last_updated_date = $this->now;
        $model->last_updated_by = \Auth::user()->id;
        $model->description = $model->description.'. Canceled reason: '.$request->get('reason');

        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.canceled-message', ['variable' => trans('accountreceivables/menu.batch-invoice').' '.$model->batch_invoice_number])
        );

        return redirect(self::URL);
    }

    public function getJsonCustomer(Request $request)
    {
        $search = $request->get('search');
        $query  = \DB::table('op.mst_customer')
                    ->select('mst_customer.customer_id', 'mst_customer.customer_name', 'mst_customer.address', 'mst_customer.phone_number')
                    ->where('mst_customer.active', '=', 'Y')
                    ->orderBy('mst_customer.customer_name', 'asc')
                    ->where(function($query) use ($search) {
                        $query->where('mst_customer.customer_name', 'ilike', '%'.$search.'%')
                                ->orWhere('mst_customer.address', 'ilike', '%'.$search.'%')
                                ->orWhere('mst_customer.phone_number', 'ilike', '%'.$search.'%');
                    })
                    ->take(10);

        return response()->json($query->get());
    }

    protected function getBatchInvoiceNumber(BatchInvoiceHeader $model)
    {
        $branch      = MasterBranch::find($model->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('ar.batch_invoice_header')
                            ->where('branch_id', '=', $model->branch_id)
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->count();

        return 'BAR.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }

    public function getJsonInvoice(Request $request)
    {
        $maxData   = 10;
        $iteration = 1;
        $isFull    = false;
        $data      = [];

        while(!$isFull) {
            $dataQuery = $this->getDataQueryInvoice($request, $maxData, $iteration);
            if (empty($dataQuery)) {
                $isFull = true;
            }

            foreach ($dataQuery as $batch) {
                $data[] = $batch;
                if (count($data) >= $maxData) {
                    $isFull = true;
                    break;
                }
            }

            $iteration++;
        }

        return response()->json($data);
    }

    protected function getDataQueryInvoice(Request $request, $maxData, $iteration)
    {
        $batchHeaderId = intval($request->get('id'));
        $customerId    = $request->get('customerId');
        $search        = $request->get('search');

        $query = \DB::table('ar.invoice')
                    ->select(
                        'invoice.*', 'trans_resi_header.resi_number', 'mst_route.route_code', 'customer_sender.customer_name as customer_sender_name',
                        'trans_resi_header.sender_name', 'customer_receiver.customer_name as customer_receiver_name', 'trans_resi_header.receiver_name'
                    )
                    ->leftJoin('ar.batch_invoice_line', 'invoice.invoice_id', '=', 'batch_invoice_line.invoice_id')
                    ->leftJoin('ar.batch_invoice_header', 'batch_invoice_line.batch_invoice_header_id', '=', 'batch_invoice_header.batch_invoice_header_id')
                    ->leftJoin('op.trans_resi_header', 'invoice.resi_header_id', '=', 'trans_resi_header.resi_header_id')
                    ->leftJoin('op.mst_route', 'trans_resi_header.route_id', '=', 'mst_route.route_id')
                    ->leftJoin('op.mst_customer as customer_sender', 'trans_resi_header.customer_id', '=', 'customer_sender.customer_id')
                    ->leftJoin('op.mst_customer as customer_receiver', 'trans_resi_header.customer_id', '=', 'customer_receiver.customer_id')
                    ->where('invoice.status', '=', Invoice::APPROVED)
                    ->where(function($query) use ($batchHeaderId) {
                        $query->whereNull('batch_invoice_line.batch_invoice_line_id')
                                ->orWhere('batch_invoice_header.status', '=', BatchInvoiceHeader::CANCELED)
                                ->orWhere('batch_invoice_header.batch_invoice_header_id', '=', $batchHeaderId);
                    })
                    ->orderBy('invoice.created_date', 'desc')
                    ->distinct();

        if (!empty($search)) {
            $query->where(function($query) use ($search) {
                $query->where('invoice.invoice_number', 'ilike', '%'.$search.'%')
                        ->orWhere('trans_resi_header.resi_number', 'ilike', '%'.$search.'%')
                        ->orWhere('mst_route.route_code', 'ilike', '%'.$search.'%')
                        ->orWhere('customer_sender.customer_name', 'ilike', '%'.$search.'%')
                        ->orWhere('trans_resi_header.sender_name', 'ilike', '%'.$search.'%')
                        ->orWhere('customer_receiver.customer_name', 'ilike', '%'.$search.'%')
                        ->orWhere('trans_resi_header.receiver_name', 'ilike', '%'.$search.'%');
            });
        }

        if (!empty($customerId)) {
            $query->where('invoice.customer_id', '=', $customerId);
        }

        $invoiceArs = [];
        $skip       = ($iteration - 1) * $maxData;
        foreach ($query->take($maxData)->skip($skip)->get() as $invoiceAr) {
            $modelInvoiceAr = Invoice::find($invoiceAr->invoice_id);
            if ($modelInvoiceAr->remaining() <= 0) {
                continue;
            }

            $createdDate                  = !empty($modelInvoiceAr->created_date) ? new \DateTime($modelInvoiceAr->created_date) : null;
            $invoiceAr->date              = $createdDate !== null ? $createdDate->format('d-m-Y') : '';
            $invoiceAr->customer_name     = !empty($invoiceAr->customer_name) ? $invoiceAr->customer_name : '';
            $invoiceAr->total_invoice     = intval($modelInvoiceAr->amount);
            $invoiceAr->total_discount    = intval($modelInvoiceAr->totalDiscount());
            $invoiceAr->total             = intval($modelInvoiceAr->totalInvoice());
            $invoiceAr->remaining         = intval($modelInvoiceAr->remaining());
            $invoiceAr->can_add_discount  = intval($modelInvoiceAr->canAddDiscount());

            $invoiceArs[] = $invoiceAr;
        }

        return $invoiceArs;
    }
}
