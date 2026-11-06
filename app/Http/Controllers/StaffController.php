<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Petugas;
use App\Models\User;
use App\Models\Kelas;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $petugas = Petugas::with('user')
            ->orderBy('jabatan')
            ->orderBy('id_petugas')
            ->get();

        return view('staff.index', compact('petugas'));
    }

    public function create()
    {
        $users = User::all();
        $kelas = Kelas::orderBy('nama_kelas')->get();
        return view('staff.create', compact('users', 'kelas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_user' => 'required|exists:tbl_user,id_user',
            'jabatan' => 'required|string|max:50',
            'no_telp' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:100',
            'status_petugas' => 'required|in:aktif,tidak_aktif',
        ]);

        Petugas::create([
            'id_user' => $request->id_user,
            'jabatan' => $request->jabatan,
            'no_telp' => $request->no_telp,
            'email' => $request->email,
            'status_petugas' => $request->status_petugas,
        ]);

        return redirect()->route('staff.index')
            ->with('success', 'Petugas berhasil ditambahkan!');
    }

    public function show($id)
    {
        $petugas = Petugas::with('user')->findOrFail($id);
        return view('staff.show', compact('petugas'));
    }

    public function edit($id)
    {
        $petugas = Petugas::findOrFail($id);
        $users = User::all();
        return view('staff.edit', compact('petugas', 'users'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_user' => 'required|exists:tbl_user,id_user',
            'jabatan' => 'required|string|max:50',
            'no_telp' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:100',
            'status_petugas' => 'required|in:aktif,tidak_aktif',
        ]);

        $petugas = Petugas::findOrFail($id);
        $petugas->update([
            'id_user' => $request->id_user,
            'jabatan' => $request->jabatan,
            'no_telp' => $request->no_telp,
            'email' => $request->email,
            'status_petugas' => $request->status_petugas,
        ]);

        return redirect()->route('staff.index')
            ->with('success', 'Data petugas berhasil diupdate!');
    }

    public function destroy($id)
    {
        $petugas = Petugas::findOrFail($id);
        $petugas->delete();

        return redirect()->route('staff.index')
            ->with('success', 'Petugas berhasil dihapus!');
    }

    public function getByRole(Request $request)
    {
        $role = $request->input('role');
        $petugas = Petugas::whereHas('user', function ($query) use ($role) {
            $query->where('role', $role);
        })->with('user')->orderBy('jabatan')->get();

        return response()->json($petugas);
    }

    public function kelasIndex()
    {
        $kelas = Kelas::withCount('siswa')
            ->orderBy('nama_kelas')
            ->get();

        return view('staff.kelas-index', compact('kelas'));
    }
}
