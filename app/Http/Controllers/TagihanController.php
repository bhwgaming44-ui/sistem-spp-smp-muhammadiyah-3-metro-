<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TagihanSpp;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\Petugas;

class TagihanController extends Controller
{
    public function index(Request $request)
    {
        $tagihans = TagihanSpp::with(['kelas', 'siswa', 'tahunAjaran'])
            ->orderBy('created_at', 'desc')
            ->get();

        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $tahunAjaranList = TahunAjaran::orderBy('id_tahun_ajaran')->get();

        $totalBelumLunas = TagihanSpp::where('status', 'belum_lunas')->count();
        $nominalBelumLunas = TagihanSpp::where('status', 'belum_lunas')->sum('nominal');

        return view('tagihan.index', compact(
            'tagihans', 'kelasList', 'tahunAjaranList',
            'totalBelumLunas', 'nominalBelumLunas'
        ));
    }

    public function create()
    {
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $tahunAjaranList = TahunAjaran::orderBy('id_tahun_ajaran')->get();
        $siswaList = Siswa::where('status_siswa', 'aktif')->orderBy('nama_siswa')->get();

        return view('tagihan.create', compact('kelasList', 'tahunAjaranList', 'siswaList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_kelas' => 'required|exists:tbl_kelas,id_kelas',
            'id_siswa' => 'nullable|exists:tbl_siswa,id_siswa',
            'id_tahun_ajaran' => 'nullable|exists:tbl_tahun_ajaran,id_tahun_ajaran',
            'id_spp' => 'nullable|exists:tbl_spp,id_spp',
            'id_user' => 'nullable|exists:tbl_user,id_user',
            'bulan' => 'required|string|max:20',
            'bulan_ke' => 'nullable|integer|between:1,12',
            'jatuh_tempo' => 'nullable|date',
            'nominal' => 'required|numeric',
            'status' => 'required|in:belum_lunas,lunas,dihapus',
            'keterangan' => 'nullable|string|max:255',
        ]);

        TagihanSpp::create($this->payloadTagihan($request));

        return redirect()->route('tagihan.index')
            ->with('success', 'Tagihan SPP berhasil dibuat!');
    }

    public function show($id)
    {
        $tagihan = TagihanSpp::with(['kelas', 'siswa', 'tahunAjaran'])->findOrFail($id);
        return view('tagihan.show', compact('tagihan'));
    }

