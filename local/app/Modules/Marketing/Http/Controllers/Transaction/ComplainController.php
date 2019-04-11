<?php

namespace App\Modules\Marketing\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Marketing\Model\Transaction\Complain;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Service\TimezoneDateConverter;
use App\Service\Penomoran;
use App\Role;
use App\Notification;

class ComplainController extends Controller
{
    const RESOURCE = 'Marketing\Transaction\Complain';
    const URL      = 'marketing/transaction/complain';

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
        } elseif (empty($request->get('page'))) {
            $request->session()->forget('filters');
        }

        $filters = $request->session()->get('filters');
        $query = $this->getQuery($request);

        return view('marketing::transaction.complain.index', [
            'models'       => $query->paginate(10),
            'filters'      => $filters,
            'optionStatus' => $this->getOptionsStatus(),
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
        ]);
    }

    protected function getQuery(Request $request)
    {
        $filters = \Session::get('filters');
        $query = \DB::table('mrk.trans_complain')
                        ->select('trans_complain.*', 'trans_resi_header.resi_number', 'trans_resi_header.item_name')
                        ->join('op.trans_resi_header', 'trans_complain.resi_id', '=', 'trans_resi_header.resi_header_id')
                        ->orderBy('created_date', 'desc');

        if (!empty($filters['complainNumber'])) {
            $query->where('complain_number', 'ilike', '%'.$filters['complainNumber'].'%');
        }

        if (!empty($filters['resiNumber'])) {
            $query->where('resi_number', 'ilike', '%'.$filters['resiNumber'].'%');
        }

        if (!empty($filters['status'])) {
            $query->where('trans_complain.status', '=', $filters['status']);
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = TimezoneDateConverter::getServerDateTime($filters['dateFrom']);
            $query->where('complain_time', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = TimezoneDateConverter::getServerDateTime($filters['dateTo']);
            $query->where('complain_time', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        return $query;
    }

    public function printExcel(Request $request)
    {
        $query   = $this->getQuery($request);
        $filters = \Session::get('filters');

        \Excel::create(trans('marketing/menu.complain'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('marketing/menu.complain'));
                });

                $sheet->cells('A3:M3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('marketing/fields.complain-number'),
                    trans('operational/fields.resi-number'),
                    trans('marketing/fields.callers-name'),
                    trans('marketing/fields.callers-phone'),
                    trans('operational/fields.item-name'),
                    trans('operational/fields.weight'),
                    trans('operational/fields.dimension'),
                    trans('marketing/fields.comment'),
                    trans('marketing/fields.temp-respon'),
                    trans('marketing/fields.last-respon'),
                    trans('shared/common.date'),
                    trans('shared/common.created-by'),
                    trans('shared/common.status'),
                ]);

                $currentRow = 4;
                foreach($query->get() as $model) {
                    $modelResi = TransactionResiHeader::find($model->resi_id);
                    $date      = !empty($model->complain_time) ? \App\Service\TimezoneDateConverter::getClientDateTime($model->complain_time) : null;
                    $user      = \App\User::find($model->created_by);

                    $data = [
                        $model->complain_number,
                        $model->resi_number,
                        $model->name,
                        $model->callers_phone,
                        $model->item_name,
                        number_format($modelResi->totalWeightAll(), 2),
                        number_format($modelResi->totalVolumeAll(), 6),
                        $model->comment,
                        $model->temporary_respon,
                        $model->last_respon,
                        !empty($date) ? $date->format('d-M-Y H:i') : '',
                        !empty($user) ? $user->full_name : '' ,
                        $model->status,
                    ];

                    $sheet->row($currentRow++, $data);
                }

                $lastDataRow = $currentRow;
                $currentRow = $lastDataRow + 1;
                if (!empty($filters['complainNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('marketing/fields.complain-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['complainNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['resiNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('operational/fields.resi-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['resiNumber'], 'C', $currentRow);
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

        $model         = new Complain();
        $model->status = Complain::OPEN;

        return view('marketing::transaction.complain.add', [
            'title'        => trans('shared/common.add'),
            'model'        => $model,
            'optionStatus' => $this->getOptionsStatus(),
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = Complain::where('complain_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        return view('marketing::transaction.complain.add', [
            'title'        => trans('shared/common.edit'),
            'model'        => $model,
            'optionStatus' => $this->getOptionsStatus(),
            'resource'     => self::RESOURCE,
            'url'          => self::URL,
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? Complain::where('complain_id', '=', $id)->first() : new Complain();

        $this->validate($request, [
            'name'         => 'required|max:150',
            'callersPhone' => 'required|max:150',
            'resiNumber'   => 'required',
            'comment'      => 'required|max:255',
            'tempRespon'   => 'required|max:255',
        ]);

        $now = new \DateTime();
        if (empty($id)) {
            $timeString = $request->get('date').' '.$request->get('hours').':'.$request->get('minute');
            $time       = !empty($timeString) ? TimezoneDateConverter::getServerDateTime($timeString) : null;
            $model->complain_time    = !empty($time) ? $time->format('Y-m-d H:i:s'):null;
            $model->name             = $request->get('name');
            $model->callers_phone    = $request->get('callersPhone');
            $model->resi_id          = intval($request->get('resiId'));
            $model->comment          = $request->get('comment');
            $model->temporary_respon = $request->get('tempRespon');
            $model->branch_id        = \Session::get('currentBranch')->branch_id;
            $model->status           = Complain::OPEN;
            $model->complain_number  = $this->getComplainNumber($model);

            $model->created_date = $now;
            $model->created_by = \Auth::user()->id;
        } else {
            $model->last_updated_date = $now;
            $model->last_updated_by = \Auth::user()->id;
        }

        if (empty($id) || ($model->isOpen() && $model->created_by == \Auth::user()->id)) {
            $model->status = $request->get('status');
        }

        $model->last_respon = $request->get('lastRespon');

        if (!empty($id) && $model->created_by != \Auth::user()->id) {
            $notif             = new Notification();
            $notif->branch_id  = $model->branch_id;
            $notif->category   = 'Complain Responsed';
            $notif->message    = 'Complain Responsed "'.$model->last_respon.'"';
            $notif->url        = self::URL.'/edit/'.$model->complain_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $model->created_by;
            $notif->role_id    = Role::OPERATOR;
            $notif->save();
        }

        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('marketing/menu.complain').' '.$model->complain_number])
        );

        return redirect(self::URL);
    }

    protected function getComplainNumber(Complain $model)
    {
        $branch      = MasterBranch::find($model->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('mrk.trans_complain')
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->count();

        return 'CF.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }

    protected function getOptionsStatus()
    {
        return [
            Complain::OPEN,
            Complain::CLOSED,
        ];
    }

    public function getJsonResi(Request $request)
    {
        $search = $request->get('search');
        $query = \DB::table('op.trans_resi_header')
                ->select(
                    'trans_resi_header.resi_header_id',
                    'trans_resi_header.resi_number',
                    'trans_resi_header.sender_name',
                    'trans_resi_header.sender_address',
                    'trans_resi_header.receiver_name',
                    'trans_resi_header.receiver_address',
                    'trans_resi_header.item_name',
                    'trans_resi_header.description',
                    'v_mst_route.route_code',
                    'v_mst_route.city_start_name',
                    'v_mst_route.city_end_name'
                )
                ->join('op.v_mst_route', 'v_mst_route.route_id', '=', 'trans_resi_header.route_id')
                ->whereRaw(
                    'trans_resi_header.resi_header_id NOT IN (SELECT resi_id FROM mrk.trans_complain WHERE trans_complain.status <> \''.Complain::CLOSED.'\')'
                )
                ->where(function ($query) use ($search) {
                    $query->where('trans_resi_header.resi_number', 'ilike', '%'.$search.'%')
                          ->orWhere('trans_resi_header.sender_name', 'ilike', '%'.$search.'%')
                          ->orWhere('trans_resi_header.sender_address', 'ilike', '%'.$search.'%')
                          ->orWhere('trans_resi_header.receiver_name', 'ilike', '%'.$search.'%')
                          ->orWhere('trans_resi_header.receiver_address', 'ilike', '%'.$search.'%')
                          ->orWhere('trans_resi_header.item_name', 'ilike', '%'.$search.'%');
                })
                ->take(10)
                ->get();
        $arrResi = [];
        foreach ($query as $resi) {
            $model = TransactionResiHeader::find($resi->resi_header_id);
            $resi->total_coly = $model->totalColy();
            $resi->customer   = !empty($model->customer) ? $model->customer->customer_name : '';
            $arrResi [] = $resi;
        }

        return response()->json($arrResi);
    }
}
