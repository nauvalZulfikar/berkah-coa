<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\TipeAkun;
use App\Models\StatusData;
use App\Exports\AkunExport;
use App\Exports\AkunTemplateExport;
use App\Imports\AkunImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AkunController extends Controller
{
    public function index(Request $request)
    {
        $query = Akun::with(['tipeAkun', 'induk', 'statusData'])->orderBy('kode');

        if ($request->filled('tipe')) {
            $query->where('id_tipe_akun', $request->tipe);
        }
        if ($request->filled('status')) {
            $query->where('id_status_data', $request->status);
        }
        if ($request->filled('cari')) {
            $query->where(function ($q) use ($request) {
                $q->where('kode_internal', 'like', '%'.$request->cari.'%')
                  ->orWhere('nama', 'like', '%'.$request->cari.'%');
            });
        }

        $allAkuns = $query->get();
        $roots = $allAkuns->whereNull('kode_induk')->values();
        $tipeAkuns = TipeAkun::where('is_aktif', 1)->get();
        $statusList = StatusData::all();

        return view('akun.index', compact('allAkuns', 'roots', 'tipeAkuns', 'statusList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_internal' => 'required|string|max:30|unique:gl_mst_akun,kode_internal',
            'nama'          => 'required|string|max:150',
            'id_tipe_akun'  => 'required|exists:gl_ref_tipe_akun,id',
            'kode_induk'    => 'nullable|exists:gl_mst_akun,kode',
        ]);

        $kode = (Akun::max('kode') ?? 0) + 1;

        $level = 1;
        if ($request->filled('kode_induk')) {
            $induk = Akun::find($request->kode_induk);
            $level = ($induk?->level_akun ?? 0) + 1;
        }

        Akun::create([
            'kode'          => $kode,
            'kode_internal' => $request->kode_internal,
            'nama'          => $request->nama,
            'kode_induk'    => $request->kode_induk ?: null,
            'id_tipe_akun'  => $request->id_tipe_akun,
            'level_akun'    => $level,
            'urutan'        => 0,
            'is_aktif'      => 1,
            'id_status_data'=> 1,
            'waktu_ubah'    => now(),
            'diubah_oleh'   => 1,
        ]);

        return redirect()->route('akun.index')->with('success', 'Akun berhasil dibuat.');
    }

    public function detail($kode)
    {
        $akun = Akun::with(['tipeAkun', 'induk', 'statusData'])->findOrFail($kode);
        return response()->json([
            'kode'          => $akun->kode,
            'kode_internal' => $akun->kode_internal,
            'nama'          => $akun->nama,
            'kode_induk'    => $akun->kode_induk,
            'id_tipe_akun'  => $akun->id_tipe_akun,
            'tipe_akun'     => $akun->tipeAkun?->tipe_akun,
            'induk_nama'    => $akun->induk?->nama,
            'level_akun'    => $akun->level_akun,
            'is_aktif'      => (bool) $akun->is_aktif,
            'keterangan'    => $akun->keterangan,
            'status_data'   => $akun->statusData?->status_data,
        ]);
    }

    public function update(Request $request, $kode)
    {
        $akun = Akun::findOrFail($kode);

        $data = $request->json()->all() ?: $request->all();

        $request->merge($data);
        $request->validate([
            'kode_internal' => 'required|string|max:30|unique:gl_mst_akun,kode_internal,'.$akun->kode.',kode',
            'nama'          => 'required|string|max:150',
            'id_tipe_akun'  => 'required|exists:gl_ref_tipe_akun,id',
            'kode_induk'    => 'nullable|exists:gl_mst_akun,kode',
        ]);

        $kodeInduk = $data['kode_induk'] ?? null;
        if ($kodeInduk) {
            if ($kodeInduk == $kode) {
                return response()->json(['error' => 'Akun tidak boleh menjadi induk dirinya sendiri.'], 422);
            }
            $descendantKodes = $this->getDescendantKodes($kode);
            if (in_array($kodeInduk, $descendantKodes)) {
                return response()->json(['error' => 'Relasi siklik terdeteksi.'], 422);
            }
        }

        $level = 1;
        if ($kodeInduk) {
            $induk = Akun::find($kodeInduk);
            $level = ($induk?->level_akun ?? 0) + 1;
        }

        $akun->update([
            'kode_internal' => $data['kode_internal'],
            'nama'          => $data['nama'],
            'id_tipe_akun'  => $data['id_tipe_akun'],
            'kode_induk'    => $kodeInduk ?: null,
            'level_akun'    => $level,
            'waktu_ubah'    => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function show($kode)
    {
        $akun = Akun::with(['tipeAkun', 'induk', 'anak', 'statusData'])->findOrFail($kode);
        return view('akun.show', compact('akun'));
    }

    public function updateInduk(Request $request, $kode)
    {
        $data = $request->json()->all() ?: $request->all();
        $kodeInduk = $data['kode_induk'] ?? null;

        if ($kodeInduk && !Akun::where('kode', $kodeInduk)->exists()) {
            return response()->json(['error' => 'Akun induk tidak ditemukan.'], 422);
        }

        if ($kodeInduk) {
            if ($kodeInduk == $kode) {
                return response()->json(['error' => 'Akun tidak boleh menjadi induk dirinya sendiri.'], 422);
            }
            $descendantKodes = $this->getDescendantKodes($kode);
            if (in_array($kodeInduk, $descendantKodes)) {
                return response()->json(['error' => 'Relasi siklik terdeteksi.'], 422);
            }
        }

        $akun = Akun::findOrFail($kode);
        $akun->kode_induk = $kodeInduk ?: null;
        $akun->waktu_ubah = now();
        $akun->save();

        return response()->json(['success' => true, 'kode_induk' => $akun->kode_induk]);
    }

    private function getDescendantKodes($kode)
    {
        $descendants = [];
        $children = Akun::where('kode_induk', $kode)->pluck('kode')->toArray();
        foreach ($children as $child) {
            $descendants[] = $child;
            $descendants = array_merge($descendants, $this->getDescendantKodes($child));
        }
        return $descendants;
    }

    public function toggleAktif(Request $request, $kode)
    {
        $akun = Akun::findOrFail($kode);
        $akun->is_aktif = $akun->is_aktif ? 0 : 1;
        $akun->id_status_data = $akun->is_aktif ? 1 : -1;
        $akun->waktu_ubah = now();
        $akun->save();

        return response()->json([
            'success'  => true,
            'is_aktif' => (bool) $akun->is_aktif,
        ]);
    }

    public function export()
    {
        $filename = 'COA_Berkah_'.now()->format('Ymd_His').'.xlsx';
        return Excel::download(new AkunExport, $filename);
    }

    public function template()
    {
        return Excel::download(new AkunTemplateExport, 'Template_Import_Akun.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls']);

        $import = new AkunImport;
        Excel::import($import, $request->file('file'));

        $msg = 'Import berhasil.';
        if (!empty($import->errors)) {
            $msg .= ' Catatan: '.implode(' ', $import->errors);
        }

        return redirect()->route('akun.index')->with('success', $msg);
    }
}
