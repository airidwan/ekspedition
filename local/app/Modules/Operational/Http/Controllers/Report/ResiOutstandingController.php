<?php

namespace App\Modules\Operational\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Service\Master\RouteService;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;

class ResiOutstandingController extends Controller
{
    const RESOURCE = 'Operational\Report\ResiOutstanding';
    const URL      = 'operational/report/resi-outstanding';

    protected $now;

    public function __construct()
    {
        $this->middleware('auth');
        $this->now = new \DateTime();
    }

    public function index(Request $request)
    {
        // dd($request->get('resiNumber'));
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        if ($request->isMethod('post')) {
            $request->session()->put('filters', $request->all());
            return redirect(self::URL.'?page=1');
        } elseif (empty($request->get('page'))) {
            $request->session()->forget('filters');
        }

        $query = $this->getQuery($request);

        return view('operational::report.resi-outstanding.index', [
            'models'   => $query->paginate(10),
            'filters'  => \Session::get('filters'),
            'resource' => self::RESOURCE,
            'url'      => self::URL,
            'optionRoute' => RouteService::getActiveRoute(),
            'optionPayment' => [
                TransactionResiHeader::CASH,
                TransactionResiHeader::BILL_TO_SENDER,
                TransactionResiHeader::BILL_TO_RECIEVER,
            ],
        ]);
    }

    protected function getQuery(Request $request)
    {
        $filters = $request->session()->get('filters');
        $query   = \DB::table('op.trans_resi_header')
                        ->select('trans_resi_header.*', 'v_received_ant_taken_resi.total_coly', 'v_received_ant_taken_resi.coly_received', 'v_received_ant_taken_resi.coly_taken')
                        ->join('op.v_received_ant_taken_resi', 'v_received_ant_taken_resi.resi_header_id', '=', 'trans_resi_header.resi_header_id')
                        ->leftJoin('op.mst_customer as customer_sender', 'trans_resi_header.customer_id', '=', 'customer_sender.customer_id')
                        ->leftJoin('op.mst_customer as customer_receiver', 'trans_resi_header.customer_receiver_id', '=', 'customer_receiver.customer_id')
                        ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                        ->where('trans_resi_header.status', '=', TransactionResiHeader::APPROVED)
                        ->whereRaw('v_received_ant_taken_resi.total_coly - v_received_ant_taken_resi.coly_received - v_received_ant_taken_resi.coly_taken > 0')
                        ->orderBy('trans_resi_header.created_date', 'desc');

        if (!empty($filters['resiNumber'])) {
            $resiNumber =  explode(',', $filters['resiNumber']);
            $query->where(function ($query) use($resiNumber) {
                for ($i = 0; $i < count($resiNumber); $i++){
                    $query->orwhere('trans_resi_header.resi_number', 'ilike',  '%' . $resiNumber[$i] .'%');
                }      
            });
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
            $query->where('route_id', '=', $filters['route']);
        }

        if (!empty($filters['payment'])) {
            $query->where('payment', '=', $filters['payment']);
        }

        if (!empty($filters['dateFrom'])) {
            $date = new \DateTime($filters['dateFrom']);
            $query->where('trans_resi_header.created_date', '>=', $date->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $date = new \DateTime($filters['dateTo']);
            $query->where('trans_resi_header.created_date', '<=', $date->format('Y-m-d 23:59:59'));
        }

        return $query;
    }

    public function printExcel(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request);

        \Excel::create(trans('operational/menu.resi-outstanding'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.resi-outstanding'));
                });

                $sheet->cells('A3:N3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('operational/fields.resi-number'),
                    trans('shared/common.date'),
                    trans('operational/fields.customer'),
                    trans('operational/fields.sender'),
                    trans('operational/fields.customer'),
                    trans('operational/fields.receiver'),
                    trans('operational/fields.route'),
                    trans('operational/fields.payment'),
                    trans('operational/fields.item-name'),
                    trans('operational/fields.item-unit'),
                    trans('operational/fields.total-coly'),
                    trans('operational/fields.coly-received'),
                    trans('operational/fields.coly-taken'),
                    trans('operational/fields.coly-remaining'),
                ]);

                $currentRow = 4;
                foreach($query->get() as $model) {
                    $modelResi = TransactionResiHeader::find($model->resi_header_id);
                    $resiDate = !empty($modelResi->created_date) ? new \DateTime($modelResi->created_date) : null;

                    $data = [
                        $modelResi->resi_number,
                        $resiDate !== null ? $resiDate->format('d-m-Y') : '',
                        !empty($modelResi->customer) ? $modelResi->customer->customer_name : '',
                        $modelResi->sender_name,
                        !empty($modelResi->customerReceiver) ? $modelResi->customerReceiver->customer_name : '',
                        $modelResi->receiver_name,
                        $modelResi->route !== null ? $modelResi->route->route_code : '',
                        $modelResi->getSingkatanPayment(),
                        $modelResi->itemName(),
                        $modelResi->itemUnit(),
                        $modelResi->totalColy(),
                        $model->coly_received,
                        $model->coly_taken,
                        $modelResi->totalColy() - $model->coly_received - $model->coly_taken,
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
                if (!empty($filters['payment'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.payment'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['payment'], 'C', $currentRow);
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
}
