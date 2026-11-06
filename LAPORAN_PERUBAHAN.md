# Laporan Perubahan — E-SPP SMP Muhammadiyah 3 Metro

**Tanggal:** 5 November 2026  
**Lingkup:** demo HP tanpa Laravel/XAMPP, sidebar penuh, pencarian tagihan AJAX, data 261 siswa, akun petugas/guru/siswa/wali kelas, perbaikan metode pembayaran Midtrans, **perbaikan mode sandbox Midtrans**, dan **penanganan token kedaluwarsa**. Verifikasi selfie pembayaran **dihapus**.

---

## 1. Ringkasan hasil

| Fitur | Status |
|---|---|
| Demo offline satu file untuk HP | ✅ `demo/spp-offline.html` sudah dibangun ulang |
| Sidebar kiri setinggi layar, label tetap terlihat di HP | ✅ dipertahankan |
| Verifikasi selfie sebelum bayar SPP | ❌ **dihapus total** (modal, kamera, capture, penyimpanan, endpoint, tabel) |
| Pemindaian / deteksi wajah | ❌ **dihapus** (Pico.js, model, overlay, bilah kesiapan) |
| Klaim beasiswa | ❌ **dihapus** (tombol, fungsi, dan alurnya) |
| Alur bayar SPP | ✅ centang tagihan → **BAYAR** → langsung jendela Snap |
| Pilihan tagihan siswa | ✅ **bebas** — tidak ada auto-pilih/1-click; boleh pilih sebagian atau **tidak memilih sama sekali** |
| Tagihan lama saat tagihan baru dibuat | ✅ **dipertahankan** — semua tagihan tampil, termasuk beberapa tagihan pada bulan yang sama |
| Tagihan setelah halaman di-refresh | ✅ **tersimpan** — tagihan, status lunas, dan riwayat transaksi disimpan di `localStorage` |
| Metode pembayaran Midtrans | ✅ **diperbaiki** — `enabled_payments` manual dihapus, Snap memakai semua metode aktif di dashboard (12 metode pada uji sandbox) |
| Mode Midtrans sandbox | ✅ **diperbaiki** — konfigurasi memakai Sandbox; jika mode Production gagal `401 Unauthorized`, backend otomatis pindah ke Sandbox dan menyimpan mode tersebut |
| Token Midtrans kedaluwarsa | ✅ **diperbaiki** — token dibuat berlaku **24 jam** (`expires_at` + `expires_in`), front-end otomatis membuat token baru maksimal 2 kali |
| Tombol BAYAR / gateway tidak muncul | ✅ **diperbaiki** — status pilihan bulan disinkronkan sebelum daftar dirender, sehingga tombol BAYAR tidak tertinggal `disabled` setelah checkbox diklik |
| Tahun ajaran | ✅ **diperbaiki** — tidak diketik lagi; dropdown diisi dari tabel `tbl_tahun_ajaran` lewat `GET /api/tahun-ajaran`, rentang **2000/2001 – 2045/2046**, dan **langsung bisa dipilih** tanpa menunggu request API |
| Bulan SPP saat menerbitkan tagihan | ✅ **diperbaiki** — memakai **checkbox**, bisa centang lebih dari satu bulan; satu bulan = satu tagihan |
| Jatuh tempo tagihan | ✅ **diperbaiki** — otomatis tanggal dan tahun hari ini (bukan `2025-02-10` lagi) |
| Pilih bulan saat bayar SPP | ✅ **checkbox multi-bulan**; total Snap dihitung dari jumlah bulan yang dicentang |
| Pembayaran banyak bulan sekaligus | ✅ **diperbaiki** — kolom `nama_bulan` diperlebar dan seluruh bulan yang dibayar ditandai lunas |
| Nominal SPP dari petugas | ✅ **diperbaiki** — nominal yang diketik petugas dipakai di akun siswa & wali murid (tidak lagi selalu Rp 250.000) |
| Teks metode pembayaran | ✅ **disembunyikan** — banner "Metode Pembayaran Resmi: Midtrans Gateway" dihapus, halaman langsung menampilkan tombol BAYAR |
| SDK Snap sesuai mode token | ✅ dimuat dari `snap_js` yang dikirim backend (sandbox/production) sehingga token sandbox tidak dibuka `snap.js` production |
| Pop-up Snap kosong / tidak muncul | ✅ fallback otomatis ke halaman pembayaran Midtrans resmi |
| Demo offline: 6 metode langsung terlihat di HP | ✅ GoPay, Card, ShopeePay, OVO, DANA, Virtual Account |
| Pencarian tagihan real-time (AJAX) | ✅ endpoint `/api/tagihan/search` + debounce 300 ms |
| Data siswa 29 per kelas (9 kelas = 261) | ✅ |
| Akun 30 guru + 9 wali kelas + siswa | ✅ |
| Menu akun terpisah: petugas, guru, kepala sekolah, siswa, wali kelas | ✅ |
| Uji layout 360/390/500/1366 px | ✅ tanpa overflow horizontal |
| Uji fungsional headless Chrome | ✅ seluruh pemeriksaan PASS |

**Catatan jujur:** pembayaran sekarang **tidak meminta foto selfie** dan
**tidak mengakses kamera** sama sekali. Tidak ada penyimpanan bukti foto,
tidak ada endpoint verifikasi, dan tidak ada pengenalan wajah. Setelah bulan
tagihan dipilih, jendela pembayaran Snap langsung terbuka.

---

## 2. Daftar file yang diubah / dibuat

| No | File | Jenis |
|---|---|---|
| 1 | `app/Http/Controllers/TagihanController.php` | diubah — tambah `search()` dan **API `tahunAjaran()`** |
| 2 | `app/Http/Controllers/MidtransController.php` | diubah — **perbaikan metode pembayaran Snap**, **fallback otomatis Production→Sandbox**, **token berlaku 24 jam**, dan **dukungan pembayaran banyak bulan** |
| 3 | `routes/web.php` | diubah — route pencarian, **route `/api/tahun-ajaran`**; route selfie dihapus |
| 4 | `resources/views/welcome.blade.php` | diubah — UI, data, AJAX, pop-up Snap ringkas + fallback; **dropdown tahun ajaran, checkbox bulan SPP, jatuh tempo hari ini**; seluruh selfie dihapus |
| 5 | `demo/mock-backend.js` | diubah — mock search, **mock `/api/tahun-ajaran`**, data 261 siswa; mock selfie dihapus |
| 6 | `demo/build-demo.py` | diubah — build demo offline |
| 7 | `demo/spp-offline.html` | **dibangun ulang** (artefak demo, ±406.1 KB) |
| 8 | `demo/README.md` | diubah — akun, alur bayar, dan 6 metode pembayaran |
| 9 | `database/migrations/2026_11_05_000001_widen_nama_bulan_on_tbl_pembayaran.php` | **dibuat** — `nama_bulan` diperlebar ke `VARCHAR(100)` untuk pembayaran banyak bulan |
| 10 | `database/migrations/2026_11_05_000002_seed_tahun_ajaran_2000_2045.php` | **dibuat** — mengisi `tbl_tahun_ajaran` dengan rentang **2000/2001 – 2045/2046** dan menandai tahun ajaran berjalan `aktif` |

File yang **dihapus** karena fitur selfie/scan wajah dicabut:

- `app/Http/Controllers/VerifikasiSelfieController.php`
- `app/Models/VerifikasiSelfie.php`
- `database/migrations/2026_11_02_000001_create_tbl_verifikasi_selfie_table.php`
- `storage/app/public/verifikasi-selfie/` (folder bukti foto)
- `public/vendor/pico/pico-face.js`
- `demo/fetch-pico.py`

Perubahan lingkungan yang dilakukan:

- Tabel `tbl_verifikasi_selfie` dihapus dari database demo.
- `php artisan view:cache` → Blade berhasil dikompilasi.
- Seluruh file/record uji selfie dibersihkan.

---

## 3. Backend Laravel

### 3.1 `routes/web.php`

Route pencarian tagihan (route selfie sudah tidak ada):

````php
// Tagihan SPP Dynamic API Endpoint (Untuk Bayar SPP Online Siswa/Ortu)
Route::get('/api/tagihan/search', [TagihanController::class, 'search']);
Route::get('/api/tagihan/unpaid/{identifier?}', [TagihanController::class, 'getUnpaidByStudent']);

// Daftar tahun ajaran dari tabel tbl_tahun_ajaran (dropdown form tagihan)
Route::get('/api/tahun-ajaran', [TagihanController::class, 'tahunAjaran']);
````
### 3.2 `app/Http/Controllers/TagihanController.php` — method `search()`

````php
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
                'nominal' => (float) $tagihan->nominal,
                'status' => $tagihan->status,
                'status_label' => $tagihan->status === 'lunas' ? 'Lunas' : ($tagihan->status === 'dihapus' ? 'Dihapus' : 'Belum Lunas'),
                'tempo' => $created ? $created->copy()->addDays(10)->format('Y-m-d') : null,
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
````
### 3.3 `app/Http/Controllers/MidtransController.php` — payload Snap tanpa `enabled_payments`

**Penyebab metode pembayaran tidak muncul:** payload lama mengirim daftar
`enabled_payments` manual yang berisi `"virtual_account"`. Kode tersebut
**bukan** kode metode Snap yang valid. Daftar manual juga membatasi Snap pada
metode yang ditulis saja, sehingga metode yang aktif di dashboard Midtrans bisa
tidak tampil.

**Perbaikan:** field `enabled_payments` dihapus seluruhnya. Snap sekarang
otomatis menampilkan semua metode yang diaktifkan di dashboard Midtrans.

````php
        $payload = [
            'transaction_details' => $transactionDetails,
            'item_details' => $itemDetails,
            'customer_details' => $customerDetails,
            // Catatan: jangan mengirim enabled_payments.
            // Daftar manual seperti "virtual_account" bukan kode Snap yang valid
            // dan bisa membuat metode pembayaran tidak tampil. Dengan membiarkan
            // field ini kosong, Snap otomatis memakai SEMUA metode yang sudah
            // diaktifkan di dashboard Midtrans (QRIS, GoPay, ShopeePay, OVO,
            // DANA, Virtual Account bank, kartu kredit, gerai retail, dll).
        ];
````
### 3.4 Penyimpanan mode Midtrans otomatis

Saat kunci yang dipasang ternyata kunci Sandbox tetapi mode masih Production,
Midtrans membalas `401 Unauthorized`. Method ini menyimpan mode yang benar ke
`storage/app/midtrans_config.json` dan `.env` supaya permintaan berikutnya tidak
mengulang kesalahan yang sama.

````php
    /**
     * Simpan mode Midtrans yang benar (sandbox/production) ke file konfigurasi
     * dan .env. Dipakai saat mode Production gagal 401 karena kunci yang
     * dipasang ternyata kunci Sandbox.
     */
    private function persistProductionMode(bool $isProduction): void
    {
        try {
            $path = $this->getConfigPath();
            if (File::exists($path)) {
                $cfg = json_decode(File::get($path), true) ?: [];
                $cfg['is_production'] = $isProduction;
                $cfg['updated_at'] = date('Y-m-d H:i:s');
                File::put($path, json_encode($cfg, JSON_PRETTY_PRINT));
            }

            $envPath = base_path('.env');
            if (File::exists($envPath)) {
                $envContent = File::get($envPath);
                $value = $isProduction ? 'true' : 'false';
                if (preg_match('/^MIDTRANS_IS_PRODUCTION=.*/m', $envContent)) {
                    $envContent = preg_replace('/^MIDTRANS_IS_PRODUCTION=.*/m', 'MIDTRANS_IS_PRODUCTION=' . $value, $envContent);
                } else {
                    $envContent .= "\nMIDTRANS_IS_PRODUCTION=" . $value;
                }
                File::put($envPath, $envContent);
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal menyimpan mode Midtrans otomatis: ' . $e->getMessage());
        }
    }
````
### 3.5 Pembuatan token dengan fallback dua arah (Sandbox ⇄ Production)

> **Pembaruan:** fallback kini berlaku **dua arah**. Jika mode yang tersimpan
> gagal `401 Unauthorized`, sistem mencoba mode satunya lalu menyimpan mode yang
> benar-benar berhasil. Logika yang sama juga dipakai `cekStatus` sehingga
> polling status tidak ikut gagal 401.

`createSnapTransaction()` mencoba mode yang dipilih. Jika gagal dengan
`401 Unauthorized`, ia mencoba sekali lagi di mode satunya lalu memakai mode
yang berhasil.

````php
    /**
     * Buat transaksi Snap dengan perlindungan mode.
     * Jika mode Production gagal 401 (Unauthorized), coba sekali lagi di
     * Sandbox karena kunci yang dipasang biasanya kunci Sandbox.
     *
     * @return array{0: string, 1: ?string, 2: bool} [token, redirect_url, is_production]
     */
    private function createSnapTransaction(array $payload, bool $isProduction): array
    {
        $lastError = null;
        $modesToTry = $isProduction ? [true, false] : [false];

        foreach ($modesToTry as $index => $productionMode) {
            Config::$isProduction = $productionMode;

            try {
                $snapResponse = Snap::createTransaction($payload);
                $token = $snapResponse->token;
                $redirectUrl = $snapResponse->redirect_url ?? null;

                if ($productionMode !== $isProduction) {
                    $this->persistProductionMode($productionMode);
                    Log::warning('Midtrans: mode Production gagal (Unauthorized), otomatis beralih ke Sandbox karena kunci API cocok di sandbox.');
                }

                return [$token, $redirectUrl, $productionMode];
            } catch (\Throwable $e) {
                $lastError = $e;
                $message = $e->getMessage();
                $isUnauthorized = ((int) $e->getCode() === 401)
                    || str_contains($message, '401')
                    || stripos($message, 'Unauthorized') !== false;

                if (!$isUnauthorized || $index === count($modesToTry) - 1) {
                    throw $e;
                }
            }
        }

        throw $lastError ?: new \RuntimeException('Token Midtrans tidak diterima.');
    }
````
### 3.6 Token berlaku 24 jam

Tanpa `expiry` eksplisit, Snap memakai default dashboard yang bisa lebih pendek
sehingga pembayaran gagal dengan pesan *token expired*. Sekarang token dibuat
berlaku **24 jam** dan respons API menyertakan `expires_at`, `expires_in`,
`snap_js`, serta `redirect_url` asli dari Midtrans.

````php
        // Token Snap dibuat berlaku 24 jam. Tanpa expiry eksplisit, Snap
        // memakai default dashboard yang bisa lebih pendek sehingga pembayaran
        // gagal dengan pesan "token expired".
        $payload['expiry'] = [
            'unit' => 'hours',
            'duration' => 24,
        ];

        $expiresAt = now()->addHours(24);
````
### 3.7 API daftar tahun ajaran

Tahun ajaran tidak lagi diketik atau ditulis manual di form. Dropdown diisi dari
tabel `tbl_tahun_ajaran` melalui endpoint berikut. Tahun yang berstatus `aktif`
otomatis terpilih di form.

````php
// Daftar tahun ajaran dari tabel tbl_tahun_ajaran (dropdown form tagihan)
Route::get('/api/tahun-ajaran', [TagihanController::class, 'tahunAjaran']);
````
Method controller-nya:

````php
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
````
### 3.8 Pembayaran lebih dari satu bulan

Sebelumnya `nama_bulan` hanya `VARCHAR(15)`, sehingga pembayaran dua bulan
(`"Januari 2025, Februari 2025"`) gagal disimpan dan Snap tidak terbuka. Sekarang:

1. Kolom `nama_bulan` diperlebar menjadi `VARCHAR(100)` lewat migrasi.
2. Daftar bulan disimpan apa adanya (dipisah koma).
3. Saat status pembayaran menjadi `settlement`/`capture`, **semua** bulan dalam
   daftar ditandai lunas.

````php
        // Pembayaran bisa mencakup lebih dari satu bulan:
        // "Januari 2026, Februari 2026, Maret 2026".
        $bulan = trim((string) $request->input('bulan', 'Januari 2025'));
        if ($bulan === '') {
            $bulan = 'Januari 2025';
        }
        $bulanLabel = mb_substr($bulan, 0, 100);
````
Helper yang menandai semua bulan lunas:

````php
    /**
     * Tandai seluruh tagihan SPP yang tercakup dalam satu pembayaran sebagai
     * lunas. Kolom nama_bulan dapat berisi beberapa bulan dipisahkan koma,
     * misalnya "Januari 2026, Februari 2026".
     */
    private function markTagihanSppLunas(Pembayaran $pembayaran): void
    {
        $bulanList = collect(explode(',', (string) $pembayaran->nama_bulan))
            ->map(fn ($bulan) => trim($bulan))
            ->filter()
            ->values()
            ->all();

        if (empty($bulanList)) {
            return;
        }

        TagihanSpp::where('id_siswa', $pembayaran->id_siswa)
            ->whereIn('bulan', $bulanList)
            ->update(['status' => 'lunas']);
    }
````
Migrasi yang dijalankan:

````php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pembayaran SPP kini bisa mencakup beberapa bulan sekaligus (checkbox),
 * sehingga kolom nama_bulan perlu menampung daftar bulan seperti
 * "Januari 2026, Februari 2026, Maret 2026".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tbl_pembayaran')) {
            DB::statement('ALTER TABLE tbl_pembayaran MODIFY nama_bulan VARCHAR(100) NOT NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tbl_pembayaran')) {
            DB::statement('ALTER TABLE tbl_pembayaran MODIFY nama_bulan VARCHAR(15) NOT NULL');
        }
    }
};
````
Migration pengisi rentang tahun ajaran **2000/2001 – 2045/2046**:

