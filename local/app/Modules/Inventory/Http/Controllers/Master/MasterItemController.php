<?php

namespace App\Modules\Inventory\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Inventory\Model\Master\MasterItem;

class MasterItemController extends Controller
{
    const RESOURCE = 'Inventory\Master\MasterItem';
    const URL = 'inventory/master/master-item';
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
        } elseif (empty($request->get('page'))) {
            $request->session()->forget('filters');
        }

        $filters = $request->session()->get('filters');
        $query   = $this->getQuery($request, $filters);

        return view('inventory::master.master-item.index', [
            'models' => $query->paginate(10),
            'filters' => $filters,
            'resource' => self::RESOURCE,
            'url' => self::URL,
            'optionsCategory' => $this->getOptionsCategory(),
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        $model = new MasterItem();
        $model->active = 'Y';

        return view('inventory::master.master-item.add', [
            'title' => trans('shared/common.add'),
            'model' => $model,
            'url' => self::URL,
            'optionsCategory' => $this->getOptionsCategory(),
            'optionsUom' => $this->getOptionsUom(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = MasterItem::where('item_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        return view('inventory::master.master-item.add', [
            'title' => trans('shared/common.edit'),
            'model' => $model,
            'url' => self::URL,
            'optionsCategory' => $this->getOptionsCategory(),
            'optionsUom' => $this->getOptionsUom(),
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? MasterItem::where('item_id', '=', $id)->first() : new MasterItem();

        $this->validate($request, [
            'itemCode' => 'required|max:100|unique:inventory.mst_item,item_code,'.$id.',item_id',
            'description' => 'required|max:255',
        ]);

        $model->item_code = $request->get('itemCode');
        $model->description = $request->get('description');

        $model->uom_id = $request->get('uom');
        $model->category_id = $request->get('category');

        $model->active = !empty($request->get('status')) ? 'Y' : 'N';

        $now = new \DateTime();
        if (empty($id)) {
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
            trans('shared/common.saved-message', ['variable' => trans('inventory/menu.master-item').' '.$model->item_code])
        );

        return redirect(self::URL);
    }

    protected function getQuery(Request $request, $filters){
        $query = \DB::table('inv.v_mst_item');

        if (!empty($filters['itemCode'])) {
            $query->where('item_code', 'ilike', '%'.$filters['itemCode'].'%');
        }

        if (!empty($filters['description'])) {
            $query->where('description', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['category'])) {
            $query->where('category_id', '=', $filters['category']);
        }

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('active', '=', 'Y');
        } else {
            $query->where('active', '=', 'N');
        }

        return $query;
    }

    public function printExcelIndex(Request $request)
    {
        $filters = \Session::get('filters');
        $query   = $this->getQuery($request, $filters)->get();

        \Excel::create(trans('inventory/menu.master-item'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('inventory/menu.master-item'));
                });

                $sheet->cells('A3:F3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('shared/common.num'),
                    trans('inventory/fields.item-code'),
                    trans('shared/common.description'),
                    trans('shared/common.category'),
                    trans('inventory/fields.wh-code'),
                    trans('inventory/fields.uom'),
                ]);
                foreach($query as $index => $model) {
                    $date = !empty($model->date) ? new \DateTime($model->date) : null;

                    $data = [
                        $index + 1,
                        $model->item_code,
                        $model->description,
                        $model->category_description,
                        $model->uom_code,
                    ];
                    $sheet->row($index + 4, $data);
                }

                $currentRow = count($query) + 5;
                if (!empty($filters['itemCode'])) {
                    $this->addLabelDescriptionCell($sheet, trans('inventory/fields.item-code'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['itemCode'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['description'])) {
                    $this->addLabelDescriptionCell($sheet, trans('shared/common.description'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet, $filters['description'], 'C', $currentRow);
                    $currentRow++;
                }
                $currentRow = count($query) + 5;
                $this->addLabelDescriptionCell($sheet, trans('shared/common.date'), 'E', $currentRow);
                $this->addValueDescriptionCell($sheet, $this->now->format('d-m-Y'), 'F', $currentRow);
                $this->addLabelDescriptionCell($sheet, trans('shared/common.user'), 'E', $currentRow + 1);
                $this->addValueDescriptionCell($sheet, \Auth::user()->full_name, 'F', $currentRow + 1);
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

    protected function getOptionsCategory()
    {
        return \DB::table('inv.mst_category')
                    ->where('active', '=', 'Y')
                    ->orderBy('description', 'asc')
                    ->get();
    }

    protected function getOptionsUom()
    {
        return \DB::table('inv.mst_uom')
                    ->where('active', '=', 'Y')
                    ->orderBy('uom_code', 'asc')
                    ->get();
    }
}
