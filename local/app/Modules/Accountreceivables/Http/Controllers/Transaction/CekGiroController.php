<?php

namespace App\Modules\Accountreceivables\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Accountreceivables\Model\Transaction\CekGiroHeader;
use App\Modules\Accountreceivables\Model\Transaction\CekGiroLine;
use App\Modules\Accountreceivables\Model\Transaction\Invoice;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Service\Master\CustomerService;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Generalledger\Service\Master\AccountCombinationService;
use App\Modules\Generalledger\Service\Master\JournalService;
use App\Modules\Generalledger\Model\Master\SettingJournal;
use App\Role;
use App\Service\Penomoran;
use App\Service\NotificationService;

class CekGiroController extends Controller
{
    const RESOURCE = 'Accountreceivables\Transaction\CekGiro';
    const URL = 'accountreceivables/transaction/cek-giro';

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
        $query   = \DB::table('ar.cek_giro_header')
                        ->select('cek_giro_header.*')
                        ->leftJoin('op.mst_customer', 'cek_giro_header.customer_id', '=', 'mst_customer.customer_id')
                        ->where('cek_giro_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->orderBy('cek_giro_header.cek_giro_date', 'desc')
                        ->orderBy('cek_giro_header.cek_giro_header_id', 'desc')
                        ->distinct();

        if (!empty($filters['cekGiroNumber'])) {
            $query->where('cek_giro_number', 'ilike', '%'.$filters['cekGiroNumber'].'%');
        }

        if (!empty($filters['cekGiroAccountNumber'])) {
            $query->where('cek_giro_account_number', 'ilike', '%'.$filters['cekGiroAccountNumber'].'%');
        }

        if (!empty($filters['customer'])) {
            $query->where('mst_customer.customer_name', 'ilike', '%'.$filters['customer'].'%');
        }

        if (!empty($filters['bankName'])) {
            $query->where('bank_name', 'ilike', '%'.$filters['bankName'].'%');
        }

        if (!empty($filters['type'])) {
            $query->where('cek_giro_header.type', '=', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', '=', $filters['status']);
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $query->where('cek_giro_header.cek_giro_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $query->where('cek_giro_header.cek_giro_date', '<=', $date->format('Y-m-d 23:59:59'));
        }

        if (!empty($filters['dueDateFrom'])) {
            $date = new \DateTime($filters['dueDateFrom']);
            $query->where('cek_giro_header.due_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dueDateTo'])) {
            $date = new \DateTime($filters['dueDateTo']);
            $query->where('cek_giro_header.due_date', '<=', $date->format('Y-m-d 23:59:59'));
        }

        return view('accountreceivables::transaction.cek-giro.index', [
            'models' => $query->paginate(10),
            'filters' => $filters,
            'resource' => self::RESOURCE,
            'url' => self::URL,
            'optionType' => [CekGiroHeader::CEK, CekGiroHeader::GIRO],
            'optionStatus' => [CekGiroHeader::OPEN, CekGiroHeader::CLOSED, CekGiroHeader::CANCELED],
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model         = new CekGiroHeader();
        $model->status = CekGiroHeader::OPEN;

        return view('accountreceivables::transaction.cek-giro.add', [
            'title' => trans('shared/common.add'),
            'model' => $model,
            'url' => self::URL,
            'resource' => self::RESOURCE,
            'optionType' => [CekGiroHeader::CEK, CekGiroHeader::GIRO],
        ]);
    }

    public function edit(Request $request, $id)
    {
        $model = CekGiroHeader::where('cek_giro_header_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        if ($request->user()->cannot('accessBranch', $model->branch_id)) {
            abort(403);
        }

        $data = [
            'title' => trans('shared/common.edit'),
            'model' => $model,
            'url' => self::URL,
            'resource' => self::RESOURCE,
            'optionType' => [CekGiroHeader::CEK, CekGiroHeader::GIRO],
        ];

        if ($request->user()->can('access', [self::RESOURCE, 'update'])) {
            return view('accountreceivables::transaction.cek-giro.add', $data);
        } else {
            return view('accountreceivables::transaction.cek-giro.detail', $data);
        }
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? CekGiroHeader::find($id) : new CekGiroHeader();

        if (empty($id)) {
            $this->validate($request, [
                'cekGiroAccountNumber' => 'required|unique:ar.cek_giro_header,cek_giro_account_number,' . $id . ',cek_giro_header_id',
                'type' => 'required',
                'cekGiroDate' => 'required',
                'dueDate' => 'required',
                'bankName' => 'required',
                'personName' => 'required',
                'address' => 'required',
                'phoneNumber' => 'required',
            ]);

            if (empty($request->get('invoiceId'))) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'You must insert minimal 1 line']);
            }

            if (empty($model->status)) {
                $model->status = CekGiroHeader::OPEN;
            }

            $model->cek_giro_account_number = $request->get('cekGiroAccountNumber');
            $model->type = $request->get('type');

            if (!empty($request->get('cekGiroDate'))) {
                $model->cek_giro_date = new \DateTime($request->get('cekGiroDate'));
            }

            if (!empty($request->get('dueDate'))) {
                $model->due_date = new \DateTime($request->get('dueDate'));
            }

            if (!empty($request->get('clearingDate'))) {
                $model->clearing_date = new \DateTime($request->get('clearingDate'));
                $model->status = CekGiroHeader::CLOSED;
            }

            $model->bank_name = $request->get('bankName');

            if (!empty($request->get('customerId'))) {
                $model->customer_id = $request->get('customerId');
            } else {
                $model->customer_id = null;
            }

            $model->person_name = $request->get('personName');
            $model->address = $request->get('address');
            $model->phone_number = $request->get('phoneNumber');
            $model->description = $request->get('description');
            $model->branch_id = \Session::get('currentBranch')->branch_id;

            $model->created_date = $this->now;
            $model->created_by = \Auth::user()->id;
            $model->cek_giro_number = $this->getCekGiroNumber($model);

            try {
                $model->save();
            } catch (\Exception $e) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }

            $model->lines()->delete();
            for ($i=0; $i < count($request->get('invoiceId')); $i++) { 
                $line =  new CekGiroLine();
                $line->cek_giro_header_id = $model->cek_giro_header_id;
                $line->invoice_id = intval($request->get('invoiceId')[$i]);
                $line->amount = intval($request->get('amountLine')[$i]);

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
                    return redirect(self::URL . '/edit/' . $model->cek_giro_header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }
            }

            $error = $this->createJournalCekGiro($model);
            if (!empty($error)) {
                return redirect(self::URL . '/edit/' . $model->cek_giro_header_id)->withInput($request->all())->withErrors(['errorMessage' => $error]);
            }
        }

