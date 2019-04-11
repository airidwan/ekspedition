<?php

namespace App\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use App\DetailUserBranch;
use App\Role;
use App\UserRole;
use App\UserRoleBranch;

class UserController extends Controller
{
    const RESOURCE = 'SysAdmin\Master\User';

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

        $query = $this->getQuery($request);
        $filters = \Session::get('filters');

        return view('master.user.index', [
            'data'     => $query->paginate(10),
            'filters'  => $filters,
            'resource' => self::RESOURCE,
        ]);
    }

    protected function getQuery(Request $request)
    {
        $filters = $request->session()->get('filters');
        $query   = \DB::table('adm.users')->orderBy('name', 'asc');

        if (!empty($filters['fullName'])) {
            $query->where('full_name', 'ilike', '%'.$filters['fullName'].'%');
        }

        if (!empty($filters['name'])) {
            $query->where('name', 'ilike', '%'.$filters['name'].'%');
        }

        if (!empty($filters['email'])) {
            $query->where('email', 'ilike', '%'.$filters['email'].'%');
        }

        if (!empty($filters['status']) || !$request->session()->has('filters')) {
            $query->where('active', '=', 'Y');
        } else {
            $query->where('active', '=', 'N');
        }

        return $query;
    }

    public function printExcel(Request $request)
    {
        $query   = $this->getQuery($request);
        $filters = \Session::get('filters');

        \Excel::create(trans('sys-admin/menu.user'), function($excel) use ($query, $filters) {
            $excel->sheet('Sheet1', function($sheet) use ($query, $filters) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('sys-admin/menu.user'));
                });

                $sheet->cells('A3:C3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, [
                    trans('sys-admin/fields.username'),
                    trans('sys-admin/fields.name'),
                    trans('sys-admin/fields.email'),
                ]);

                $currentRow = 4;
                foreach($query->get() as $model) {
                    $data = [
                        $model->name,
                        $model->full_name,
                        $model->email,
                    ];

                    $sheet->row($currentRow++, $data);
                }

                $lastDataRow = $currentRow;
                $currentRow = $lastDataRow + 1;
                if (!empty($filters['fullName'])) {
                    $this->addLabelDescriptionCell($sheet, trans('sys-admin/fields.full-name'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['fullName'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['username'])) {
                    $this->addLabelDescriptionCell($sheet, trans('sys-admin/fields.username'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['username'], 'C', $currentRow);
                    $currentRow++;
                }
                if (!empty($filters['email'])) {
                    $this->addLabelDescriptionCell($sheet, trans('sys-admin/fields.email'), 'B', $currentRow);
                    $this->addValueDescriptionCell($sheet,  $filters['email'], 'C', $currentRow);
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

        return view(
            'master.user.add',
            [
                'title' => trans('shared/common.add'),
                'model' => new User(),
                'roleOptions' => $this->getOptionRole(),
                'cabangOptions' => $this->getOptionBranch(),
            ]
        );
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $user = User::find($id);
        if ($user === null) {
            abort(404);
        }

        return view(
            'master.user.add',
            [
                'title' => trans('shared/common.edit'),
                'model' => $user,
                'roleOptions' => $this->getOptionRole(),
                'cabangOptions' => $this->getOptionBranch(),
            ]
        );
    }

    public function save(Request $request)
    {
        $id         = intval($request->get('id'));
        $user       = !empty($id) ? User::find($id) : new User();
        $validation = [
            'name'     => 'required|max:255|unique:adm.users,name,'.$id.',id',
            'email'    => 'required|email|max:255|unique:adm.users,email,'.$id.',id',
            'fullName' => 'required|max:255|unique:adm.users,full_name,'.$id.',id',
        ];

        if (empty($id)) {
            $validation['password'] = 'required|min:6';
        }

        $this->validate($request, $validation);

        $errors = [];
        if (empty($request->get('roleBranch'))) {
            $errors[] = trans('sys-admin/fields.role-branch-required');
        }

        if (!empty($errors)) {
            return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => $errors[0]]);
        }

        $user->full_name = $request->get('fullName');
        $user->name = $request->get('name');
        $user->email = $request->get('email');
        $user->active = !empty($request->get('status')) ? 'Y' : 'N';

        if (!empty($request->get('password'))) {
            $user->password = bcrypt($request->get('password'));
        }

        $now = new \DateTime();
        if (empty($id)) {
            $user->created_by = \Auth::user()->id;
        } else {
            $user->updated_by = \Auth::user()->id;
        }

        $foto = $request->file('foto');
        if ($foto !== null) {
            $fotoName   = md5($now->format('YmdHis')) . "." . $foto->guessExtension();
            $user->foto = $fotoName;
        }

        $user->save();

        if ($foto !== null) {
            $foto->move(\Config::get('app.paths.foto-user'), $fotoName);
        }

        foreach ($user->userRole()->get() as $userRole) {
            foreach ($userRole->userRoleBranch() as $userRoleBranch) {
                $userRoleBranch->delete();
            }

            $userRole->delete();
        }

        $now = new \DateTime();
        foreach ($request->get('roleBranch') as $roleId => $branchs) {
            $userRole = new UserRole();
            $userRole->user_id = $user->id;
            $userRole->role_id = $roleId;
            $userRole->created_at = $now;
            $userRole->created_by = \Auth::user()->id;
            $userRole->save();

            foreach ($branchs as $branchId) {
                $userRoleBranch = new UserRoleBranch();
                $userRoleBranch->user_role_id = $userRole->user_role_id;
                $userRoleBranch->branch_id = $branchId;
                $userRoleBranch->created_at = $now;
                $userRoleBranch->created_by = \Auth::user()->id;
                $userRoleBranch->save();
            }
        }

        $request->session()->flash('successMessage', trans('shared/common.saved-message', ['variable' => 'User ' . $user->name]));
        return redirect('sys-admin/master/user');
    }

    protected function getOptionRole()
    {
        return \DB::table('adm.roles')
                    ->where('active', '=', 'Y')
                    ->orderBy('name', 'asc')
                    ->get();
    }

    protected function getOptionBranch()
    {
        return \DB::table('op.mst_branch')
                    ->where('active','=','Y')
                    ->orderBy('branch_code', 'asc')
                    ->get();
    }
}
