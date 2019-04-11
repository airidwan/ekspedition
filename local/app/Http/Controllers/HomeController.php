<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Service\CurrentRoleService;
use App\Service\CurrentBranchService;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;
use App\User;
use App\Dashboard;

class HomeController extends Controller
{
    protected $now;
    protected $months;
    protected $branchs;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->now = new \DateTime();
        $this->months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        if (!empty(\Session::get('currentBranch'))) {
            $this->branchs = $this->getAllBranchs();
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        /** KALAU HO ATAU KEPALA CABANG TAMPILKAN GRAFIK **/
        if (\Session::get('currentBranch')->branch_code_numeric == MasterBranch::KODE_NUMERIC_HO ||
            \Session::get('currentRole')->id == Role::BRANCH_MANAGER) {

            $model = Dashboard::where('month', $this->now->format('m'))
                            ->where('year', $this->now->format('Y'))
                            ->where('branch_id', \Session::get('currentBranch')->branch_id)
                            ->first();

            $data = [
                'totalResi' => $model->total_resi,
                'totalManifest' => $model->total_manifest,
                'totalDO' => $model->total_do,
                'totalResiReceived' => $model->total_resi_received,

                'dataGraphResiPerMonth' => json_decode($model->data_resi_per_month, true),
                'dataGraphDOPerMonth' => json_decode($model->data_do_per_month, true),
                'dataGraphResiVsReceived' => json_decode($model->data_resi_vs_received, true),
                'dataGraphResiThisMonth' => json_decode($model->data_resi_this_month, true),
                'dataGraphResiReceivedThisMonth' => json_decode($model->data_resi_received_this_month, true),
            ];

            return view('home', $data);
        }

        /** KALAU LAINNYA TAMPILKAN NOTIFICATION **/
        if ($request->isMethod('post')) {
            $request->session()->put('filters', $request->all());
        } elseif (empty($request->get('page'))) {
            $request->session()->forget('filters');
        }

        $filters = $request->session()->get('filters');
        $query = \DB::table('notification')
                        ->where('user_id', '=', \Auth::user()->id)
                        ->orderBy('created_at', 'desc');

        if (!empty($filters['category'])) {
            $query->where('category', 'ilike', '%'.$filters['category'].'%');
        }

        if (!empty($filters['message'])) {
            $query->where('message', 'ilike', '%'.$filters['message'].'%');
        }

        $status = !empty($filters['status']) ? $filters['status'] : '';
        if ($status == 'READ') {
            $query->whereNotNull('read_at');
        } elseif($status == 'UN READ') {
            $query->whereNull('read_at');
        }

        if (!empty($filters['role'])) {
            $query->where('role_id', '=', $filters['role']);
        }

        if (!empty($filters['branch'])) {
            $query->where('branch_id', '=', $filters['branch']);
        }

        return view('notification.index', [
            'models' => $query->paginate(10),
            'filters' => $filters,
            'optionRole' => $this->getOptionRole(),
            'optionBranch' => $this->getOptionBranch(),
        ]);
    }

    public function updateDataDashboard(Request $request){
        $model = Dashboard::where('month', $this->now->format('m'))
                            ->where('year', $this->now->format('Y'))
                            ->first();

        if($model === null){
            $model = new Dashboard();
        }

        $model->total_resi                      = $this->getTotalResi();
        $model->total_manifest                  = $this->getTotalManifest();
        $model->total_do                        = $this->getTotalDO();
        $model->total_resi_received             = $this->getTotalResiReceived();

        $model->data_resi_per_month             = json_encode($this->getDataGraphResiPerMonth());
        $model->data_do_per_month               = json_encode($this->getDataGraphDOPerMonth());
        $model->data_resi_vs_received           = json_encode($this->getDataGraphResiVsReceived());
        $model->data_resi_this_month            = json_encode($this->getDataGraphResiThisMonth());
        $model->data_resi_received_this_month   = json_encode($this->getDataGraphResiReceivedThisMonth());

        $model->month = $this->now->format('m');
        $model->year  = $this->now->format('Y');

        $model->save();
    }

    protected function getAllBranchs()
    {
        $query = \DB::table('op.mst_branch')
                    ->where('active', '=', 'Y')
                    ->orderBy('mst_branch.branch_code', 'asc');

        if (\Session::get('currentBranch')->branch_code_numeric != MasterBranch::KODE_NUMERIC_HO) {
            $query->where('mst_branch.branch_id', '=', \Session::get('currentBranch')->branch_id);
        }

        return $query->get();
    }

    protected function getDataGraphResiPerMonth()
    {
        $data = [];
        foreach ($this->months as $key => $month) {
            $intMonth          = $key + 1;
            $dataItem          = [];
            $dataItem['month'] = $month;

            foreach ($this->branchs as $branch) {
                $dataItem[$branch->branch_id] = $this->getTotalResi($branch->branch_id, $intMonth);
            }

            $data[] = $dataItem;
        }

        $yKeys = [];
        foreach ($this->branchs as $branch) {
            $yKeys[] = $branch->branch_id;
        }

        $labels = [];
        foreach ($this->branchs as $branch) {
            $labels[] = $branch->branch_code;
        }

        return [
            'data'   => $data,
            'xKey'   => 'month',
            'yKeys'  => $yKeys,
            'labels' => $labels,
        ];
    }

