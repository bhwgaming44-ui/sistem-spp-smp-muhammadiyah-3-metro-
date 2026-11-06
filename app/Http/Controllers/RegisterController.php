<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Siswa;
use App\Models\WaliMurid;

class RegisterController extends Controller
{
    public function show()
    {
        try {
            $kelasList = \App\Models\Kelas::orderBy('nama_kelas')->get();
            $tahunAjaranList = \App\Models\TahunAjaran::orderBy('id_tahun_ajaran')->get();
        } catch (\Throwable $e) {
            $kelasList = collect();
            $tahunAjaranList = collect();
        }

        return view('register.register', compact('kelasList', 'tahunAjaranList'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'nisn' => 'required|string|max:20|unique:tbl_siswa,nisn',
            'nis' => 'required|string|max:20|unique:tbl_siswa,nis',
            'nama_siswa' => 'required|string|max:100',
            'jenis_kelamin' => 'required|in:Laki-Laki,Perempuan',
            'id_kelas' => 'required|exists:tbl_kelas,id_kelas',
            'id_tahun_ajaran' => 'required|exists:tbl_tahun_ajaran,id_tahun_ajaran',
            'nama_ortu' => 'required|string|max:100',
            'no_telp_ortu' => 'required|string|max:15',
            'email_ortu' => 'required|email|max:100',
            'alamat' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        DB::transaction(function () use ($request) {
            $siswa = Siswa::create([
                'nisn' => $request->nisn,
                'nis' => $request->nis,
                'nama_siswa' => $request->nama_siswa,
                'jenis_kelamin' => $request->jenis_kelamin,
                'id_kelas' => $request->id_kelas,
                'id_tahun_ajaran' => $request->id_tahun_ajaran,
                'alamat' => $request->alamat,
                'password' => bcrypt($request->password),
                'status_siswa' => 'aktif',
            ]);

            // Data orang tua/wali disimpan terpisah di tbl_wali_murid (1:1).
            WaliMurid::create([
                'id_siswa' => $siswa->id_siswa,
                'nama_wali' => $request->nama_ortu,
                'no_telp' => $request->no_telp_ortu,
                'email' => $request->email_ortu,
            ]);
        });

        return redirect()->route('register.show')
            ->with('success', 'Pendaftaran siswa berhasil! Silakan login dengan NISN dan password yang Anda buat.');
    }
}
