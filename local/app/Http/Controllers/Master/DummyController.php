<?php

namespace App\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\DummyHeader;
use App\User;

class DummyController extends Controller
{
    const RESOURCE = 'SysAdmin\Master\Dummy';

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
        }

        $filters = $request->session()->get('filters');
        $query   = \DB::table('dummy_headers')->orderBy('kolom_date', 'desc');

        if (!empty($filters['kolomString'])) {
            $query->where('kolom_string', 'LIKE', '%'.$filters['kolomString'].'%');
        }

        if (!empty($filters['kolomSelect'])) {
            $query->where('kolom_select', '=', $filters['kolomSelect']);
        }

        if (!empty($filters['tanggalAwal'])) {
            $tanggalAwal = new \DateTime($filters['tanggalAwal']);
            $query->where('kolom_date', '>=', $tanggalAwal->format('Y-m-d 00:00:00'));
        }

        if (!empty($filters['tanggalAkhir'])) {
            $tanggalAkhir = new \DateTime($filters['tanggalAkhir']);
            $query->where('kolom_date', '<=', $tanggalAkhir->format('Y-m-d 23:59:59'));
        }

        return view('master.dummy.index', [
            'headers' => $query->paginate(10),
            'filters' => $filters,
        ]);
    }

    public function add(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'insert'])) {
            abort(403);
        }

        return view(
            'master.dummy.add',
            ['title' => trans('shared/common.add'), 'model' => new DummyHeader(), "lovs" => User::all()]
        );
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'update'])) {
            abort(403);
        }

        $header = DummyHeader::find($id);
        if ($header === null) {
            abort(404);
        }

        return view(
            'master.dummy.add',
            ['title' => trans('shared/common.edit'), 'model' => $header, "lovs" => User::all()]
        );
    }

    public function save(Request $request)
    {
        $id     = $request->get('id');
        $header = !empty($id) ? DummyHeader::find($id) : new DummyHeader();

        $this->validate($request, [
            'kolomString' => 'required|max:255',
            'kolomDate'   => 'required',
        ]);

        if (empty($request->get('kolomStringLines'))) {
            return redirect(\URL::previous())
                    ->withInput($request->all())
                    ->withErrors(['errorMessage' => 'Header ' . $header->kolomString . ' harus punya minimal 1 line']);
        }

        $kolom_date    = !empty($request->get('kolomDate')) ? new \DateTime($request->get('kolomDate')) : null;
        $now           = new \DateTime();
        $kolomFoto     = $request->file('kolomFoto');
        $kolomFotoName = $kolomFoto !== null ? md5($now->format('YmdHis')) . "." . $kolomFoto->guessExtension() : $header->kolom_foto;
        $id            = !empty($id) ? $id : -1;

        \DB::select(
            \DB::raw(
                "SELECT * from insert_update(
                $id,
                '" . $request->get('kolomString') . "',
                '" . $request->get('kolomSelect') . "',
                '" . $request->get('kolomAutocomplete') . "',
                '" . intval($request->get('kolomCurrency')) . "',
                '" . $request->get('kolomTextarea') . "',
                '" . $kolom_date->format('Y-m-d H:i:s') . "',
                '" . $request->get('kolomCheckbox') . "',
                '" . $request->get('kolomRadio') . "',
                '" . $kolomFotoName . "')"
            )
        );

        if ($kolomFoto !== null) {
            $kolomFoto->move(\Config::get('app.paths.kolom-foto-dummy'), $kolomFotoName);
        }

        if ($id == -1) {
            $query = \DB::select(\DB::raw("SELECT id from dummy_headers order by id desc LIMIT 1"));
            $id    = $query[0]->id;
        }

        $query   = \DB::select(\DB::raw( "SELECT * from delete_lines($id)" ));

        for ($i=0; $i < count($request->get('kolomStringLines')); $i++) {
            $kolomDate      = !empty($request->get('kolomDateLines')[$i]) ? new \DateTime($request->get('kolomDateLines')[$i]): null;
            $date           = !empty($kolomDate) ? "'".$kolomDate->format('Y-m-d H:i:s')."'":'NULL';
            $query = \DB::select(
                \DB::raw(
                    "SELECT * from insert_line(
                     $id ,
                    '". $request->get('kolomStringLines')[$i] . "',
                    '". $request->get('kolomSelectLines')[$i] ."',
                    '". intval($request->get('kolomCurrencyLines')[$i]) ."',
                     $date)"
                )
            );
        }

        $request->session()->flash(
            'successMessage',
            trans('shared/common.saved-message', ['variable' => 'Header ' . $request->get('kolomString')])
        );

        return redirect('sys-admin/master/dummy');
    }

    public function delete(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'delete'])) {
            abort(403);
        }

        $header = DummyHeader::find($request->get('id'));
        if ($header === null) {
            abort(404);
        }

        \DB::select(\DB::raw("SELECT * from delete_header($header->id)"));

        $request->session()->flash(
            'successMessage',
            trans('shared/common.deleted-message', ['variable' => 'Header ' . $header->kolom_string])
        );

        return redirect('sys-admin/master/dummy');
    }

    public function getHeader(Request $request){
        $term = $request->get('term');
        $query   = \DB::table('dummy_headers')->orderBy('kolom_date', 'desc');
        $query->where('kolom_string', 'ilike', '%'.$term.'%');

        $data = [];
        foreach ($query->get() as $header) {
            $data[] = [
                'id'    => $header->id,
                'label' => $header->kolom_string,
                'value' => $header->kolom_string,
            ];
        }

        return response()->json($data);
    }
}
