<?php

namespace App\Modules\Operational\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Transaction\CustomerTakingTransact;
use App\Modules\Operational\Model\Transaction\CustomerTaking;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Transaction\ResiStock;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Service\Penomoran;
use App\Service\TimezoneDateConverter;
use App\Modules\Operational\Service\Transaction\HistoryResiService;

class CustomerTakingTransactController extends Controller
{
    const RESOURCE = 'Operational\Transaction\LetterOfGoodExpenditureTransact';
    const URL      = 'operational/transaction/customer-taking-transact';

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
        $query = \DB::table('op.trans_customer_taking_transact')
                    ->join('op.trans_customer_taking', 'trans_customer_taking.customer_taking_id', '=', 'trans_customer_taking_transact.customer_taking_id')
                    ->join('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_customer_taking.resi_header_id')
                    ->leftJoin('op.mst_stock_resi', 'mst_stock_resi.resi_header_id', '=', 'trans_customer_taking.resi_header_id')
                    ->where('trans_customer_taking_transact.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('trans_customer_taking_transact.created_date', 'desc')
                    ->orderBy('customer_taking_transact_id', 'desc');

        if (!empty($filters['customerTakingTransactNumber'])) {
            $query->where('customer_taking_transact_number', 'ilike', '%'.$filters['customerTakingTransactNumber'].'%');
        }

        if (!empty($filters['resiNumber'])) {
            $query->where('resi_number', 'ilike', '%'.$filters['resiNumber'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = TimezoneDateConverter::getServerDateTime($filters['dateFrom']);
            $query->where('customer_taking_transact_time', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = TimezoneDateConverter::getServerDateTime($filters['dateTo']);
            $query->where('customer_taking_transact_time', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        return view('operational::transaction.customer-taking-transact.index', [
            'models'       => $query->paginate(10),
            'filters'      => $filters,
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
        ]);
    }

    public function add(Request $request, $id=null)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new CustomerTakingTransact();

        if ($id !== null) {
            $modelCt = CustomerTaking::find($id);
        }else{
            $modelCt = $model->customerTaking;
        }

        return view('operational::transaction.customer-taking-transact.add', [
            'title'          => trans('shared/common.add'),
            'model'          => $model,
            'modelCt'        => $modelCt,
            'resource'       => self::RESOURCE,
            'url'            => self::URL,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = CustomerTakingTransact::where('customer_taking_transact_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }
        $modelCt = $model->customerTaking;

        return view('operational::transaction.customer-taking-transact.add', [
            'title'        => trans('shared/common.edit'),
            'model'        => $model,
            'modelCt'      => $modelCt,
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? CustomerTakingTransact::where('customer_taking_transact_id', '=', $id)->first() : new CustomerTakingTransact();

        $this->validate($request, [
            'customerTakingId'  => 'required',
            'takerName'         => 'required',
            'takerAddress'      => 'required',
            'takerPhone'        => 'required',
            'colyTaken'         => 'required',
        ]);

        $timeString = $request->get('date').' '.$request->get('hour').':'.$request->get('minute');
        $time       = !empty($timeString) ? TimezoneDateConverter::getServerDateTime($timeString) : null;

        $model->customer_taking_transact_time    = !empty($time) ? $time->format('Y-m-d H:i:s'):null;
        $model->customer_taking_id = $request->get('customerTakingId');
        $model->taker_name         = $request->get('takerName');
        $model->taker_address      = $request->get('takerAddress');
        $model->taker_phone        = $request->get('takerPhone');
        $model->coly_taken         = $request->get('colyTaken');
        $model->note               = $request->get('note');

        if (empty($model->branch_id)) {
            $model->branch_id        = \Session::get('currentBranch')->branch_id;
        }

        $now = new \DateTime();
        if (empty($id)) {
            $model->customer_taking_transact_number = $this->getCustomerTakingTransactNumber($model);
            $model->created_date = $now;
            $model->created_by = \Auth::user()->id;
        } else {
            $model->last_updated_date = $now;
            $model->last_updated_by = \Auth::user()->id;
        }

        $modelCustomer = CustomerTaking::find($model->customer_taking_id);
        $modelStock    = ResiStock::where('resi_header_id', '=', $modelCustomer->resi_header_id)
                                    ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)->first();

        if ($model->coly_taken > $modelStock->coly) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Coly taken exceed!']);
        }

        $modelStock->coly -= $model->coly_taken;
        if ($modelStock->coly <= 0) {
            $modelStock->delete();
        } else {
            $modelStock->save();
        }

        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        HistoryResiService::saveHistory(
            $modelCustomer->resi_header_id,
            'LGE Transact',
            'LGE Transact Number: '.$model->customer_taking_transact_number.'. Coly: '.$model->coly_taken.'. Person Name: '.$model->taker_name
        );

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.customer-taking-transact').' '.$model->customer_taking_transact_number])
        );

        return redirect(self::URL);
    }

    protected function getCustomerTakingTransactNumber(CustomerTakingTransact $model)
    {
        $branch      = MasterBranch::find(\Session::get('currentBranch')->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('op.trans_customer_taking_transact')
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->where('branch_id', '=', $branch->branch_id)
                            ->count();

        return 'LGET.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }

    protected function getOptionsCustomerTaking(){
        $listResi = \DB::table('op.trans_customer_taking')
        ->select(
            'trans_customer_taking.*','trans_resi_header.resi_header_id','mst_customer.customer_id','trans_resi_header.receiver_name', 'trans_resi_header.receiver_address',
            'trans_resi_header.receiver_phone','mst_customer.customer_name','trans_resi_header.resi_number','trans_resi_header.item_name',
            'trans_resi_header.description', 'v_mst_route.route_code', 'v_mst_route.city_start_name', 'v_mst_route.city_end_name',
            'mst_delivery_area.delivery_area_name'
        )
        ->join('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_customer_taking.resi_header_id')
        ->join('op.v_mst_route', 'v_mst_route.route_id', '=', 'trans_resi_header.route_id')
        ->join('op.mst_stock_resi', 'op.mst_stock_resi.resi_header_id', '=', 'trans_resi_header.resi_header_id')
        ->leftJoin('op.mst_delivery_area', 'mst_delivery_area.delivery_area_id', '=', 'trans_resi_header.delivery_area_id')
        ->leftJoin('op.mst_customer', 'mst_customer.customer_id', '=', 'trans_resi_header.customer_id')
        ->where('trans_customer_taking.branch_id', '=', \Session::get('currentBranch')->branch_id)
        ->get();

        $arrResi = [];
        foreach($listResi as $resi) {
            $modelResi = TransactionResiHeader::find($resi->resi_header_id);
            $resi->customer_name   = $modelResi->getCustomerName();
            $resi->total_coly      = $modelResi->totalColy();
            $resi->total_weight    = $modelResi->totalWeightAll();
            $resi->total_receipt   = $modelResi->totalReceipt();
            $resi->total_volume    = $modelResi->totalVolumeAll();
            $resi->total_available = $modelResi->totalAvailable();

            $arrResi [] = $resi;
        }
        return $arrResi;
    }

    protected function getJsonCustomerTaking(Request $request){
        $search   = $request->get('search');
        $listResi = \DB::table('op.trans_customer_taking')
            ->select(
                'trans_customer_taking.*','trans_resi_header.resi_header_id','mst_customer.customer_id','trans_resi_header.receiver_name', 'trans_resi_header.receiver_address',
                'trans_resi_header.receiver_phone','mst_customer.customer_name','trans_resi_header.resi_number','trans_resi_header.item_name',
                'trans_resi_header.description', 'v_mst_route.route_code', 'v_mst_route.city_start_name', 'v_mst_route.city_end_name',
                'mst_delivery_area.delivery_area_name', 'mst_stock_resi.coly as keong'
            )
            ->join('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_customer_taking.resi_header_id')
            ->join('op.v_mst_route', 'v_mst_route.route_id', '=', 'trans_resi_header.route_id')
            ->join('op.mst_stock_resi', 'op.mst_stock_resi.resi_header_id', '=', 'trans_resi_header.resi_header_id')
            ->leftJoin('op.mst_delivery_area', 'mst_delivery_area.delivery_area_id', '=', 'trans_resi_header.delivery_area_id')
            ->leftJoin('op.mst_customer', 'mst_customer.customer_id', '=', 'trans_resi_header.customer_id')
            ->where('trans_customer_taking.branch_id', '=', \Session::get('currentBranch')->branch_id)
            ->where('mst_stock_resi.branch_id', '=', \Session::get('currentBranch')->branch_id)
            ->where('mst_stock_resi.coly', '>', 0)
            ->where(function ($query) use ($search) {
                    $query->where('trans_resi_header.resi_number', 'ilike', '%'.$search.'%')
                      ->orWhere('trans_customer_taking.customer_taking_number', 'ilike', '%'.$search.'%')
                      ->orWhere('mst_customer.customer_name', 'ilike', '%'.$search.'%')
                      ->orWhere('trans_resi_header.receiver_name', 'ilike', '%'.$search.'%')
                      ->orWhere('trans_resi_header.receiver_address', 'ilike', '%'.$search.'%')
                      ->orWhere('trans_resi_header.receiver_phone', 'ilike', '%'.$search.'%')
                      ->orWhere('trans_resi_header.item_name', 'ilike', '%'.$search.'%')
                      ->orWhere('trans_resi_header.description', 'ilike', '%'.$search.'%');
                })
            ->take(10);

        $arrResi = [];
        foreach($listResi->get() as $resi) {
            $modelResi = TransactionResiHeader::find($resi->resi_header_id);
            $resi->total_available = $modelResi->totalAvailable();
            $resi->customer_name   = $modelResi->getCustomerName();
            $resi->total_coly      = $modelResi->totalColy();
            $resi->total_weight    = $modelResi->totalWeightAll();
            $resi->total_receipt   = $modelResi->totalReceipt();
            $resi->total_volume    = $modelResi->totalVolumeAll();

            $arrResi [] = $resi;
        }
        return response()->json($arrResi);
    }
}