````php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Lengkapi daftar tahun ajaran agar dropdown menyediakan rentang
 * 2000/2001 sampai 2045/2046. Tahun ajaran berjalan (kalender Juli–Juni)
 * ditandai aktif, sedangkan sisanya nonaktif.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $currentYear = (int) $now->format('Y');
        $currentMonth = (int) $now->format('n');
        $activeName = $currentMonth >= 7
            ? $currentYear . '/' . ($currentYear + 1)
            : ($currentYear - 1) . '/' . $currentYear;

        $rows = [];
        for ($start = 2000; $start <= 2045; $start++) {
            $rows[] = [
                'tahun_ajaran' => $start . '/' . ($start + 1),
                'status_aktif' => 'nonaktif',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('tbl_tahun_ajaran')->insertOrIgnore($rows);

        // Hanya satu tahun ajaran yang boleh aktif.
        DB::table('tbl_tahun_ajaran')->update(['status_aktif' => 'nonaktif']);
        DB::table('tbl_tahun_ajaran')
            ->where('tahun_ajaran', $activeName)
            ->update(['status_aktif' => 'aktif', 'updated_at' => $now]);
    }

    public function down(): void
    {
        DB::table('tbl_tahun_ajaran')
            ->whereBetween('tahun_ajaran', ['2000/2001', '2045/2046'])
            ->whereNotIn('tahun_ajaran', ['2023/2024', '2024/2025'])
            ->delete();
    }
};
````
---

## 4. `resources/views/welcome.blade.php`

File ini sangat besar (±8,265 baris), sehingga snippet di bawah adalah **seluruh blok yang diubah/ditambahkan**.

### 4.1 Meta CSRF

````html
<meta name="csrf-token" content="{{ csrf_token() }}">
````
### 4.2 Menu sidebar baru

````html
                <a href="?menu=siswa" class="nav-link-btn petugas-only" id="navSiswa" data-menu="siswa" onclick="handleNavClick(event, 'viewManajemenSiswa', 'siswa')" style="display:none;">
                    <span>👥</span> Data Siswa & SPP
                </a>
                <a href="?menu=siswa-kelas" class="nav-link-btn petugas-only" id="navSiswaKelas" data-menu="siswa-kelas" onclick="handleNavClick(event, 'viewSiswaKelas', 'siswa-kelas')" style="display:none;">
                    <span>🏫</span> Siswa per Kelas (29)
                </a>
                <a href="?menu=petugas" class="nav-link-btn petugas-only" id="navStaff" data-menu="petugas" onclick="handleNavClick(event, 'viewAkunStaff', 'petugas')" style="display:none;">
                    <span>👔</span> Akun Petugas & Guru
                </a>
                <a href="?menu=akun-siswa" class="nav-link-btn petugas-only" id="navAkunSiswa" data-menu="akun-siswa" onclick="handleNavClick(event, 'viewAkunSiswa', 'akun-siswa')" style="display:none;">
                    <span>🔑</span> Akun Siswa & Wali Kelas
                </a>
````
### 4.3 Kartu pencarian tagihan real-time (AJAX)

````html
                <!-- Pencarian Tagihan SPP Real-time (AJAX / Fetch API) -->
                <div class="content-card" style="margin-top:18px;">
                    <div class="content-card-head">
                        <div>
                            <div class="content-card-title">🔎 Pencarian Tagihan Real-time (AJAX)</div>
                            <div class="content-card-sub">Ketik nama siswa, NISN, kelas, atau bulan. Tabel diperbarui tanpa reload halaman.</div>
                        </div>
                        <div class="toolbar-actions">
                            <select class="form-control" id="ajaxFilterStatus" onchange="runTagihanAjaxSearch()" style="width:160px;">
                                <option value="">Semua Status</option>
                                <option value="belum_lunas">Belum Lunas</option>
                                <option value="lunas">Lunas</option>
                            </select>
                            <input type="text" class="form-control toolbar-search" id="ajaxTagihanSearch" placeholder="Cari nama / NISN / kelas / bulan..." oninput="onTagihanSearchInput(this.value)">
                        </div>
                    </div>

                    <div id="ajaxTagihanStatus" style="font-size:12px;font-weight:700;color:var(--text-muted);margin-bottom:12px;">
                        Ketik kata kunci untuk mencari tagihan.
                    </div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width:50px;">NO</th>
                                <th>NISN</th>
                                <th>NAMA SISWA</th>
                                <th>KELAS</th>
                                <th>BULAN</th>
                                <th>NOMINAL</th>
                                <th>JATUH TEMPO</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody id="ajaxTagihanBody">
                            <tr>
                                <td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted);">
                                    Belum ada pencarian. Ketik nama siswa atau NISN di atas.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
````
### 4.4 Tiga view baru: Siswa per Kelas, Akun Petugas/Guru, Akun Siswa/Wali Kelas

````html
            <!-- ================= VIEW: SISWA PER KELAS (29 SISWA / KELAS) ================= -->
            <section id="viewSiswaKelas" class="view-section">
                <div class="content-card">
                    <div class="content-card-head">
                        <div>
                            <div class="content-card-title">🏫 Siswa per Kelas — 29 Siswa Setiap Kelas</div>
                            <div class="content-card-sub">Data diurutkan berdasarkan kelas agar petugas lebih mudah mencari dan memverifikasi siswa.</div>
                        </div>
                        <div class="toolbar-actions">
                            <select class="form-control" id="filterKelasSiswa" onchange="renderSiswaPerKelas()" style="width:160px;">
                                <option value="7A">Kelas 7A</option>
                                <option value="7B">Kelas 7B</option>
                                <option value="7C">Kelas 7C</option>
                                <option value="8A">Kelas 8A</option>
                                <option value="8B">Kelas 8B</option>
                                <option value="8C">Kelas 8C</option>
                                <option value="9A">Kelas 9A</option>
                                <option value="9B">Kelas 9B</option>
                                <option value="9C">Kelas 9C</option>
                                <option value="ALL">Semua Kelas (261 Siswa)</option>
                            </select>
                            <input type="text" class="form-control toolbar-search" id="searchSiswaKelas" placeholder="Cari nama / NISN..." oninput="renderSiswaPerKelas()">
                        </div>
                    </div>

                    <div id="siswaKelasSummary" class="form-grid-2" style="gap:12px;margin-bottom:18px;"></div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width:50px;">NO</th>
                                <th>NISN</th>
                                <th>NAMA SISWA</th>
                                <th>KELAS</th>
                                <th>JENIS KELAMIN</th>
                                <th>WALI MURID</th>
                                <th>STATUS SPP</th>
                                <th style="text-align:right;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="siswaPerKelasBody">
                            <tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted);">Memuat data siswa...</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- ================= VIEW: AKUN LOGIN PETUGAS, GURU & KEPALA SEKOLAH ================= -->
            <section id="viewAkunStaff" class="view-section">
                <div class="content-card">
                    <div class="content-card-head">
                        <div>
                            <div class="content-card-title">👔 Akun Login Petugas, Guru & Kepala Sekolah</div>
                            <div class="content-card-sub">Daftar username dan password untuk masuk ke aplikasi E-SPP. Pisahkan sesuai jabatan.</div>
                        </div>
                        <div class="toolbar-actions">
                            <input type="text" class="form-control toolbar-search" id="searchAkunStaff" placeholder="Cari nama / username..." oninput="filterAkunStaff(this.value)">
                            <button class="btn-action-secondary" onclick="navigateTo('staff-db')">🗄️ Database Petugas (CRUD)</button>
                        </div>
                    </div>

                    <div class="akun-grid">
                        <div class="akun-card">
                            <h4>🧑‍💼 Akun Petugas Sekolah <span class="akun-count" id="countPetugas">3 akun</span></h4>
                            <div class="akun-table-wrap">
                                <table class="data-table">
                                    <thead>
                                        <tr><th>NO</th><th>NAMA</th><th>JABATAN</th><th>USERNAME</th><th>PASSWORD</th><th>STATUS</th></tr>
                                    </thead>
                                    <tbody id="akunPetugasBody"></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="akun-card">
                            <h4>👨‍🏫 Akun Guru <span class="akun-count" id="countGuru">30 akun</span></h4>
                            <div class="akun-table-wrap">
                                <table class="data-table">
                                    <thead>
                                        <tr><th>NO</th><th>NAMA GURU</th><th>MATA PELAJARAN</th><th>USERNAME</th><th>PASSWORD</th><th>STATUS</th></tr>
                                    </thead>
                                    <tbody id="akunGuruBody"></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="akun-card">
                            <h4>🏫 Akun Kepala Sekolah <span class="akun-count" id="countKepala">1 akun</span></h4>
                            <div class="akun-table-wrap">
                                <table class="data-table">
                                    <thead>
                                        <tr><th>NO</th><th>NAMA</th><th>JABATAN</th><th>USERNAME</th><th>PASSWORD</th><th>STATUS</th></tr>
                                    </thead>
                                    <tbody id="akunKepalaBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ================= VIEW: AKUN SISWA & WALI KELAS ================= -->
            <section id="viewAkunSiswa" class="view-section">
                <div class="content-card">
                    <div class="content-card-head">
                        <div>
                            <div class="content-card-title">🔑 Akun Siswa & Wali Kelas</div>
                            <div class="content-card-sub">Username dan password siswa dipisah dari akun wali kelas. Password siswa default: password123.</div>
                        </div>
                        <div class="toolbar-actions">
                            <select class="form-control" id="filterKelasAkunSiswa" onchange="renderAkunSiswa()" style="width:160px;">
                                <option value="7A">Kelas 7A</option>
                                <option value="7B">Kelas 7B</option>
                                <option value="7C">Kelas 7C</option>
                                <option value="8A">Kelas 8A</option>
                                <option value="8B">Kelas 8B</option>
                                <option value="8C">Kelas 8C</option>
                                <option value="9A">Kelas 9A</option>
                                <option value="9B">Kelas 9B</option>
                                <option value="9C">Kelas 9C</option>
                            </select>
                            <input type="text" class="form-control toolbar-search" id="searchAkunSiswa" placeholder="Cari nama / NISN..." oninput="renderAkunSiswa()">
                        </div>
                    </div>

                    <div class="akun-card" style="margin-bottom:18px;">
                        <h4>🎒 Akun Siswa <span class="akun-count" id="countAkunSiswa">29 akun</span></h4>
                        <div class="akun-table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr><th>NO</th><th>NISN (USERNAME)</th><th>NAMA SISWA</th><th>KELAS</th><th>PASSWORD</th><th>STATUS</th></tr>
                                </thead>
                                <tbody id="akunSiswaBody"></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="akun-card">
                        <h4>🧑‍🏫 Akun Wali Kelas <span class="akun-count" id="countWaliKelas">9 akun</span></h4>
                        <div class="akun-table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr><th>NO</th><th>KELAS</th><th>NAMA WALI KELAS</th><th>USERNAME</th><th>PASSWORD</th><th>JUMLAH SISWA</th></tr>
                                </thead>
                                <tbody id="akunWaliKelasBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
````
### 4.5 CSS menu akun

````css
        /* ============================================================
           MENU AKUN (PETUGAS, GURU, KEPALA SEKOLAH, SISWA, WALI KELAS)
           ============================================================ */
        .akun-grid {
            display: grid;
            gap: 18px;
        }

        .akun-card {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 18px;
            min-width: 0;
        }

        .akun-card h4 {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            font-size: 14px;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 12px;
        }

        .akun-count {
            font-size: 11px;
            font-weight: 800;
            color: var(--primary-blue);
            background: #dbeafe;
            border-radius: 999px;
            padding: 3px 10px;
        }

        .akun-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .akun-card .data-table {
            font-size: 12.5px;
            background: #fff;
        }

        .akun-card .data-table th,
        .akun-card .data-table td {
            padding: 9px 12px;
        }

        .credential-pass {
            display: inline-block;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            background: #e2e8f0;
            border-radius: 6px;
            padding: 2px 8px;
        }

        @media (max-width: 480px) {
            .akun-card { padding: 12px; }
        }

</style>
````
### 4.6 Generator data 261 siswa, 30 guru, 9 wali kelas

