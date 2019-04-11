<?php

namespace App\Modules\Marketing\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Marketing\Model\Transaction\OperatorBook;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Service\Penomoran;
use App\Service\TimezoneDateConverter;

class OperatorBookController extends Controller
{
    const RESOURCE = 'Marketing\Transaction\OperatorBook';
    const URL      = 'marketing/transaction/operator-book';

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

        return view('marketing::transaction.operator-book.index', [
            'models'      => $query->paginate(10),
            'filters'     => $filters,
            'resource'    => self::RESOURCE,
            'url'         => self::URL,
        ]);
    }

    protected function getQuery(Request $request)
    {
        $filters = \Session::get('filters');
        $query = \DB::table('mrk.trans_obook')->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('obook_time', 'desc');

        if (!empty($filters['obookNumber'])) {
            $query->where('obook_number', 'ilike', '%'.$filters['obookNumber'].'%');
        }

        if (!empty($filters['caller'])) {
            $query->where('callers_name', 'ilike', '%'.$filters['caller'].'%');
        }

        if (!empty($filters['description'])) {
            $query->where('description', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = TimezoneDateConverter::getServerDateTime($filters['dateFrom']);
            $query->where('obook_time', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = TimezoneDateConverter::getServerDateTime($filters['dateTo']);
            $query->where('obook_time', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        return $query;
    }

    public function printExcel(Request $request)
    {
        $query   = $this->getQuery($request);
        $filters = \Session::get('filters');

        \Excel::create(trans('marketing/menu.operator-book'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('marketing/menu.operator-book'));
                });

                $sheet->cells('A3:G3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('marketing/fields.obook-number'),
                    trans('marketing/fields.callers-name'),
                    trans('marketing/fields.callers-phone'),
                    trans('shared/common.deskripsi'),
                    trans('shared/common.date'),
                    trans('shared/common.time'),
                    trans('shared/common.created-by'),
                ]);

                $currentRow = 4;
                foreach($query->get() as $model) {
                    $date = !empty($model->obook_time) ? \App\Service\TimezoneDateConverter::getClientDateTime($model->obook_time) : null;
                    $user = \App\User::find($model->created_by);

                    $data = [
                        $model->obook_number,
                        $model->callers_name,
                        $model->callers_phone,
                        $model->description,
                        !empty($date) ? $date->format('d-M-Y') : '',
                        !empty($date) ? $date->format('H:i') : '',
                        !empty($user) ? $user->full_name : '',
                    ];

                    $sheet->row($currentRow++, $data);
                }

                $lastDataRow = $currentRow;
                $currentRow = $lastDataRow + 1;
                if (!empty($filters['obookNumber'])) {
                    $this->addLabelDescriptionCell($sheet, trans('marketing/fields.obook-number'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['obookNumber'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['caller'])) {
                    $this->addLabelDescriptionCell($sheet, trans('marketing/fields.callers-name'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['caller'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['description'])) {
                    $this->addLabelDescriptionCell($sheet, trans('marketing/fields.description'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['description'], 'C', $currentRow);
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

        $model = new OperatorBook();

        return view('marketing::transaction.operator-book.add', [
            'title' => trans('shared/common.add'),
            'model' => $model,
            'resource'    => self::RESOURCE,
            'url' => self::URL,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = OperatorBook::where('obook_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        return view('marketing::transaction.operator-book.add', [
            'title' => trans('shared/common.edit'),
            'model' => $model,
            'resource'    => self::RESOURCE,
            'url'   => self::URL,
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? OperatorBook::where('obook_id', '=', $id)->first() : new OperatorBook();

        $this->validate($request, [
            'callersName' => 'required|max:255',
            'date'        => 'required',
        ]);

        $timeString = $request->get('date').' '.$request->get('hours').':'.$request->get('minute');
        $time       = !empty($timeString) ? TimezoneDateConverter::getServerDateTime($timeString) : null;

        $model->obook_time    = !empty($time) ? $time->format('Y-m-d H:i:s'):null;
        $model->callers_name  = $request->get('callersName');
        $model->callers_phone = $request->get('callersPhone');
        $model->description   = $request->get('description');
        $model->branch_id     = \Session::get('currentBranch')->branch_id;

        $now = new \DateTime();
        if (empty($id)) {
            $model->obook_number = $this->getObookNumber($model);
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

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('marketing/menu.operator-book').' '.$model->obook_number])
        );

        return redirect(self::URL);
    }

    protected function getObookNumber(OperatorBook $model)
    {
        $branch      = MasterBranch::find($model->branch_id);
        $createdDate = $model->created_date instanceof \DateTime ? $model->created_date : new \DateTime($model->created_date);
        $count       = \DB::table('mrk.trans_obook')
                            ->where('created_date', '>=', $createdDate->format('Y-01-01 00:00:00'))
                            ->where('created_date', '<=', $createdDate->format('Y-12-31 23:59:59'))
                            ->where('branch_id', '=', $branch->branch_id)
                            ->count();

        return 'OB.'.$branch->branch_code.'.'.$createdDate->format('y').'.'.Penomoran::getStringNomor($count + 1, 4);
    }
}
