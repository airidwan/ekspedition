<?php

namespace App\Modules\Purchasing\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Inventory\Http\Controllers\Transaction\Receipt;
use App\Modules\Purchasing\Model\Transaction\PurchaseOrderHeader;
use App\Modules\Purchasing\Model\Transaction\PurchaseOrderLine;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Notification;
use App\Service\NotificationService;
use App\Role;

class PurchaseApproveController extends Controller
{
    const RESOURCE = 'Purchasing\Transaction\PurchaseApprove';
    const URL      = 'purchasing/transaction/purchase-approve';

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

        if (empty($filters['jenis']) || $filters['jenis'] == 'headers') {
            $query = \DB::table('po.v_po_headers')->orderBy('po_date', 'desc');
        } else {
            $query = \DB::table('po.v_po_lines')->orderBy('po_date', 'desc');
        }

        if (!empty($filters['poNumber'])) {
            $query->where('po_number', 'ilike', '%'.$filters['poNumber'].'%');
        }

        $query->where('branch_id', '=', $request->session()->get('currentBranch')->branch_id);

        if (!empty($filters['supplier'])) {
            $query->where('supplier_id', '=', $filters['supplier']);
        }

        if (!empty($filters['type'])) {
            $query->where('type_id', '=', $filters['type']);
        }

        $query->where('status', '=', PurchaseOrderHeader::INPROCESS);

        if (!empty($filters['dateFrom'])) {
            $dateFrom = new \DateTime($filters['dateFrom']);
            $query->where('po_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = new \DateTime($filters['dateTo']);
            $query->where('po_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
        }

        return view('purchasing::transaction.purchase-approve.index', [
            'models'            => $query->paginate(10),
            'filters'           => $filters,
            'resource'          => self::RESOURCE,
            'url'               => self::URL,
            'optionsBranch'     => $this->getOptionsBranch(),
            'optionsSupplier'   => $this->getOptionsSupplier(),
            'optionsType'       => $this->getOptionsType(),
            'optionsStatus'     => $this->getOptionsStatus(),
        ]);
    }


    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $model = PurchaseOrderHeader::where('header_id', '=', $id)->first();
        if ($model === null) {
            abort(404);
        }

        if ($request->user()->cannot('accessBranch', $model->branch_id)) {
            abort(403);
        }

        return view('purchasing::transaction.purchase-approve.add', [
            'title'             => trans('shared/common.edit'),
            'model'             => $model,
            'resource'          => self::RESOURCE,
            'url'               => self::URL,
            'optionsSupplier'   => $this->getOptionsSupplier(),
            'optionsType'       => $this->getOptionsType(),
            'optionsStatus'     => $this->getOptionsStatus(),
            'optionsItem'       => $this->getOptionsItem(),
            'optionsWarehouse'  => $this->getOptionsWarehouse(),
        ]);
    }

    public function save(Request $request)
    {
        $id = intval($request->get('id'));
        $model = !empty($id) ? PurchaseOrderHeader::find($id) : new PurchaseOrderHeader();
        

        $this->validate($request, [
            'note' => 'required',
        ]);

        $userNotif = NotificationService::getUserNotification([Role::FINANCE_ADMIN, Role::PURCHASING_ADMIN]);

        foreach ($userNotif as $user) {
            $notif             = new Notification();
            $notif->branch_id  = \Session::get('currentBranch')->branch_id;
            $notif->url        = PurchaseOrderController::URL.'/edit/'.$model->header_id;
            $notif->created_at = new \DateTime();
            $notif->user_id    = $user->id;
            $notif->role_id    = $user->role_id;
            $notif->message    = $model->po_number.' - '.$request->get('note');
            if ($request->get('btn-approve') !== null) {
                $notif->category   = 'Purhase Order Approved';
            } elseif ($request->get('btn-reject') !== null) {
                $notif->category   = 'Purhase Order Rejected';
            }
            $notif->save();
        }

        if ($request->get('btn-approve') !== null) {
            $model->status        = PurchaseOrderHeader::APPROVED;
            $model->approved_by   = \Auth::user()->id;
            $model->approved_date = new \DateTime(); 
            $model->note          = 'Approved : '.$request->get('note');
        } elseif ($request->get('btn-reject') !== null) {
            $model->status = PurchaseOrderHeader::INCOMPLETE;
            $model->note          = 'Rejected : '.$request->get('note');
        }

        try {
            $model->save();
        } catch (\Exception $e) {
            return redirect(self::URL.'/edit/'.$model->header_id)->withInput($request->all())->withErrors(['errorMessage' => $e->getMessage()]);
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => trans('purchasing/menu.master-type-po').' '.$model->po_number])
        );

        return redirect(self::URL);
    }

    protected function getOptionsBranch()
    {
        return \DB::table('op.mst_branch')->where('active', '=', 'Y')->orderBy('branch_name')->get();
    }

    protected function getOptionsSupplier()
    {
        return \DB::table('ap.mst_vendor')->orderBy('vendor_name')->where('active', '=', 'Y')->get();
    }

    protected function getOptionsType()
    {
        return \DB::table('po.mst_po_type')->orderBy('type_name')->where('active', '=', 'Y')->get();
    }

    protected function getOptionsStatus()
    {
        return [
            PurchaseOrderHeader::INCOMPLETE,
            PurchaseOrderHeader::INPROCESS,
            PurchaseOrderHeader::APPROVED,
            PurchaseOrderHeader::CANCELED,
        ];
    }

    protected function getOptionsWarehouse()
    {
        return \DB::table('inv.mst_warehouse')->orderBy('wh_code')->where('active', '=', 'Y')->where('branch_id', '=', \Session::get('currentBranch')->branch_id)->get();
    }

    protected function getOptionsItem()
    {
        return \DB::table('inv.v_mst_item')->orderBy('item_code')->where('active', '=', 'Y')->get();
    }
}
