<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunAjaran;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $query = Siswa::with(['kelas', 'tahunAjaran']);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_siswa', 'like', '%' . $search . '%')
                  ->orWhere('nisn', 'like', '%' . $search . '%')
                  ->orWhere('nis', 'like', '%' . $search . '%');
            });
        }

        if ($request->has('kelas') && $request->kelas) {
            $query->where('id_kelas', $request->kelas);
        }

        $siswa = $query->orderBy('nama_siswa')->paginate(15);
        $kelasList = Kelas::orderBy('nama_kelas')->get();

        return view('siswa.index', compact('siswa', 'kelasList'));
    }

    public function apiList(Request $request)
    {
        $query = Siswa::with(['kelas', 'tahunAjaran']);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_siswa', 'like', '%' . $search . '%')
                  ->orWhere('nisn', 'like', '%' . $search . '%')
                  ->orWhere('nis', 'like', '%' . $search . '%');
            });
        }

        $siswa = $query->orderBy('nama_siswa')->get();

        return response()->json($siswa);
    }

    public function show($nisn)
    {
        $siswa = Siswa::with(['kelas', 'tahunAjaran'])->where('nisn', $nisn)->firstOrFail();
        return view('siswa.show', compact('siswa'));
    }
}