    protected function getDataGraphDOPerMonth()
    {
        $data = [];
        foreach ($this->months as $key => $month) {
            $intMonth          = $key + 1;
            $dataItem          = [];
            $dataItem['month'] = $month;

            foreach ($this->branchs as $branch) {
                $dataItem[$branch->branch_id] = $this->getTotalDO($branch->branch_id, $intMonth);
            }

            $data[] = $dataItem;
        }

        $yKeys = [];
        foreach ($this->branchs as $branch) {
            $yKeys[] = $branch->branch_id;
        }

        $labels = [];
        foreach ($this->branchs as $branch) {
            $labels[] = $branch->branch_code;
        }

        return [
            'data'   => $data,
            'xKey'   => 'month',
            'yKeys'  => $yKeys,
            'labels' => $labels,
        ];
    }

    protected function getDataGraphResiVsReceived()
    {
        $data = [];
        foreach ($this->months as $key => $month) {
            $intMonth             = $key + 1;
            $dataItem             = [];
            $dataItem['month']    = $month;
            $dataItem['resi']     = $this->getTotalResi(null, $intMonth);
            $dataItem['received'] = $this->getTotalResiReceived(null, $intMonth);

            $data[] = $dataItem;
        }

        $yKeys  = ['resi', 'received'];
        $labels = ['Total Resi', 'Total Resi Received'];

        return [
            'data'   => $data,
            'xKey'   => 'month',
            'yKeys'  => $yKeys,
            'labels' => $labels,
        ];
    }

    protected function getDataGraphResiThisMonth()
    {
        $data = [];
        foreach ($this->branchs as $branch) {
            $dataItem['label'] = $branch->branch_code;
            $dataItem['value'] = $this->getTotalResi($branch->branch_id);

            $data[] = $dataItem;
        }

        return [
            'data'   => $data,
        ];
    }

    protected function getDataGraphResiReceivedThisMonth()
    {
        $data = [];
        foreach ($this->branchs as $branch) {
            $dataItem['label'] = $branch->branch_code;
            $dataItem['value'] = $this->getTotalResiReceived($branch->branch_id);

            $data[] = $dataItem;
        }

        return [
            'data'   => $data,
        ];
    }

    public function changeProfile(Request $request)
    {
        if ($request->isMethod('post')) {

            $id         = \Auth::user()->id;
            $user       = !empty($id) ? User::find($id) : new User();

            $validation = [
                'name'     => 'required|max:255|unique:adm.users,name,'.$id.',id',
                'email'    => 'required|email|max:255|unique:adm.users,email,'.$id.',id',
                'fullName' => 'required|max:255|unique:adm.users,full_name,'.$id.',id',
            ];

            $this->validate($request, $validation);

            $user->full_name    = $request->get('fullName');
            $user->name         = $request->get('name');
            $user->email        = $request->get('email');

            $now = new \DateTime();
            $user->updated_by = \Auth::user()->id;

            $foto = $request->file('foto');
            if ($foto !== null) {
                $fotoName   = md5($now->format('YmdHis')) . "." . $foto->guessExtension();
                $user->foto = $fotoName;
            }

            $user->save();

            if ($foto !== null) {
                $foto->move(\Config::get('app.paths.foto-user'), $fotoName);
            }

            $request->session()->flash('successMessage', 'Profile anda berhasil diubah');
            return redirect('/');
        }    
        $model = User::find(\Auth::user()->id);
        return view('auth.change-profile',[
                'model' => $model,
            ]);
    }

    public function changePassword(Request $request)
    {
        if ($request->isMethod('post')) {
            $this->validate($request, [
                'oldPassword'           => 'required',
                'password'              => 'required|min:6|confirmed|different:oldPassword',
                'password_confirmation' => 'required|min:6|',
            ]);

            if (!\Hash::check($request->get('oldPassword'), \Auth::user()->password)) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(
                    ['oldPassword' => 'Your old password is incorrect']
                );
            }

            \Auth::user()->password = bcrypt($request->get('password'));
            \Auth::user()->save();

            $request->session()->flash('successMessage', 'Password anda berhasil diubah');
            return redirect('/');
        }