    public function edit($id)
    {
        $tagihan = TagihanSpp::findOrFail($id);
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $tahunAjaranList = TahunAjaran::orderBy('id_tahun_ajaran')->get();
        $siswaList = Siswa::where('status_siswa', 'aktif')->orderBy('nama_siswa')->get();

        return view('tagihan.edit', compact('tagihan', 'kelasList', 'tahunAjaranList', 'siswaList'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_kelas' => 'required|exists:tbl_kelas,id_kelas',
            'id_siswa' => 'nullable|exists:tbl_siswa,id_siswa',
            'id_tahun_ajaran' => 'nullable|exists:tbl_tahun_ajaran,id_tahun_ajaran',
            'id_spp' => 'nullable|exists:tbl_spp,id_spp',
            'id_user' => 'nullable|exists:tbl_user,id_user',
            'bulan' => 'required|string|max:20',
            'bulan_ke' => 'nullable|integer|between:1,12',
            'jatuh_tempo' => 'nullable|date',
            'nominal' => 'required|numeric',
            'status' => 'required|in:belum_lunas,lunas,dihapus',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $tagihan = TagihanSpp::findOrFail($id);
        $tagihan->update($this->payloadTagihan($request));

        return redirect()->route('tagihan.index')
            ->with('success', 'Tagihan SPP berhasil diupdate!');
    }

    /**
     * Bangun atribut tagihan lengkap (termasuk relasi baru id_spp, id_user,
     * bulan_ke, dan jatuh_tempo) dari input form.
     */
    private function payloadTagihan(Request $request): array
    {
        $bulanKe = $request->filled('bulan_ke')
            ? (int) $request->bulan_ke
            : $this->bulanKeDari($request->bulan);

        $idSpp = $request->filled('id_spp')
            ? (int) $request->id_spp
            : $this->cariIdSpp($request->id_kelas, $request->id_tahun_ajaran);

        $idUser = $request->filled('id_user')
            ? (int) $request->id_user
            : $this->idUserSesi($request);

        return [
            'id_kelas' => $request->id_kelas,
            'id_siswa' => $request->id_siswa,
            'id_tahun_ajaran' => $request->id_tahun_ajaran,
            'id_spp' => $idSpp,
            'id_user' => $idUser,
            'bulan' => $request->bulan,
            'bulan_ke' => $bulanKe,
            'nominal' => $request->nominal,
            'jatuh_tempo' => $request->jatuh_tempo ?: now()->addDays(10)->toDateString(),
            'status' => $request->status,
            'keterangan' => $request->keterangan,
        ];
    }

    private function bulanKeDari(?string $bulan): int
    {
        $bulan = trim((string) $bulan);

        if (is_numeric($bulan)) {
            return max(1, min(12, (int) $bulan));
        }

        $map = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
            'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
            'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
        ];

        $kata = strtolower(explode(' ', $bulan)[0]);

        return $map[$kata] ?? (int) now()->format('n');
    }

    private function cariIdSpp($idKelas, $idTahunAjaran): ?int
    {
        if (!$idKelas || !$idTahunAjaran) {
            return null;
        }

        $tingkat = Kelas::where('id_kelas', $idKelas)->value('tingkat');

        if (!$tingkat) {
            return null;
        }

        return \App\Models\Spp::where('id_tahun_ajaran', $idTahunAjaran)
            ->where('tingkat', (string) $tingkat)
            ->value('id_spp');
    }

    private function idUserSesi(Request $request): ?int
    {
        $user = $request->session()->get('user');

        return is_array($user) ? ($user['id_user'] ?? null) : null;
    }

    public function destroy($id)
    {
        $tagihan = TagihanSpp::findOrFail($id);
        $tagihan->update([
            'status' => 'dihapus',
            'updated_at' => now(),
        ]);

        return redirect()->route('tagihan.index')
            ->with('success', 'Tagihan SPP berhasil dihapus!');
    }

    /**
     * Pencarian tagihan SPP secara real-time (dipakai AJAX / Fetch API).
     *
     * GET /api/tagihan/search?query=ahmad&status=belum_lunas&kelas=7A
     *
     * Mengembalikan JSON berisi daftar tagihan + data siswa/kelas yang cocok
     * sehingga tabel di Blade bisa diperbarui tanpa reload halaman.
     */
    public function search(Request $request)
    {
        $query = trim((string) $request->query('query', $request->query('q', '')));
        $status = (string) $request->query('status', '');
        $kelas = trim((string) $request->query('kelas', ''));

        $tagihans = TagihanSpp::with(['siswa', 'kelas', 'tahunAjaran'])
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($sub) use ($query) {
                    $sub->where('bulan', 'like', '%' . $query . '%')
                        ->orWhere('keterangan', 'like', '%' . $query . '%')
                        ->orWhereHas('siswa', function ($siswa) use ($query) {
                            $siswa->where('nama_siswa', 'like', '%' . $query . '%')
                                ->orWhere('nisn', 'like', '%' . $query . '%')
                                ->orWhere('nis', 'like', '%' . $query . '%');
                        })
                        ->orWhereHas('kelas', function ($k) use ($query) {
                            $k->where('nama_kelas', 'like', '%' . $query . '%');
                        });
                });
            })
            ->when(in_array($status, ['belum_lunas', 'lunas', 'dihapus'], true), function ($builder) use ($status) {
                $builder->where('status', $status);
            })
            ->when($kelas !== '', function ($builder) use ($kelas) {
                $builder->whereHas('kelas', function ($k) use ($kelas) {
                    $k->where('nama_kelas', $kelas);
                });
            })
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $data = $tagihans->map(function (TagihanSpp $tagihan) {
            $created = $tagihan->created_at;

            return [
                'id' => $tagihan->id_tagihan_spp,
                'id_tagihan' => $tagihan->id_tagihan_spp,
                'nisn' => optional($tagihan->siswa)->nisn,
                'nis' => optional($tagihan->siswa)->nis,
                'nama_siswa' => optional($tagihan->siswa)->nama_siswa ?? 'Tanpa Siswa',
                'kelas' => optional($tagihan->kelas)->nama_kelas ?? '-',
                'bulan' => $tagihan->bulan,
                'bulan_ke' => $tagihan->bulan_ke,
                'nominal' => (float) $tagihan->nominal,
                'status' => $tagihan->status,
                'status_label' => $tagihan->status === 'lunas' ? 'Lunas' : ($tagihan->status === 'dihapus' ? 'Dihapus' : 'Belum Lunas'),
                'tempo' => $tagihan->jatuh_tempo
                    ? $tagihan->jatuh_tempo->format('Y-m-d')
                    : ($created ? $created->copy()->addDays(10)->format('Y-m-d') : null),
                'tahun_ajaran' => optional($tagihan->tahunAjaran)->tahun_ajaran,
                'keterangan' => $tagihan->keterangan,
            ];
        });

        return response()->json([
            'status' => 'success',
            'query' => $query,
            'count' => $data->count(),
            'total_nominal' => $data->sum('nominal'),
            'data' => $data,
        ]);
    }

    /**
     * Daftar tahun ajaran dari tabel tbl_tahun_ajaran.
     *
     * Dipakai oleh dropdown "Tahun Ajaran" pada form penerbitan tagihan,
     * baik pada aplikasi Laravel maupun demo offline (mock backend).
     *
     * GET /api/tahun-ajaran
     */
    public function tahunAjaran()
    {
        try {
            $list = TahunAjaran::orderBy('tahun_ajaran')
                ->get(['id_tahun_ajaran', 'tahun_ajaran', 'status_aktif']);

            return response()->json([
                'status' => 'success',
                'count' => $list->count(),
                'data' => $list,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memuat daftar tahun ajaran: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    /**
     * Mengambil daftar tagihan belum lunas untuk siswa tertentu secara dinamis
     * Query: WHERE (id_siswa = :id OR nisn = :nisn) AND status = 'belum_lunas'
     */
    public function getUnpaidByStudent(Request $request, $identifier = null)
    {
        $identifier = $identifier ?? $request->query('nisn') ?? $request->query('student_id');

        $query = TagihanSpp::with(['kelas', 'siswa', 'tahunAjaran'])
            ->where('status', 'belum_lunas');

        if ($identifier) {
            $query->where(function ($q) use ($identifier) {
                $q->where('id_siswa', $identifier)
                  ->orWhereHas('siswa', function ($sq) use ($identifier) {
                      $sq->where('nisn', $identifier);
                  });
            });
        }

        $tagihans = $query->orderBy('created_at', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'count' => $tagihans->count(),
            'total_nominal' => $tagihans->sum('nominal'),
            'data' => $tagihans
        ]);
    }
}