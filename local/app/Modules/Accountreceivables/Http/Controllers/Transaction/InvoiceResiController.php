<?php

namespace App\Modules\Accountreceivables\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Accountreceivables\Model\Transaction\InvoiceArHeader;
use App\Modules\Accountreceivables\Model\Transaction\InvoiceArLine;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Service\Master\CustomerService;
use App\Role;
use App\Service\Penomoran;
use App\Service\NotificationService;

class InvoiceResiController extends Controller
{
    const RESOURCE = 'Accountreceivables\Transaction\InvoiceResi';
    const URL = 'accountreceivables/transaction/invoice-resi';

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
        $query   = \DB::table('ar.inv_ar_header')
                        ->select('inv_ar_header.*')
                        ->leftJoin('op.mst_customer', 'inv_ar_header.customer_id', '=', 'mst_customer.customer_id')
                        ->leftJoin('ar.inv_ar_line', 'inv_ar_header.inv_ar_header_id', '=', 'inv_ar_line.inv_ar_header_id')
                        ->leftJoin('op.trans_resi_header', 'inv_ar_line.resi_header_id', '=', 'trans_resi_header.resi_header_id')
                        ->where('inv_ar_header.type', '=', InvoiceArHeader::INV_RESI)
                        ->where('inv_ar_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->orderBy('inv_ar_header.created_date', 'desc')
                        ->distinct();

        if (!empty($filters['invoiceNumber'])) {
            $query->where('inv_ar_number', 'ilike', '%'.$filters['invoiceNumber'].'%');
        }

        if (!empty($filters['customer'])) {
            $query->where('mst_customer.customer_name', 'ilike', '%'.$filters['customer'].'%');
        }

        if (!empty($filters['billTo'])) {
            $query->where('bill_to', 'ilike', '%'.$filters['billTo'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $query->where('inv_ar_header.created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $query->where('inv_ar_header.created_date', '<=', $date->format('Y-m-d 23:59:59'));
        }

        if (!empty($filters['type'])) {
            $query->where('inv_ar_header.type', '=', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('inv_ar_header.status', '=', $filters['status']);
        }

        return view('accountreceivables::transaction.invoice-resi.index', [
            'models' => $query->paginate(10),
            'filters' => $filters,
            'resource' => self::RESOURCE,
            'url' => self::URL,
            'optionStatus' => [
                InvoiceArHeader::OPEN,
                InvoiceArHeader::INPROCESS,
                InvoiceArHeader::APPROVED,
            ],
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new InvoiceArHeader();
        $model->status = InvoiceArHeader::OPEN;
        $model->type = InvoiceArHeader::INV_RESI;

        return view('accountreceivables::transaction.invoice-resi.add', [
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

        $model = InvoiceArHeader::where('inv_ar_header_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        if ($request->user()->cannot('accessBranch', $model->branch_id)) {
            abort(403);
        }

        return view('accountreceivables::transaction.invoice-resi.add', [
            'title' => trans('shared/common.add'),
            'model' => $model,
            'url' => self::URL,
            'resource' => self::RESOURCE,
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? InvoiceArHeader::find($id) : new InvoiceArHeader();

        $this->validate($request, [
            'billTo' => 'required',
            'billToAddress' => 'required',
            'billToPhone' => 'required',
            'customerId' => 'required_unless:customerName,',
        ], [
            'customerId.required_unless' => 'Customer is not valid'
        ]);

        if (empty($request->get('lineId'))) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'You must insert minimal 1 line']);
        }

        if ($request->get('btn-request-approve') !== null) {
            if (empty($request->get('requestApproveNote'))) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Request Approve note is required']);
            }
        }

        if ($request->get('btn-approve') !== null) {
            for ($i=0; $i < count($request->get('lineId')); $i++) {
                if (!empty($request->get('discount')[$i]) || !empty($request->get('extraPrice')[$i])) {
                    return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Discount and extra price must be empty']);
                }
            }
        }

        if (empty($model->status)) {
            $model->status = InvoiceArHeader::OPEN;
        }

        if (empty($model->type)) {
            $model->type = InvoiceArHeader::INV_RESI;
        }

        if ($model->isOpen()) {
            if (!empty($request->get('customerId'))) {
                $model->customer_id = $request->get('customerId');
            }

            $model->bill_to = $request->get('billTo');
            $model->bill_to_address = $request->get('billToAddress');
            $model->bill_to_phone = $request->get('billToPhone');
            $model->description = $request->get('description');
            $model->req_approve_note = $request->get('requestApproveNote');
            $model->branch_id = \Session::get('currentBranch')->branch_id;

            if (empty($id)) {
                $model->created_date = $this->now;
                $model->created_by = \Auth::user()->id;
            } else {
                $model->last_updated_date = $this->now;
                $model->last_updated_by = \Auth::user()->id;
            }

            if (empty($model->inv_ar_number)) {
                $model->inv_ar_number = $this->getInvoiceNumber($model);
            }

            try {
                $model->save();
            } catch (\Exception $e) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            $model->lines()->delete();
            for ($i=0; $i < count($request->get('lineId')); $i++) { 
                $line =  new InvoiceArLine();
                $line->inv_ar_header_id = $model->inv_ar_header_id;
                $line->resi_header_id = $request->get('resiId')[$i];
                $line->amount = intval($request->get('amount')[$i]);
                $line->discount = intval($request->get('discount')[$i]);
                $line->extra_price = intval($request->get('extraPrice')[$i]);

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
                    return redirect(self::URL . '/edit/' . $model->inv_ar_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }
            }
        }

        if ($request->get('btn-request-approve') !== null && $model->isOpen()) {
            $model->status = InvoiceArHeader::INPROCESS;
            $model->last_updated_date = $this->now;
            $model->last_updated_by = \Auth::user()->id;
            $model->approved = null;
            $model->approved_by = null;
            $model->approved_date = null;
            $model->approved_note = null;

            try {
                $model->save();
            } catch (\Exception $e) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            /** notifikasi request approve **/
            NotificationService::createNotification(
                'Invoice AR Request for Approval',
                'Invoice AR '.$model->inv_ar_number,
                ApproveInvoiceController::URL.'/edit/'.$model->inv_ar_header_id,
                [Role::BRANCH_MANAGER]
            );
        }

        if ($request->get('btn-approve') !== null && $model->isOpen()) {
            $model->status = InvoiceArHeader::APPROVED;
            $model->last_updated_date = $this->now;
            $model->last_updated_by = \Auth::user()->id;
            $model->approved = true;
            $model->approved_by = \Auth::user()->id;
            $model->approved_date = $this->now;

            try {
                $model->save();
            } catch (\Exception $e) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('accountreceivables/menu.invoice-resi').' '.$model->inv_ar_number])
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
                    ->where('mst_customer.customer_name', 'ilike', '%'.$search.'%')
                    ->take(10);

        return response()->json($query->get());
    }

    public function getJsonResi(Request $request)
    {
        $search     = $request->get('search');
        $customerId = $request->get('customerId');
        $sqlInvoice = 'SELECT inv_ar_line.resi_header_id FROM ar.inv_ar_line JOIN ar.inv_ar_header ON inv_ar_line.inv_ar_header_id = inv_ar_header.inv_ar_header_id '.
                        'WHERE inv_ar_header.type = \''.InvoiceArHeader::INV_RESI.'\'';

        $id = $request->get('id');
        if (!empty($id)) {
            $sqlInvoice .= ' AND inv_ar_header.inv_ar_header_id <> '.$id;
        }

        $query = \DB::table('op.trans_resi_header')
                    ->select('trans_resi_header.*', 'mst_route.route_code', 'mst_customer.customer_name', 'customer_receiver.customer_name as customer_receiver_name')
                    ->leftJoin('op.mst_route', 'trans_resi_header.route_id', '=', 'mst_route.route_id')
                    ->leftJoin('op.mst_customer', 'trans_resi_header.customer_id', '=', 'mst_customer.customer_id')
                    ->leftJoin('op.mst_customer as customer_receiver', 'trans_resi_header.customer_receiver_id', '=', 'customer_receiver.customer_id')
                    ->where('trans_resi_header.status', '=', TransactionResiHeader::APPROVED)
                    ->whereRaw('trans_resi_header.resi_header_id NOT IN ('. $sqlInvoice .')')
                    ->where('trans_resi_header.resi_number', 'ilike', '%'.$search.'%')
                    ->orderBy('trans_resi_header.created_date', 'desc')
                    ->take(10);

        if (!empty($customerId)) {
            $query->whereRaw('trans_resi_header.customer_id = \''.$customerId.'\' OR trans_resi_header.customer_receiver_id = \''.$customerId.'\'');
        }

        $resis = [];
        foreach ($query->get() as $resi) {
            $modelResi = TransactionResiHeader::find($resi->resi_header_id);
            $resi->total = $modelResi->total();

            $resis[] = $resi;
        }

        return response()->json($resis);
    }

    protected function getInvoiceNumber(InvoiceArHeader $model)
    {
        $branch      = MasterBranch::find($model->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('ar.inv_ar_header')
                            ->where('branch_id', '=', $model->branch_id)
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->count();

        return 'IAR.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }
}