        return view('auth.change-password');
    }

    public function gantiRoleDanCabang(Request $request)
    {
        CurrentRoleService::changeCurrentRole($request->get('gantiRole', 0));
        CurrentBranchService::changeCurrentBranch($request->get('gantiCabang', 0));
        \Session::put('showWelcomeModal', true);

        return redirect('/');
    }

    public function getJsonRoleBranch(Request $request, $roleId)
    {
        $branchs = \DB::table('op.mst_branch')
                        ->select('mst_branch.*')
                        ->join('adm.user_role_branch', 'mst_branch.branch_id', '=', 'user_role_branch.branch_id')
                        ->join('adm.user_role', 'user_role_branch.user_role_id', '=', 'user_role.user_role_id')
                        ->where('user_role.user_id', '=', \Auth::user()->id)
                        ->where('user_role.role_id', '=', $roleId)
                        ->where('mst_branch.active', '=', 'Y')
                        ->orderBy('mst_branch.branch_name', 'asc')
                        ->get();

        return response()->json($branchs);
    }

    protected function getTotalResi($branchId = null, $month = null)
    {
        $date  = !empty($month) ? new \DateTime($this->now->format('Y').'-'.$month.'-1') : new \DateTime($this->now->format('Y-m-1'));
        $query = \DB::table('op.trans_resi_header')
                        ->where('created_date', '>=', $date->format('Y-m-1 00:00:00'))
                        ->where('created_date', '<=', $date->format('Y-m-t 23:59:59'))
                        ->whereIn('status', [TransactionResiHeader::APPROVED]);

        if (\Session::get('currentBranch')->branch_code_numeric != MasterBranch::KODE_NUMERIC_HO) {
            $query->where('branch_id', '=', \Session::get('currentBranch')->branch_id);
        } elseif (!empty($branchId)) {
            $query->where('branch_id', '=', $branchId);
        }

        return $query->count();
    }

    protected function getTotalManifest($branchId = null, $month = null)
    {
        $date  = !empty($month) ? new \DateTime($this->now->format('Y').'-'.$month.'-1') : new \DateTime($this->now->format('Y-m-1'));
        $query = \DB::table('op.trans_manifest_header')
                        ->where('created_date', '>=', $date->format('Y-m-1 00:00:00'))
                        ->where('created_date', '<=', $date->format('Y-m-t 23:59:59'))
                        ->whereIn('status', [ManifestHeader::OTR, ManifestHeader::ARRIVED, ManifestHeader::CLOSED, ManifestHeader::CLOSED_WARNING]);

        if (\Session::get('currentBranch')->branch_code_numeric != MasterBranch::KODE_NUMERIC_HO) {
            $query->where('branch_id', '=', \Session::get('currentBranch')->branch_id);
        } elseif (!empty($branchId)) {
            $query->where('branch_id', '=', $branchId);
        }

        return $query->count();
    }

    protected function getTotalDO($branchId = null, $month = null)
    {
        $date  = !empty($month) ? new \DateTime($this->now->format('Y').'-'.$month.'-1') : new \DateTime($this->now->format('Y-m-1'));
        $query = \DB::table('op.trans_delivery_order_header')
                        ->where('created_date', '>=', $date->format('Y-m-1 00:00:00'))
                        ->where('created_date', '<=', $date->format('Y-m-t 23:59:59'))
                        ->whereIn('status', [DeliveryOrderHeader::ON_THE_ROAD, DeliveryOrderHeader::CLOSED]);

        if (\Session::get('currentBranch')->branch_code_numeric != MasterBranch::KODE_NUMERIC_HO) {
            $query->where('branch_id', '=', \Session::get('currentBranch')->branch_id);
        } elseif (!empty($branchId)) {
            $query->where('branch_id', '=', $branchId);
        }

        return $query->count();
    }

    protected function getTotalResiReceived($branchId = null, $month = null)
    {
        $date  = !empty($month) ? new \DateTime($this->now->format('Y').'-'.$month.'-1') : new \DateTime($this->now->format('Y-m-1'));
        $query = \DB::table('op.trans_resi_header')
                        ->select('trans_resi_header.resi_header_id')
                        ->join('op.v_received_ant_taken_resi', 'v_received_ant_taken_resi.resi_header_id', '=', 'trans_resi_header.resi_header_id')
                        ->where('trans_resi_header.created_date', '>=', $date->format('Y-m-1 00:00:00'))
                        ->where('trans_resi_header.created_date', '<=', $date->format('Y-m-t 23:59:59'))
                        ->whereRaw('v_received_ant_taken_resi.total_coly - v_received_ant_taken_resi.coly_received - v_received_ant_taken_resi.coly_taken <= 0')
                        ->distinct();

        if (\Session::get('currentBranch')->branch_code_numeric != MasterBranch::KODE_NUMERIC_HO) {
            $query->where('trans_resi_header.branch_id', '=', \Session::get('currentBranch')->branch_id);
        } elseif (!empty($branchId)) {
            $query->where('trans_resi_header.branch_id', '=', $branchId);
        }

        return $query->count();
    }

    protected function getOptionRole()
    {
        return \DB::table('adm.roles')
                    ->select('roles.*')
                    ->join('adm.user_role', 'user_role.role_id', '=', 'roles.id')
                    ->where('user_role.user_id', '=', \Auth::user()->id)
                    ->orderBy('roles.name', 'asc')
                    ->get();
    }

    protected function getOptionBranch()
    {
        return \DB::table('op.mst_branch')
                    ->select('mst_branch.*')
                    ->join('adm.user_role_branch', 'user_role_branch.branch_id', '=', 'mst_branch.branch_id')
                    ->join('adm.user_role', 'user_role.user_role_id', '=', 'user_role_branch.user_role_id')
                    ->where('user_role.user_id', '=', \Auth::user()->id)
                    ->orderBy('mst_branch.branch_name', 'asc')
                    ->distinct()
                    ->get();
    }
}
