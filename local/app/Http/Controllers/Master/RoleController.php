<?php

namespace App\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Role;

class RoleController extends Controller
{
    const RESOURCE = 'SysAdmin\Master\Role';

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
        $query   = \DB::table('adm.roles')->orderBy('name', 'asc');

        if (!empty($filters['name'])) {
            $query->where('name', 'LIKE', '%'.$filters['name'].'%');
        }

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('active', '=', 'Y');
        } else {
            $query->where('active', '=', 'N');
        }

        return view('master.role.index', [
            'data'     => $query->paginate(10),
            'filters'  => $filters,
            'resource' => self::RESOURCE,
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        return view(
            'master.role.add',
            [
                'title'     => trans('shared/common.add'),
                'model'     => new Role(),
                'resources' => \Config::get('app.resources'),
            ]
        );
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $role = Role::find($id);
        if ($role === null) {
            abort(404);
        }

        return view(
            'master.role.add',
            [
                'title'     => trans('shared/common.edit'),
                'model'     => $role,
                'resources' => \Config::get('app.resources'),
            ]
        );
    }

    public function save(Request $request)
    {
        $id   = intval($request->get('id'));
        $role = !empty($id) ? Role::find($id) : new Role();

        $this->validate($request, [
            'name' => 'required|max:255|unique:adm.roles,name,'.$id.',id',
        ]);

        $role->name = $request->get('name');
        $role->active = !empty($request->get('status')) ? 'Y' : 'N';

        $now = new \DateTime();
        if (empty($id)) {
            $role->created_by = \Auth::user()->id;
        } else {
            $role->updated_by = \Auth::user()->id;
        }

        $role->save();

        if ($role->accessControls()->count() > 0) {
            $role->accessControls()->forceDelete();
        }

        foreach ($request->get('privileges', []) as $resource => $privileges) {
            foreach ($privileges as $privilege => $access) {
                $role->accessControls()->create(
                    [
                        'resource' => $resource,
                        'privilege' => $privilege,
                        'access'    => true,
                    ]
                );
            }
        }

        $request->session()->flash('successMessage', trans('shared/common.saved-message', ['variable' => 'Role ' . $role->name]));
        return redirect('sys-admin/master/role');
    }
}
