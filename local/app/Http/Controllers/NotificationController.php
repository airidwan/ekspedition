<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Notification;
use App\Service\NotificationService;
use App\Service\CurrentRoleService;
use App\Service\CurrentBranchService;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
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

    public function getNotifications()
    {
        $notifications = NotificationService::getNotifications(\Auth::user()->id);
        $count         = NotificationService::getCountNotification(\Auth::user()->id);

        $arrNotification = [];
        foreach ($notifications as $notification) {
            $createdAt = new \DateTime($notification->created_at);
            $role = $notification->role;
            $branch = $notification->branch;

            $arrNotification[] = [
                'notification_id' => $notification->notification_id,
                'category' => $notification->category,
                'message' => $notification->message,
                'url' => $notification->url,
                'created_at' => $createdAt->format('d-m-Y H:i'),
                'role' => $role !== null ? $role->name : '',
                'branch' => $branch !== null ? $branch->branch_name : '',
            ];
        }

        return response()->json(['count' => $count, 'notifications' => $arrNotification]);
    }

    public function readNotification(Request $request, $id)
    {
        $notification = Notification::find($id);

        if ($notification !== null) {
            $notification->read_at = new \DateTime();
            $notification->save();
        }

        CurrentRoleService::changeCurrentRole($notification->role_id);
        CurrentBranchService::changeCurrentBranch($notification->branch_id);

        if (!empty($notification->url)) {
            return redirect($notification->url);
        } else {
            return redirect(\URL::previous());
        }
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
