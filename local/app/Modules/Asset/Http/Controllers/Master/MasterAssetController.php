<?php

namespace App\Modules\Asset\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Asset\Model\Master\MasterAsset;

class MasterAssetController extends Controller
{
    const RESOURCE = 'Asset\Transaction\MassAdditionAsset';
    const URL      = 'asset/transaction/mass-addition-asset';

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
            return redirect(self::URL.'?page=1');
        } elseif (empty($request->get('page'))) {
            $request->session()->forget('filters');
        }

        $filters = $request->session()->get('filters');
        $query = \DB::table('ast.v_mask_add_asset_lov');

        if (!empty($filters['receiptNumber'])) {
            $query->where('receipt_number', 'ilike', '%'.$filters['receiptNumber'].'%');
        }

        if (!empty($filters['poNumber'])) {
            $query->where('po_number', 'ilike', '%'.$filters['poNumber'].'%');
        }

        if (!empty($filters['itemCode'])) {
            $query->where('item_code', 'ilike', '%'.$filters['itemCode'].'%');
        }

        if (!empty($filters['description'])) {
            $query->where('item_description', 'ilike', '%'.$filters['description'].'%');
        }

        if (!empty($filters['warehouse'])) {
            $query->where('wh_id', '=', $filters['warehouse']);
        }
        
        $query->where('branch_id', '=', \Session::get('currentBranch')->branch_id);

        return view('asset::master.master-asset.index', [
            'models'            => $query->paginate(10),
            'filters'           => $filters,
            'resource'          => self::RESOURCE,
            'url'               => self::URL,
            'optionsWarehouse'  => $this->getOptionsWarehouse(),
        ]);
    }

    protected function getOptionsWarehouse()
    {
        return \DB::table('inv.mst_warehouse')
                    ->where('branch_id', '=', \Session::get('currentBranch')->branch_id)
                    ->orderBy('wh_code', 'asc')
                    ->get();
    }

}