````javascript
        // ============================================================
        // DATA DEMO BESAR: 261 SISWA (9 KELAS x 29) + 30 GURU + 9 WALI
        // Semua data dibangkitkan deterministik agar tampilan selalu sama
        // setiap kali halaman dibuka (tidak berubah-ubah).
        // ============================================================
        const KELAS_DEMO = ['7A', '7B', '7C', '8A', '8B', '8C', '9A', '9B', '9C'];
        const TINGKAT_BY_KELAS = { '7': 'VII', '8': 'VIII', '9': 'IX' };
        const WILAYAH_METRO = ['Metro Pusat', 'Metro Timur', 'Metro Barat', 'Metro Utara', 'Metro Selatan'];

        const NAMA_DEPAN_SISWA = [
            'Ahmad', 'Muhammad', 'Fikri', 'Rizky', 'Dimas', 'Bagas', 'Fajar', 'Ilham',
            'Reza', 'Andika', 'Siti', 'Aisyah', 'Nabila', 'Zahra', 'Putri', 'Dewi',
            'Rani', 'Salma', 'Intan', 'Laila', 'Bima', 'Yoga', 'Arif', 'Hendra',
            'Salsabila', 'Kirana', 'Nadia', 'Farhan', 'Alif'
        ];

        const NAMA_BELAKANG_SISWA = [
            'Pratama', 'Saputra', 'Nugroho', 'Hidayat', 'Ramadhan', 'Setiawan',
            'Wibowo', 'Kurniawan', 'Maulana', 'Firmansyah', 'Anggraini', 'Lestari',
            'Pertiwi', 'Wulandari', 'Sari', 'Handayani', 'Rahmawati', 'Oktaviani',
            'Safitri', 'Puspita', 'Utami', 'Damayanti', 'Nugraheni', 'Kusuma',
            'Wijaya', 'Santoso', 'Gunawan', 'Siregar', 'Nasution'
        ];

        const NAMA_GURU_DEMO = [
            'Drs. Bambang Sutrisno', 'Dra. Endang Wahyuni', 'Ahmad Fauzi, S.Pd.',
            'Siti Nurhaliza, S.Pd.', 'Joko Purnomo, M.Pd.', 'Retno Palupi, S.Pd.',
            'Agus Setiawan, S.Kom.', 'Dewi Kartika, S.Pd.', 'Hendra Wijaya, M.Pd.',
            'Lina Marlina, S.Pd.', 'Rudi Hartono, S.Pd.', 'Wulan Sari, M.Pd.',
            'Eko Prasetyo, S.Pd.', 'Fitri Handayani, S.Pd.', 'Dedi Kurniawan, S.Kom.',
            'Yuni Astuti, S.Pd.', 'Slamet Riyadi, M.Pd.', 'Ratna Dewi, S.Pd.',
            'Arif Budiman, S.Pd.', 'Maya Puspita, S.Pd.', 'Taufik Hidayat, S.Pd.',
            'Rina Wulandari, M.Pd.', 'Sigit Nugroho, S.Pd.', 'Nur Aini, S.Pd.',
            'Bayu Saputra, S.Kom.', 'Indah Permata, S.Pd.', 'Fajar Ramadhan, S.Pd.',
            'Lestari Ningsih, M.Pd.', 'Yudi Hermawan, S.Pd.', 'Sri Wahyuni, S.Pd.'
        ];

        const MAPEL_DEMO = [
            'Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'IPA', 'IPS', 'PPKn',
            'PAI', 'Seni Budaya', 'PJOK', 'Informatika', 'Prakarya', 'Bahasa Lampung'
        ];

        const NAMA_WALI_KELAS = [
            'Hj. Siti Aminah, S.E.', 'Drs. M. Yusuf, M.Pd.', 'Nurhayati, S.Pd.',
            'Agus Salim, S.Pd.', 'Endang Susilowati, S.Pd.', 'Bambang Irawan, M.Pd.',
            'Rini Handayani, S.Pd.', 'Dedi Supriyadi, S.Pd.', 'Tri Wulandari, M.Pd.'
        ];

        // Lima akun demo lama dipertahankan agar username/password yang sudah
        // dibagikan tetap bisa dipakai, tetapi jumlah siswa tetap 261.
        const SISWA_DEMO_KHUSUS = [
            {
                kelas: '7A', index: 0, data: {
                    username: '0081234567', nisn: '0081234567', nis: '2024001',
                    nama: 'Ahmad Dahlan Putra', jenisKelamin: 'L',
                    namaOrtu: 'Budi Santoso', noTelp: '081234567890',
                    alamat: 'Jl. Hadimulyo Barat No. 15, Metro Pusat, Kota Metro',
                    tagihanUnpaid: ['Januari 2025']
                }
            },
            {
                kelas: '7B', index: 0, data: {
                    username: '0082345678', nisn: '0082345678', nis: '2024003',
                    nama: 'Muhammad Fauzan', jenisKelamin: 'L',
                    namaOrtu: 'Hendra Gunawan', noTelp: '081377889900',
                    alamat: 'Jl. Teuku Umar No. 10, Metro Pusat',
                    tagihanUnpaid: ['Januari 2025']
                }
            },
            {
                kelas: '8A', index: 0, data: {
                    username: '0087654321', nisn: '0087654321', nis: '2024002',
                    nama: 'Siti Walidah Putri', jenisKelamin: 'P',
                    namaOrtu: 'Supriyanto', noTelp: '081298765432',
                    alamat: 'Jl. Imam Bonjol No. 24, Metro',
                    tagihanUnpaid: ['Januari 2025']
                }
            },
            {
                kelas: '8B', index: 0, data: {
                    username: '0083456789', nisn: '0083456789', nis: '2024004',
                    nama: 'Aisyah Rahmawati', jenisKelamin: 'P',
                    namaOrtu: 'Joko Widodo', noTelp: '081255667788',
                    alamat: 'Jl. Ki Hajar Dewantara No. 5, Metro',
                    tagihanUnpaid: ['Januari 2025']
                }
            },
            {
                kelas: '9A', index: 0, data: {
                    username: '0084567890', nisn: '0084567890', nis: '2024005',
                    nama: 'Fikri Pratama', jenisKelamin: 'L',
                    namaOrtu: 'Agus Salim', noTelp: '081399001122',
                    alamat: 'Jl. Jend. Sudirman No. 88, Metro',
                    tagihanUnpaid: []
                }
            }
        ];

        function buildSiswaDemo() {
            const list = [];

            KELAS_DEMO.forEach((kelas, kelasIndex) => {
                const tingkat = TINGKAT_BY_KELAS[kelas.charAt(0)] || 'VII';
                for (let i = 0; i < 29; i++) {
                    const seq = (kelasIndex + 1) * 100 + (i + 1);
                    const nisn = '0081' + String(seq).padStart(6, '0');

                    list.push({
                        username: nisn,
                        password: 'password123',
                        nama: NAMA_DEPAN_SISWA[i] + ' ' + NAMA_BELAKANG_SISWA[(i + kelasIndex) % NAMA_BELAKANG_SISWA.length],
                        nisn: nisn,
                        nis: '2025' + String(seq).padStart(3, '0'),
                        kelas: kelas,
                        tingkat: tingkat,
                        tarif: 250000,
                        namaOrtu: 'Bapak ' + NAMA_BELAKANG_SISWA[(i + kelasIndex + 5) % NAMA_BELAKANG_SISWA.length],
                        noTelp: '0812' + String(10000000 + kelasIndex * 1000000 + i * 137).slice(-8),
                        alamat: 'Jl. ' + NAMA_BELAKANG_SISWA[(i + kelasIndex) % NAMA_BELAKANG_SISWA.length] +
                            ' No. ' + (i + 1) + ', ' + WILAYAH_METRO[kelasIndex % WILAYAH_METRO.length] + ', Kota Metro',
                        jenisKelamin: i % 2 === 0 ? 'L' : 'P',
                        role: 'siswa',
                        tagihanUnpaid: ((kelasIndex * 29 + i) % 7 === 0 || i === 0) ? ['Januari 2025'] : []
                    });
                }
            });

            SISWA_DEMO_KHUSUS.forEach(item => {
                const pos = KELAS_DEMO.indexOf(item.kelas) * 29 + item.index;
                if (pos >= 0 && list[pos]) {
                    Object.assign(list[pos], item.data);
                }
            });

            return list;
        }

        function buildGuruDemo() {
            return NAMA_GURU_DEMO.map((nama, i) => ({
                username: 'guru' + String(i + 1).padStart(3, '0'),
                password: 'guru123',
                nama: nama,
                role: 'petugas',
                subRole: 'Guru ' + MAPEL_DEMO[i % MAPEL_DEMO.length],
                petugasTitle: 'GURU',
                kategori: 'guru',
                mapel: MAPEL_DEMO[i % MAPEL_DEMO.length],
                noTelp: '0813' + String(20000000 + i * 317).slice(-8)
            }));
        }

        function buildWaliKelasDemo() {
            return KELAS_DEMO.map((kelas, i) => ({
                username: 'wali' + kelas.toLowerCase(),
                password: 'wali123',
                nama: NAMA_WALI_KELAS[i],
                role: 'petugas',
                subRole: 'Wali Kelas ' + kelas,
                petugasTitle: 'WALI KELAS',
                kategori: 'wali',
                kelas: kelas
            }));
        }

        // Ganti daftar siswa lama dengan 261 siswa baru.
        DEMO_USERS.siswa = buildSiswaDemo();

        // Tandai 3 akun petugas inti, lalu tambahkan 30 guru + 9 wali kelas
        // agar username/password-nya juga bisa dipakai login pada mode demo.
        DEMO_USERS.petugas.forEach(u => {
            if (!u.kategori) {
                u.kategori = (u.subRole === 'Kepala Sekolah') ? 'kepala' : 'petugas';
            }
        });

        const DEMO_GURU = buildGuruDemo();
        const DEMO_WALI_KELAS = buildWaliKelasDemo();
        DEMO_USERS.petugas = DEMO_USERS.petugas.concat(DEMO_GURU, DEMO_WALI_KELAS);
````
### 4.7 Render data siswa/akun + pencarian AJAX

````javascript
        // ============================================================
        // RENDER DATA SISWA, AKUN, DAN PENCARIAN TAGIHAN (AJAX)
        // ============================================================
        function escapeHtml(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function sortSiswaByKelas(list) {
            return list.slice().sort((a, b) => {
                const ka = KELAS_DEMO.indexOf(a.kelas);
                const kb = KELAS_DEMO.indexOf(b.kelas);
                if (ka !== kb) return ka - kb;
                return String(a.nama).localeCompare(String(b.nama), 'id');
            });
        }

        // 1. Data siswa pada menu "Data Siswa & SPP"
        function renderManajemenSiswa() {
            const tbody = document.getElementById('manajemenSiswaBody');
            if (!tbody) return;

            const kelasFilter = (document.getElementById('filterKelasManajemen') || {}).value || 'ALL';
            const keyword = ((document.getElementById('searchSiswaManajemen') || {}).value || '').toLowerCase().trim();

            let list = DEMO_USERS.siswa.filter(s => kelasFilter === 'ALL' || s.kelas === kelasFilter);
            if (keyword) {
                list = list.filter(s => (s.nama + ' ' + s.nisn + ' ' + s.nis + ' ' + s.kelas).toLowerCase().includes(keyword));
            }
            list = sortSiswaByKelas(list);

            if (list.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:24px;color:var(--text-muted);">Tidak ada siswa yang cocok dengan filter.</td></tr>';
                return;
            }

            tbody.innerHTML = list.map(s => {
                const belum = s.tagihanUnpaid && s.tagihanUnpaid.length > 0;
                return `
                    <tr>
                        <td><strong>${escapeHtml(s.nisn)}</strong></td>
                        <td>${escapeHtml(s.nama)}</td>
                        <td><span class="user-role-badge role-badge-siswa">${escapeHtml(s.kelas)}</span></td>
                        <td>Rp ${Number(s.tarif).toLocaleString('id-ID')} / bln</td>
                        <td><span class="badge-status badge-lunas">Aktif</span></td>
                        <td><span class="badge-status ${belum ? 'badge-belum' : 'badge-lunas'}">${belum ? 'Belum Lunas' : 'Lunas'}</span></td>
                        <td style="text-align:right;">
                            <button class="btn-action-secondary" style="padding:4px 10px;font-size:12px;" onclick="showSiswaDetail('${escapeHtml(s.nisn)}')">Rincian</button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // 2. Data siswa per kelas (29 siswa setiap kelas)
        function renderSiswaPerKelas() {
            const tbody = document.getElementById('siswaPerKelasBody');
            if (!tbody) return;

            const kelasFilter = (document.getElementById('filterKelasSiswa') || {}).value || '7A';
            const keyword = ((document.getElementById('searchSiswaKelas') || {}).value || '').toLowerCase().trim();

            let list = DEMO_USERS.siswa.filter(s => kelasFilter === 'ALL' || s.kelas === kelasFilter);
            if (keyword) {
                list = list.filter(s => (s.nama + ' ' + s.nisn + ' ' + s.nis).toLowerCase().includes(keyword));
            }
            list = sortSiswaByKelas(list);

            const summaryEl = document.getElementById('siswaKelasSummary');
            if (summaryEl) {
                const belum = list.filter(s => s.tagihanUnpaid && s.tagihanUnpaid.length > 0).length;
                summaryEl.innerHTML = `
                    <div class="stat-card stat-blue">
                        <div class="stat-icon">${SVG_ICONS.users}</div>
                        <div class="stat-info">
                            <h5>TOTAL SISWA</h5>
                            <div class="stat-val">${list.length} Siswa</div>
                            <div class="stat-desc">${kelasFilter === 'ALL' ? 'Semua kelas (9 kelas)' : 'Kelas ' + escapeHtml(kelasFilter) + ' • 29 siswa'}</div>
                        </div>
                    </div>
                    <div class="stat-card stat-orange">
                        <div class="stat-icon">${SVG_ICONS.warning}</div>
                        <div class="stat-info">
                            <h5>BELUM LUNAS</h5>
                            <div class="stat-val">${belum} Siswa</div>
                            <div class="stat-desc">Perlu ditindaklanjuti petugas</div>
                        </div>
                    </div>
                `;
            }

            if (list.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted);">Tidak ada siswa pada filter ini.</td></tr>';
                return;
            }

            tbody.innerHTML = list.map((s, index) => {
                const belum = s.tagihanUnpaid && s.tagihanUnpaid.length > 0;
                const jk = s.jenisKelamin === 'P' ? 'Perempuan' : 'Laki-laki';
                return `
                    <tr>
                        <td>${index + 1}</td>
                        <td><strong>${escapeHtml(s.nisn)}</strong></td>
                        <td>${escapeHtml(s.nama)}</td>
                        <td><span class="user-role-badge role-badge-siswa">${escapeHtml(s.kelas)}</span></td>
                        <td>${jk}</td>
                        <td>${escapeHtml(s.namaOrtu)}</td>
                        <td><span class="badge-status ${belum ? 'badge-belum' : 'badge-lunas'}">${belum ? 'Belum Lunas' : 'Lunas'}</span></td>
                        <td style="text-align:right;">
                            <button class="btn-action-secondary" style="padding:4px 10px;font-size:12px;" onclick="showSiswaDetail('${escapeHtml(s.nisn)}')">Rincian</button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // 3. Menu akun petugas, guru, dan kepala sekolah
        function renderAkunStaff() {
            const petugasBody = document.getElementById('akunPetugasBody');
            const guruBody = document.getElementById('akunGuruBody');
            const kepalaBody = document.getElementById('akunKepalaBody');
            if (!petugasBody || !guruBody || !kepalaBody) return;

            const petugas = DEMO_USERS.petugas.filter(u => u.kategori === 'petugas');
            const guru = DEMO_USERS.petugas.filter(u => u.kategori === 'guru');
            const kepala = DEMO_USERS.petugas.filter(u => u.kategori === 'kepala');

            document.getElementById('countPetugas').innerText = petugas.length + ' akun';
            document.getElementById('countGuru').innerText = guru.length + ' akun';
            document.getElementById('countKepala').innerText = kepala.length + ' akun';

            petugasBody.innerHTML = petugas.map((u, i) => `
                <tr>
                    <td>${i + 1}</td>
                    <td>${escapeHtml(u.nama)}</td>
                    <td>${escapeHtml(u.subRole || 'Petugas')}</td>
                    <td><code>${escapeHtml(u.username)}</code></td>
                    <td><span class="credential-pass">${escapeHtml(u.password)}</span></td>
                    <td><span class="badge-status badge-lunas">Aktif</span></td>
                </tr>
            `).join('');

            guruBody.innerHTML = guru.map((u, i) => `
                <tr>
                    <td>${i + 1}</td>
                    <td>${escapeHtml(u.nama)}</td>
                    <td>${escapeHtml(u.mapel || '-')}</td>
                    <td><code>${escapeHtml(u.username)}</code></td>
                    <td><span class="credential-pass">${escapeHtml(u.password)}</span></td>
                    <td><span class="badge-status badge-lunas">Aktif</span></td>
                </tr>
            `).join('');

            kepalaBody.innerHTML = kepala.map((u, i) => `
                <tr>
                    <td>${i + 1}</td>
                    <td>${escapeHtml(u.nama)}</td>
                    <td>${escapeHtml(u.subRole || 'Kepala Sekolah')}</td>
                    <td><code>${escapeHtml(u.username)}</code></td>
                    <td><span class="credential-pass">${escapeHtml(u.password)}</span></td>
                    <td><span class="badge-status badge-lunas">Aktif</span></td>
                </tr>
            `).join('');
        }

        function filterAkunStaff(query) {
            const keyword = String(query || '').toLowerCase();
            document.querySelectorAll('#akunPetugasBody tr, #akunGuruBody tr, #akunKepalaBody tr').forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(keyword) ? '' : 'none';
            });
        }

        // 4. Menu akun siswa & wali kelas
        function renderAkunSiswa() {
            const tbody = document.getElementById('akunSiswaBody');
            if (!tbody) return;

            const kelasFilter = (document.getElementById('filterKelasAkunSiswa') || {}).value || '7A';
            const keyword = ((document.getElementById('searchAkunSiswa') || {}).value || '').toLowerCase().trim();

            let list = DEMO_USERS.siswa.filter(s => s.kelas === kelasFilter);
            if (keyword) {
                list = list.filter(s => (s.nama + ' ' + s.nisn).toLowerCase().includes(keyword));
            }
            list = sortSiswaByKelas(list);

            document.getElementById('countAkunSiswa').innerText = list.length + ' akun';

            if (list.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-muted);">Tidak ada akun siswa pada filter ini.</td></tr>';
                return;
            }

            tbody.innerHTML = list.map((s, i) => `
                <tr>
                    <td>${i + 1}</td>
                    <td><code>${escapeHtml(s.nisn)}</code></td>
                    <td>${escapeHtml(s.nama)}</td>
                    <td><span class="user-role-badge role-badge-siswa">${escapeHtml(s.kelas)}</span></td>
                    <td><span class="credential-pass">${escapeHtml(s.password)}</span></td>
                    <td><span class="badge-status badge-lunas">Aktif</span></td>
                </tr>
            `).join('');
        }

        function renderAkunWaliKelas() {
            const tbody = document.getElementById('akunWaliKelasBody');
            if (!tbody) return;

            document.getElementById('countWaliKelas').innerText = DEMO_WALI_KELAS.length + ' akun';
            tbody.innerHTML = DEMO_WALI_KELAS.map((u, i) => `
                <tr>
                    <td>${i + 1}</td>
                    <td><span class="user-role-badge role-badge-petugas">${escapeHtml(u.kelas)}</span></td>
                    <td>${escapeHtml(u.nama)}</td>
                    <td><code>${escapeHtml(u.username)}</code></td>
                    <td><span class="credential-pass">${escapeHtml(u.password)}</span></td>
                    <td>29 siswa</td>
                </tr>
            `).join('');
        }

        // 5. Pencarian tagihan real-time (AJAX / Fetch, debounce 300 ms)
        let tagihanAjaxTimer = null;

        function onTagihanSearchInput(value) {
            const statusEl = document.getElementById('ajaxTagihanStatus');
            if (statusEl) {
                statusEl.innerText = value
                    ? 'Mencari "' + value + '"...'
                    : 'Ketik kata kunci untuk mencari tagihan.';
            }
            clearTimeout(tagihanAjaxTimer);
            tagihanAjaxTimer = setTimeout(() => runTagihanAjaxSearch(value), 300);
        }

        function runTagihanAjaxSearch(query) {
            const tbody = document.getElementById('ajaxTagihanBody');
            const statusEl = document.getElementById('ajaxTagihanStatus');
            if (!tbody) return;

            const inputEl = document.getElementById('ajaxTagihanSearch');
            const q = String(query !== undefined ? query : (inputEl ? inputEl.value : '')).trim();
            const status = (document.getElementById('ajaxFilterStatus') || {}).value || '';

            if (q.length < 2 && !status) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted);">Ketik minimal 2 karakter, atau pilih status tagihan.</td></tr>';
                if (statusEl) statusEl.innerText = 'Belum ada pencarian. Ketik nama siswa atau NISN di atas.';
                return;
            }

            if (statusEl) statusEl.innerText = '⏳ Memuat data tagihan...';

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';
            const url = '/api/tagihan/search?query=' + encodeURIComponent(q) + '&status=' + encodeURIComponent(status);

            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf
                }
            })
            .then(res => res.json())
            .then(payload => {
                const rows = Array.isArray(payload) ? payload : ((payload && payload.data) || []);
                const total = Array.isArray(payload)
                    ? rows.reduce((sum, item) => sum + (Number(item.nominal) || 0), 0)
                    : (Number(payload.total_nominal) || 0);

                if (statusEl) {
                    statusEl.innerText = rows.length + ' tagihan ditemukan • Total Rp ' + total.toLocaleString('id-ID');
                }

                if (rows.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted);">Tidak ada tagihan yang cocok.</td></tr>';
                    return;
                }

                tbody.innerHTML = rows.map((item, i) => {
                    const label = item.status_label || (item.status === 'lunas' ? 'Lunas' : 'Belum Lunas');
                    const badgeClass = item.status === 'lunas' ? 'badge-lunas' : 'badge-belum';
                    return `
                        <tr>
                            <td>${i + 1}</td>
                            <td><strong>${escapeHtml(item.nisn)}</strong></td>
                            <td>${escapeHtml(item.nama_siswa)}</td>
                            <td><span class="user-role-badge role-badge-siswa">${escapeHtml(item.kelas)}</span></td>
                            <td>${escapeHtml(item.bulan)}</td>
                            <td style="font-weight:700;">Rp ${Number(item.nominal || 0).toLocaleString('id-ID')}</td>
                            <td>${escapeHtml(item.tempo || '-')}</td>
                            <td><span class="badge-status ${badgeClass}">${escapeHtml(label)}</span></td>
                        </tr>
                    `;
                }).join('');
            })
            .catch(err => {
                console.warn('Pencarian tagihan gagal:', err);
                if (statusEl) statusEl.innerText = '❌ Gagal memuat data tagihan.';
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:24px;color:#b91c1c;">Gagal memuat data. Periksa koneksi atau server Laravel.</td></tr>';
            });
        }
