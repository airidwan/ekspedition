<?php

namespace App\Modules\Operational\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Transaction\CustomerTaking;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Http\Controllers\Transaction\CustomerTakingTransactController;
use App\Notification;
use App\Service\Penomoran;
use App\Service\NotificationService;
use App\Service\TimezoneDateConverter;
use App\Role;
use App\Modules\Operational\Service\Transaction\HistoryResiService;


class CustomerTakingController extends Controller
{
    const RESOURCE = 'Operational\Transaction\LetterOfGoodExpenditure';
    const URL      = 'operational/transaction/customer-taking';

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
        $query = \DB::table('op.trans_customer_taking')
                    ->select('trans_customer_taking.*', 'mst_stock_resi.coly')
                    ->join('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_customer_taking.resi_header_id')
                    ->leftJoin('op.mst_stock_resi', 'mst_stock_resi.resi_header_id', '=', 'trans_customer_taking.resi_header_id')
                    ->where('trans_customer_taking.branch_id', '=', \Session::get('currentBranch')->branch_id);
                    // ->where('mst_stock_resi.branch_id', '=', \Session::get('currentBranch')->branch_id);

        if (!empty($filters['customerTakingNumber'])) {
            $query->where('customer_taking_number', 'ilike', '%'.$filters['customerTakingNumber'].'%');
        }

