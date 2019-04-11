<?php

namespace App\Modules\Operational\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Transaction\ResiStockCorrection;
use App\Modules\Operational\Model\Transaction\DeliveryOrderLine;
use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Transaction\CustomerTakingTransact;
use App\Modules\Operational\Model\Transaction\OfficialReport;
use App\Modules\Operational\Model\Transaction\ResiStock;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Service\Transaction\OfficialReportService;
use App\Service\Penomoran;
use App\Notification;
use App\Service\NotificationService;
use App\Role;

class ResiStockCorrectionController extends Controller
{
    const RESOURCE = 'Operational\Transaction\ResiStockCorrection';
    const URL      = 'operational/transaction/resi-stock-correction';

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
        $query = \DB::table('op.trans_resi_stock_correction')
                    ->select(
                        'trans_resi_stock_correction.*',
                        'trans_official_report.official_report_number',
                        'trans_delivery_order_header.delivery_order_number',
                        'trans_delivery_order_line.total_coly as total_coly_ct',
                        'trans_customer_taking_transact.customer_taking_transact_number',
                        'trans_customer_taking_transact.coly_taken',
                        'resi_do.resi_number as do_resi_number',
                        'resi_ct.resi_number as ct_resi_number'
                        )
                    ->leftJoin('op.trans_official_report', 'trans_official_report.official_report_id', '=', 'trans_resi_stock_correction.official_report_id')
                    ->leftJoin('op.trans_delivery_order_line', 'trans_delivery_order_line.delivery_order_line_id', '=', 'trans_resi_stock_correction.delivery_order_line_id')
                    ->leftJoin('op.trans_delivery_order_header', 'trans_delivery_order_header.delivery_order_header_id', '=', 'trans_delivery_order_line.delivery_order_header_id')
                    ->leftJoin('op.trans_resi_header as resi_do', 'resi_do.resi_header_id', '=', 'trans_delivery_order_line.resi_header_id')
                    ->leftJoin('op.trans_customer_taking_transact', 'trans_customer_taking_transact.customer_taking_transact_id', '=', 'trans_resi_stock_correction.customer_taking_transact_id')
                    ->leftJoin('op.trans_customer_taking', 'trans_customer_taking.customer_taking_id', '=', 'trans_customer_taking_transact.customer_taking_id')
                    ->leftJoin('op.trans_resi_header as resi_ct', 'resi_ct.resi_header_id', '=', 'trans_customer_taking.resi_header_id')
                    ->where('trans_resi_stock_correction.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('trans_resi_stock_correction.resi_stock_correction_number', 'desc');

        if (!empty($filters['resiStockNumber'])) {
            $query->where('trans_resi_stock_correction.resi_stock_correction_number', 'ilike', '%'.$filters['resiStockNumber'].'%');
        }

        if (!empty($filters['type'])) {
            $query->where('trans_resi_stock_correction.type', '=', $filters['type']);
        }

        if (!empty($filters['officalReportNumber'])) {
            $query->where('trans_official_report.official_report_number', 'ilike', '%'.$filters['officalReportNumber'].'%');
        }

        if (!empty($filters['doCtNumber'])) {
            $query->where(function($query) use ($filters){
                $query->where('trans_delivery_order_header.delivery_order_number', 'ilike', '%'.$filters['doCtNumber'].'%')
                    ->orWhere('trans_customer_taking_transact.customer_taking_transact_number', 'ilike', '%'.$filters['doCtNumber'].'%');
                });
        }

        if (!empty($filters['resiNumber'])) {
            $query->where(function($query) use ($filters){
                $query->where('resi_do.resi_number', 'ilike', '%'.$filters['resiNumber'].'%')
                    ->orWhere('resi_ct.resi_number', 'ilike', '%'.$filters['resiNumber'].'%');
                });
        }

        if (!empty($filters['note'])) {
            $query->where('trans_resi_stock_correction.note', 'ilike', '%'.$filters['note'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = new \DateTime($filters['dateFrom']);
            $query->where('created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = new \DateTime($filters['dateTo']);
            $query->where('created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        return view('operational::transaction.resi-stock-correction.index', [
            'models'       => $query->paginate(10),
            'filters'      => $filters,
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
            'optionType'     => $this->getOptionsType(),
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new ResiStockCorrection();

        return view('operational::transaction.resi-stock-correction.add', [
            'title'          => trans('shared/common.add'),
            'model'          => $model,
            'optionResiDo'   => $this->getOptionsResiDo(),
            'optionResiCt'   => $this->getOptionsResiCt(),
            'optionType'     => $this->getOptionsType(),
            'optionOfficial' => OfficialReportService::getResiCorrectionOfficialReport(),
            'resource'       => self::RESOURCE,
            'url'            => self::URL,
            ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? ResiStockCorrection::where('resi_stock_correction_id', '=', $id)->first() : new ResiStockCorrection();

        $this->validate($request, [
            'type'              => 'required',
            'doCtId'            => 'required_if:type,'.ResiStockCorrection::CUSTOMER_TAKING.'|required_if:type,'.ResiStockCorrection::DELIVERY_ORDER,
            'officialReportId'  => 'required',
            'note'              => 'required',
            ]);

        $model->type                = $request->get('type');
        $model->official_report_id  = $request->get('officialReportId');
        $model->note                = $request->get('note');
        $model->total_coly          = $request->get('totalColy');

        if ($model->type == ResiStockCorrection::DELIVERY_ORDER) {
            $model->delivery_order_line_id      = $request->get('doCtId');
            $modelDo     = DeliveryOrderLine::find($model->delivery_order_line_id);
            $modelHeader = $modelDo->header;
            $doCtNumber  = $modelHeader->delivery_order_number;
            $resiId      = $modelDo->resi_header_id;
            $totalColy   = $modelDo->total_coly;
        }else if($model->type == ResiStockCorrection::CUSTOMER_TAKING){
            $model->customer_taking_transact_id = $request->get('doCtId');
            $modelCt    = CustomerTakingTransact::find($model->customer_taking_transact_id);
            $modelCtr   = $modelCt->customerTaking;
            $doCtNumber = $modelCt->customer_taking_transact_number;
            $resiId     = $modelCtr->resi_header_id;
            $totalColy  = $modelCt->coly_taken;
        }else{
            $model->resi_header_id = $request->get('resiHeaderId');
            $resiId     = $model->resi_header_id;
            $doCtNumber = '';
        }

        $now = new \DateTime();
        if (empty($id)) {
            $date                                = new \DateTime($request->get('date'));
            $model->branch_id                    = \Session::get('currentBranch')->branch_id;
            $model->resi_stock_correction_number = $this->getResiStockCorrectionNumber($model);
            $model->created_date                 = !empty($date) ? $date->format('Y-m-d H:i:s'):null;
            $model->created_by                   = \Auth::user()->id;
        } else {
            $model->last_updated_date = $now;
            $model->last_updated_by = \Auth::user()->id;
        }

        $modelResi = TransactionResiHeader::find($resiId);

        $check     = $this->checkExistStock($resiId, $model->branch_id);
        $resiStock = $check ? ResiStock::where('resi_header_id', '=', $resiId)->where('branch_id', '=', $model->branch_id)->first() : new ResiStock();

        if($model->type != ResiStockCorrection::CORRECTION_MINUS){
            $max       = $modelResi->totalColy() - $resiStock->coly;
            if ($model->total_coly > $max) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Coly Exceed! Maximum coly correction is '.$max. '. Check on resi stock.']);
            }

            $resiStock->branch_id      = $model->branch_id;
            $resiStock->resi_header_id = $resiId;

            if ($check) {
                $resiStock->coly              = $resiStock->coly + $model->total_coly;
                $resiStock->last_updated_date = $now;
                $resiStock->last_updated_by   = \Auth::user()->id;
            }else{
                $resiStock->coly              = $model->total_coly;
                $resiStock->created_date      = $now;
                $resiStock->created_by        = \Auth::user()->id;
                $resiStock->last_updated_date = $now;
                $resiStock->last_updated_by   = \Auth::user()->id;
            }
            $resiStock->save();
        }else{
            $max       = $resiStock->coly;
            if ($model->total_coly > $max) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Coly Exceed! Maximum coly correction is '.$max. '. Check on resi stock.']);
            }

            $resiStock->branch_id         = $model->branch_id;
            $resiStock->resi_header_id    = $resiId;
            $resiStock->coly              = $resiStock->coly - $model->total_coly;
            $resiStock->last_updated_date = $now;
            $resiStock->last_updated_by   = \Auth::user()->id;

            if($resiStock->coly <= 0){
                $resiStock->delete();
            }else{
                $resiStock->save();
            }
        }
        
        $modelOr                = OfficialReport::find($model->official_report_id);
        $modelOr->respon        = $model->type.' '.$model->resi_stock_correction_number;
        $modelOr->status        = OfficialReport::CLOSED;
        $modelOr->respon_date   = $now;
        $modelOr->respon_by     = \Auth::user()->id;
        $modelOr->save();

        $userNotif = NotificationService::getUserNotification([Role::WAREHOUSE_ADMIN, Role::WAREHOUSE_MANAGER]);
        foreach ($userNotif as $user) {
            $notif             = new Notification();
            $notif->branch_id  = \Session::get('currentBranch')->branch_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->category   = 'Resi Stock '.$model->type;
            $notif->message    = 'Resi Stock '.$modelResi->resi_number. ' from '.$doCtNumber .'. ' . $request->get('note');
            $notif->save();
        }
     
        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(self::URL.'/add/')->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('operational/menu.resi-stock-correction').' '.$model->delivery_order_number])
            );

        return redirect(self::URL);
    }

    protected function getOptionsType(){
        return [
            ResiStockCorrection::CORRECTION_PLUS,
            ResiStockCorrection::CORRECTION_MINUS,
            ResiStockCorrection::DELIVERY_ORDER,
            ResiStockCorrection::CUSTOMER_TAKING,
        ];
    }

    public function getJsonResi(Request $request)
    {
        $search   = $request->get('search');
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
        $arrResi = [];
        foreach ($query->get() as $resi) {
            $modelResi          = TransactionResiHeader::find($resi->resi_header_id);
            $resi->total_coly   = $modelResi->totalColy();
            $arrResi[]          = $resi;
        }
        return response()->json($arrResi);
    }

    protected function checkExistStock($resiId, $branchId){
        if (\DB::table('op.mst_stock_resi')
                ->where('mst_stock_resi.resi_header_id', '=', $resiId)
                ->where('mst_stock_resi.branch_id', '=', $branchId)
                ->count() > 0) {
            return true;
        }
        return false;
    }

    protected function getResiStockCorrectionNumber(ResiStockCorrection $model)
    {
        $branch      = MasterBranch::find(\Session::get('currentBranch')->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('op.trans_resi_stock_correction')
        ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
        ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
        ->count();

        return 'RSC.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }

    protected function getOptionsResiDo(){
        $lastMonth = new \DateTime();
        $lastMonth->sub(new \DateInterval('P300D'));
        $listResi = \DB::table('op.trans_delivery_order_line')
        ->select(
            'trans_delivery_order_line.delivery_order_line_id',
            'trans_delivery_order_line.delivery_cost',
            'trans_delivery_order_line.total_coly',
            'trans_resi_header.resi_header_id',
            'trans_resi_header.receiver_name',
            'trans_resi_header.receiver_address',
            'trans_resi_header.receiver_phone',
            'trans_resi_header.resi_number',
            'trans_resi_header.item_name',
            'trans_resi_header.description',
            'trans_delivery_order_header.delivery_order_number',
            'mst_delivery_area.delivery_area_name'
            )
        ->join('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_delivery_order_line.resi_header_id')
        ->join('op.trans_delivery_order_header', 'op.trans_delivery_order_header.delivery_order_header_id', '=', 'trans_delivery_order_line.delivery_order_header_id')
        ->join('op.mst_delivery_area', 'mst_delivery_area.delivery_area_id', '=', 'trans_resi_header.delivery_area_id')
        ->leftJoin('op.trans_resi_stock_correction', 'trans_resi_stock_correction.delivery_order_line_id', '=', 'trans_delivery_order_line.delivery_order_line_id')
        ->where('trans_delivery_order_header.status', '=', DeliveryOrderHeader::CLOSED)
        ->where('trans_delivery_order_header.delivery_start_time', '>=', $lastMonth->format('Y-m-d H:i:s'))
        ->where('trans_delivery_order_header.branch_id', '=', \Session::get('currentBranch')->branch_id)
        ->distinct()
        ->get();

        $arrResi = [];
        foreach($listResi as $resi) {
            $modelResi = TransactionResiHeader::find($resi->resi_header_id);
            $resi->customer_name = $modelResi->getCustomerName();
            $arrResi [] = $resi;
        }
        return $arrResi;
    }

    protected function getOptionsResiCt(){
        $lastMonth = new \DateTime();
        $lastMonth->sub(new \DateInterval('P30D'));

        $listResi = \DB::table('op.trans_customer_taking_transact')
        ->select(
            'trans_customer_taking_transact.customer_taking_transact_id',
            'trans_customer_taking_transact.customer_taking_transact_number',
            'trans_customer_taking_transact.coly_taken',
            'trans_customer_taking_transact.taker_name',
            'trans_customer_taking_transact.taker_address',
            'trans_customer_taking_transact.taker_phone',
            'trans_resi_header.resi_header_id',
            'trans_resi_header.receiver_name',
            'trans_resi_header.receiver_address',
            'trans_resi_header.receiver_phone',
            'trans_resi_header.resi_number',
            'trans_resi_header.item_name',
            'trans_resi_header.description',
            'mst_delivery_area.delivery_area_name'
            )
        ->leftJoin('op.trans_customer_taking', 'trans_customer_taking.customer_taking_id', '=', 'trans_customer_taking_transact.customer_taking_id')
        ->join('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_customer_taking.resi_header_id')
        ->leftjoin('op.mst_delivery_area', 'mst_delivery_area.delivery_area_id', '=', 'trans_resi_header.delivery_area_id')
        ->leftJoin('op.trans_resi_stock_correction', 'trans_resi_stock_correction.customer_taking_transact_id', '=', 'trans_customer_taking_transact.customer_taking_transact_id')
        ->where('trans_customer_taking_transact.created_date', '>=', $lastMonth->format('Y-m-d H:i:s'))
        ->where('trans_customer_taking_transact.branch_id', '=', \Session::get('currentBranch')->branch_id)
        ->distinct()
        ->get();

        $arrResi = [];
        foreach($listResi as $resi) {
            $modelResi = TransactionResiHeader::find($resi->resi_header_id);
            $resi->customer_name = $modelResi->getCustomerName();
            $arrResi [] = $resi;
        }
        return $arrResi;
    }
}
