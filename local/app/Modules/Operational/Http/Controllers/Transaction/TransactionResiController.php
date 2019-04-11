<?php

namespace App\Modules\Operational\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Accountreceivables\Http\Controllers\Transaction\ReceiptController;
use App\Modules\Marketing\Model\Transaction\PickupRequest;
use App\Modules\Operational\Model\Transaction\PickupFormHeader;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Transaction\TransactionResiLine;
use App\Modules\Operational\Model\Transaction\TransactionResiLineVolume;
use App\Modules\Operational\Model\Transaction\TransactionResiNego;
use App\Modules\Operational\Model\Master\MasterRoute;
use App\Modules\Operational\Model\Master\MasterShippingPrice;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Model\Transaction\ResiStock;
use App\Modules\Operational\Model\Master\MasterCustomer;
use App\Modules\Operational\Model\Master\DetailCustomerBranch;
use App\Modules\Accountreceivables\Model\Transaction\Invoice;
use App\Modules\Operational\Service\Master\CustomerService;
use App\Modules\Operational\Service\Master\RouteService;
use App\Modules\Operational\Service\Master\UnitService;
use App\Modules\Operational\Service\Transaction\HistoryResiService;
use App\Modules\Inventory\Service\Master\WarehouseService;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Modules\Generalledger\Service\Master\AccountCombinationService;
use App\Modules\Generalledger\Service\Master\JournalService;
use App\Modules\Generalledger\Model\Master\SettingJournal;
use App\Service\Penomoran;
use App\Service\NotificationService;
use App\Notification;
use App\Role;

class TransactionResiController extends Controller
{
    const RESOURCE = 'Operational\Transaction\TransactionResi';
    const URL      = 'operational/transaction/transaction-resi';
    const DESC     = 'CUSTOMER';

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
        $query   = $this->getQuery($request, $filters);