        if (!empty($filters['resiNumber'])) {
            $query->where('resi_number', 'ilike', '%'.$filters['resiNumber'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = TimezoneDateConverter::getServerDateTime($filters['dateFrom']);
            $query->where('customer_taking_time', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = TimezoneDateConverter::getServerDateTime($filters['dateTo']);
            $query->where('customer_taking_time', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        return view('operational::transaction.customer-taking.index', [
            'models'       => $query->paginate(10),
            'filters'      => $filters,
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new CustomerTaking();
        return view('operational::transaction.customer-taking.add', [
            'title'        => trans('shared/common.add'),
            'model'        => $model,
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = CustomerTaking::where('customer_taking_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        return view('operational::transaction.customer-taking.add', [
            'title'        => trans('shared/common.edit'),
            'model'        => $model,
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? CustomerTaking::where('customer_taking_id', '=', $id)->first() : new CustomerTaking();

        $this->validate($request, [
            'note'    => 'required',
            'resiId'  => 'required',
        ]);

        $timeString = $request->get('date').' '.$request->get('hour').':'.$request->get('minute');
        $time       = !empty($timeString) ? TimezoneDateConverter::getServerDateTime($timeString) : null;
        
        $model->customer_taking_time    = !empty($time) ? $time->format('Y-m-d H:i:s'):null;
        $model->note             = $request->get('note');
        $model->resi_header_id  = intval($request->get('resiId'));

        if (empty($model->branch_id)) {
            $model->branch_id        = \Session::get('currentBranch')->branch_id;
        }

        $now = new \DateTime();
        if (empty($id)) {
            $model->customer_taking_number = $this->getCustomerTakingNumber($model);
            $model->created_date = $now;
            $model->created_by = \Auth::user()->id;
        } else {
            $model->last_updated_date = $now;
            $model->last_updated_by = \Auth::user()->id;
        }

        try {
            $model->save();            
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $userNotif = NotificationService::getUserNotification([Role::WAREHOUSE_ADMIN]);

        foreach ($userNotif as $user) {
            $notif             = new Notification();
            $notif->branch_id  = \Session::get('currentBranch')->branch_id;
            $notif->category   = 'Letter of Goods Expenditure Created';
            $notif->message    = $model->customer_taking_number.' is ready to transact.';
            $notif->url        = CustomerTakingTransactController::URL.'/add/'.$model->customer_taking_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->save();
        }

        if (empty($id)) {
            HistoryResiService::saveHistory(
                $model->resi_header_id,
                'Letter of Goods Expenditure',
                'LGE Number: '.$model->customer_taking_number.'. Note: '.$model->note
            );
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.customer-taking').' '.$model->customer_taking_number])
        );

        return redirect(self::URL . '/edit/' . $model->customer_taking_id);

    }

    public function printPdfDetail(Request $request, $id)
    {
        $filters = \Session::get('filters');
        $model   = CustomerTaking::find($id);

        $header = view('print.header-pdf', ['title' => trans('operational/menu.customer-taking')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });
        $html = view('operational::transaction.customer-taking.print-pdf-detail', [
            'model'  => $model,
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('operational/menu.customer-taking').' - '.$model->pickup_request_number);
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A5');
        \PDF::writeHTML($html);
        \PDF::Output($model->pickup_request_number.'.pdf');
        \PDF::reset();
    }

    protected function getCustomerTakingNumber(CustomerTaking $model)
    {
        $branch      = MasterBranch::find(\Session::get('currentBranch')->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('op.trans_customer_taking')
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->where('branch_id', '=', $branch->branch_id)
                            ->count();

        return 'LGE.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }

    protected function getJsonResi(Request $request){
        $search   = $request->get('search');
        $listResi = \DB::table('op.trans_resi_header')
            ->select(
                'trans_resi_header.*', 'mst_customer.customer_name', 'mst_customer.customer_id', 'v_mst_route.route_code',
                'v_mst_route.city_start_name', 'v_mst_route.city_end_name', 'mst_delivery_area.delivery_area_name'
            )
            ->join('op.v_mst_route', 'v_mst_route.route_id', '=', 'trans_resi_header.route_id')
            ->join('op.mst_stock_resi', 'op.mst_stock_resi.resi_header_id', '=', 'trans_resi_header.resi_header_id')
            ->leftJoin('op.mst_delivery_area', 'mst_delivery_area.delivery_area_id', '=', 'trans_resi_header.delivery_area_id')
            ->leftJoin('op.mst_customer', 'mst_customer.customer_id', '=', 'trans_resi_header.customer_id')
            ->leftJoin('op.trans_customer_taking', 'trans_customer_taking.resi_header_id', '=', 'trans_resi_header.resi_header_id')
            ->where(function ($query) use ($search){
                    $query->where('trans_customer_taking.resi_header_id', '=', null)
                      ->orWhere('trans_customer_taking.branch_id', '<>', \Session::get('currentBranch')->branch_id);
            })
            ->where('mst_stock_resi.branch_id', '=', \Session::get('currentBranch')->branch_id)
            ->where(function ($query) use ($search) {
                    $query->where('trans_resi_header.resi_number', 'ilike', '%'.$search.'%')
                      ->orWhere('trans_resi_header.item_name', 'ilike', '%'.$search.'%')
                      ->orWhere('trans_resi_header.sender_name', 'ilike', '%'.$search.'%')
                      ->orWhere('trans_resi_header.receiver_name', 'ilike', '%'.$search.'%')
                      ->orWhere('trans_resi_header.description', 'ilike', '%'.$search.'%');
                })
            ->take(10);

        $arrResi = [];
        foreach($listResi->get() as $resi) {
            $modelResi = TransactionResiHeader::find($resi->resi_header_id);
            $resi->customer_name = !empty($modelResi->customerReceiver) ? $modelResi->customerReceiver->customer_name : '';
            $resi->total_coly   = $modelResi->totalColy();
            $resi->total_weight = $modelResi->totalWeightAll();
            $resi->total_receipt = $modelResi->totalReceipt(); 
            $resi->total_volume = $modelResi->totalVolumeAll();
            $resi->total_available = $modelResi->totalAvailable();
            $resi->total_remaining_invoice = $modelResi->totalRemainingInvoice();
            $resi->is_tagihan = $modelResi->isTagihan() ? 'V' : 'X' ;

            $arrResi [] = $resi;
        }
        return response()->json($arrResi);
    }
}
