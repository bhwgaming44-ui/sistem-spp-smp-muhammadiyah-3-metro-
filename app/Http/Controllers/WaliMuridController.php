<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WaliMurid;
use App\Models\Siswa;
use App\Models\Kelas;

class WaliMuridController extends Controller
{
    public function index(Request $request)
    {
        $query = WaliMurid::with(['siswa.kelas', 'siswa.tahunAjaran']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_wali', 'like', '%' . $search . '%')
                  ->orWhere('no_telp', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhereHas('siswa', function ($siswa) use ($search) {
                      $siswa->where('nama_siswa', 'like', '%' . $search . '%')
                             ->orWhere('nisn', 'like', '%' . $search . '%')
                             ->orWhere('nis', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($request->filled('kelas')) {
            $query->whereHas('siswa.kelas', function ($kelas) use ($request) {
                $kelas->where('nama_kelas', $request->kelas);
            });
        }

        $waliMurid = $query->orderBy('nama_wali')->paginate(15)->withQueryString();
        $kelasList = Kelas::orderBy('nama_kelas')->get();

        return view('wali-murid.index', compact('waliMurid', 'kelasList'));
    }

    public function apiList(Request $request)
    {
        $query = WaliMurid::with(['siswa.kelas', 'siswa.tahunAjaran']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_wali', 'like', '%' . $search . '%')
                  ->orWhere('no_telp', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhereHas('siswa', function ($siswa) use ($search) {
                      $siswa->where('nama_siswa', 'like', '%' . $search . '%')
                             ->orWhere('nisn', 'like', '%' . $search . '%');
                  });
            });
        }

        return response()->json(
            $query->orderBy('nama_wali')->get()
        );
    }

    public function show($nisn)
    {
        $siswa = Siswa::with(['kelas', 'tahunAjaran', 'waliMurid'])
            ->where('nisn', $nisn)
            ->firstOrFail();

        $waliMurid = $siswa->waliMurid;

        return view('wali-murid.show', compact('siswa', 'waliMurid'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nisn' => 'required|string|max:10|exists:tbl_siswa,nisn',
            'nama_wali' => 'required|string|max:100',
            'no_telp' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:100',
        ]);

        $siswa = Siswa::where('nisn', $request->nisn)->firstOrFail();

        WaliMurid::updateOrCreate(
            ['id_siswa' => $siswa->id_siswa],
            [
                'nama_wali' => $request->nama_wali,
                'no_telp' => $request->no_telp,
                'email' => $request->email,
            ]
        );

        return back()->with('success', 'Data wali murid untuk siswa ' . $siswa->nama_siswa . ' berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_wali' => 'required|string|max:100',
            'no_telp' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:100',
        ]);

        $waliMurid = WaliMurid::findOrFail($id);
        $waliMurid->update([
            'nama_wali' => $request->nama_wali,
            'no_telp' => $request->no_telp,
            'email' => $request->email,
        ]);

        return back()->with('success', 'Data wali murid berhasil diperbarui.');
    }

    public function destroy($id)
    {
        WaliMurid::findOrFail($id)->delete();

        return back()->with('success', 'Data wali murid berhasil dihapus.');
    }
}