        if ($request->get('btn-clearing') !== null) {
            $model->status = CekGiroHeader::CLOSED;
            $model->clearing_date = new \DateTime();
            try {
                $model->save();
            } catch (\Exception $e) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
            }
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('accountreceivables/menu.cek-giro').' '.$model->cek_giro_number])
        );

        return redirect(self::URL);
    }

    protected function createJournalCekGiro(CekGiroHeader $model)
    {
        $cekGiro = CekGiroHeader::find($model->cek_giro_header_id);
        foreach ($cekGiro->lines as $cekGiroLine) {
            $journalHeader = new JournalHeader();

            $journalHeader->category       = JournalHeader::CEK_GIRO;
            $journalHeader->period         = new \DateTime($this->now->format('Y-m-1'));
            $journalHeader->status         = JournalHeader::OPEN;
            $journalHeader->description    = 'Cek Giro Number: '.$model->cek_giro_number.'. Invoice Number: '.$cekGiroLine->invoice->invoice_number.
                                                '. Resi Number: '.$cekGiroLine->invoice->resi->resi_number;
            $journalHeader->branch_id      = $cekGiro->branch_id;
            $journalHeader->journal_date   = $this->now;
            $journalHeader->created_date   = $this->now;
            $journalHeader->created_by     = \Auth::user()->id;
            $journalHeader->journal_number = JournalService::getJournalNumber($journalHeader);

            try {
                $journalHeader->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }

            /** CEK / GIRO **/
            $settingCoa  = SettingJournal::where('setting_name', SettingJournal::CEK_GIRO)->first();
            $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

            $line = new JournalLine();
            $line->journal_header_id      = $journalHeader->journal_header_id;
            $line->account_combination_id = $combination->account_combination_id;
            $line->debet                  = $cekGiroLine->amount;
            $line->credit                 = 0;
            $line->created_date           = $this->now;
            $line->created_by             = \Auth::user()->id;

            try {
                $line->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }

            /** PIUTANG USAHA - CEK / GIRO **/
            $settingCoa  = SettingJournal::where('setting_name', SettingJournal::PIUTANG_USAHA)->first();
            $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

            $line = new JournalLine();
            $line->journal_header_id      = $journalHeader->journal_header_id;
            $line->account_combination_id = $combination->account_combination_id;
            $line->debet                  = 0;
            $line->credit                 = $cekGiroLine->amount;
            $line->created_date           = $this->now;
            $line->created_by             = \Auth::user()->id;

            try {
                $line->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
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

            foreach ($dataQuery as $invoice) {
                if ($invoice->remaining > 0) {
                    $data[] = $invoice;
                }
    
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
        $search = $request->get('search');
        $customerId = $request->get('customerId');
        $query  = \DB::table('ar.invoice')
                    ->select(
                        'invoice.*', 'trans_resi_header.resi_number', 'trans_resi_header.sender_name', 'trans_resi_header.receiver_name',
                        'trans_resi_header.payment', 'mst_route.route_code', 'customer_sender.customer_name as customer_sender_name',
                        'customer_receiver.customer_name as customer_receiver_name'
                    )
                    ->leftJoin('ar.receipt', 'invoice.invoice_id', '=', 'receipt.invoice_id')
                    ->leftJoin('ar.cek_giro_line', 'invoice.invoice_id', '=', 'cek_giro_line.invoice_id')
                    ->leftJoin('ar.cek_giro_header', 'cek_giro_line.cek_giro_header_id', '=', 'cek_giro_header.cek_giro_header_id')
                    ->leftJoin('op.trans_resi_header', 'invoice.resi_header_id', '=', 'trans_resi_header.resi_header_id')
                    ->leftJoin('op.mst_route', 'trans_resi_header.route_id', '=', 'mst_route.route_id')
                    ->leftJoin('op.mst_customer as customer_sender', 'trans_resi_header.customer_id', '=', 'customer_sender.customer_id')
                    ->where(function($query) {
                        $query->whereNull('cek_giro_line.cek_giro_line_id')
                                ->orWhere('cek_giro_header.status', '<>', CekGiroHeader::OPEN);
                    })
                    ->leftJoin('op.mst_customer as customer_receiver', 'trans_resi_header.customer_receiver_id', '=', 'customer_receiver.customer_id')
                    ->orderBy('invoice.created_date', 'desc')
                    ->distinct();

        if (!empty($search)) {
            $query->where(function($query) use ($search) {
                $query->where('invoice.invoice_number', 'ilike', '%'.$search.'%')
                        ->orWhere('trans_resi_header.resi_number', 'ilike', '%'.$search.'%')
                        ->orWhere('mst_route.route_code', 'ilike', '%'.$search.'%')
                        ->orWhere('customer_sender.customer_name', 'ilike', '%'.$search.'%')
                        ->orWhere('customer_receiver.customer_name', 'ilike', '%'.$search.'%');
            });
        }

        if (!empty($customerId)) {
            $query->where(function($query) use ($customerId) {
                $query->where('trans_resi_header.customer_id', '=', $customerId)
                        ->orWhere('trans_resi_header.customer_receiver_id', '=', $customerId);
            });
        }

        $invoices = [];
        $skip     = ($iteration - 1) * $maxData;
        foreach ($query->take($maxData)->skip($skip)->get() as $invoice) {
            $modelInvoice       = Invoice::find($invoice->invoice_id);
            $createdDate        = !empty($modelInvoice->created_date) ? new \DateTime($modelInvoice->created_date) : null;
            $invoice->date      = $createdDate !== null ? $createdDate->format('d-m-Y') : '';
            $invoice->remaining = $modelInvoice->remaining();

            $invoices[] = $invoice;
        }

        return $invoices;
    }

    public function cancel(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'cancel'])) {
            abort(403);
        }

        $model = CekGiroHeader::find($request->get('id'));
        if ($model === null) {
            abort(404);
        }

        $model->status = CekGiroHeader::CANCELED;
        $model->description .= '. Canceled Reason: ' . $request->get('reason', '');
        $model->last_updated_date = new \DateTime();
        $model->last_updated_by   = \Auth::user()->id;

        $model->save();

        $request->session()->flash(
            'successMessage',
            trans('shared/common.canceled-message', ['variable' => trans('accountreceivables/menu.cek-giro').' '.$model->cek_giro_number])
        );

        return redirect(self::URL);
    }

    protected function getCekGiroNumber(CekGiroHeader $model)
    {
        $branch      = MasterBranch::find($model->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('ar.cek_giro_header')
                            ->where('branch_id', '=', $model->branch_id)
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->count();

        return 'CG.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 5);
    }

}
