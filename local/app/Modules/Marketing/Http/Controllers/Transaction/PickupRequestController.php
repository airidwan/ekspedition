<?php

namespace App\Modules\Marketing\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Marketing\Model\Transaction\PickupRequest;
use App\Modules\Operational\Http\Controllers\Transaction\PickupFormController;
use App\Modules\Marketing\Http\Controllers\Transaction\PickupRequestController;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Service\Penomoran;
use App\Modules\Operational\Service\Master\CustomerService;
use App\Notification;
use App\Service\TimezoneDateConverter;
use App\Service\NotificationService;
use App\Role;

class PickupRequestController extends Controller
{
    const RESOURCE = 'Marketing\Transaction\PickupRequest';
    const URL      = 'marketing/transaction/pickup-request';

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
        $query = $this->getQuery($request);

        return view('marketing::transaction.pickup-request.index', [
            'models'         => $query->paginate(10),
            'filters'        => $filters,
            'optionStatus'   => $this->getOptionsStatus(),
            'resource'       => self::RESOURCE,
            'url'            => self::URL,
        ]);
    }

    protected function getQuery(Request $request)
    {
        $filters = \Session::get('filters');
        $query = \DB::table('mrk.trans_pickup_request')
                        ->select('trans_pickup_request.*', 'mst_customer.customer_name as mst_customer_name')
                        ->leftJoin('op.mst_customer', 'mst_customer.customer_id', '=', 'trans_pickup_request.customer_id')
                        ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->orderBy('created_date', 'desc');

        if (!empty($filters['pickupRequestNumber'])) {
            $query->where('pickup_request_number', 'ilike', '%'.$filters['pickupRequestNumber'].'%');
        }

        if (!empty($filters['customerName'])) {
            $query->where(function($query) use ($filters) {
                $query->where('trans_pickup_request.customer_name', 'ilike', '%'.$filters['customerName'].'%')
                        ->orWhere('mst_customer.customer_name', 'ilike', '%'.$filters['customerName'].'%');
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', '=', $filters['status']);
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = TimezoneDateConverter::getServerDateTime($filters['dateFrom']);
            $query->where('pickup_request_time', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = TimezoneDateConverter::getServerDateTime($filters['dateTo']);
            $query->where('pickup_request_time', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        return $query;
    }

    public function printExcel(Request $request)
    {
        $query   = $this->getQuery($request);
        $filters = \Session::get('filters');

        \Excel::create(trans('marketing/menu.pickup-request'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('marketing/menu.pickup-request'));
                });

                $sheet->cells('A3:M3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('marketing/fields.pickup-request-number'),
                    trans('operational/fields.customer'),
                    trans('operational/fields.sender'),
                    trans('operational/fields.item-name'),
                    trans('operational/fields.total-coly'),
                    trans('operational/fields.weight'),
                    trans('operational/fields.dimension'),
                    trans('operational/fields.pickup-cost'),
                    trans('shared/common.date'),
                    trans('shared/common.time'),
                    trans('shared/common.status'),
                    trans('shared/common.note'),
                    trans('operational/fields.approved-note'),
                ]);

                $currentRow = 4;
                foreach($query->get() as $model) {
                    $date = !empty($model->pickup_request_time) ? \App\Service\TimezoneDateConverter::getClientDateTime($model->pickup_request_time) : null;

                    $data = [
                        $model->pickup_request_number,
                        $model->mst_customer_name,
                        $model->customer_name,
                        $model->item_name,
                        $model->total_coly,
                        number_format($model->weight, 2),
                        number_format($model->dimension, 6),
                        number_format($model->pickup_cost),
                        !empty($date) ? $date->format('d-M-Y') : '',
                        !empty($date) ? $date->format('H:i') : '',
                        $model->status,
                        $model->note,
                        $model->note_add,
                    ];

                    $sheet->row($currentRow++, $data);
                }

                $lastDataRow = $currentRow;
                $currentRow = $lastDataRow + 1;
                if (!empty($filters['pickupRequestNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('marketing/fields.pickup-request-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['pickupRequestNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['customerName'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.customer'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['customerName'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['status'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.status'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['status'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['dateFrom'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.date-from'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['dateFrom'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['dateTo'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.date-to'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['dateTo'], 'C', $currentRow);
                    $currentRow++;
                }

                $currentRow = $lastDataRow + 1;
                $this->addLabelDescriptionCell($sheet, trans('shared/common.date'), 'E', $currentRow);
                $this->addValueDescriptionCell($sheet, $this->now->format('d-m-Y'), 'F', $currentRow);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.user'), 'E', $currentRow + 1);
                $this->addValueDescriptionCell($sheet, \Auth::user()->full_name, 'F', $currentRow + 1);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.branch'), 'E', $currentRow + 2);
                $this->addValueDescriptionCell($sheet, \Session::get('currentBranch')->branch_name, 'F', $currentRow + 2);
            });

        })->export('xlsx');
    }

    protected function addLabelDescriptionCell($sheet, $value, $column, $row)
    {
        $sheet->cell($column.$row, function($cell) use($value) {
            $cell->setFont(['bold' => true]);
            $cell->setValue($value);
        });
    }

    protected function addValueDescriptionCell($sheet, $value, $column, $row)
    {
        $sheet->cell($column.$row, function($cell) use($value) {
            $cell->setValue($value);
        });
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new PickupRequest();
        $model->status = PickupRequest::OPEN;

        return view('marketing::transaction.pickup-request.add', [
            'title'        => trans('shared/common.add'),
            'model'        => $model,
            'optionStatus' => $this->getOptionsStatus(),
            'optionCustomer' => CustomerService::getActiveCustomer(),
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = PickupRequest::where('pickup_request_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        $data = [
            'title'        => trans('shared/common.edit'),
            'model'        => $model,
            'optionCustomer' => CustomerService::getActiveCustomer(),
            'optionStatus' => $this->getOptionsStatus(),
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
        ];

        if ($request->user()->can('access', [self::RESOURCE, 'update'])) {
            return view('marketing::transaction.pickup-request.add', $data);
        } else {
            return view('marketing::transaction.pickup-request.detail', $data);
        }
    }

    public function approve(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'approve'])) {
            abort(403);
        }

        $model = PickupRequest::where('pickup_request_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        return view('marketing::transaction.pickup-request.approve', [
            'title'        => trans('shared/common.approve'),
            'model'        => $model,
            'optionCustomer' => CustomerService::getActiveCustomer(),
            'optionStatus' => $this->getOptionsStatus(),
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? PickupRequest::where('pickup_request_id', '=', $id)->first() : new PickupRequest();

        $this->validate($request, [
            'callersName'   => 'required|max:150',
            'senderName'  => 'required|max:150',
            'address'       => 'required|max:250',
            'phoneNumber'   => 'required|max:50',
            'itemName'      => 'required|max:255',
            'totalColy'     => 'required|max:12',
            'note'          => 'required|max:250',
        ]);

        $timeString = $request->get('date').' '.$request->get('hours').':'.$request->get('minute');
        $time       = !empty($timeString) ? TimezoneDateConverter::getServerDateTime($timeString) : null;

        if(!empty($request->get('customerId'))){
            $model->customer_id   = intval($request->get('customerId'));
        }

        $model->pickup_request_time = !empty($time) ? $time->format('Y-m-d H:i:s'):null;
        $model->callers_name  = $request->get('callersName');
        $model->customer_name = $request->get('senderName');
        $model->address       = $request->get('address');
        $model->phone_number  = $request->get('phoneNumber');
        $model->item_name     = $request->get('itemName');
        $model->total_coly    = intval(str_replace(',', '', $request->get('totalColy')));
        $model->weight        = floatval(str_replace(',', '', $request->get('weight')));
        $model->dimension_long   = floatval(str_replace(',', '', $request->get('dimensionL')));
        $model->dimension_width  = floatval(str_replace(',', '', $request->get('dimensionW')));
        $model->dimension_height = floatval(str_replace(',', '', $request->get('dimensionH')));
        $model->dimension     = floatval(str_replace(',', '', $request->get('dimension')));
        $model->pickup_cost   = intval(str_replace(',', '', $request->get('pickupCost')));
        $model->note          = $request->get('note');
        $model->branch_id     = \Session::get('currentBranch')->branch_id;

        $now = new \DateTime();
        if (empty($id)) {
            $model->status       = PickupRequest::OPEN;
            $model->pickup_request_number = $this->getPickupRequestNumber($model);
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

        $userNotif = NotificationService::getUserNotification([Role::OPERATIONAL_ADMIN]);
        foreach ($userNotif as $user) {
            $notif             = new Notification();
            $notif->branch_id  = \Session::get('currentBranch')->branch_id;
            $notif->url        = PickupRequestController::URL.'/approve/'.$model->pickup_request_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->category   = 'Pickup Request Created';
            $notif->message    = 'Pickup Request '.$model->pickup_request_number. ' need approval';
            $notif->save();
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('marketing/menu.pickup-request').' '.$model->pickup_request_number])
        );

        return redirect(self::URL);
    }

    public function saveApprove(Request $request)
    {
        // var_dump($request->all());exit();
        $id = intval($request->get('id'));
        $model = !empty($id) ? PickupRequest::where('pickup_request_id', '=', $id)->first() : new PickupRequest();

        $this->validate($request, [
            'noteAdd'          => 'required',
        ]);

        if ($request->get('btn-approve') !== null) {
            $model->status      = PickupRequest::APPROVED;
        }

        $model->note_add    = $request->get('noteAdd');
        $model->pickup_cost = intval(str_replace(',', '', $request->get('pickupCost')));

        $now = new \DateTime();
        $model->last_updated_date = $now;
        $model->last_updated_by   = \Auth::user()->id;

        try {
            $model->save();            
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('marketing/menu.pickup-request').' '.$model->pickup_request_number])
        );

        return redirect(self::URL);
    }

    public function printPdfDetail(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $filters = \Session::get('filters');
        $model   = PickupRequest::find($id);

        if ($model === null) {
            abort(404);
        }

        $header = view('print.header-pdf', ['title' => trans('marketing/menu.pickup-request')])->render();
        \PDF::setHeaderCallback(function($pdf) use ($header) {
            $pdf->writeHTML($header);
        });
        $html = view('marketing::transaction.pickup-request.print-pdf-detail', [
            'model'  => $model,
            'filters' => $filters,
        ])->render();

        \PDF::SetTitle(trans('marketing/menu.pickup-request').' - '.$model->pickup_request_number);
        \PDF::SetMargins(5, 20, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A5');
        \PDF::writeHTML($html);
        \PDF::Output($model->pickup_request_number.'.pdf');
        \PDF::reset();
    }

    public function cancelPr(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'cancel'])) {
            abort(403);
        }

        $model = PickupRequest::find($request->get('id'));
        if ($model === null) {
            abort(404);
        }

        $model->status = PickupRequest::CANCELED;
        $model->last_updated_date = new \DateTime();
        $model->last_updated_by   = \Auth::user()->id;

        $model->save();

        $userNotif = NotificationService::getUserNotification([Role::OPERATIONAL_ADMIN]);
        foreach ($userNotif as $user) {
            $notif             = new Notification();
            $notif->branch_id  = \Session::get('currentBranch')->branch_id;
            $notif->url        = PickupRequestController::URL.'/edit/'.$model->pickup_request_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->category   = 'Canceled Pickup Request';
            $notif->message    = 'Canceled Pickup Request'.$model->pickup_request_number. '. ' . $request->get('reason', '');
            $notif->save();
        }

        $request->session()->flash(
            'successMessage',
            trans('purchasing/fields.canceled-message', ['variable' => trans('marketing/menu.pickup-request').' '.$model->pickup_request_number])
            );

        return redirect(self::URL);
    }

    protected function getPickupRequestNumber(PickupRequest $model)
    {
        $branch      = MasterBranch::find(\Session::get('currentBranch')->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('mrk.trans_pickup_request')
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->where('branch_id', '=', $branch->branch_id)
                            ->count();

        return 'PR.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }

    protected function getOptionsStatus()
    {
        return [
            PickupRequest::OPEN,
            PickupRequest::APPROVED,
            PickupRequest::CLOSED,
            PickupRequest::CANCELED,
        ];
    }
}