        return view('operational::transaction.transaction-resi.index', [
            'models' => $query->paginate(10),
            'filters' => $filters,
            'resource' => self::RESOURCE,
            'url' => self::URL,
            'optionRoute' => $this->getOptionsRoute(),
            'optionRegion' => $this->getOptionsRegion(),

            'optionPayment' => [
                TransactionResiHeader::CASH,
                TransactionResiHeader::BILL_TO_SENDER,
                TransactionResiHeader::BILL_TO_RECIEVER,
            ],
            'optionStatus' => [
                TransactionResiHeader::INCOMPLETE,
                TransactionResiHeader::INPROCESS,
                TransactionResiHeader::APPROVED,
                TransactionResiHeader::CANCELED,
            ]
        ]);
    }

    protected function getOptionsRoute()
    {
        return \DB::table('op.mst_route')
                    ->where('mst_route.active', '=', 'Y')
                    ->where('mst_route.city_start_id', '=', \Session::get('currentBranch')->city_id)
                    ->orderBy('route_code', 'asc')
                    ->get();
    }

    protected function getOptionsRegion()
    {
        return \DB::table('op.mst_region')
                    ->where('active', '=', 'Y')
                    ->orderBy('region_name', 'asc')
                    ->get();
    }

    public function getQuery(Request $request, $filters){
        $query   = \DB::table('op.trans_resi_header')
                        ->select('trans_resi_header.resi_header_id')
                        ->leftJoin('op.mst_customer as customer_sender', 'trans_resi_header.customer_id', '=', 'customer_sender.customer_id')
                        ->leftJoin('op.mst_customer as customer_receiver', 'trans_resi_header.customer_receiver_id', '=', 'customer_receiver.customer_id')
                        ->leftJoin('op.mst_route', 'trans_resi_header.route_id', '=', 'mst_route.route_id')
                        ->leftJoin('op.mst_city', 'mst_route.city_end_id', '=', 'mst_city.city_id')
                        ->leftJoin('op.dt_region_city', 'mst_city.city_id', '=', 'dt_region_city.city_id')
                        ->leftJoin('op.mst_region', 'dt_region_city.region_id', '=', 'mst_region.region_id')
                        ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->orderBy('trans_resi_header.created_date', 'desc');

        if (!empty($filters['resiNumber'])) {
            $query->where('resi_number', 'ilike', '%'.$filters['resiNumber'].'%');
        }

        if (!empty($filters['customer'])) {
            $query->where(function($query) use ($filters) {
                $query->where('customer_sender.customer_name', 'ilike', '%'.$filters['customer'].'%')
                        ->orWhere('customer_receiver.customer_name', 'ilike', '%'.$filters['customer'].'%');
            });
        }

        if (!empty($filters['sender'])) {
            $query->where('sender_name', 'ilike', '%'.$filters['sender'].'%');
        }

        if (!empty($filters['receiver'])) {
            $query->where('receiver_name', 'ilike', '%'.$filters['receiver'].'%');
        }

        if (!empty($filters['route'])) {
            $query->whereRaw('mst_route.route_id IN ('. implode(', ', $filters['route']) .')');
        }

        if (!empty($filters['region'])) {
            $query->whereNotNull('mst_region.region_id')->whereRaw('mst_region.region_id IN ('. implode(', ', $filters['region']) .')');
        }

        if (!empty($filters['payment'])) {
            $query->where('payment', '=', $filters['payment']);
        }

        if (!empty($filters['insurance'])) {
            if ($filters['insurance'] == 'insurance') {
                $query->where('insurance', '=', true);
            } else {
                $query->where(function($query) {
                    $query->whereNull('insurance')
                            ->orWhere('insurance', '=', false);
                });
            }
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $query->where('trans_resi_header.created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $query->where('trans_resi_header.created_date', '<=', $date->format('Y-m-d 23:59:59'));
        }

        if (!empty($filters['status'])) {
            $query->where('trans_resi_header.status', '=', $filters['status']);
        }
        return $query;
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new TransactionResiHeader();
        $model->status = TransactionResiHeader::INCOMPLETE;
        $model->type = TransactionResiHeader::REGULER;

        return view('operational::transaction.transaction-resi.add', [
            'title' => trans('shared/common.add'),
            'model' => $model,
            'url' => self::URL,
            'resource' => self::RESOURCE,
            'optionPickupRequest' => $this->getOptionPickupRequest(),
            'optionCustomer' => CustomerService::getActiveCustomer(),
            'optionRoute' => RouteService::getActiveRoute(),
            'optionUnit' => [],
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = TransactionResiHeader::where('resi_header_id', '=', $id)->first();
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
            'optionPickupRequest' => $this->getOptionPickupRequest(),
            'optionCustomer' => CustomerService::getActiveCustomer(),
            'optionRoute' => RouteService::getActiveRoute(),
            'optionUnit' => UnitService::getActiveRouteUnit($model->route_id),
        ];

        if ($request->user()->can('access', [self::RESOURCE, 'update'])) {
            return view('operational::transaction.transaction-resi.add', $data);
        } else {
            return view('operational::transaction.transaction-resi.detail', $data);
        }
    }

    public function printPdfIndex(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $filters = $request->session()->get('filters');
        $query   = $this->getQuery($request, $filters);

        $html = view('operational::transaction.transaction-resi.print-pdf-index', ['models' => $query->get()])->render();

        \PDF::SetTitle(trans('operational/menu.resi').' '.\Session::get('currentBranch')->branch_code);
        \PDF::SetMargins(5, 5, 5, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'A4');
        \PDF::writeHTML($html);
        \PDF::Output(trans('operational/menu.resi').' '.\Session::get('currentBranch')->branch_code.'.pdf');
        \PDF::reset();
    }

    public function printExcelIndex(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }
        
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters);

        \Excel::create(trans('operational/menu.resi'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.resi'));
                });

                $sheet->cells('A3:Y3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('operational/fields.resi-number'),
                    trans('operational/fields.date'),
                    trans('operational/fields.customer'),
                    trans('operational/fields.sender'),
                    trans('operational/fields.address'),
                    trans('operational/fields.customer'),
                    trans('operational/fields.receiver'),
                    trans('operational/fields.address'),
                    trans('operational/fields.route'),
                    trans('operational/fields.payment'),
                    trans('operational/fields.insurance'),
                    trans('operational/fields.item-name'),
                    trans('operational/fields.item-unit'),
                    trans('operational/fields.coly'),
                    trans('operational/fields.weight'),
                    trans('operational/fields.total-price'),
                    trans('operational/fields.volume'),
                    trans('operational/fields.total-price'),
                    trans('operational/fields.qty-unit'),
                    trans('operational/fields.total-price'),
                    trans('operational/fields.total-amount'),
                    trans('operational/fields.discount'),
                    trans('shared/common.total'),
                    trans('operational/fields.description'),
                    trans('shared/common.status'),
                ]);

                $currentRow = 4;
                foreach($query->get() as $model) {
                    $model = TransactionResiHeader::find($model->resi_header_id);
                    $resiDate = !empty($model->created_date) ? new \DateTime($model->created_date) : null;

                    $data = [
                        $model->resi_number,
                        $resiDate !== null ? $resiDate->format('d-m-Y') : '',
                        !empty($model->customer) ? $model->customer->customer_name : '',
                        $model->sender_name,
                        $model->sender_address,
                        !empty($model->customerReceiver) ? $model->customerReceiver->customer_name : '',
                        $model->receiver_name,
                        $model->receiver_address,
                        $model->route !== null ? $model->route->route_code : '',
                        $model->getSingkatanPayment(),
                        $model->insurance ? 'V' : 'X',
                        $model->itemName(),
                        $model->itemUnit(),
                        $model->totalColy(),
                        $model->totalWeight(),
                        $model->totalWeightPrice(),
                        $model->totalVolume(),
                        $model->totalVolumePrice(),
                        $model->totalUnit(),
                        $model->totalUnitPrice(),
                        $model->totalAmount(),
                        $model->discount,
                        $model->total(),
                        $model->description,
                        $model->status,
                    ];

                    $sheet->row($currentRow++, $data);
                }

                $lastDataRow = $currentRow;
                $currentRow = $lastDataRow + 1;
                if (!empty($filters['resiNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.resi-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['resiNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['customer'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.customer'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['customer'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['sender'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.sender'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['sender'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['receiver'])) { 
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.receiver'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['receiver'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['route'])) {
                    $route = \DB::table('op.mst_route')->where('route_id', '=', $filters['route'])->first();
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.route'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $route->route_code, 'C', $currentRow);
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
                if (!empty($filters['payment'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.payment'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['payment'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['insurance'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.insurance'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['insurance'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['status'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.status'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['payment'], 'C', $currentRow);
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

    public function printPdf(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $model = TransactionResiHeader::find($id);
        if ($model === null) {
            abort(404);
        }

        $html = view('operational::transaction.transaction-resi.print-pdf', ['model' => $model, 'tanpaBiaya' => false])->render();

        \PDF::SetTitle(trans('operational/menu.resi').' '.$model->resi_number);
        \PDF::SetMargins(0, 4, 2, 0);
        \PDF::SetFont('Helvetica', '', '', '', 'false');
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'RESI');
        \PDF::writeHTML($html);
        \PDF::Output($model->resi_number.'.pdf');
        \PDF::reset();
    }

    public function printPdfTanpaBiaya(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $model = TransactionResiHeader::find($id);
        if ($model === null) {
            abort(404);
        }

        $html = view('operational::transaction.transaction-resi.print-pdf', ['model' => $model, 'tanpaBiaya' => true])->render();

        \PDF::SetTitle(trans('operational/menu.resi').' '.$model->resi_number);
        \PDF::SetMargins(0, 4, 2, 0);
        \PDF::SetAutoPageBreak(TRUE, 10);
        \PDF::AddPage('L', 'RESI');
        \PDF::writeHTML($html);
        \PDF::Output('No Price '.$model->resi_number.'.pdf');
        \PDF::reset();
    }

    public function printVoucher(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        $model = TransactionResiHeader::find($id);
        if ($model === null) {
            abort(404);
        }

        $html = view('operational::transaction.transaction-resi.print-voucher', ['model' => $model])->render();

        $resolution= array(37, 75);
        \PDF::SetTitle(trans('operational/menu.resi').' '.$model->resi_number);
        \PDF::SetMargins(1, 1, 1, 1);
        \PDF::SetAutoPageBreak(TRUE, 0);
        \PDF::AddPage('L', $resolution);
        \PDF::writeHTML($html);
        \PDF::Output('Voucher '.$model->resi_number.'.pdf');
        \PDF::reset();
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? TransactionResiHeader::find($id) : new TransactionResiHeader();

        if ($request->get('btn-approve') !== null && $model->status == TransactionResiHeader::APPROVED) {
            return redirect(self::URL . '/edit/' . $model->resi_header_id)->withInput($request->all())->withErrors(['errorMessage' => 'Resi number '.$model->resi_number.' now status is '.$model->status. '. Please refresh your page!']);
        }

        if ($request->get('btn-booking-number') !== null) {
            $this->populateModelCustomer($request, $model);

            $model->branch_id = \Session::get('currentBranch')->branch_id;

            if (empty($model->status)) {
                $model->status = TransactionResiHeader::INCOMPLETE;
            }

            if (empty($id)) {
                $model->created_date = $this->now;
                $model->created_by = \Auth::user()->id;
            } else {
                $model->last_updated_date = $this->now;
                $model->last_updated_by = \Auth::user()->id;
            }

            if (empty($model->resi_number)) {
                $model->resi_number = $this->getResiNumber($model);
            }

            try {
                $model->save();
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'constraint_resi_number') !== false) {
                    return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Too many request resi number. Please save/approve this resi again!']);
                }else{
                    return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
                }
            }

            $process = empty($id) ? 'Create Resi' : 'Update Resi';
            HistoryResiService::saveHistory($model->resi_header_id, $process);

            $request->session()->flash(
                'successMessage',
                trans('shared/common.saved-message', ['variable' => trans('operational/menu.resi').' '.$model->resi_number])
            );

            return redirect(self::URL);
        }

        $error = $this->validateRequest($request);
        if (!empty($error)) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $error]);
        }

        $error = $this->populateModelResiHeader($request, $model, $id);
        if (!empty($error)) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $error]);
        }

        $this->deleteAllLineVolume($model);
        $error = $this->populateModelResiLineDetail($request, $model, $id);
        if (!empty($error)) {
            return redirect(self::URL . '/edit/' . $model->resi_header_id)->withInput($request->all())->withErrors(['errorMessage' => $error]);
        }

        $error = $this->populateModelResiLineUnit($request, $model, $id);
        if (!empty($error)) {
            return redirect(self::URL . '/edit/' . $model->resi_header_id)->withInput($request->all())->withErrors(['errorMessage' => $error]);
        }

        if ($request->get('btn-approve') === null && !$this->isValidNegoResi($request, $model)) {
            $process = empty($id) ? 'Create Resi' : 'Update Resi';
            HistoryResiService::saveHistory($model->resi_header_id, $process);
        }

        $error = $this->negoResi($request, $model, $id);
        if (!empty($error)) {
            return redirect(self::URL . '/edit/' . $model->resi_header_id)->withInput($request->all())->withErrors(['errorMessage' => $error]);
        }

        $error = $this->approveResi($request, $model, $id);
        if (!empty($error)) {
            return redirect(self::URL . '/edit/' . $model->resi_header_id)->withInput($request->all())->withErrors(['errorMessage' => $error]);
        }

        if ($request->get('btn-approve') === null) {
            $request->session()->flash(
                'successMessage',
                trans('shared/common.saved-message', ['variable' => trans('operational/menu.resi').' '.$model->resi_number])
            );
            return redirect(self::URL);

        } else {
            $request->session()->flash(
                'approvedMessage',
                trans('shared/common.approved-message', ['variable' => trans('operational/menu.resi').' '.$model->resi_number])
            );
            return redirect(self::URL . '/edit/' . $model->resi_header_id);
        }
    }

    protected function validateRequest(Request $request)
    {
        $this->validate($request, [
            'routeId' => 'required',
            'senderName' => 'required|max:150',
            'senderAddress' => 'required',
            'senderPhone' => 'required|max:150',
            'receiverName' => 'required|max:150',
            'receiverAddress' => 'required',
            'receiverPhone' => 'required|max:150',
            'payment' => 'required|max:150',
        ]);

        if ($this->isItemNameEmpty($request) && $request->get('type') != TransactionResiHeader::CARTER) {
            $this->validate($request, ['itemNameHeader' => 'required']);
        }

        if (!empty($request->get('negoPrice')) && empty($request->get('requestedNote'))) {
            return 'Note/description request nego required on tab nego';
        }

        $lineDetailId = $request->get('lineDetailId');
        $lineUnitId = $request->get('lineUnitId');
        if (empty($lineDetailId) && empty($lineUnitId)) {
            return 'You must insert minimal of a line detail or unit';
        }

        if ($request->get('type') == TransactionResiHeader::CARTER && $request->get('btn-approve') !== null) {
            return 'Cannot approve resi carter';
        } elseif ($request->get('type') == TransactionResiHeader::CARTER && empty($request->get('negoPrice'))) {
            return 'Price Nego is required';
        }
    }

    protected function isItemNameEmpty(Request $request)
    {
        if (!empty($request->get('itemNameHeader'))) {
            return false;
        }

        for ($i=0; $i < count($request->get('lineDetailId')); $i++) {
            if (!empty($request->get('itemName')[$i])) {
                return false;
            }
        }

        return true;
    }

    protected function populateModelResiHeader(Request $request, TransactionResiHeader $model, $id)
    {
        $pickupRequestId = !empty($request->get('pickupRequestId')) ? $request->get('pickupRequestId') : null;
        $model->pickup_request_id = $pickupRequestId;

        $this->populateModelCustomer($request, $model);

        $model->route_id = $request->get('routeId');
        $model->item_name = str_replace("'", "`", $request->get('itemNameHeader'));
        $model->description = str_replace("'", "`", $request->get('description'));
        $model->payment = $request->get('payment');
        $model->type = $request->get('type');
        $model->insurance = !empty($request->get('insurance'));

        $model->branch_id = \Session::get('currentBranch')->branch_id;

        $route = MasterRoute::find($model->route_id);
        $model->minimum_rates = $route !== null ? $route->minimum_rates : 0;

        if (empty($model->status)) {
            $model->status = TransactionResiHeader::INCOMPLETE;
        }

        if (empty($id)) {
            $model->created_date = $this->now;
            $model->created_by = \Auth::user()->id;
        } else {
            $model->last_updated_date = $this->now;
            $model->last_updated_by = \Auth::user()->id;
        }

        if (empty($model->resi_number)) {
            $model->resi_number = $this->getResiNumber($model);
        }

        try {
            $model->save();
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'constraint_resi_number') !== false) {
                return 'Too many request resi number. Please save/approve this resi again!';
            }else{
                return $e->getMessage();
            }
        }
    }

    protected function populateModelCustomer(Request $request, TransactionResiHeader $model)
    {
        $senderName     = str_replace("'", "`", $request->get('senderName'));
        $senderAddress  = str_replace("'", "`", $request->get('senderAddress'));
        $senderPhone    = str_replace("'", "`", $request->get('senderPhone'));
        
        $receiverName     = str_replace("'", "`", $request->get('receiverName'));
        $receiverPhone    = str_replace("'", "`", $request->get('receiverPhone'));
        $receiverAddress  = str_replace("'", "`", $request->get('receiverAddress'));

        if (!empty($request->get('customerId'))) {
            $model->customer_id = $request->get('customerId');
        } else {
            if (!empty($request->get('saveToCustomerSender'))) {
                $customer = $this->getOrCreateCustomer($senderName, $senderAddress, $senderPhone);
                $model->customer_id = $customer->customer_id;
            }else{
                $model->customer_id = null;
            }
        }

        $model->sender_name = $senderName;
        $model->sender_address = $senderAddress;
        $model->sender_phone = $senderPhone;

        if (!empty($request->get('customerReceiverId'))) {
            $model->customer_receiver_id = $request->get('customerReceiverId');
        } else {
            if (!empty($request->get('saveToCustomerReceiver'))) {
                $customer = $this->getOrCreateCustomer($receiverName, $receiverAddress, $receiverPhone);
                $model->customer_receiver_id = $customer->customer_id;
            }else{
                $model->customer_receiver_id = null;
            }
        }

        $model->receiver_name = $receiverName;
        $model->receiver_address = $receiverAddress;
        $model->receiver_phone = $receiverPhone;
    }

    protected function getOrCreateCustomer($customerName, $customerAddress, $customerPhone)
    {
        $customer = MasterCustomer::where('customer_name', '=', $customerName)->orderBy('customer_id')->first();
        if ($customer !== null) {
            $customer->active = 'Y';
            $isbranchActive = false;
            foreach($customer->customerBranch as $customerBranch) {
                if ($customerBranch->branch_id == \Session::get('currentBranch')->branch_id) {
                    $isbranchActive = true;
                }
            }

            if (!$isbranchActive) {
                $newCustomerBranch = new DetailCustomerBranch();
                $newCustomerBranch->customer_id = $customer->customer_id;
                $newCustomerBranch->branch_id = \Session::get('currentBranch')->branch_id;
                $newCustomerBranch->active = 'Y';
                $newCustomerBranch->created_date = $this->now;
                $newCustomerBranch->created_by = \Auth::user()->id;
                $newCustomerBranch->save();
            }

        } else {
            $count    = \DB::table('op.mst_customer')->where('branch_id_insert', '=', \Session::get('currentBranch')->branch_id)->count();
            $customer = new MasterCustomer();
            $customer->customer_code    = 'C.'.\Session::get('currentBranch')->branch_code.'.'.Penomoran::getStringNomor($count+1, 4);
            $customer->customer_name    = $customerName;
            $customer->address          = $customerAddress;
            $customer->phone_number     = $customerPhone;
            $customer->branch_id_insert = \Session::get('currentBranch')->branch_id;
            $customer->active           = 'Y';

            $customer->subaccount_code  = MasterCoa::NONAME_SUB_ACCOUNT;

            $customer->save();

            $newCustomerBranch = new DetailCustomerBranch();
            $newCustomerBranch->customer_id = $customer->customer_id;
            $newCustomerBranch->branch_id = \Session::get('currentBranch')->branch_id;
            $newCustomerBranch->active = 'Y';
            $newCustomerBranch->created_date = $this->now;
            $newCustomerBranch->created_by = \Auth::user()->id;
            $newCustomerBranch->save();

            // $modelCoa = new MasterCoa();

            // $modelCoa->description = $customerName.' ('.self::DESC.')';
            // $modelCoa->segment_name = MasterCoa::SUB_ACCOUNT;

            // $modelCoa->coa_code = MasterCoa::NONAME_SUB_ACCOUNT;
            // $modelCoa->created_date = $this->now;
            // $modelCoa->created_by = \Auth::user()->id;
            // $modelCoa->active = 'Y';

            // $modelCoa->save();
        }

        return $customer;
    }

    protected function deleteAllLineVolume(TransactionResiHeader $model)
    {
        foreach ($model->line as $line) {
            $line->lineVolume()->delete();
        }
    }

    protected function populateModelResiLineDetail(Request $request, TransactionResiHeader $model, $id)
    {
        $rateKg = $model->route !== null ? $model->route->rate_kg : 0;
        $rateM3 = $model->route !== null ? $model->route->rate_m3 : 0;

        /** delete all line detail **/
        $model->lineDetail()->delete();

        /** add line detail **/
        for ($i=0; $i < count($request->get('lineDetailId')); $i++) {
            $line =  new TransactionResiLine();
            $line->resi_header_id = $model->resi_header_id;
            $line->item_name = str_replace("'", "`", $request->get('itemName')[$i]);
            $line->coly = intval(str_replace(',', '', $request->get('coly')[$i]));
            $line->qty_weight = intval(str_replace(',', '', $request->get('qtyWeight')[$i]));
            $line->weight_unit = floatval(str_replace(',', '', $request->get('weight')[$i]));
            $line->weight = floatval(str_replace(',', '', $request->get('totalWeightLine')[$i]));
            $line->price_weight = ceil($line->weight * $rateKg);

            if (empty($id)) {
                $line->created_date = $this->now;
                $line->created_by = \Auth::user()->id;
            } else {
                $line->last_updated_date = $this->now;
                $line->last_updated_by = \Auth::user()->id;
            }

            try {
                $line->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }

            /** add line volume **/
            $totalPriceVolume = 0;
            foreach (json_decode($request->get('lineVolume')[$i]) as $postLineVolume) {
                $lineVolume = new TransactionResiLineVolume();
                $lineVolume->resi_line_id = $line->resi_line_id;
                $lineVolume->qty_volume = $postLineVolume->qty;
                $lineVolume->dimension_long = $postLineVolume->dimensionL;
                $lineVolume->dimension_width = $postLineVolume->dimensionW;
                $lineVolume->dimension_height = $postLineVolume->dimensionH;
                $lineVolume->volume = $postLineVolume->volume / $postLineVolume->qty;
                $lineVolume->total_volume = $postLineVolume->volume;
                $lineVolume->price_volume = $lineVolume->total_volume * $rateM3;

                $totalPriceVolume += $lineVolume->price_volume;

                if (empty($id)) {
                    $lineVolume->created_date = $this->now;
                    $lineVolume->created_by = \Auth::user()->id;
                }else{
                    $lineVolume->last_updated_date = $this->now;
                    $lineVolume->last_updated_by = \Auth::user()->id;
                }

                try {
                    $lineVolume->save();
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            }

            /** line.total_price **/
            $line->total_price = $totalPriceVolume > $line->price_weight ? $totalPriceVolume : $line->price_weight;

            try {
                $line->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
    }

    protected function populateModelResiLineUnit(Request $request, TransactionResiHeader $model, $id)
    {
        /** delete line unit **/
        $model->line()->whereNotNull('unit_id')->delete();

        /** add line unit **/
        for ($i=0; $i < count($request->get('lineUnitId')); $i++) {
            $unit = MasterShippingPrice::find(intval($request->get('unitId')[$i]));

            $line =  new TransactionResiLine();
            $line->resi_header_id = $model->resi_header_id;
            $line->unit_id = $unit->shipping_price_id;
            $line->item_name = $request->get('itemNameUnit')[$i];
            $line->coly = intval($request->get('totalUnit')[$i]);
            $line->total_unit = intval($request->get('totalUnit')[$i]);
            $line->total_price = intval($line->total_unit * $unit->delivery_rate);

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
                return $e->getMessage();
            }
        }
    }

    protected function negoResi(Request $request, TransactionResiHeader $model, $id)
    {
        if ($request->get('btn-approve') !== null) {
            return;
        }

        if (!$this->isValidNegoResi($request, $model)) {
            return;
        }

        /** nege resi **/
        $negoPrice = intval(str_replace(',', '', $request->get('negoPrice')));
        $nego = new TransactionResiNego();
        $nego->resi_header_id = $model->resi_header_id;
        $nego->nego_price = $negoPrice;
        $nego->requested_note = $request->get('requestedNote');
        $nego->created_date = $this->now;
        $nego->created_by = \Auth::user()->id;

        try {
            $nego->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        /** set status jadi inprocess **/
        $model->status = TransactionResiHeader::INPROCESS;
        try {
            $model->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        HistoryResiService::saveHistory($model->resi_header_id, 'Nego Resi', 'Nego price: '.number_format($nego->nego_price).'. note: '.$nego->requested_note);

        /** notifikasi nego **/
        NotificationService::createNotification(
            'Resi Negotiation Request',
            'Resi ' . $model->resi_number . '. '.$nego->requested_note,
            ApproveNegoResiController::URL.'/edit/'.$model->resi_header_id,
            [Role::BRANCH_MANAGER]
        );
    }

    protected function isValidNegoResi(Request $request, TransactionResiHeader $model)
    {
        if ($model->type == TransactionResiHeader::CARTER) {
            return true;
        }

        if ($request->user()->cannot('access', [self::RESOURCE, 'nego'])) {
            return false;
        }

        if (!$model->isIncomplete()) {
            return false;
        }

        if (empty($request->get('negoPrice'))) {
            return false;
        }

        return true;
    }

    protected function approveResi(Request $request, TransactionResiHeader $model, $id)
    {
        if ($request->get('btn-approve') === null) {
            return;
        }

        /** set status resi jadi approved **/
        $model->status = TransactionResiHeader::APPROVED;
        try {
            $model->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        /** add resi stock **/
        $resiStock = new ResiStock();
        $resiStock->resi_header_id = $model->resi_header_id;
        $resiStock->branch_id = $model->branch_id;
        $resiStock->coly = $model->totalColy();
        $resiStock->created_date = $this->now;
        $resiStock->created_by = \Auth::user()->id;
        $resiStock->last_updated_date = $this->now;
        $resiStock->last_updated_by = \Auth::user()->id;

        try {
            $resiStock->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        $error = $this->createInvoiceResi($model);
        if (!empty($error)) {
            return $error;
        }

        $error = $this->createInvoicePickup($model);
        if (!empty($error)) {
            return $error;
        }

        HistoryResiService::saveHistory($model->resi_header_id, 'Approve Resi');
    }

    protected function createInvoiceResi(TransactionResiHeader $model)
    {
        $resi            = TransactionResiHeader::find($model->resi_header_id);
        $invoice         = new Invoice();
        $invoice->status = Invoice::APPROVED;
        $invoice->type   = Invoice::INV_RESI;

        if ($model->isBillToReceiver()) {
            if (!empty($model->customer_receiver_id)) {
                    $invoice->customer_id = $model->customer_receiver_id;
            }

            $invoice->bill_to = !empty($model->customerReceiver) ? $model->customerReceiver->customer_name : $model->receiver_name;
            $invoice->bill_to_address = $model->receiver_address;
            $invoice->bill_to_phone = $model->receiver_phone;
        } else {
            if (!empty($model->customer_id)) {
                    $invoice->customer_id = $model->customer_id;
            }

            $invoice->bill_to = !empty($model->customer) ? $model->customer->customer_name : $model->sender_name;
            $invoice->bill_to_address = $model->sender_address;
            $invoice->bill_to_phone = $model->sender_phone;
        }

        $invoice->branch_id        = $model->branch_id;
        $invoice->created_date     = $this->now;
        $invoice->created_by       = \Auth::user()->id;
        $invoice->invoice_number   = $this->getInvoiceNumber($invoice);
        $invoice->resi_header_id   = $model->resi_header_id;
        $invoice->amount           = $resi->total();
        $invoice->current_discount = 1;

        try {
            $invoice->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        $error = $this->createJournalInvoiceResi($invoice);
        if (!empty($error)) {
            return $error;
        }

        /** notifikasi invoice ke kasir **/
        if ($model->isCash() && $model->isReguler()) {
            NotificationService::createNotification(
                'Invoice Resi Cash',
                'Invoice ' . $invoice->invoice_number . ' terbentuk untuk Resi ' . $model->resi_number . '.',
                ReceiptController::URL.'/add',
                [Role::CASHIER]
            );
        }
    }

    protected function createJournalInvoiceResi(Invoice $model)
    {
        $invoice       = Invoice::find($model->invoice_id);
        $resi          = $invoice->resi;
        $journalHeader = new JournalHeader();

        $journalHeader->category       = JournalHeader::INVOICE_RESI;
        $journalHeader->period         = new \DateTime($this->now->format('Y-m-1'));
        $journalHeader->status         = JournalHeader::OPEN;
        $journalHeader->description    = 'Invoice Number: '.$invoice->invoice_number.'. Resi Number: '.$resi->resi_number;
        $journalHeader->branch_id      = $invoice->branch_id;
        $journalHeader->journal_date   = $this->now;
        $journalHeader->created_date   = $this->now;
        $journalHeader->created_by     = \Auth::user()->id;
        $journalHeader->journal_number = JournalService::getJournalNumber($journalHeader);

        try {
            $journalHeader->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        /** PIUTANG USAHA **/
        $settingCoa  = SettingJournal::where('setting_name', SettingJournal::PIUTANG_USAHA)->first();
        $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

        $line = new JournalLine();
        $line->journal_header_id      = $journalHeader->journal_header_id;
        $line->account_combination_id = $combination->account_combination_id;
        $line->debet                  = $resi->total();
        $line->credit                 = 0;
        $line->created_date           = $this->now;
        $line->created_by             = \Auth::user()->id;

        try {
            $line->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        /** PENDAPATAN UTAMA **/
        $persentase      = $this->getPersentasePendapatanUtama($resi->route);
        $totalPendapatan = 0;
        foreach ($persentase as $branchId => $persen) {
            $settingCoa       = SettingJournal::where('setting_name', SettingJournal::PENDAPATAN_UTAMA)->first();
            $combination      = AccountCombinationService::getCombination($settingCoa->coa->coa_code, null, $branchId);
            $pendapatan       = floor($persen / 100 * $resi->totalAmountAsli());
            $totalPendapatan += $pendapatan;

            $line = new JournalLine();
            $line->journal_header_id      = $journalHeader->journal_header_id;
            $line->account_combination_id = $combination->account_combination_id;
            $line->debet                  = 0;
            $line->credit                 = $pendapatan;
            $line->created_date           = $this->now;
            $line->created_by             = \Auth::user()->id;

            try {
                $line->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }

        /** PEMBULATAN **/
        $pembulatan = $totalPendapatan - $resi->totalAmount();
        if ($pembulatan != 0) {
            $settingCoa  = SettingJournal::where('setting_name', SettingJournal::PEMBULATAN)->first();
            $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

            $line = new JournalLine();
            $line->journal_header_id      = $journalHeader->journal_header_id;
            $line->account_combination_id = $combination->account_combination_id;
            $line->debet                  = $pembulatan;
            $line->credit                 = 0;
            $line->created_date           = $this->now;
            $line->created_by             = \Auth::user()->id;

            try {
                $line->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
    }

    protected function getPersentasePendapatanUtama(MasterRoute $route)
    {
        $persentase    = [];
        $currentBranch = \Session::get('currentBranch');

        if ($route->details->count() == 0) {
            $persentase[$currentBranch->branch_id] = 100;
        } else {
            foreach ($route->details as $detail) {
                if ($detail->city_start_id == $currentBranch->city_id) {
                    $persentase[$currentBranch->branch_id] = $detail->rate_kg / $route->rate_kg * 100;
                } else {
                    $mainBranch = MasterBranch::where('city_id', '=', $detail->city_start_id)->where('main_branch', '=', true)->first();
                    $persentase[$mainBranch->branch_id] = $detail->rate_kg / $route->rate_kg * 100;
                }
            }
        }

        return $persentase;
    }

    protected function createInvoicePickup(TransactionResiHeader $model)
    {
        $pickupRequest   = !empty($model->pickup_request_id) ? PickupRequest::find($model->pickup_request_id) : null;
        $invoice         = new Invoice();
        $invoice->status = Invoice::APPROVED;
        $invoice->type   = Invoice::INV_PICKUP;

        if ($pickupRequest === null || empty($pickupRequest->pickup_cost)) {
            return;
        }

        if ($model->isBillToReceiver()) {
            if (!empty($model->customer_receiver_id)) {
                    $invoice->customer_id = $model->customer_receiver_id;
            }

            $invoice->bill_to         = !empty($model->customerReceiver) ? $model->customerReceiver->customer_name : $model->receiver_name;
            $invoice->bill_to_address = $model->receiver_address;
            $invoice->bill_to_phone   = $model->receiver_phone;
        } else {
            if (!empty($model->customer_id)) {
                    $invoice->customer_id = $model->customer_id;
            }

            $invoice->bill_to         = !empty($model->customer) ? $model->customer->customer_name : $model->sender_name;
            $invoice->bill_to_address = $model->sender_address;
            $invoice->bill_to_phone   = $model->sender_phone;
        }

        $invoice->branch_id         = \Session::get('currentBranch')->branch_id;
        $invoice->created_date      = new \DateTime();
        $invoice->created_by        = \Auth::user()->id;
        $invoice->invoice_number    = $this->getInvoiceNumber($invoice);
        $invoice->resi_header_id    = !empty($pickupRequest->resi) ? $pickupRequest->resi->resi_header_id : null;
        $invoice->pickup_request_id = $pickupRequest->pickup_request_id;
        $invoice->amount            = $pickupRequest->pickup_cost;
        $invoice->current_discount  = 1;

        try {
            $invoice->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        $error = $this->createJournalInvoicePickup($invoice);
        if (!empty($error)) {
            return $error;
        }
    }

    protected function createJournalInvoicePickup(Invoice $model)
    {
        $invoice       = Invoice::find($model->invoice_id);
        $resi          = $invoice->resi;
        $pickupRequest = $invoice->pickupRequest;
        $journalHeader = new JournalHeader();

        $journalHeader->category       = JournalHeader::INVOICE_PICKUP;
        $journalHeader->period         = new \DateTime($this->now->format('Y-m-1'));
        $journalHeader->status         = JournalHeader::OPEN;
        $journalHeader->description    = 'Invoice Number: '.$invoice->invoice_number.'. Resi Number: '.$resi->resi_number;
        $journalHeader->branch_id      = $invoice->branch_id;
        $journalHeader->journal_date   = $this->now;
        $journalHeader->created_date   = $this->now;
        $journalHeader->created_by     = \Auth::user()->id;
        $journalHeader->journal_number = JournalService::getJournalNumber($journalHeader);

        try {
            $journalHeader->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        /** PIUTANG USAHA **/
        $settingCoa  = SettingJournal::where('setting_name', SettingJournal::PIUTANG_USAHA)->first();
        $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

        $line = new JournalLine();
        $line->journal_header_id      = $journalHeader->journal_header_id;
        $line->account_combination_id = $combination->account_combination_id;
        $line->debet                  = $pickupRequest->pickup_cost;
        $line->credit                 = 0;
        $line->created_date           = $this->now;
        $line->created_by             = \Auth::user()->id;

        try {
            $line->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        /** PENDAPATAN UTAMA **/
        $settingCoa  = SettingJournal::where('setting_name', SettingJournal::PENDAPATAN_LAIN_LAIN)->first();
        $combination = AccountCombinationService::getCombination($settingCoa->coa->coa_code);

        $line = new JournalLine();
        $line->journal_header_id      = $journalHeader->journal_header_id;
        $line->account_combination_id = $combination->account_combination_id;
        $line->debet                  = 0;
        $line->credit                 = $pickupRequest->pickup_cost;
        $line->created_date           = $this->now;
        $line->created_by             = \Auth::user()->id;

        try {
            $line->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    protected function getResiNumber(TransactionResiHeader $model)
    {
        $branch      = MasterBranch::find($model->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('op.trans_resi_header')
                            ->where('branch_id', '=', $model->branch_id)
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->count();

        return 'R.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.$branch->branch_code_numeric.Penomoran::getStringNomor($count + 1, 5);
    }

    public function getJsonItemUnitRute(Request $request, $routeId)
    {
        return response()->json(UnitService::getActiveRouteUnit($routeId, $request->get('search')));
    }

    protected function getOptionPickupRequest()
    {
        return \DB::table('mrk.trans_pickup_request')
                    ->select('trans_pickup_request.*')
                    ->leftJoin('op.trans_resi_header', 'trans_pickup_request.pickup_request_id', '=', 'trans_resi_header.pickup_request_id')
                    ->join('op.trans_pickup_form_line', 'trans_pickup_request.pickup_request_id', '=', 'trans_pickup_form_line.pickup_request_id')
                    ->join('op.trans_pickup_form_header', 'trans_pickup_form_line.pickup_form_header_id', '=', 'trans_pickup_form_header.pickup_form_header_id')
                    ->where('trans_pickup_request.status', '=', PickupRequest::CLOSED)
                    ->where('trans_pickup_form_header.status', '=', PickupFormHeader::CLOSED)
                    ->whereNull('trans_resi_header.resi_header_id')
                    ->where('trans_pickup_request.branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('pickup_request_number', 'desc')
                    ->get();
    }

    protected function getInvoiceNumber(Invoice $model)
    {
        $branch      = MasterBranch::find($model->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('ar.invoice')
                            ->where('branch_id', '=', $model->branch_id)
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->count();

        return 'IAR.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 6);
    }

    protected function getJsonRoute(Request $request)
    {
        $term  = $request->get('term');
        $query = RouteService::getQueryActiveRoute()->select('v_mst_route.*');
        $query->where(function($query) use ($term) {
            $query->where('route_code', 'ilike', '%'.$term.'%')
                    ->orWhere('city_start_name', 'ilike', '%'.$term.'%')
                    ->orWhere('city_end_name', 'ilike', '%'.$term.'%');
        });

        return response()->json($query->take(10)->get());
    }
}