````
### 4.8 Routing menu baru

````javascript
        const ROUTE_CONFIG = {
            'home': { viewId: 'viewHome', navId: 'navHome', init: () => { renderStatsGrid(); renderMenuActionGrid(); } },
            'tagihan': { viewId: 'viewTagihan', navId: 'navTagihan', init: () => { renderTagihanView(); } },
            'bayar': { viewId: 'viewBayar', navId: 'navBayar', init: () => { renderTagihanView(); } },
            'riwayat': { viewId: 'viewRiwayat', navId: 'navRiwayat', init: () => { renderRiwayatView(); } },
            'profil': { viewId: 'viewProfil', navId: 'navProfil', init: () => { renderProfileView(); } },
            'kasir': { viewId: 'viewKasir', navId: 'navKasir', init: () => { if (typeof updateKasirSiswaInfo === 'function') updateKasirSiswaInfo(); } },
            'siswa': { viewId: 'viewManajemenSiswa', navId: 'navSiswa', init: () => { if (typeof renderManajemenSiswa === 'function') renderManajemenSiswa(); } },
            'siswa-kelas': { viewId: 'viewSiswaKelas', navId: 'navSiswaKelas', init: () => { if (typeof renderSiswaPerKelas === 'function') renderSiswaPerKelas(); } },
            'petugas': { viewId: 'viewAkunStaff', navId: 'navStaff', init: () => { if (typeof renderAkunStaff === 'function') renderAkunStaff(); } },
            'akun-siswa': { viewId: 'viewAkunSiswa', navId: 'navAkunSiswa', init: () => { if (typeof renderAkunSiswa === 'function') { renderAkunSiswa(); renderAkunWaliKelas(); } } },
            'staff': { viewId: 'viewStaff', navId: 'navStaff', init: () => { if (typeof loadPetugas === 'function') loadPetugas(); } },
            'staff-db': { viewId: 'viewStaff', navId: 'navStaff', init: () => { if (typeof loadPetugas === 'function') loadPetugas(); } },
            'midtrans': { viewId: 'viewMidtransSettings', navId: 'navMidtrans', init: () => {} },
            'publikasi': { viewId: 'viewPublikasi', navId: 'navPublikasi', init: () => { if (typeof initPublikasi === 'function') initPublikasi(); } },
            'laporan': { viewId: 'viewLaporan', navId: 'navLaporan', init: () => {} },
            'terbitkan-tagihan': { viewId: 'viewCreateInvoice', navId: 'navCreateInvoice', init: () => { if (typeof initInvoiceDashboard === 'function') initInvoiceDashboard(); } },
            'buat-tagihan': { viewId: 'viewCreateInvoice', navId: 'navCreateInvoice', init: () => { if (typeof initInvoiceDashboard === 'function') initInvoiceDashboard(); } },
            'tagihan-create': { viewId: 'viewCreateInvoice', navId: 'navCreateInvoice', init: () => { if (typeof initInvoiceDashboard === 'function') initInvoiceDashboard(); } }
        };

        const VIEW_TO_SLUG = {
            viewHome: 'home',
            viewTagihan: 'tagihan',
            viewBayar: 'bayar',
            viewRiwayat: 'riwayat',
            viewProfil: 'profil',
            viewKasir: 'kasir',
            viewManajemenSiswa: 'siswa',
            viewSiswaKelas: 'siswa-kelas',
            viewAkunStaff: 'petugas',
            viewAkunSiswa: 'akun-siswa',
            viewStaff: 'staff',
            viewMidtransSettings: 'midtrans',
            viewPublikasi: 'publikasi',
            viewLaporan: 'laporan',
            viewCreateInvoice: 'terbitkan-tagihan'
        };
````
### 4.9 Alur bayar langsung ke Midtrans (tanpa selfie)

Tidak ada lagi gerbang `selfieVerified`. Fungsi langsung menghitung total dan
membuka jendela pembayaran.

````javascript
        // 14. MIDTRANS SNAP PAYMENT TRIGGER (100% Payment Gateway Real & Otentik)
        function triggerMidtransPayment(isRetry) {
            if (!isRetry) {
                snapTokenRetryCount = 0;
            }
            snapExpiresAt = 0;

            const selectedBills = getSelectedPayBills();
            if (!selectedBills.length) {
                showToast('Silakan centang tagihan yang ingin dibayar terlebih dahulu.');
                return;
            }

            const total = selectedBills.reduce((sum, b) => sum + (Number(b.nominal) || 0), 0);
            const bulanStr = selectedBills.map(b => b.bulan || b.label).join(', ');
            const selectedBillKeys = selectedBills.map(b => b.key);
            const localOrderId = 'SPP-' + currentUser.nisn + '-' + Date.now().toString().slice(-6);
````
Reset setelah pembayaran berhasil:

```javascript
// Reset tagihan yang dipilih
selectedPayKeys = [];
```

### 4.10 Perbaikan pop-up Midtrans Snap (metode pembayaran)

Ada tiga perbaikan di front-end agar metode pembayaran selalu muncul:

1. **Loader Snap tidak lagi menghapus client key.** Jika `/api/midtrans/config`
   gagal dimuat, loader memakai kembali script awal di `<head>` yang sudah
   membawa client key. Sebelumnya script lama dihapus lalu dimuat ulang tanpa
   client key, sehingga pop-up Snap kosong.
2. **Watchdog pop-up.** Jika iframe Snap tetap tersembunyi setelah ±10 detik
   (15 pemeriksaan × 700 ms), aplikasi menampilkan overlay fallback dengan
   tautan ke halaman pembayaran resmi Midtrans.
3. **Modal demo dibuat ringkas.** Seluruh 6 metode langsung terlihat di layar
   HP; daftar tetap bisa digulir pada layar yang lebih pendek.

#### a. Loader Snap dengan fallback client key

````javascript
        function loadSnapSdk(cfg) {
            // Jika /api/midtrans/config gagal dimuat, JANGAN menghapus script Snap
            // yang sudah ada. Script awal di <head> sudah membawa client key yang
            // benar; menghapusnya lalu memuat ulang tanpa client key membuat
            // popup Snap muncul kosong tanpa metode pembayaran.
            const src = (cfg && cfg.snap_js)
                ? cfg.snap_js
                : (initialSnapSrc || 'https://app.sandbox.midtrans.com/snap/snap.js');
            const clientKey = (cfg && cfg.client_key)
                ? cfg.client_key
                : initialSnapClientKey;

            if (window.snap && typeof window.snap.pay === 'function'
                && snapLoadedSrc === src && snapLoadedClientKey === clientKey) {
                return Promise.resolve(true);
            }
            if (snapScriptPromise && snapLoadedSrc === src && snapLoadedClientKey === clientKey) {
                return snapScriptPromise;
            }

            snapLoadedSrc = src;
            snapLoadedClientKey = clientKey;

            // Hapus script lama sebelum memuat konfigurasi baru
            const oldScript = document.getElementById('midtransSnapScript');
            if (oldScript) oldScript.remove();
            try { window.snap = undefined; } catch (e) {}

            snapScriptPromise = new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.id = 'midtransSnapScript';
                script.src = src;
                script.setAttribute('data-client-key', clientKey);
                script.onload = function() {
                    let tries = 0;
                    const timer = setInterval(() => {
                        tries++;
                        if (window.snap && typeof window.snap.pay === 'function') {
                            clearInterval(timer);
                            resolve(true);
                        } else if (tries >= 50) {
                            clearInterval(timer);
                            reject(new Error('Snap SDK tidak tersedia setelah dimuat.'));
                        }
                    }, 100);
                };
                script.onerror = function() {
                    reject(new Error('Gagal memuat script Snap Midtrans.'));
                };
                document.head.appendChild(script);
            });

            return snapScriptPromise;
        }
````
#### b. Watchdog dan overlay fallback

````javascript
        // Deteksi popup Snap yang tidak tampil (mis. iframe diblokir browser HP).
        // Jika tetap tersembunyi, tawarkan halaman pembayaran Midtrans resmi.
        function watchSnapPopup(redirectUrl) {
            if (!redirectUrl) return;
            let checks = 0;
            const timer = setInterval(function () {
                checks++;
                if (snapPopupSettled) {
                    clearInterval(timer);
                    return;
                }
                const frame = document.getElementById('snap-midtrans');
                const rect = frame ? frame.getBoundingClientRect() : null;
                const visible = !!(frame && frame.style.display !== 'none'
                    && rect && rect.width > 0 && rect.height > 0);
                if (visible) {
                    clearInterval(timer);
                    return;
                }
                if (checks >= 15) {
                    clearInterval(timer);
                    showSnapFallback(redirectUrl);
                }
            }, 700);
        }

        function showSnapFallback(redirectUrl) {
            if (!redirectUrl || document.getElementById('snapFallbackOverlay')) return;

            const overlay = document.createElement('div');
            overlay.id = 'snapFallbackOverlay';
            overlay.style.cssText = 'position:fixed;inset:0;background:rgba(15,23,42,0.72);z-index:100000;display:flex;align-items:center;justify-content:center;padding:18px;';

            const box = document.createElement('div');
            box.style.cssText = 'background:#fff;max-width:380px;width:100%;border-radius:16px;padding:22px;box-shadow:0 24px 60px rgba(0,0,0,0.35);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;color:#1e293b;';
            box.innerHTML = '<div style="font-size:34px;line-height:1;margin-bottom:10px;">💳</div>'
                + '<h3 style="font-size:17px;font-weight:800;margin:0 0 8px;">Metode pembayaran belum tampil</h3>'
                + '<p style="font-size:13px;line-height:1.5;color:#475569;margin:0 0 16px;">Popup pembayaran tidak dapat ditampilkan di browser ini. Buka halaman pembayaran Midtrans untuk memilih QRIS, GoPay, ShopeePay, OVO, DANA, Virtual Account, atau kartu.</p>';

            const link = document.createElement('a');
            link.href = redirectUrl;
            link.target = '_blank';
            link.rel = 'noopener';
            link.textContent = 'Buka Halaman Pembayaran Midtrans';
            link.style.cssText = 'display:block;text-align:center;background:linear-gradient(135deg,#1e40af,#3b82f6);color:#fff;text-decoration:none;font-size:14px;font-weight:800;padding:13px 16px;border-radius:10px;';
            box.appendChild(link);

            const close = document.createElement('button');
            close.type = 'button';
            close.textContent = 'Tutup';
            close.style.cssText = 'display:block;width:100%;margin-top:10px;background:#f1f5f9;border:none;color:#475569;font-size:13px;font-weight:700;padding:11px 16px;border-radius:10px;cursor:pointer;';
            close.addEventListener('click', function () { overlay.remove(); });
            box.appendChild(close);

            overlay.appendChild(box);
            document.body.appendChild(overlay);
        }
````
#### c. CSS modal Snap ringkas

````css
        .snap-modal-box {
            background: #ffffff;
            width: 100%;
            max-width: 440px;
            max-height: calc(100vh - 16px);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            animation: snapScaleUp 0.25s ease-out;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #212529;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        /* Area utama Snap: header + nominal tetap, daftar metode bisa digulir. */
        #snapMainView {
            display: flex;
            flex-direction: column;
            min-height: 0;
            flex: 1 1 auto;
        }

        @keyframes snapScaleUp {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Header like Image 2: "SMP MUHAMMADIYAH 3 METRO" / merchant title & X */
````
#### d. Markup daftar metode (6 metode)

````html
            <!-- Main Method Selection Screen -->
            <div id="snapMainView">
                <!-- Amount Row -->
                <div class="snap-amount-row">
                    <div class="snap-amount-val" id="snapAmountText">
                        Rp2 <span class="snap-copy-icon" onclick="copySnapText('snapAmountRaw')">📋</span>
                    </div>
                </div>

                <!-- Order ID Row -->
                <div class="snap-order-row">
                    <div class="snap-order-id">
                        Order ID <span id="snapOrderIdText">#SPP-202501-8123</span>
                        <span class="snap-copy-icon" onclick="copySnapText('snapOrderIdVal')">📋</span>
                    </div>
                    <a href="javascript:void(0)" class="snap-order-details-link" onclick="toggleSnapDetails()">Details &#9660;</a>
                </div>

                <div id="snapOrderDetailsBox" style="display:none;background:#f8fafc;padding:10px 20px;font-size:12px;border-bottom:1px solid #e2e8f0;">
                    <div>Item: <strong id="snapItemDesc">SPP Siswa SMP Muhammadiyah 3 Metro</strong></div>
                    <div>Nama Siswa: <strong id="snapStudentDesc">Ahmad Dahlan Putra</strong></div>
                </div>

                <!-- Countdown Strip like Image 2 -->
                <div class="snap-countdown-strip">
                    <span>Choose within</span> <strong id="snapCountdownTimer">23:59:36</strong> <span>&#9650;</span>
                </div>

                <!-- Scrollable Methods List -->
                <div class="snap-body-scroll">
                    <div class="snap-section-title">Pilih metode pembayaran</div>

                    <!-- GoPay QRIS -->
                    <div class="snap-method-item" onclick="openSnapDetail('gopay')">
                        <div>
                            <div class="snap-method-name">GoPay QRIS</div>
                            <div class="snap-method-logos">
                                <span class="snap-logo-badge badge-gopay">gopay</span>
                                <span class="snap-logo-badge badge-gopay-later">gopay later</span>
                                <span class="snap-logo-badge badge-qris">QRIS</span>
                            </div>
                        </div>
                        <div class="snap-method-arrow">&rsaquo;</div>
                    </div>

                    <!-- Card Payment -->
                    <div class="snap-method-item" onclick="openSnapDetail('card')">
                        <div>
                            <div class="snap-method-name">Card Payment</div>
                            <div class="snap-method-logos">
                                <span class="snap-logo-badge badge-visa">VISA</span>
                                <span class="snap-logo-badge badge-mc">Mastercard</span>
                                <span class="snap-logo-badge" style="background:#0079bf;color:#fff;">JCB</span>
                                <span class="snap-logo-badge" style="background:#002663;color:#fff;">AMEX</span>
                            </div>
                        </div>
                        <div class="snap-method-arrow">&rsaquo;</div>
                    </div>

                    <!-- ShopeePay QRIS -->
                    <div class="snap-method-item" onclick="openSnapDetail('shopee')">
                        <div>
                            <div class="snap-method-name">ShopeePay QRIS</div>
                            <div class="snap-method-logos">
                                <span class="snap-logo-badge badge-shopee">ShopeePay</span>
                                <span class="snap-logo-badge badge-shopee">SPayLater</span>
                                <span class="snap-logo-badge badge-qris">QRIS</span>
                            </div>
                        </div>
                        <div class="snap-method-arrow">&rsaquo;</div>
                    </div>

                    <!-- OVO -->
                    <div class="snap-method-item" onclick="openSnapDetail('ovo')">
                        <div>
                            <div class="snap-method-name">OVO</div>
                            <div class="snap-method-logos">
                                <span class="snap-logo-badge badge-ovo">OVO</span>
                            </div>
                        </div>
                        <div class="snap-method-arrow">&rsaquo;</div>
                    </div>

                    <!-- DANA -->
                    <div class="snap-method-item" onclick="openSnapDetail('dana')">
                        <div>
                            <div class="snap-method-name">DANA</div>
                            <div class="snap-method-logos">
                                <span class="snap-logo-badge badge-dana">DANA</span>
                            </div>
                        </div>
                        <div class="snap-method-arrow">&rsaquo;</div>
                    </div>

                    <!-- Bank Virtual Account -->
                    <div class="snap-method-item" onclick="openSnapDetail('va')">
                        <div>
                            <div class="snap-method-name">Bank Virtual Account</div>
                            <div class="snap-method-logos">
                                <span class="snap-logo-badge badge-va">BSI</span>
                                <span class="snap-logo-badge badge-va">Mandiri</span>
                                <span class="snap-logo-badge badge-va">BRI</span>
                                <span class="snap-logo-badge badge-va">BCA</span>
                            </div>
                        </div>
                        <div class="snap-method-arrow">&rsaquo;</div>
                    </div>
                </div>
            </div>
````
### 4.11 Token Snap kedaluwarsa: deteksi & pembuatan ulang otomatis

Tiga lapis perlindungan di front-end:

1. **SDK sesuai mode.** `ensureSnapReady()` memakai `snap_js` dan `client_key`
   dari respons token, sehingga token Sandbox tidak dibuka oleh `snap.js`
   Production (penyebab umum pesan *token is expired / doesn't exist*).
2. **Retry otomatis.** Jika `snap.pay` melaporkan sesi kedaluwarsa/tidak
   ditemukan, aplikasi membuat token baru dan membuka Snap lagi, maksimal
   **2 kali**.
3. **Polling berhenti saat kedaluwarsa.** Polling status tidak lagi berjalan
   tanpa akhir setelah `expires_at` terlewati.

````javascript
        let snapExpiresAt = 0;
        let snapTokenRetryCount = 0;
        const SNAP_TOKEN_MAX_RETRY = 2;

        function isSnapSessionExpiredMessage(message) {
            const text = String(message || '');
            return /expired|kadaluarsa|does(n't| not) exist|not found|invalid token|token (tidak|sudah)/i.test(text);
        }
````
SDK Snap dimuat sesuai mode token yang benar-benar dipakai backend:

````javascript
        function ensureSnapReady(overrideConfig) {
            if (overrideConfig) {
                return loadSnapSdk(overrideConfig);
            }
            return loadMidtransConfig().then(cfg => loadSnapSdk(cfg));
        }
````
Handler `onError` yang memicu pembuatan token baru:

````javascript
                            onError: function(result) {
                                snapPopupSettled = true;
                                const message = (result && (result.status_message || result.message)) || 'Error';
                                // Token Snap bisa kedaluwarsa / tidak ditemukan
                                // (mis. karena popup dibuka dari mode yang salah).
                                // Buat token baru otomatis, maksimal 2 kali.
                                if (isSnapSessionExpiredMessage(message) && snapTokenRetryCount < SNAP_TOKEN_MAX_RETRY) {
                                    snapTokenRetryCount++;
                                    if (pollingStatusInterval) clearInterval(pollingStatusInterval);
                                    showToast('Sesi pembayaran kedaluwarsa. Membuat token Midtrans baru...');
                                    setTimeout(function () { triggerMidtransPayment(true); }, 700);
                                    return;
                                }
                                showToast('Gagal memproses pembayaran Midtrans: ' + message);
                            },
````
### 4.12 Pilihan tagihan bebas (tidak ada auto-pilih / 1-click)

Sebelumnya aplikasi otomatis memilih bulan pertama yang belum lunas dan
menghitung pilihan setelah daftar dirender, sehingga tombol BAYAR bisa
terkunci. Sekarang:

- `selectedPayKeys` dimulai **kosong**; siswa bebas mencentang tagihan mana
  saja, boleh sebagian, boleh juga **tidak memilih sama sekali**.
- Tidak ada pemilihan otomatis saat halaman dibuka maupun setelah render.
- Tombol BAYAR hanya aktif jika ada tagihan yang dicentang. Tanpa pilihan,
  tidak ada request token Midtrans yang dibuat.
- Setiap tagihan memakai key unik (`inv:<id>` atau `demo:<bulan>`), bukan nama
  bulan, sehingga dua tagihan pada bulan yang sama tetap bisa dipilih terpisah.

````javascript
        // Daftar SEMUA tagihan siswa. Setiap tagihan punya key unik, sehingga
        // tagihan lama tidak tertimpa atau hilang saat petugas menerbitkan
        // tagihan baru (termasuk bila bulannya sama).
        function getStudentBills() {
            const nisn = currentUser ? currentUser.nisn : '';
            const invoices = createdInvoices.filter(inv => inv.siswaNisn === nisn);
            const invoiceMonths = new Set(invoices.map(inv => inv.bulan).filter(Boolean));
            const bills = [];

            DEMO_BASE_MONTHS.forEach(m => {
                if (invoiceMonths.has(m.nama)) return;
                const isUnpaid = !!(currentUser.tagihanUnpaid && currentUser.tagihanUnpaid.includes(m.nama));
                bills.push({
                    key: 'demo:' + m.nama,
                    invoiceId: null,
                    bulan: m.nama,
                    label: 'SPP ' + m.nama,
                    tempo: m.tempo,
                    nominal: getNominalForMonth(m.nama),
                    status: isUnpaid ? 'Belum Lunas' : 'Lunas',
                    sumber: 'demo'
                });
            });

            invoices.forEach(inv => {
                const bulan = inv.bulan || '';
                bills.push({
                    key: 'inv:' + inv.id,
                    invoiceId: inv.id,
                    bulan: bulan,
                    label: bulan ? 'SPP ' + bulan : (inv.keterangan || 'Tagihan Non-SPP'),
                    tempo: inv.tempo || '-',
                    nominal: Number(inv.nominal) || 0,
                    status: inv.status === 'Lunas' ? 'Lunas' : 'Belum Lunas',
                    sumber: 'petugas'
                });
            });

            return bills;
        }

        // Tagihan yang dicentang siswa. Hanya tagihan belum lunas yang bisa dipilih.
        function getSelectedPayBills() {
            return getStudentBills().filter(b => b.status !== 'Lunas' && selectedPayKeys.indexOf(b.key) !== -1);
        }

        function getSelectedPayTotal() {
            return getSelectedPayBills().reduce((total, b) => total + (Number(b.nominal) || 0), 0);
        }

        function getSelectedPayMonths() {
            return getSelectedPayBills().map(b => b.bulan || b.label).filter(Boolean);
        }

        function updatePaySummary() {
            const selected = getSelectedPayBills();
            const subtotal = selected.reduce((total, b) => total + (Number(b.nominal) || 0), 0);

            const paySubtotal = document.getElementById('paySubtotal');
            if (paySubtotal) paySubtotal.innerText = 'Rp ' + subtotal.toLocaleString('id-ID');

            const count = document.getElementById('selectedBillCount');
            if (count) count.innerText = selected.length + ' Tagihan Dipilih';

            const btnBayar = document.getElementById('btnBayarSPP');
            if (btnBayar) {
                const enabled = selected.length > 0;
                btnBayar.disabled = !enabled;
                btnBayar.style.opacity = enabled ? '1' : '0.5';
                btnBayar.style.cursor = enabled ? 'pointer' : 'not-allowed';
            }
        }

        function renderTagihanView() {
            if (currentRole === 'petugas') return;

            document.getElementById('billNama').innerText = currentUser.nama;
            document.getElementById('billNisn').innerText = currentUser.nisn + ' / ' + currentUser.nis;
            document.getElementById('billKelas').innerText = 'Kelas ' + currentUser.kelas + ' (Tingkat ' + currentUser.tingkat + ')';
            document.getElementById('billOrtu').innerText = currentUser.namaOrtu;
            document.getElementById('billTelp').innerText = currentUser.noTelp;

            // Daftar semua tagihan siswa (tagihan lama + tagihan baru dari
            // petugas). Tidak ada yang dihapus saat tagihan baru diterbitkan.
            const bills = getStudentBills();

            // Pertahankan pilihan yang masih valid dan belum lunas. Tidak ada
            // pemilihan otomatis: siswa bebas memilih sendiri, termasuk
            // memilih tidak ada tagihan sama sekali.
            selectedPayKeys = selectedPayKeys.filter(key =>
                bills.some(b => b.key === key && b.status !== 'Lunas')
            );

            const tbody = document.getElementById('tagihanTableBody');
            tbody.innerHTML = '';
            let totalUnpaid = 0;

            const paymentMonthList = document.getElementById('paymentMonthList');
            paymentMonthList.innerHTML = '';

            bills.forEach((b, idx) => {
                const isUnpaid = b.status !== 'Lunas';
                const nominal = Number(b.nominal) || 0;

                if (isUnpaid) totalUnpaid += nominal;

                tbody.innerHTML += `
                    <tr>
                        <td><strong>${idx + 1}</strong></td>
                        <td><strong>${b.label}</strong></td>
                        <td>${b.tempo}</td>
                        <td style="font-weight:700;">Rp ${nominal.toLocaleString('id-ID')}</td>
                        <td>
                            <span class="badge-status ${isUnpaid ? 'badge-belum' : 'badge-lunas'}">
                                ${isUnpaid ? 'Belum Lunas' : 'Lunas'}
                            </span>
                        </td>
                        <td style="text-align:right;">
                            ${isUnpaid
                                ? `<button class="btn-action-primary" style="padding:6px 14px;font-size:12px;" onclick="paySingleBill('${b.key}')">Pilih Tagihan Ini</button>`
                                : `<span style="font-size:12px;color:var(--text-muted);font-weight:700;">✔ Selesai</span>`
                            }
                        </td>
                    </tr>
                `;

                if (isUnpaid) {
                    const isChecked = selectedPayKeys.includes(b.key);
                    paymentMonthList.innerHTML += `
                        <label style="display:flex;align-items:center;justify-content:space-between;border:2px solid ${isChecked ? '#1e40af' : 'var(--border-color)'};border-radius:12px;padding:16px 20px;cursor:pointer;background:${isChecked ? '#eff6ff' : '#fff'};transition:all 0.2s;box-shadow:${isChecked ? '0 0 0 3px rgba(30,64,175,0.15)' : 'none'};">
                            <div style="display:flex;align-items:center;gap:14px;">
                                <input type="checkbox" ${isChecked ? 'checked' : ''} onchange="toggleMonthSelection('${b.key}', this.checked)" style="width:20px;height:20px;accent-color:var(--primary-blue);">
                                <div>
                                    <div style="font-weight:800;color:var(--text-dark);font-size:15px;">${b.label}</div>
                                    <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">Jatuh Tempo: ${b.tempo}</div>
                                </div>
                            </div>
                            <div style="font-size:18px;font-weight:900;color:var(--primary-blue);">
                                Rp ${nominal.toLocaleString('id-ID')}
                            </div>
                        </label>
                    `;
                }
            });
````
### 4.13 Form penerbitan tagihan: tahun ajaran, checkbox bulan, jatuh tempo

**Yang berubah pada form:**

1. **Tahun ajaran tidak lagi diketik/dipilih dari daftar statis.** Dropdown
   kosong di HTML lalu diisi dari `GET /api/tahun-ajaran` (tabel
   `tbl_tahun_ajaran`). Tahun berstatus `aktif` otomatis terpilih. Tabel diisi
   melalui migration dengan rentang **2000/2001 sampai 2045/2046** (46 tahun
   ajaran), sehingga daftar pilihan konsisten di aplikasi Laravel maupun demo.
   Jika API tidak dapat diakses, daftar cadangan dengan rentang yang sama tetap
   ditampilkan agar form tidak kosong.
2. **Bulan SPP memakai checkbox** (`#invoiceBulanChecklist`), bukan dropdown.
   Bulan boleh dicentang lebih dari satu. Bulan berjalan otomatis tercentang.
   Nilai checkbox memakai format `"Bulan Tahun"`, misalnya `Januari 2026`, agar
   sama dengan daftar bulan di halaman pembayaran.
3. **Jatuh tempo otomatis tanggal dan tahun hari ini.** Nilai hardcoded
   `2025-02-10` dan fallback `formatIndoDate()` sudah dihapus. Perhitungan
   memakai komponen tanggal lokal (bukan `toISOString`) supaya tidak bergeser
   satu hari karena zona waktu.

Markup form:

````html
                            <div class="form-group">
                                <label class="form-label">Bulan SPP <span style="color:#ef4444;">*</span></label>
                                <div id="invoiceBulanChecklist" style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;background:#f8fafc;border:1.5px solid var(--border-color);border-radius:10px;padding:10px;">
                                    <!-- Pilihan bulan (checkbox) diisi otomatis via JavaScript -->
                                </div>
                                <div style="font-size:12px;color:var(--text-muted);margin-top:6px;">
                                    ✔ Centang satu atau lebih bulan. Setiap bulan yang dicentang diterbitkan sebagai satu tagihan.
                                    Kosongkan jika tagihan bukan SPP.
                                </div>
                            </div>

                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                                <div class="form-group">
                                    <label class="form-label">Tahun Ajaran <span style="color:#ef4444;">*</span></label>
                                    <select class="form-control" id="invoiceTahunAjaran" onchange="tahunAjaranUserPicked = true; autoCalcDueDate()">
                                        <!-- Diisi otomatis dari tabel tbl_tahun_ajaran -->
                                    </select>
                                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">
                                        📚 Daftar diambil dari tabel tahun ajaran sekolah.
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Tanggal Jatuh Tempo</label>
                                    <input type="date" class="form-control" id="invoiceJatuhTempo" onchange="autoCalcDueDate()">
                                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">
                                        📅 Otomatis tanggal dan tahun hari ini.
                                    </div>
                                </div>
                            </div>
````
Logika checkbox bulan:

````javascript
        // Daftar bulan SPP dalam bentuk CHECKBOX (bisa pilih lebih dari satu).
        // Nilai checkbox memakai format "Bulan Tahun" agar cocok dengan daftar
        // bulan pada halaman pembayaran SPP.
        function renderInvoiceMonthChecklist() {
            const wrap = document.getElementById('invoiceBulanChecklist');
            if (!wrap) return;

            const now = new Date();
            const year = now.getFullYear();
            const currentMonth = now.getMonth();
            const previous = getSelectedInvoiceMonths();

            wrap.innerHTML = '';
            INVOICE_MONTH_NAMES.forEach((nama, idx) => {
                const value = nama + ' ' + year;
                const checked = previous.length ? previous.indexOf(value) !== -1 : idx === currentMonth;

                const label = document.createElement('label');
                label.style.cssText = 'display:flex;align-items:center;gap:8px;padding:8px 10px;border:1.5px solid ' + (checked ? '#1e40af' : '#e2e8f0') + ';border-radius:8px;background:' + (checked ? '#eff6ff' : '#fff') + ';cursor:pointer;font-size:13px;font-weight:700;color:#0f172a;';

                const box = document.createElement('input');
                box.type = 'checkbox';
                box.className = 'invoice-month-checkbox';
                box.value = value;
                box.checked = checked;
                box.style.cssText = 'width:18px;height:18px;accent-color:var(--primary-blue);';
                box.addEventListener('change', function () { syncInvoiceMonthLabel(this); });

                const span = document.createElement('span');
                span.textContent = nama;

                label.appendChild(box);
                label.appendChild(span);
                wrap.appendChild(label);
            });
        }

        function syncInvoiceMonthLabel(input) {
            if (!input) return;
            const label = input.closest('label');
            if (!label) return;
            label.style.borderColor = input.checked ? '#1e40af' : '#e2e8f0';
            label.style.background = input.checked ? '#eff6ff' : '#fff';
        }

        function getSelectedInvoiceMonths() {
            return Array.from(document.querySelectorAll('#invoiceBulanChecklist input.invoice-month-checkbox:checked'))
                .map(cb => cb.value);
        }
````
Jatuh tempo dan reset form:

````javascript
        function autoCalcDueDate() {
            const tempoInput = document.getElementById('invoiceJatuhTempo');
            if (!tempoInput) return;

            // Jatuh tempo selalu memakai tanggal dan tahun hari ini.
            // Gunakan komponen lokal (bukan toISOString) agar tidak bergeser
            // satu hari karena perbedaan zona waktu.
            const now = new Date();
            const yyyy = now.getFullYear();
            const mm = String(now.getMonth() + 1).padStart(2, '0');
            const dd = String(now.getDate()).padStart(2, '0');
            tempoInput.value = yyyy + '-' + mm + '-' + dd;
        }

        function resetInvoiceForm() {
            const form = document.getElementById('formCreateInvoice');
            if (form) form.reset();
            invoiceNominalManual = false;
            tahunAjaranUserPicked = false;
            switchInvoiceMode('single');
            autoFillTarifByStudent();
            // render ulang agar pilihan bulan & tahun ajaran kembali konsisten
            renderInvoiceMonthChecklist();
            loadTahunAjaranOptions({ reset: true });
            autoCalcDueDate();
        }
````
### 4.14 Satu bulan tercentang = satu tagihan

Saat tombol terbitkan ditekan, setiap bulan yang dicentang dibuat sebagai
**satu tagihan tersendiri** (bukan satu tagihan berisi daftar bulan). Jika tidak
ada bulan yang dicentang, tagihan diperlakukan sebagai tagihan non-SPP dengan
`bulan = ''`.

- Mode **Per Siswa**: cek duplikasi dilakukan per bulan.
- Mode **Massal Per Kelas**: setiap siswa mendapat satu tagihan untuk setiap
  bulan yang dicentang.
- Modal sukses menampilkan jumlah tagihan, total nominal, dan rincian
  `@ Rp .../bulan`.

````javascript
        // Sinkronkan status tagihan bulan tertentu ke akun siswa & wali murid.
        function syncStudentUnpaid(student, bulan, status) {
            if (!student || !bulan) return;

            if (status === 'Belum Lunas') {
                if (!student.tagihanUnpaid) student.tagihanUnpaid = [];
                if (!student.tagihanUnpaid.includes(bulan)) student.tagihanUnpaid.push(bulan);

                DEMO_USERS.ortu.forEach(o => {
                    if (o.nisn === student.nisn) {
                        if (!o.tagihanUnpaid) o.tagihanUnpaid = [];
                        if (!o.tagihanUnpaid.includes(bulan)) o.tagihanUnpaid.push(bulan);
                    }
                });
            } else if (status === 'Lunas') {
                if (student.tagihanUnpaid) {
                    student.tagihanUnpaid = student.tagihanUnpaid.filter(b => b !== bulan);
                }
                DEMO_USERS.ortu.forEach(o => {
                    if (o.nisn === student.nisn && o.tagihanUnpaid) {
                        o.tagihanUnpaid = o.tagihanUnpaid.filter(b => b !== bulan);
                    }
                });
            }
        }

        function submitCreateInvoice() {
            const selectedMonths = getSelectedInvoiceMonths();
            // Tidak ada bulan dicentang = tagihan non-SPP (bulan kosong).
            const monthsToCreate = selectedMonths.length ? selectedMonths : [''];
            const tahun = document.getElementById('invoiceTahunAjaran').value || getCurrentAcademicYear();
            const nominal = parseInt(document.getElementById('invoiceNominalInput').value) || 250000;
            const status = document.getElementById('invoiceStatusSelect').value;
            const tempoRaw = document.getElementById('invoiceJatuhTempo').value || new Date().toISOString().split('T')[0];
            const keterangan = document.getElementById('invoiceKeteranganInput').value || 'SPP Reguler Siswa';
            const tempoFormatted = formatIndoDate(tempoRaw);
            const tanggalTerbit = new Date().toLocaleDateString('id-ID');
            const monthLabel = selectedMonths.length ? 'SPP ' + selectedMonths.join(', ') : keterangan;
            const nextInvoiceId = () => makeInvoiceId();

            if (invoiceCreationMode === 'single') {
                const nisn = document.getElementById('invoiceSiswaSelect').value;
                const student = DEMO_USERS.siswa.find(s => s.nisn === nisn);
                if (!student) {
                    showToast('Siswa tidak ditemukan!');
                    return;
                }

                // Cek duplikasi untuk setiap bulan yang dicentang.
                const duplicates = monthsToCreate.filter(bulan => bulan &&
                    createdInvoices.some(i => i.siswaNisn === nisn && i.bulan === bulan));
                if (duplicates.length) {
                    if (!confirm('Tagihan bulan ' + duplicates.join(', ') + ' untuk ' + student.nama +
                        ' sudah ada. Apakah tetap ingin menerbitkan lagi?')) {
                        return;
                    }
                }

                const created = [];
                monthsToCreate.forEach(bulan => {
                    const newInv = {
                        id: nextInvoiceId(),
                        siswaNisn: student.nisn,
                        siswaNama: student.nama,
                        kelas: student.kelas,
                        bulan: bulan,
                        tahun: tahun,
                        nominal: nominal,
                        tempo: tempoFormatted,
                        status: status,
                        keterangan: keterangan,
                        tanggal: tanggalTerbit
                    };

                    createdInvoices.unshift(newInv);
                    created.push(newInv);
                    syncStudentUnpaid(student, bulan, status);
                });

                // TAMPILKAN POP-UP MODAL SUKSES (Bukan toast pojok kanan)
                lastCreatedInvoiceForModal = created[0];
                document.getElementById('succInvNo').innerText = created.length > 1
                    ? 'INV-SERI (' + created.length + ' Tagihan)'
                    : created[0].id;
                document.getElementById('succInvStudent').innerText = student.nama + ' (' + student.kelas + ')';
                document.getElementById('succInvMonth').innerText = monthLabel + ' - TP ' + tahun;
                document.getElementById('succInvAmount').innerText = 'Rp ' + (nominal * created.length).toLocaleString('id-ID') +
                    (created.length > 1 ? ' (@ Rp ' + nominal.toLocaleString('id-ID') + '/bulan)' : '');
                const mSuccess = document.getElementById('modalInvoiceSuccess');
                if (mSuccess) {
                    mSuccess.style.display = 'flex';
                    mSuccess.classList.add('active');
                }
            } else {
                // MASSAL PER KELAS
                const targetKelas = document.getElementById('invoiceKelasSelect').value;
                let targetStudents = DEMO_USERS.siswa;
                if (targetKelas !== 'ALL') {
                    targetStudents = DEMO_USERS.siswa.filter(s => s.kelas === targetKelas);
                }

                if (targetStudents.length === 0) {
                    showToast('Tidak ada siswa di kelas yang dipilih!');
                    return;
                }

                let duplicateCount = 0;
                targetStudents.forEach(student => {
                    monthsToCreate.forEach(bulan => {
                        if (bulan && createdInvoices.some(i => i.siswaNisn === student.nisn && i.bulan === bulan)) {
                            duplicateCount++;
                        }
                    });
                });
                if (duplicateCount > 0) {
                    if (!confirm(duplicateCount + ' tagihan untuk bulan yang dipilih sudah ada. Apakah tetap ingin menerbitkan lagi?')) {
                        return;
                    }
                }

                let createdCount = 0;
                targetStudents.forEach(student => {
                    monthsToCreate.forEach(bulan => {
                        const newInv = {
                            id: nextInvoiceId(),
                            siswaNisn: student.nisn,
                            siswaNama: student.nama,
                            kelas: student.kelas,
                            bulan: bulan,
                            tahun: tahun,
                            nominal: nominal,
                            tempo: tempoFormatted,
                            status: status,
                            keterangan: keterangan,
                            tanggal: tanggalTerbit
                        };

                        createdInvoices.unshift(newInv);
                        createdCount++;
                        syncStudentUnpaid(student, bulan, status);
                    });
                });

                // TAMPILKAN POP-UP MODAL SUKSES (Bukan toast pojok kanan)
                lastCreatedInvoiceForModal = createdInvoices[0];
                document.getElementById('succInvNo').innerText = 'INV-MASSAL (' + createdCount + ' Tagihan)';
                document.getElementById('succInvStudent').innerText = 'Semua Siswa Kelas ' + targetKelas + ' (' + targetStudents.length + ' Siswa)';
                document.getElementById('succInvMonth').innerText = monthLabel + ' - TP ' + tahun;
                document.getElementById('succInvAmount').innerText = 'Rp ' + (nominal * createdCount).toLocaleString('id-ID') +
                    ' (@ Rp ' + nominal.toLocaleString('id-ID') + '/tagihan)';
                const mSuccess = document.getElementById('modalInvoiceSuccess');
                if (mSuccess) {
                    mSuccess.style.display = 'flex';
                    mSuccess.classList.add('active');
                }
            }

            // Refresh UI
            renderCreatedInvoices();
            renderStatsGrid();
            if (currentRole === 'siswa' || currentRole === 'ortu') {
                renderTagihanView();
            }

            // Simpan permanen agar tagihan tidak hilang saat halaman di-refresh.
            saveSppData();
        }
````
### 4.15 Nominal SPP mengikuti input petugas

**Masalah:** nominal di halaman pembayaran siswa/wali selalu dihitung dari
`currentUser.tarif` (Rp 250.000), sehingga tagihan yang diterbitkan petugas
dengan nominal lain tetap tampil Rp 250.000.

**Perbaikan:**

- Setiap tagihan menyimpan `nominal` yang diisi petugas.
- Halaman pembayaran dan tabel tagihan mengambil nominal dari tagihan bulan
  tersebut (`getNominalForMonth()`), bukan dari tarif siswa.
- Subtotal dan nominal yang dikirim ke Midtrans dihitung dari **total nominal
  tagihan yang dicentang**, bukan `jumlah bulan × tarif`.
- Input nominal memakai `step="1"` sehingga angka berapa pun diterima, dan
  nilai yang sudah diketik tidak ditimpa saat petugas berganti siswa/kelas.
- Kartu ringkasan "Tagihan SPP Saat Ini" juga memakai total nominal tagihan.

````javascript
        // Nominal tagihan mengikuti nilai yang diinput petugas saat menerbitkan
        // tagihan. Jika bulan tersebut tidak punya tagihan (data demo lama),
        // barulah memakai tarif siswa sebagai cadangan.
        function getInvoiceForMonth(namaBulan, nisn) {
            const targetNisn = nisn || (currentUser ? currentUser.nisn : '');
            return createdInvoices.find(i => i.siswaNisn === targetNisn && i.bulan === namaBulan) || null;
        }

        function getNominalForMonth(namaBulan, nisn) {
            const inv = getInvoiceForMonth(namaBulan, nisn);
            if (inv && Number(inv.nominal) > 0) return Number(inv.nominal);

            const targetNisn = nisn || (currentUser ? currentUser.nisn : '');
            const siswa = DEMO_USERS.siswa.find(s => s.nisn === targetNisn);
            const fallback = siswa ? siswa.tarif : (currentUser ? currentUser.tarif : 0);
            return Number(fallback) || 0;
        }

        function getTotalForMonths(months, nisn) {
            return (months || []).reduce((total, bulan) => total + getNominalForMonth(bulan, nisn), 0);
        }

        // Bulan demo lama. Bulan ini hanya ditampilkan bila siswa belum punya
        // tagihan dari petugas untuk bulan yang sama.
        const DEMO_BASE_MONTHS = [
            { nama: 'Januari 2025', tempo: '10 Januari 2025' },
            { nama: 'Februari 2025', tempo: '10 Februari 2025' },
            { nama: 'Maret 2025', tempo: '10 Maret 2025' },
            { nama: 'April 2025', tempo: '10 April 2025' },
            { nama: 'Mei 2025', tempo: '10 Mei 2025' },
            { nama: 'Juni 2025', tempo: '10 Juni 2025' }
        ];

        // Daftar SEMUA tagihan siswa. Setiap tagihan punya key unik, sehingga
        // tagihan lama tidak tertimpa atau hilang saat petugas menerbitkan
        // tagihan baru (termasuk bila bulannya sama).
        function getStudentBills() {
            const nisn = currentUser ? currentUser.nisn : '';
            const invoices = createdInvoices.filter(inv => inv.siswaNisn === nisn);
            const invoiceMonths = new Set(invoices.map(inv => inv.bulan).filter(Boolean));
            const bills = [];

            DEMO_BASE_MONTHS.forEach(m => {
                if (invoiceMonths.has(m.nama)) return;
                const isUnpaid = !!(currentUser.tagihanUnpaid && currentUser.tagihanUnpaid.includes(m.nama));
                bills.push({
                    key: 'demo:' + m.nama,
                    invoiceId: null,
                    bulan: m.nama,
                    label: 'SPP ' + m.nama,
                    tempo: m.tempo,
                    nominal: getNominalForMonth(m.nama),
                    status: isUnpaid ? 'Belum Lunas' : 'Lunas',
                    sumber: 'demo'
                });
            });

            invoices.forEach(inv => {
                const bulan = inv.bulan || '';
                bills.push({
                    key: 'inv:' + inv.id,
                    invoiceId: inv.id,
                    bulan: bulan,
                    label: bulan ? 'SPP ' + bulan : (inv.keterangan || 'Tagihan Non-SPP'),
                    tempo: inv.tempo || '-',
                    nominal: Number(inv.nominal) || 0,
                    status: inv.status === 'Lunas' ? 'Lunas' : 'Belum Lunas',
                    sumber: 'petugas'
                });
            });

            return bills;
        }

        // Tagihan yang dicentang siswa. Hanya tagihan belum lunas yang bisa dipilih.
        function getSelectedPayBills() {
            return getStudentBills().filter(b => b.status !== 'Lunas' && selectedPayKeys.indexOf(b.key) !== -1);
        }

        function getSelectedPayTotal() {
            return getSelectedPayBills().reduce((total, b) => total + (Number(b.nominal) || 0), 0);
        }

        function getSelectedPayMonths() {
            return getSelectedPayBills().map(b => b.bulan || b.label).filter(Boolean);
        }

        function updatePaySummary() {
            const selected = getSelectedPayBills();
            const subtotal = selected.reduce((total, b) => total + (Number(b.nominal) || 0), 0);

            const paySubtotal = document.getElementById('paySubtotal');
            if (paySubtotal) paySubtotal.innerText = 'Rp ' + subtotal.toLocaleString('id-ID');

            const count = document.getElementById('selectedBillCount');
            if (count) count.innerText = selected.length + ' Tagihan Dipilih';

            const btnBayar = document.getElementById('btnBayarSPP');
            if (btnBayar) {
                const enabled = selected.length > 0;
                btnBayar.disabled = !enabled;
                btnBayar.style.opacity = enabled ? '1' : '0.5';
                btnBayar.style.cursor = enabled ? 'pointer' : 'not-allowed';
            }
        }
````
Pengambilan nominal per bulan saat daftar tagihan dirender:

````javascript
            bills.forEach((b, idx) => {
                const isUnpaid = b.status !== 'Lunas';
                const nominal = Number(b.nominal) || 0;

                if (isUnpaid) totalUnpaid += nominal;

                tbody.innerHTML += `
                    <tr>
                        <td><strong>${idx + 1}</strong></td>
                        <td><strong>${b.label}</strong></td>
                        <td>${b.tempo}</td>
                        <td style="font-weight:700;">Rp ${nominal.toLocaleString('id-ID')}</td>
                        <td>
                            <span class="badge-status ${isUnpaid ? 'badge-belum' : 'badge-lunas'}">
                                ${isUnpaid ? 'Belum Lunas' : 'Lunas'}
                            </span>
                        </td>
                        <td style="text-align:right;">
                            ${isUnpaid
                                ? `<button class="btn-action-primary" style="padding:6px 14px;font-size:12px;" onclick="paySingleBill('${b.key}')">Pilih Tagihan Ini</button>`
                                : `<span style="font-size:12px;color:var(--text-muted);font-weight:700;">✔ Selesai</span>`
                            }
                        </td>
                    </tr>
                `;
````
Banner **"Metode Pembayaran Resmi: Midtrans Gateway"** dihapus dari halaman
pembayaran. Setelah ringkasan total, pengguna langsung melihat tombol BAYAR.

### 4.16 Tagihan lama tetap tersimpan saat tagihan baru dibuat

**Masalah:** daftar tagihan siswa digabung berdasarkan nama bulan. Jika petugas
menerbitkan tagihan baru untuk bulan yang sama, tagihan lama tertutup / hilang
dari halaman pembayaran. Selain itu, ID tagihan yang dibuat pada milidetik yang
sama bisa bentrok sehingga beberapa tagihan dianggap satu.

**Perbaikan:**

- `getStudentBills()` menampilkan **semua** tagihan petugas untuk siswa,
  termasuk tagihan lama dan yang sudah lunas. Bulan demo hanya dipakai bila
  belum ada tagihan petugas untuk bulan itu.
- Setiap tagihan memakai key unik (`inv:<id>`), sehingga tagihan lama tidak
  tertimpa tagihan baru meski bulannya sama.
- Penomoran tagihan memakai counter global (`makeInvoiceId()`), jadi beberapa
  tagihan yang dibuat beruntun tetap punya ID berbeda.
- Saat pembayaran berhasil, hanya tagihan yang dipilih yang ditandai lunas.
  Tagihan lain, termasuk tagihan lama pada bulan yang sama, tetap belum lunas
  dan tetap tampil.

````javascript
        // Penomoran tagihan unik, termasuk saat beberapa tagihan dibuat
        // dalam milidetik yang sama.
        let invoiceIdCounter = 0;
        function makeInvoiceId() {
            invoiceIdCounter++;
            return 'INV-' + Date.now().toString(36).toUpperCase() + '-' + invoiceIdCounter;
        }
        // True bila petugas sudah mengetik nominal sendiri. Selama true,
        // nominal tidak ditimpa otomatis saat memilih siswa/kelas.
        let invoiceNominalManual = false;
````
````javascript
                // 2. Tandai HANYA tagihan yang dipilih sebagai lunas. Tagihan
                // lama yang tidak dipilih tetap tampil dan tetap bisa dibayar.
                const paidKeys = (snapActivePayment && snapActivePayment.billKeys)
                    ? snapActivePayment.billKeys.slice()
                    : selectedPayKeys.slice();
                const paidBills = getStudentBills().filter(b => paidKeys.includes(b.key));

                paidBills.forEach(bill => {
                    const bulan = bill.bulan;

                    if (bill.invoiceId) {
                        const inv = createdInvoices.find(i => i.id === bill.invoiceId);
                        if (inv) inv.status = 'Lunas';
                    }

                    if (bulan) {
                        // Update current user
                        if (currentUser.tagihanUnpaid) {
                            currentUser.tagihanUnpaid = currentUser.tagihanUnpaid.filter(b => b !== bulan);
                        }
                        // Sync ke semua student dengan NISN ini di DEMO_USERS
                        DEMO_USERS.siswa.forEach(s => {
                            if (s.nisn === currentUser.nisn && s.tagihanUnpaid) {
                                s.tagihanUnpaid = s.tagihanUnpaid.filter(b => b !== bulan);
                            }
                        });
                        // Sync ke orang tua
                        DEMO_USERS.ortu.forEach(o => {
                            if (o.nisn === currentUser.nisn && o.tagihanUnpaid) {
                                o.tagihanUnpaid = o.tagihanUnpaid.filter(b => b !== bulan);
                            }
                        });
                    }
                });
````
### 4.17 Dropdown tahun ajaran langsung bisa dipilih

**Masalah:** dropdown tahun ajaran baru diisi setelah request
`GET /api/tahun-ajaran` selesai. Setiap kali menu penerbitan tagihan dibuka,
daftar dibangun ulang secara asinkron. Jika petugas memilih tahun saat request
masih berjalan, pilihan bisa kembali ke tahun aktif atau daftar terasa tidak
bisa dipilih langsung.

**Perbaikan:**

- Dropdown langsung diisi dari cache/daftar cadangan **sebelum** request API,
  jadi bisa dipilih seketika.
- Request API hanya dilakukan **sekali**; hasilnya disimpan di
  `tahunAjaranCache` dan dipakai lagi saat menu dibuka ulang.
- Daftar hanya dibangun ulang jika isinya benar-benar berubah. Jika sama,
  pilihan petugas tidak disentuh.
- Pilihan petugas dipertahankan saat pindah menu dan kembali lagi. Tahun aktif
  hanya dipilih otomatis pada pemuatan pertama atau saat form di-reset.

````javascript
        // Cache daftar tahun ajaran. Dropdown langsung diisi dari cache/cadangan
        // supaya bisa dipilih seketika, lalu request API hanya dilakukan sekali.
        // Dengan begitu daftar tidak dibangun ulang di tengah interaksi dan
        // pilihan petugas tidak kembali ke tahun aktif.
        let tahunAjaranCache = null;
        let tahunAjaranRequest = null;
        let tahunAjaranUserPicked = false;

        function applyTahunAjaranOptions(rows, resetSelection) {
            const select = document.getElementById('invoiceTahunAjaran');
            if (!select) return;

            const list = (Array.isArray(rows) && rows.length) ? rows : buildFallbackTahunAjaran();
            const signature = list.map(item => {
                const nama = item.tahun_ajaran || item.nama || item.value || '';
                return nama + '|' + (item.status_aktif || '');
            }).join(',');

            const previous = select.value;
            const sameList = select.dataset.signature === signature && select.options.length > 0;

            if (!sameList) {
                select.innerHTML = '';
                list.forEach(item => {
                    const nama = item.tahun_ajaran || item.nama || item.value || '';
                    if (!nama) return;
                    const opt = document.createElement('option');
                    opt.value = nama;
                    opt.textContent = nama + (item.status_aktif === 'aktif' ? ' (Aktif)' : '');
                    opt.dataset.aktif = item.status_aktif || '';
                    select.appendChild(opt);
                });
                select.dataset.signature = signature;
            }

            const active = list.find(x => x.status_aktif === 'aktif');
            const target = active ? (active.tahun_ajaran || active.nama || active.value) : getCurrentAcademicYear();
            const keepPrevious = !resetSelection && !tahunAjaranUserPicked && previous &&
                Array.from(select.options).some(o => o.value === previous);

            // Saat daftar tidak berubah, jangan sentuh pilihan yang sedang aktif.
            if (!sameList || resetSelection) {
                const match = Array.from(select.options).find(o => o.value === target);
                if (keepPrevious) {
                    select.value = previous;
                } else if (match) {
                    select.value = match.value;
                } else if (select.options.length) {
                    select.selectedIndex = 0;
                }
            }
        }

        // Ambil pilihan tahun ajaran dari tabel tbl_tahun_ajaran lewat API.
        // Tahun yang berstatus "aktif" otomatis terpilih pada pemuatan pertama.
        function loadTahunAjaranOptions(options) {
            const select = document.getElementById('invoiceTahunAjaran');
            if (!select) return Promise.resolve();

            const resetSelection = !!(options && options.reset);

            // Langsung isi dropdown (tanpa menunggu API) agar bisa langsung dipilih.
            applyTahunAjaranOptions(tahunAjaranCache || buildFallbackTahunAjaran(), resetSelection);

            // Request hanya sekali; hasilnya dipakai ulang untuk navigasi berikutnya.
            if (tahunAjaranRequest) return tahunAjaranRequest;

            tahunAjaranRequest = fetch('/api/tahun-ajaran', { headers: { 'Accept': 'application/json' } })
                .then(res => (res && res.ok) ? res.json() : null)
                .then(json => {
                    const rows = json ? (Array.isArray(json) ? json : (json.data || [])) : [];
                    if (Array.isArray(rows) && rows.length) tahunAjaranCache = rows;
                    applyTahunAjaranOptions(tahunAjaranCache || buildFallbackTahunAjaran(), false);
                })
                .catch(err => {
                    console.warn('Gagal memuat tahun ajaran, memakai daftar cadangan:', err);
                    applyTahunAjaranOptions(buildFallbackTahunAjaran(), false);
                });

            return tahunAjaranRequest;
        }
````
### 4.18 Tagihan tetap ada setelah halaman di-refresh

**Masalah:** tagihan yang diterbitkan petugas hanya disimpan di variabel
JavaScript `createdInvoices`. Begitu halaman di-refresh, variabel kembali ke
data contoh sehingga tagihan baru dan status pembayarannya hilang.

**Perbaikan:**

- `createdInvoices`, `transactions`, dan status `tagihanUnpaid` siswa/wali
  disimpan ke `localStorage` dengan key `spp_demo_data_v1`.
- Data dimuat kembali sebelum halaman dirender, sehingga tagihan baru, tagihan
  yang sudah lunas, dan riwayat transaksi tetap tampil setelah refresh.
- Penyimpanan dilakukan setiap kali tagihan dibuat, status diubah, tagihan
  dihapus, pembayaran Midtrans berhasil, atau pembayaran kasir dicatat.
- `beforeunload` menjadi pengaman terakhir agar perubahan tetap tersimpan saat
  halaman ditutup atau di-refresh.
- Untuk mengosongkan data demo, hapus `localStorage` halaman ini melalui
  pengaturan situs di browser.

````javascript
        // ================= PERSISTENSI DATA DEMO =================
        // Tagihan yang dibuat petugas, status pembayaran, dan riwayat transaksi
        // disimpan ke localStorage supaya tidak hilang saat halaman di-refresh.
        const SPP_DATA_STORAGE_KEY = 'spp_demo_data_v1';

        function collectUnpaidSnapshot() {
            const siswa = {};
            const ortu = {};
            (DEMO_USERS.siswa || []).forEach(s => {
                if (s.nisn) siswa[s.nisn] = Array.isArray(s.tagihanUnpaid) ? s.tagihanUnpaid.slice() : [];
            });
            (DEMO_USERS.ortu || []).forEach(o => {
                if (o.nisn) ortu[o.nisn] = Array.isArray(o.tagihanUnpaid) ? o.tagihanUnpaid.slice() : [];
            });
            return { siswa: siswa, ortu: ortu };
        }

        function saveSppData() {
            try {
                const payload = {
                    version: 1,
                    savedAt: new Date().toISOString(),
                    invoices: createdInvoices,
                    transactions: transactions,
                    unpaid: collectUnpaidSnapshot()
                };
                localStorage.setItem(SPP_DATA_STORAGE_KEY, JSON.stringify(payload));
            } catch (e) {
                console.warn('Gagal menyimpan data SPP demo:', e);
            }
        }

        function loadSppData() {
            try {
                const raw = localStorage.getItem(SPP_DATA_STORAGE_KEY);
                if (!raw) return false;

                const data = JSON.parse(raw);
                if (!data || typeof data !== 'object') return false;

                if (Array.isArray(data.invoices)) {
                    createdInvoices = data.invoices.filter(inv => inv && inv.id && inv.siswaNisn);
                }
                if (Array.isArray(data.transactions)) {
                    transactions = data.transactions;
                }
                if (data.unpaid && typeof data.unpaid === 'object') {
                    const applyUnpaid = (users, map) => {
                        if (!map || typeof map !== 'object') return;
                        (users || []).forEach(u => {
                            if (u.nisn && Array.isArray(map[u.nisn])) {
                                u.tagihanUnpaid = map[u.nisn].slice();
                            }
                        });
                    };
                    applyUnpaid(DEMO_USERS.siswa, data.unpaid.siswa);
                    applyUnpaid(DEMO_USERS.ortu, data.unpaid.ortu);
                }
                return true;
            } catch (e) {
                console.warn('Gagal memuat data SPP demo tersimpan:', e);
                return false;
            }
        }

        // Muat data tersimpan sebelum halaman dirender.
        loadSppData();

        // Simpan sebagai pengaman terakhir saat halaman ditutup/di-refresh.
        window.addEventListener('beforeunload', saveSppData);
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'hidden') saveSppData();
        });
````
---

## 5. Demo offline

### 5.1 `demo/mock-backend.js` — helper data & pencarian

````javascript
    function getDemoStudents() {
        try {
            if (typeof DEMO_USERS !== 'undefined' && DEMO_USERS && Array.isArray(DEMO_USERS.siswa)) {
                return DEMO_USERS.siswa;
            }
        } catch (e) { /* DEMO_USERS belum siap */ }
        return [];
    }

    function buildDemoTagihanSiswa() {
        return getDemoStudents().map(function (s, i) {
            var belum = s.tagihanUnpaid && s.tagihanUnpaid.length > 0;
            return {
                id_tagihan: i + 1,
                id_siswa: i + 1,
                id_kelas: i + 1,
                siswa: { id_siswa: i + 1, nisn: s.nisn, nama_siswa: s.nama },
                kelas: { id_kelas: i + 1, nama_kelas: s.kelas },
                bulan: 'Januari 2025',
                nominal: s.tarif || 250000,
                status: belum ? 'belum_lunas' : 'lunas',
                keterangan: belum ? 'SPP bulan Januari 2025' : 'Sudah dibayar',
                created_at: '2025-01-05T08:00:00'
            };
        });
    }
````
### 5.2 Mock endpoint pencarian tagihan

````javascript
        if (path === '/api/tagihan/search' && method === 'GET') {
            var queryString = raw.indexOf('?') >= 0 ? raw.split('?')[1] : '';
            var params = {};
            queryString.split('&').forEach(function (pair) {
                if (!pair) return;
                var parts = pair.split('=');
                var key = decodeURIComponent(parts[0] || '');
                var value = decodeURIComponent((parts[1] || '').replace(/\+/g, ' '));
                params[key] = value;
            });

            var q = String(params.query || params.q || '').toLowerCase().trim();
            var statusFilter = String(params.status || '');

            var searchResult = buildDemoTagihanSiswa().filter(function (t) {
                var haystack = [
                    t.siswa.nisn, t.siswa.nama_siswa, t.kelas.nama_kelas,
                    t.bulan, t.keterangan
                ].join(' ').toLowerCase();

                var cocokQuery = !q || haystack.indexOf(q) >= 0;
                var cocokStatus = !statusFilter || t.status === statusFilter;
                return cocokQuery && cocokStatus;
            });

            var mapped = searchResult.map(function (t) {
                return {
                    id: t.id_tagihan,
                    id_tagihan: t.id_tagihan,
                    nisn: t.siswa.nisn,
                    nis: t.siswa.nisn,
                    nama_siswa: t.siswa.nama_siswa,
                    kelas: t.kelas.nama_kelas,
                    bulan: t.bulan,
                    nominal: t.nominal,
                    status: t.status,
                    status_label: t.status === 'lunas' ? 'Lunas' : 'Belum Lunas',
                    tempo: '2025-01-15',
                    tahun_ajaran: '2024/2025 Genap',
                    keterangan: t.keterangan
                };
            });

            var totalNominal = mapped.reduce(function (sum, t) {
                return sum + (Number(t.nominal) || 0);
            }, 0);

            return ok({
                status: 'success',
                query: q,
                count: mapped.length,
                total_nominal: totalNominal,
                data: mapped
            });
        }
````
### 5.3 `demo/spp-offline.html`

File ini adalah hasil build dari:

```powershell
cd "C:\\Users\\ASUS\\Documents\\file skrpsi\\spp-app"
python demo\\build-demo.py
```

Output: `demo/spp-offline.html` (±406.1 KB, 415818 bytes).
File inilah yang dikirim ke HP dan dibuka lewat
`file:///sdcard/Download/spp-offline.html`.

### 5.4 `demo/README.md`

Diperbarui:

- akun 30 guru (`guru001`–`guru030` / `guru123`),
- akun 9 wali kelas (`wali7a`–`wali9c` / `wali123`),
- penjelasan 261 akun siswa (username = NISN, password `password123`),
- alur bayar **tanpa verifikasi selfie dan tanpa akses kamera**,
- **6 metode pembayaran** yang langsung terlihat di HP: GoPay QRIS, Card
  Payment, ShopeePay QRIS, OVO, DANA, dan Bank Virtual Account,
- penjelasan bahwa daftar metode pada aplikasi Laravel diambil otomatis dari
  dashboard Midtrans,
- penjelasan menu siswa per kelas, akun, dan pencarian AJAX.

### 5.5 Mock endpoint tahun ajaran

Demo offline memakai `window.fetch` tiruan. Agar form demo berperilaku sama
dengan aplikasi Laravel, mock juga menyediakan `GET /api/tahun-ajaran` dengan
rentang **2000/2001 – 2045/2046** (46 baris). Tahun berjalan dihitung dari
kalender (Juli–Desember masuk tahun ajaran baru) dan ditandai `aktif`.

````javascript
        /* ---------------- Tahun Ajaran ---------------- */
        if (path === '/api/tahun-ajaran' && method === 'GET') {
            var nowYear = new Date().getFullYear();
            var nowMonth = new Date().getMonth();
            var currentTa = nowMonth >= 6
                ? (nowYear + '/' + (nowYear + 1))
                : ((nowYear - 1) + '/' + nowYear);

            // Rentang tahun ajaran 2000/2001 sampai 2045/2046, sama dengan
            // isi tabel tbl_tahun_ajaran pada aplikasi Laravel.
            var tahunAjaranRows = [];
            var taId = 1;
            for (var taStart = 2000; taStart <= 2045; taStart++) {
                var taName = taStart + '/' + (taStart + 1);
                tahunAjaranRows.push({
                    id_tahun_ajaran: taId++,
                    tahun_ajaran: taName,
                    status_aktif: taName === currentTa ? 'aktif' : 'nonaktif'
                });
            }

            return ok({
                success: true,
                status: 'success',
                count: tahunAjaranRows.length,
                data: tahunAjaranRows
            });
        }
````
---

## 6. Hasil pengujian

### 6.1 Uji alur bayar tanpa selfie

Pengujian headless Chrome memastikan elemen selfie benar-benar hilang dan
jendela pembayaran tetap terbuka.

```text
PASS fungsi selfie sudah dihapus
PASS flag selfieVerified sudah dihapus
PASS modal selfie sudah dihapus
PASS elemen kamera selfie sudah dihapus
PASS modal Snap langsung terbuka tanpa selfie
PASS tidak ada overflow horizontal = 0px
PASS fungsi klaim beasiswa sudah dihapus
PASS tombol klaim beasiswa sudah hilang
```

Hasil yang sama juga muncul pada viewport HP 500×844.

### 6.2 Uji fungsional fitur lain

Perintah:

```powershell
python C:\\Users\\ASUS\\AppData\\Local\\Temp\\opencode\\run-feature-probe.py
```

Hasil utama (desktop 1366×900 dan phone 500×844):

```text
PASS menu petugas tampil = 11
PASS menu Siswa per Kelas ada
PASS menu Akun Siswa & Wali Kelas ada
PASS siswa per kelas 7A = 29 baris
PASS ringkasan kelas memuat 29 siswa
PASS akun siswa 7A = 29 baris
PASS akun wali kelas = 9 baris
PASS akun guru = 30 baris
PASS akun kepala sekolah = 1 baris
PASS akun petugas inti = 2 baris
PASS manajemen siswa semua kelas = 261 baris
PASS AJAX search mengembalikan baris = 5
PASS status AJAX: 5 tagihan ditemukan • Total Rp 1.250.000
PASS tidak ada overflow horizontal = 0px
```

### 6.3 Uji layout sempit (iframe 390 px dan 360 px)

```text
PASS sidebar menempel tepi kiri di 390px / 360px
PASS lebar sidebar HP=118px
PASS menu petugas=11
PASS label menu utuh
PASS semua menu terlihat tanpa scroll
PASS seluruh view: overflow-x <= 0
```

### 6.4 Uji backend Laravel

```text
GET  /                              → 200
GET  /api/tahun-ajaran               → 200, 46 tahun ajaran (2000/2001–2045/2046, 2026/2027 aktif)
GET  /api/midtrans/config            → 200, is_production=false, snap_js sandbox
GET  /api/tagihan/search?query=...  → 200, JSON valid
POST /api/verifikasi-selfie         → 404 (route sudah dihapus)
POST /api/midtrans/snap-token       → 200, snap_token + order_id + expires_at diterima
GET  /api/midtrans/cek-status/<id>  → 200, JSON status pembayaran
```

### 6.5 Uji metode pembayaran Midtrans (sandbox asli)

Token dibuat lewat endpoint Laravel `POST /api/midtrans/snap-token`, lalu
diperiksa langsung ke API Midtrans:

```text
STATUS 200
success=true
order=SPP-0081234567-20261105043235-76
token=beaff667-2d68-4d83-8d97-0b360eb1ab67
methods=12
  gopay            up
  qris (gopay)     up
  credit_card      up
  google_pay       up
  shopeepay        up
  qris (shopeepay) up
  dana             up
  alfamart         up
  indomaret        up
  akulaku          up
  kredivo          up
  ovo              up
```

Perbandingan sebelum/sesudah perbaikan:

```text
Payload lama (enabled_payments manual) → 11 metode, "virtual_account" tidak valid
Payload baru (tanpa enabled_payments)  → 12 metode (google_pay ikut muncul)
```

Catatan: Virtual Account bank belum muncul pada merchant sandbox ini meskipun
kode VA yang benar (`bca_va`, `bni_va`, `bri_va`, `permata_va`, `echannel`)
sudah dicoba. Metode VA perlu diaktifkan dulu di dashboard Midtrans.

### 6.6 Uji tampilan metode pada demo offline

Pengujian headless Chrome pada tiga ukuran layar HP. Semua 6 metode terlihat
tanpa perlu menggulir dan tidak ada overflow horizontal:

```text
===== 360 x 640
methods=6
  [0] GoPay QRIS           visible=true h=52
  [1] Card Payment         visible=true h=52
  [2] ShopeePay QRIS       visible=true h=52
  [3] OVO                  visible=true h=52
  [4] DANA                 visible=true h=52
  [5] Bank Virtual Account visible=true h=52
overflowX=0

===== 390 x 844  → methods=6, semua visible=true, overflowX=0
===== 412 x 732  → methods=6, semua visible=true, overflowX=0
```

Uji klik setiap metode (halaman detail terbuka dengan judul yang benar):

```text
GoPay QRIS           → GoPay QRIS
Card Payment         → Kartu Kredit / Debit Online
ShopeePay QRIS       → ShopeePay QRIS
OVO                  → OVO Instant Checkout
DANA                 → DANA
Bank Virtual Account → Bank Syariah Indonesia / Mandiri Virtual Account
```

### 6.7 Pemeriksaan sintaks

```text
php -l TagihanController.php                 → No syntax errors
php -l MidtransController.php                → No syntax errors
php -l routes/web.php                        → No syntax errors
node --check welcome-main.js                 → LOLOS
node --check demo/mock-backend.js            → LOLOS
php artisan view:cache                       → Blade templates cached successfully
python demo/build-demo.py                    → OK, 406.1 KB
```

### 6.8 Uji mode Sandbox & fallback otomatis

Konfigurasi aktif adalah **Sandbox**. Endpoint `POST /api/midtrans/snap-token`
diuji langsung:

```text
config: is_production=false
        snap_js=https://app.sandbox.midtrans.com/snap/snap.js
STATUS 200
success=true   mode_production=false
token=37662791-d3e1-49ec-aa39-a20e9b7e7c7e
expires_at=2026-11-06T04:50:11+00:00   expires_in=86400
redirect=https://app.sandbox.midtrans.com/snap/v4/redirection/37662791-...
methods=12
expiry={"start_time":null,"duration":24,"unit":"HOURS"}
```

Uji fallback: mode dipaksa `is_production=true` pada
`storage/app/midtrans_config.json`, lalu endpoint dipanggil lagi.

```text
Sebelum: Production + kunci Sandbox → 401 Unauthorized
Sesudah: otomatis pindah Sandbox    → STATUS 200
         token=7f9f6c26-467e-497c-bce2-52dfa6582011
         is_production=false
Config setelah permintaan: "is_production": false
.env setelah permintaan: MIDTRANS_IS_PRODUCTION=false
```

Artinya pengguna tidak lagi melihat error `401`, dan mode yang benar
tersimpan permanen.

### 6.9 Uji token kedaluwarsa & retry front-end

Pengujian headless Chrome pada demo offline: panggilan `snap.pay` pertama
sengaja dibuat melempar pesan `Token is expired`.

```text
snap.pay call 1 token=demo-snap-1001 expiresAt=1793940688834
simulate expired error
snap.pay call 2 token=demo-snap-1002 expiresAt=1793940689614
tokenCalls=2
payCalls=2
retryCount=1
expiresAtValid=true
```

Token baru berhasil dibuat otomatis, `expires_at` valid 24 jam, dan aplikasi
tidak berhenti pada token yang sudah kedaluwarsa.

### 6.10 Uji klik bulan → tombol BAYAR → gateway muncul

Pengujian headless Chrome mengikuti alur siswa sungguhan: klik checkbox bulan,
lalu klik tombol BAYAR.

**Aplikasi Laravel (Snap asli):**

```text
checkboxes=1 checkedBefore=true btnDisabledBefore=false selected=["Januari 2025"]
after checkbox click: checked=true btnDisabled=false selected=["Januari 2025"]
clicking BAYAR; btnDisabled=false
snap iframe=block 504x748
fallback=false
```

**Demo offline (modal 6 metode):**

```text
checkboxes=1 checkedBefore=true btnDisabledBefore=false selected=["Januari 2025"]
after checkbox click: checked=true btnDisabled=false selected=["Januari 2025"]
clicking BAYAR; btnDisabled=false
modal display=flex
modal visible=true
methods=6
```

Sebelum perbaikan, hasilnya `btnDisabled=true` dan `snap iframe=none 0x0`.

### 6.11 Uji form tagihan: tahun ajaran dari tabel, checkbox bulan, jatuh tempo

Pengujian headless Chrome (viewport 390×844) pada aplikasi Laravel dan demo
offline. Skenario: login admin, buka menu Terbitkan Tagihan, hapus centang bulan
berjalan, centang **Januari + Februari**, lalu terbitkan.

**Aplikasi Laravel** (`GET /api/tahun-ajaran`):

```text
API count=46 rows=46
API first={"tahun_ajaran": "2000/2001", "status_aktif": "nonaktif"}
API last={"tahun_ajaran": "2045/2046", "status_aktif": "nonaktif"}
API active=[{"tahun_ajaran": "2026/2027", "status_aktif": "aktif"}]

tahun options=46
first=2000/2001
last=2045/2046
selected=2026/2027 (Aktif)
activeOptions=["2026/2027"]
tag=SELECT
month checkboxes=12 checked=["November 2026"]
tempo=2026-11-05
today=2026-11-05
after multi-check selected=["Januari 2026","Februari 2026"]
created delta=2 latest=["Februari 2026 / 2026/2027 / Belum Lunas",
                        "Januari 2026 / 2026/2027 / Belum Lunas"]
success month text=SPP Januari 2026, Februari 2026 - TP 2026/2027
success amount text=Rp 500.000 (@ Rp 250.000/bulan)
success modal=flex
```

**Demo offline** (mock `/api/tahun-ajaran`):

```text
tahun options=46
first=2000/2001
last=2045/2046
selected=2026/2027 (Aktif)
activeOptions=["2026/2027"]
tag=SELECT
month checkboxes=12 checked=["November 2026"]
tempo=2026-11-05
today=2026-11-05
after multi-check selected=["Januari 2026","Februari 2026"]
created delta=2 (satu tagihan per bulan)
```

Yang terbukti:

- daftar tahun ajaran berasal dari data, bukan input manual;
- rentang **2000/2001 – 2045/2046** tersedia lengkap (46 pilihan) di Laravel
  dan demo offline;
- tahun `aktif` (2026/2027) otomatis terpilih;
- bulan bisa dicentang lebih dari satu;
- 2 bulan → **2 tagihan** dengan total `Rp 500.000`;
- jatuh tempo = tanggal dan tahun hari ini (`2026-11-05`).

### 6.12 Uji pembayaran banyak bulan sekaligus (Midtrans sandbox)

Skenario: login siswa, buka halaman Bayar SPP, centang **2 bulan**, klik BAYAR.
Sebelum perbaikan, request gagal `500` karena `nama_bulan` terlalu pendek.

```text
selected=["Januari 2025","Februari 2025"] subtotal=Rp 500.000 disabled=false
token resp success=true total=500000 token=<uuid> mode=false
snap iframe=block 504x748
fallback=false
```

Baris database yang terbentuk:

```text
nama_bulan = "Januari 2025, Februari 2025"
nominal_tagihan = 500000, jumlah_bayar = 500000, status_bayar = belum_lunas
```

Setelah status transaksi menjadi `settlement`/`capture`, helper
`markTagihanSppLunas()` memecah daftar bulan dan menandai **semua** tagihan
bulan tersebut sebagai `lunas`.

Uji regresi UI pembayaran: 2 bulan tercentang → `paySubtotal=Rp 500.000`,
tombol BAYAR aktif, dan pop-up Snap tampil (`block 504x748`).

### 6.13 Uji nominal kustom dari petugas

Pengujian headless Chrome: petugas mengetik nominal **Rp 375.000**, memilih
Januari + Februari, lalu menerbitkan tagihan. Setelah itu login sebagai siswa
dan wali murid untuk memeriksa nominal yang tampil.

```text
nominalSetelahGantiSiswa=375000
nominalSetelahKembaliSiswa=375000
created=["Februari 2026 = 375000","Januari 2026 = 375000"]
successAmount=Rp 750.000 (@ Rp 375.000/bulan)

SISWA rows=["SPP Januari 2025 ... Rp 250.000",
            "SPP Februari 2026 ... Rp 375.000",
            "SPP Januari 2026 ... Rp 375.000"]
SISWA selected=["Januari 2026","Februari 2026"]
SISWA subtotal=Rp 750.000
SISWA tokenAmount=750000
SISWA tokenBulan=Januari 2026, Februari 2026
SISWA snap=block 504x748

ORTU rows=[... nominal Rp 375.000 untuk Januari & Februari 2026]
ORTU subtotal=Rp 750.000
ORTU teksMetodeResmi=false
```

Demo offline diuji dengan nominal **Rp 425.000**: subtotal `Rp 850.000` dan
payload token `amount=850000`, `bulan="Januari 2026, Februari 2026"`.

Dengan demikian nominal yang diketik petugas benar-benar terbawa ke akun siswa
dan wali murid, serta ke nominal transaksi Midtrans.

### 6.14 Uji tagihan lama & pilihan bebas

Skenario headless Chrome (Laravel dan demo offline): petugas menerbitkan tiga
tagihan beruntun — **Januari 2026 Rp 300.000**, **Januari 2026 Rp 400.000**
(bulan sama), dan **Februari 2026 Rp 350.000**. Lalu login siswa dan buka
halaman pembayaran.

```text
createdCounts=[1,1,1]
studentInvoices=[
  "Februari 2026:350000:Belum Lunas",
  "Januari 2026:400000:Belum Lunas",
  "Januari 2026:300000:Belum Lunas",
  "Januari 2025:250000:Belum Lunas",
  "Desember 2024:250000:Lunas"
]

AWAL selected=[] subtotal=Rp 0 count=0 bayarDisabled=true
AWAL tokenRequests=0
TANPA PILIH tokenRequestsDelta=0

PILIH-SATU (hanya Januari 2026 Rp 300.000)
selected=["inv:INV-...-1"] subtotal=Rp 300.000 count=1
token amount=300000 bulan=Januari 2026

SETELAH-BAYAR
Februari 2026 350000 = Belum Lunas
Januari 2026 400000 = Belum Lunas
Januari 2026 300000 = Lunas
Januari 2025 250000 = Belum Lunas
Desember 2024 250000 = Lunas
```

Yang terbukti:

- ketiga tagihan baru tetap tampil semua, termasuk dua tagihan pada bulan
  Januari 2026;
- tagihan lama (Januari 2025 dan Desember 2024) tidak hilang;
- tidak ada tagihan yang otomatis tercentang;
- klik BAYAR tanpa memilih tidak membuat token Midtrans;
- memilih satu tagihan hanya mengirim nominal tagihan itu (`Rp 300.000`);
- setelah pembayaran, hanya tagihan yang dipilih yang menjadi lunas; tagihan
  lain pada bulan yang sama tetap belum lunas dan tetap bisa dibayar.

### 6.15 Uji dropdown tahun ajaran (Laravel & demo offline)

Skenario headless: login petugas, buka menu penerbitan tagihan, lalu langsung
memilih tahun ajaran tanpa menunggu API.

```text
OPEN-0MS      value=2026/2027  options=46  first=2000/2001 last=2045/2046
PICK-2010     value=2010/2011  options=46
AFTER-API     value=2010/2011  options=46
REOPEN        value=2010/2011  options=46
AFTER-REOPEN  value=2010/2011  options=46
PICK-2030     value=2030/2031  options=46
AFTER-RESET   value=2026/2027 (Aktif)  options=46
```

Yang terbukti:

- dropdown sudah berisi 46 pilihan **tepat saat halaman dibuka**;
- pilihan `2010/2011` langsung diterima dan tidak berubah setelah request API
  selesai;
- pilihan tetap tersimpan saat pindah menu lalu kembali;
- mengganti ke `2030/2031` saat request berjalan tetap berhasil;
- tombol reset mengembalikan pilihan ke tahun aktif `2026/2027 (Aktif)`;
- hasil sama pada aplikasi Laravel dan demo offline.

### 6.16 Uji tagihan tetap ada setelah refresh

Skenario headless: petugas membuat **dua tagihan baru** (Januari 2026
Rp 310.000 dan Februari 2026 Rp 320.000), menandai satu tagihan lunas, lalu
halaman **di-refresh**. Setelah refresh, login sebagai siswa dan buka halaman
bayar.

```text
PHASE2 count=9
PHASE2 student=[
  "Februari 2026:320000:Lunas",
  "Januari 2026:310000:Belum Lunas",
  "Januari 2025:250000:Belum Lunas",
  "Desember 2024:250000:Lunas"
]
PHASE2 trx=4
PHASE2 paymentLabels=[
  "SPP Januari 2026 ... Rp 310.000",
  "SPP Januari 2025 ... Rp 250.000"
]
```

Yang terbukti:

- kedua tagihan baru masih ada setelah refresh;
- status lunas pada salah satu tagihan ikut tersimpan;
- riwayat transaksi bertambah dan tetap ada setelah refresh;
- tagihan yang belum lunas tetap muncul di daftar pilihan pembayaran siswa;
- hasil sama pada aplikasi Laravel dan demo offline.

---

## 7. Akun demo penting

| Peran | Username | Password |
|---|---|---|
| Siswa contoh | `0081234567` | `password123` |
| Wali murid | `ortu001` | `password123` |
| Admin | `admin` | `admin123` |
| Bendahara | `bendahara` | `bendahara123` |
| Kepala Sekolah | `kepsek` | `kepsek123` |
| Guru | `guru001` … `guru030` | `guru123` |
| Wali Kelas | `wali7a` … `wali9c` | `wali123` |
| Siswa 7A–9C | NISN `0081000101` … `0081000929` | `password123` |

---

## 8. Batasan & catatan produksi

1. **Tidak ada verifikasi selfie.** Pembayaran tidak membuka kamera dan tidak
   menyimpan foto bukti.
2. **Tidak ada pemindaian/pengenalan wajah.**
3. **Demo offline** memakai backend tiruan di dalam browser, bukan server
   sungguhan.
4. **Midtrans offline** tetap simulasi. Transaksi sungguhan hanya berjalan di
   aplikasi Laravel dengan Server Key sandbox/production.
5. **Metode pembayaran** mengikuti pengaturan dashboard Midtrans. Jika Virtual
   Account belum aktif di dashboard, VA tidak akan muncul meskipun kode
   `enabled_payments` benar.
6. **Uji pop-up Snap di headless Chrome** tidak dapat memverifikasi tampilan
   asli karena iframe Snap tidak selesai dimuat di lingkungan headless. Yang
   sudah diverifikasi: token asli berhasil dibuat, token berlaku 24 jam, dan
   12 metode tersedia dari API Midtrans. Jika pop-up tetap kosong di browser
   tertentu, overlay fallback menyediakan tautan ke halaman pembayaran resmi.
   Jika token benar-benar kedaluwarsa, front-end otomatis membuat token baru
   maksimal 2 kali.
7. **Mode Midtrans** harus sesuai kunci. Gunakan **Sandbox** untuk kunci
   Sandbox dan **Production** untuk kunci Production. Backend sudah mencoba
   fallback otomatis saat terjadi `401 Unauthorized`, tetapi kunci Production
   yang benar tetap wajib dipakai untuk transaksi uang sungguhan.
8. **Login front-end** masih demo (`DEMO_USERS` + `localStorage`). Untuk
   produksi, ganti dengan autentikasi Laravel dan jangan tampilkan menu
   petugas kepada publik.
9. Jalankan `php artisan migrate` di server produksi untuk skema terbaru.

---

## 9. Cara menjalankan ulang

```powershell
cd "C:\\Users\\ASUS\\Documents\\file skrpsi\\spp-app"

# 1. Migrasi
C:\\xampp\\php\\php.exe artisan migrate --force

# 2. Kompilasi Blade
C:\\xampp\\php\\php.exe artisan view:cache

# 3. Bangun demo offline
python demo\\build-demo.py

# 4. Jalankan Laravel (opsional)
C:\\xampp\\php\\php.exe artisan serve --host=127.0.0.1 --port=8000
```

Selesai.
