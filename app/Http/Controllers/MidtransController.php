<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\TagihanSpp;
use App\Models\LogMidtrans;
use App\Models\DetailPembayaran;
use App\Models\Spp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransController extends Controller
{
    private function getConfigPath(): string
    {
        return storage_path('app/midtrans_config.json');
    }

    private function readConfig(): array
    {
        $config = [
            'enabled' => config('services.midtrans.enabled', true),
            'is_production' => config('services.midtrans.is_production', false),
            'merchant_id' => config('services.midtrans.merchant_id', ''),
            'client_key' => config('services.midtrans.client_key', ''),
            'server_key' => config('services.midtrans.server_key', ''),
            'qris_name' => 'SMP MUHAMMADIYAH 3 METRO',
            'va_name' => 'Bank Syariah Indonesia / Mandiri',
            'card_name' => 'Kartu Kredit / Debit Online',
            'gopay_name' => 'GoPay QRIS',
        ];

        $path = $this->getConfigPath();
        if (File::exists($path)) {
            $storedConfig = json_decode(File::get($path), true);
            if (is_array($storedConfig)) {
                $config = array_merge($config, $storedConfig);
            }
        }

        $config['enabled'] = filter_var($config['enabled'], FILTER_VALIDATE_BOOLEAN);
        $config['is_production'] = filter_var($config['is_production'], FILTER_VALIDATE_BOOLEAN);

        return $config;
    }

    public function getConfig()
    {
        $data = $this->readConfig();
        $data['has_server_key'] = !empty($data['server_key']);
        unset($data['server_key']);

        $data['snap_js'] = !empty($data['is_production'])
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';

        return response()->json($data);
    }

    /**
     * Uji koneksi ke API Midtrans TANPA membuat transaksi atau baris
     * pembayaran baru. Kredensial dianggap valid bila Midtrans membalas
     * 404 "Transaction doesn't exist" untuk order uji yang memang tidak ada.
     */
    public function testConnection()
    {
        $cfg = $this->initMidtrans();

        if (empty(Config::$serverKey) || empty(Config::$clientKey)) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Server Key atau Client Key Midtrans belum diisi.',
            ], 400);
        }

        $probeOrderId = 'PING-' . now()->format('YmdHis') . '-' . rand(1000, 9999);

        $probe = function () use ($probeOrderId) {
            try {
                Transaction::status($probeOrderId);
            } catch (\Throwable $e) {
                // Order uji tidak ada di Midtrans (404) justru menandakan
                // Server Key dan mode sudah benar.
                if ($this->isNotFoundError($e)) {
                    return true;
                }
                throw $e;
            }

            return true;
        };

        try {
            [, $usedProduction] = $this->runWithModeFallback(
                $probe,
                (bool) ($cfg['is_production'] ?? false)
            );
            Config::$isProduction = $usedProduction;
            $mode = $usedProduction ? 'Production' : 'Sandbox';

            return response()->json([
                'success' => true,
                'status' => 'success',
                'mode' => $mode,
                'is_production' => $usedProduction,
                'merchant_id' => $cfg['merchant_id'] ?? null,
                'message' => 'Koneksi API Midtrans berhasil (' . $mode . ').',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Midtrans testConnection error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'status' => 'error',
                'mode' => !empty($cfg['is_production']) ? 'Production' : 'Sandbox',
                'message' => 'Koneksi ke Midtrans gagal: ' . $e->getMessage(),
                'hint' => 'Pastikan Server Key & Client Key sesuai mode yang dipilih di dashboard Midtrans (Sandbox/Production).',
            ], 502);
        }
    }

    public function saveConfig(Request $request)
    {
        $currentConfig = $this->readConfig();
        // Field yang tidak dikirim (mis. saat hanya menyimpan nama metode
        // pembayaran) memakai nilai yang sudah tersimpan, bukan nilai default.
        $enabled = $request->has('enabled')
            ? filter_var($request->input('enabled'), FILTER_VALIDATE_BOOLEAN)
            : (bool) ($currentConfig['enabled'] ?? true);
        $isProduction = $request->has('is_production')
            ? filter_var($request->input('is_production'), FILTER_VALIDATE_BOOLEAN)
            : (bool) ($currentConfig['is_production'] ?? false);
        $merchantId = trim((string) $request->input('merchant_id', ''));
        $clientKey = trim((string) $request->input('client_key', ''));
        $serverKey = trim((string) $request->input('server_key', '')) ?: (string) $currentConfig['server_key'];
        $merchantId = $merchantId ?: (string) $currentConfig['merchant_id'];
        $clientKey = $clientKey ?: (string) $currentConfig['client_key'];

        if ($enabled && ($serverKey === '' || $clientKey === '')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Masukkan Server Key dan Client Key Midtrans sebelum mengaktifkan gateway.',
            ], 422);
        }

        $qrisName = trim((string) $request->input('qris_name', (string) ($currentConfig['qris_name'] ?? '')));
        $vaName = trim((string) $request->input('va_name', (string) ($currentConfig['va_name'] ?? '')));
        $cardName = trim((string) $request->input('card_name', (string) ($currentConfig['card_name'] ?? '')));
        $gopayName = trim((string) $request->input('gopay_name', (string) ($currentConfig['gopay_name'] ?? '')));

        // Jangan izinkan nama merchant lama "KOPMA" tersimpan lagi
        if ($qrisName === '' || stripos($qrisName, 'kopma') !== false) {
            $qrisName = 'SMP MUHAMMADIYAH 3 METRO';
        }

        $configData = [
            'enabled' => $enabled,
            'is_production' => $isProduction,
            'merchant_id' => $merchantId,
            'client_key' => $clientKey,
            'server_key' => $serverKey,
            'qris_name' => $qrisName ?: 'SMP MUHAMMADIYAH 3 METRO',
            'va_name' => $vaName ?: 'Bank Syariah Indonesia / Mandiri',
            'card_name' => $cardName ?: 'Kartu Kredit / Debit Online',
            'gopay_name' => $gopayName ?: 'GoPay QRIS',
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // 1. Simpan ke storage JSON
        $path = $this->getConfigPath();
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($configData, JSON_PRETTY_PRINT));

        // 2. Update .env bila ada
        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            $envContent = File::get($envPath);
            $replacements = [
                'MIDTRANS_ENABLED' => $enabled ? 'true' : 'false',
                'MIDTRANS_IS_PRODUCTION' => $isProduction ? 'true' : 'false',
                'MIDTRANS_MERCHANT_ID' => $merchantId,
                'MIDTRANS_CLIENT_KEY' => $clientKey,
                'MIDTRANS_SERVER_KEY' => $serverKey,
            ];

            foreach ($replacements as $key => $val) {
                if (preg_match("/^{$key}=.*/m", $envContent)) {
                    $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$val}", $envContent);
                } else {
                    $envContent .= "\n{$key}={$val}";
                }
            }
            File::put($envPath, $envContent);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Pengaturan Midtrans berhasil diperbarui!',
            'data' => [
                'enabled' => $configData['enabled'],
                'is_production' => $configData['is_production'],
                'merchant_id' => $configData['merchant_id'],
                'client_key' => $configData['client_key'],
                'has_server_key' => $configData['server_key'] !== '',
                'qris_name' => $configData['qris_name'],
                'va_name' => $configData['va_name'],
                'card_name' => $configData['card_name'],
                'gopay_name' => $configData['gopay_name'],
            ],
        ]);
    }

    private function initMidtrans(): array
    {
        $cfg = $this->readConfig();

        Config::$serverKey = $cfg['server_key'] ?? '';
        Config::$clientKey = $cfg['client_key'] ?? '';
        Config::$isProduction = filter_var($cfg['is_production'] ?? env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        return $cfg;
    }

    /**
     * Simpan mode Midtrans yang benar (sandbox/production) ke file konfigurasi
     * dan .env. Dipakai saat mode Production gagal 401 karena kunci yang
     * dipasang ternyata kunci Sandbox.
     */
    private function persistProductionMode(bool $isProduction): void
    {
        try {
            // Tulis ulang file konfigurasi (buat bila belum ada) agar mode yang
            // benar-benar berhasil tersimpan meskipun deployment belum punya
            // storage/app/midtrans_config.json.
            $cfg = $this->readConfig();
            $cfg['is_production'] = $isProduction;
            $cfg['updated_at'] = date('Y-m-d H:i:s');

            $path = $this->getConfigPath();
            File::ensureDirectoryExists(dirname($path));
            File::put($path, json_encode($cfg, JSON_PRETTY_PRINT));

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

    /**
     * Apakah exception dari Midtrans berarti kunci API ditolak (401)?
     */
    private function isUnauthorizedError(\Throwable $e): bool
    {
        $message = $e->getMessage();

        return ((int) $e->getCode() === 401)
            || str_contains($message, '401')
            || stripos($message, 'Unauthorized') !== false
            || stripos($message, 'Access denied') !== false;
    }

    /**
     * Apakah exception berarti transaksi tidak ditemukan (404)?
     */
    private function isNotFoundError(\Throwable $e): bool
    {
        $message = $e->getMessage();

        return ((int) $e->getCode() === 404)
            || str_contains($message, '404')
            || stripos($message, "doesn't exist") !== false
            || stripos($message, 'not found') !== false;
    }

    /**
     * Jalankan panggilan API Midtrans dengan perlindungan mode dua arah.
     *
     * Mode yang dikonfigurasi dicoba lebih dulu. Jika Midtrans membalas 401
     * (kunci tidak cocok dengan mode tersebut), mode satunya dicoba otomatis
     * lalu mode yang berhasil disimpan. Ini menangani dua kasus sekaligus:
     * kunci Sandbox dipakai di mode Production, maupun sebaliknya.
     *
     * @return array{0: mixed, 1: bool} [hasil panggilan API, mode yang berhasil]
     */
    private function runWithModeFallback(callable $callback, bool $isProduction): array
    {
        // Mode terkonfigurasi lebih dulu, lalu mode satunya.
        $modesToTry = [$isProduction, !$isProduction];
        $lastError = null;

        foreach ($modesToTry as $index => $productionMode) {
            Config::$isProduction = $productionMode;

            try {
                $result = $callback();

                if ($productionMode !== $isProduction) {
                    $this->persistProductionMode($productionMode);
                    Log::warning(sprintf(
                        'Midtrans: mode %s ditolak (401), otomatis beralih ke %s dan menyimpan mode tersebut.',
                        $isProduction ? 'Production' : 'Sandbox',
                        $productionMode ? 'Production' : 'Sandbox'
                    ));
                }

                return [$result, $productionMode];
            } catch (\Throwable $e) {
                $lastError = $e;

                // Hanya 401 yang layak dicoba ulang di mode lain. Error lain
                // (validasi payload, 404 transaksi, gangguan jaringan) langsung
                // dilempar supaya tidak menutupi masalah sebenarnya.
                if (!$this->isUnauthorizedError($e) || $index === count($modesToTry) - 1) {
                    throw $e;
                }
            }
        }

        throw $lastError ?: new \RuntimeException('Midtrans tidak merespons.');
    }

    /**
     * Buat transaksi Snap dengan perlindungan mode dua arah.
     *
     * @return array{0: string, 1: ?string, 2: bool} [token, redirect_url, is_production]
     */
    private function createSnapTransaction(array $payload, bool $isProduction): array
    {
        [$snapResponse, $productionMode] = $this->runWithModeFallback(
            fn () => Snap::createTransaction($payload),
            $isProduction
        );

        return [
            $snapResponse->token,
            $snapResponse->redirect_url ?? null,
            $productionMode,
        ];
    }

    /**
     * Generate Midtrans Snap Token & Catat di Database
     */
    public function createSnapToken(Request $request)
    {
        $cfg = $this->initMidtrans();

        if (!filter_var($cfg['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Payment Gateway Midtrans sedang dinonaktifkan oleh administrator.',
            ], 400);
        }

        $serverKey = Config::$serverKey;
        $clientKey = Config::$clientKey;
        $isProduction = Config::$isProduction;

        if (empty($serverKey) || empty($clientKey)) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Server Key atau Client Key Midtrans belum diisi. Silakan atur di menu Pengaturan Midtrans.',
            ], 400);
        }

        $amount = (int) $request->input('amount', 250000);
        $studentName = $request->input('student_name', 'Ahmad Dahlan Putra');
        $nisn = $request->input('nisn', '0081234567');
        // Pembayaran bisa mencakup lebih dari satu bulan:
        // "Januari 2026, Februari 2026, Maret 2026".
        $bulan = trim((string) $request->input('bulan', 'Januari 2025'));
        if ($bulan === '') {
            $bulan = 'Januari 2025';
        }
        $bulanLabel = mb_substr($bulan, 0, 100);
        $customQrisName = trim((string) $request->input('qris_name', ''));

        // Nama merchant QRIS: ganti nama lama "KOPMA" dengan nama sekolah
        if ($customQrisName === '' || stripos($customQrisName, 'kopma') !== false) {
            $customQrisName = 'SMP MUHAMMADIYAH 3 METRO';
        }

        // Cari atau ambil record siswa (beserta data wali murid)
        $siswa = Siswa::with('waliMurid')->where('nisn', $nisn)->first();
        if (!$siswa) {
            $siswa = Siswa::with('waliMurid')->first();
        }

        $orderId = 'SPP-' . ($siswa ? $siswa->nisn : '0081234567') . '-' . date('YmdHis') . '-' . rand(10, 99);

        // Map nama bulan ke index 1-12
        $bulanMap = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
            'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
            'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12
        ];
        $firstWord = strtolower(explode(' ', trim($bulan))[0]);
        $bulanKe = $bulanMap[$firstWord] ?? (int) date('n');

        $transactionDetails = [
            'order_id' => $orderId,
            'gross_amount' => $amount,
        ];

        $customerDetails = [
            'first_name' => $studentName,
            'email' => optional($siswa?->waliMurid)->email
                ?: 'wali.' . $nisn . '@smpm3metro.sch.id',
            'phone' => optional($siswa?->waliMurid)->no_telp
                ?: '0812' . rand(10000000, 99999999),
        ];

        // Nama merchant untuk metode pembayaran di Midtrans Snap
        // Ditampilkan di header popup pembayaran Midtrans & detail struk QRIS
        $merchantName = $customQrisName ?: ($cfg['qris_name'] ?? 'SMP MUHAMMADIYAH 3 METRO');

        $itemDetails = [
            [
                'id' => 'SPP-' . preg_replace('/[^a-zA-Z0-9]/', '', $bulan),
                'price' => $amount,
                'quantity' => 1,
                'name' => 'SPP ' . substr($bulan, 0, 30) . ' - ' . $merchantName,
                'merchant_name' => $merchantName,
            ],
        ];

        $payload = [
            'transaction_details' => $transactionDetails,
            'item_details' => $itemDetails,
            'customer_details' => $customerDetails,
            'merchant_name' => $merchantName,
            'qris_name' => $merchantName,
            'gopay_name' => $merchantName,
            // Catatan: jangan mengirim enabled_payments.
            // Daftar manual seperti "virtual_account" bukan kode Snap yang valid
            // dan bisa membuat metode pembayaran tidak tampil. Dengan membiarkan
            // field ini kosong, Snap otomatis memakai SEMUA metode yang sudah
            // diaktifkan di dashboard Midtrans (QRIS, GoPay, ShopeePay, OVO,
            // DANA, Virtual Account bank, kartu kredit, gerai retail, dll).
        ];

        // Token Snap dibuat berlaku 24 jam. Tanpa expiry eksplisit, Snap
        // memakai default dashboard yang bisa lebih pendek sehingga pembayaran
        // gagal dengan pesan "token expired".
        $payload['expiry'] = [
            'unit' => 'hours',
            'duration' => 24,
        ];

        $expiresAt = now()->addHours(24);

        try {
            [$snapToken, $snapRedirectUrl, $isProduction] = $this->createSnapTransaction($payload, $isProduction);
            Config::$isProduction = $isProduction;

            $snapJs = $isProduction
                ? 'https://app.midtrans.com/snap/snap.js'
                : 'https://app.sandbox.midtrans.com/snap/snap.js';
            $redirectUrl = $snapRedirectUrl ?: (
                ($isProduction ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com')
                . '/snap/v4/redirection/' . $snapToken
            );

            // Catat transaksi di tabel tbl_pembayaran
            $idTahunAjaran = $siswa ? $siswa->id_tahun_ajaran : 2;
            $idSiswa = $siswa ? $siswa->id_siswa : 1;
            $idKelas = $siswa ? $siswa->id_kelas : 1;
            $idSpp = $this->cariIdSpp($idKelas, $idTahunAjaran);
            $idUserSesi = $request->session()->get('user');
            $idUser = is_array($idUserSesi) ? ($idUserSesi['id_user'] ?? null) : null;

            $pembayaran = Pembayaran::updateOrCreate(
                [
                    'id_siswa' => $idSiswa,
                    'id_tahun_ajaran' => $idTahunAjaran,
                    'bulan_ke' => $bulanKe,
                ],
                [
                    'order_id' => $orderId,
                    'id_user' => $idUser,
                    'id_kelas' => $idKelas,
                    'id_spp' => $idSpp ?: 1,
                    'tgl_bayar' => now(),
                    'nama_bulan' => $bulanLabel,
                    'nominal_tagihan' => $amount,
                    'jumlah_bayar' => $amount,
                    'snap_token' => $snapToken,
                    'qris_name' => $customQrisName ?: ($cfg['qris_name'] ?? 'SMP MUHAMMADIYAH 3 METRO'),
                    'transaction_status' => 'pending',
                    'status_bayar' => 'belum_lunas',
                    'metode_bayar' => 'midtrans',
                    'keterangan' => mb_substr('Pembayaran SPP ' . $bulan . ' via Midtrans Snap', 0, 150),
                ]
            );

            // Hubungkan pembayaran dengan tagihan bulan terkait.
            $this->sinkronkanDetailPembayaran(
                $pembayaran,
                $bulanLabel,
                (float) $amount,
                $idKelas,
                $idSpp,
                $idUser
            );

            // Log riwayat Midtrans
            LogMidtrans::create([
                'order_id' => $orderId,
                'id_pembayaran' => $pembayaran->id_pembayaran,
                'status_code' => '201',
                'transaction_status' => 'pending',
                'raw_response' => json_encode([
                    'token' => $snapToken,
                    'order_id' => $orderId,
                    'redirect_url' => $redirectUrl,
                    'expires_at' => $expiresAt->toIso8601String(),
                    'mode' => $isProduction ? 'production' : 'sandbox',
                ]),
            ]);

            return response()->json([
                'success' => true,
                'status' => 'success',
                'snap_token' => $snapToken,
                'qris_name' => $customQrisName ?: ($cfg['qris_name'] ?? 'SMP MUHAMMADIYAH 3 METRO'),
                'order_id' => $orderId,
                'client_key' => $clientKey,
                'is_production' => $isProduction,
                'snap_js' => $snapJs,
                'expires_at' => $expiresAt->toIso8601String(),
                'expires_in' => 86400,
                'total' => (float) $amount,
                'redirect_url' => $redirectUrl,
            ]);
        } catch (\Throwable $e) {
            Log::error('Midtrans createSnapToken error: ' . $e->getMessage());

            $isUnauthorized = $this->isUnauthorizedError($e);
            $isAccessDenied = str_contains($e->getMessage(), '403')
                || stripos($e->getMessage(), 'Access denied') !== false;

            if ($isUnauthorized) {
                $hint = 'Server Key ditolak Midtrans (401) pada mode Sandbox maupun Production. '
                    . 'Periksa kembali: (1) Server Key disalin utuh dari dashboard Midtrans, '
                    . '(2) Client Key berasal dari merchant yang sama, '
                    . '(3) tidak ada spasi/karakter tambahan pada kunci, '
                    . '(4) akun Midtrans sudah aktif. '
                    . 'Kunci Sandbox umumnya berawalan "SB-Mid-server-" dan "SB-Mid-client-".';
            } elseif ($isAccessDenied) {
                $hint = 'Midtrans menolak akses. Pastikan Server Key dan Client Key berasal dari merchant yang sama dan sesuai mode Sandbox/Production, serta akun Midtrans sudah aktif.';
            } elseif ($isProduction) {
                $hint = 'Mode Production gagal. Jika Anda memakai kunci Sandbox, pilih Mode Sandbox di menu Pengaturan Midtrans.';
            } else {
                $hint = 'Pastikan Server Key & Client Key sesuai dengan mode Sandbox di dashboard.midtrans.com -> Settings -> Access Keys.';
            }

            return response()->json([
                'success' => false,
                'status' => 'error',
                'code' => $isUnauthorized ? 'MIDTRANS_401' : 'MIDTRANS_ERROR',
                'message' => $isUnauthorized
                    ? 'Midtrans menolak Server Key (401 Unauthorized). Kunci API tidak cocok dengan mode Sandbox/Production.'
                    : 'Gagal membuat sesi pembayaran Midtrans: ' . $e->getMessage(),
                'mode' => $isProduction ? 'Production' : 'Sandbox',
                'detail' => $e->getMessage(),
                'hint' => $hint,
            ], $isUnauthorized ? 401 : 500);
        }
    }

    /**
     * Alias method snapToken
     */
    public function snapToken(Request $request, $id = null)
    {
        return $this->createSnapToken($request);
    }

    /**
     * Tandai seluruh tagihan SPP yang tercakup dalam satu pembayaran sebagai
     * lunas. Kolom nama_bulan dapat berisi beberapa bulan dipisahkan koma,
     * misalnya "Januari 2026, Februari 2026".
     */
    private function markTagihanSppLunas(Pembayaran $pembayaran): void
    {
        $idTagihan = $pembayaran->detailPembayaran()
            ->pluck('id_tagihan_spp')
            ->filter()
            ->all();

        if (!empty($idTagihan)) {
            TagihanSpp::whereIn('id_tagihan_spp', $idTagihan)
                ->update(['status' => 'lunas']);

            return;
        }

        // Fallback untuk data lama yang belum punya detail pembayaran.
        $bulanList = collect(explode(',', (string) $pembayaran->nama_bulan))
            ->map(fn ($bulan) => trim($bulan))
            ->filter()
            ->values()
            ->all();

        $query = TagihanSpp::where('id_siswa', $pembayaran->id_siswa)
            ->where('id_tahun_ajaran', $pembayaran->id_tahun_ajaran);

        if (!empty($bulanList)) {
            $query->whereIn('bulan', $bulanList);
        } else {
            $query->where('bulan_ke', $pembayaran->bulan_ke);
        }

        $query->update(['status' => 'lunas']);
    }

    /**
     * Hubungkan satu pembayaran dengan satu/beberapa tagihan bulan terkait.
     * Jika tagihan belum ada, tagihan dibuat otomatis agar relasi tetap utuh.
     */
    private function sinkronkanDetailPembayaran(
        Pembayaran $pembayaran,
        string $bulanLabel,
        float $amount,
        ?int $idKelas = null,
        ?int $idSpp = null,
        ?int $idUser = null
    ): void {
        $bulanList = collect(explode(',', $bulanLabel))
            ->map(fn ($bulan) => trim($bulan))
            ->filter()
            ->values();

        if ($bulanList->isEmpty()) {
            return;
        }

        $nominalPerBulan = round($amount / max(1, $bulanList->count()), 2);

        foreach ($bulanList as $index => $namaBulan) {
            $bulanKe = $this->bulanKeDariLabel($namaBulan) ?: ((int) $pembayaran->bulan_ke + $index);
            $bulanKe = (($bulanKe - 1) % 12) + 1;

            $tagihan = TagihanSpp::firstOrCreate(
                [
                    'id_siswa' => $pembayaran->id_siswa,
                    'id_tahun_ajaran' => $pembayaran->id_tahun_ajaran,
                    'bulan_ke' => $bulanKe,
                ],
                [
                    'id_kelas' => $idKelas ?: $pembayaran->id_kelas,
                    'id_spp' => $idSpp ?: $pembayaran->id_spp,
                    'id_user' => $idUser,
                    'bulan' => mb_substr($namaBulan, 0, 20),
                    'nominal' => $nominalPerBulan,
                    'jatuh_tempo' => now()->addDays(10)->toDateString(),
                    'status' => 'belum_lunas',
                    'keterangan' => 'Tagihan dibuat otomatis dari pembayaran ' . $pembayaran->order_id,
                ]
            );

            DetailPembayaran::updateOrCreate(
                [
                    'id_pembayaran' => $pembayaran->id_pembayaran,
                    'id_tagihan_spp' => $tagihan->id_tagihan_spp,
                ],
                ['nominal' => $nominalPerBulan]
            );
        }
    }

    private function bulanKeDariLabel(?string $bulan): int
    {
        $map = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
            'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
            'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
        ];

        $raw = trim((string) $bulan);

        if (is_numeric($raw)) {
            return max(1, min(12, (int) $raw));
        }

        $kata = strtolower(explode(' ', $raw)[0]);

        return $map[$kata] ?? 0;
    }

    private function cariIdSpp(?int $idKelas, ?int $idTahunAjaran): ?int
    {
        if (!$idKelas || !$idTahunAjaran) {
            return null;
        }

        $tingkat = \App\Models\Kelas::where('id_kelas', $idKelas)->value('tingkat');

        if (!$tingkat) {
            return null;
        }

        return Spp::where('id_tahun_ajaran', $idTahunAjaran)
            ->where('tingkat', (string) $tingkat)
            ->value('id_spp');
    }

    /**
     * Cek status transaksi LANGSUNG ke API Midtrans (Polling real-time)
     * Mengupdate tbl_pembayaran dan tbl_tagihan_spp secara otomatis
     */
    public function cekStatus(Request $request, $orderId)
    {
        $cfg = $this->initMidtrans();

        try {
            // Cek status juga memakai fallback mode, supaya polling tidak
            // gagal 401 ketika mode tersimpan tidak cocok dengan kunci API.
            [$status, $usedProduction] = $this->runWithModeFallback(
                fn () => Transaction::status($orderId),
                (bool) ($cfg['is_production'] ?? false)
            );
            Config::$isProduction = $usedProduction;

            $pembayaran = Pembayaran::where('order_id', $orderId)->first();

            $update = [
                'transaction_status' => $status->transaction_status ?? 'pending',
                'payment_type' => $status->payment_type ?? null,
                'transaction_id' => $status->transaction_id ?? null,
            ];

            if (!empty($status->va_numbers[0]->va_number)) {
                $update['va_number'] = $status->va_numbers[0]->va_number;
                $update['bank'] = $status->va_numbers[0]->bank ?? null;
            } elseif (!empty($status->permata_va_number)) {
                $update['va_number'] = $status->permata_va_number;
                $update['bank'] = 'permata';
            }

            if (!empty($status->pdf_url)) {
                $update['pdf_url'] = $status->pdf_url;
            }

            $transactionStatus = $status->transaction_status ?? 'pending';
            $isLunas = false;

            // Hanya settlement, atau capture yang lolos verifikasi fraud,
            // yang dianggap lunas.
            if ($transactionStatus === 'settlement'
                || ($transactionStatus === 'capture' && ($status->fraud_status ?? 'accept') === 'accept')) {
                $update['status_bayar'] = 'lunas';
                $update['jumlah_bayar'] = (float) ($status->gross_amount ?? $pembayaran?->nominal_tagihan ?? 250000);

                // Jangan menimpa tanggal bayar yang sudah tercatat.
                if (!$pembayaran || $pembayaran->status_bayar !== 'lunas') {
                    $update['tgl_bayar'] = now();
                }
                $isLunas = true;

                // Update TagihanSpp bila ada
                if ($pembayaran) {
                    $this->markTagihanSppLunas($pembayaran);
                }
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'], true)) {
                $update['status_bayar'] = 'belum_lunas';
            }

            if ($pembayaran) {
                $pembayaran->update($update);
            }

            // Simpan ke LogMidtrans hanya saat status berubah. Polling berjalan
            // tiap 3 detik; tanpa deduplikasi tabel log cepat membengkak.
            $lastLog = LogMidtrans::where('order_id', $orderId)
                ->latest('id_log_midtrans')
                ->first();

            if (!$lastLog || $lastLog->transaction_status !== $transactionStatus) {
                LogMidtrans::create([
                    'order_id' => $orderId,
                    'id_pembayaran' => $pembayaran?->id_pembayaran,
                    'status_code' => $status->status_code ?? '200',
                    'transaction_status' => $transactionStatus,
                    'raw_response' => json_encode($status, JSON_UNESCAPED_UNICODE),
                ]);
            }

            return response()->json([
                'success' => true,
                'paid' => $isLunas,
                'status_bayar' => $isLunas ? 'lunas' : ($pembayaran ? $pembayaran->status_bayar : 'belum_lunas'),
                'transaction_status' => $transactionStatus,
                'payment_type' => $status->payment_type ?? null,
                'order_id' => $orderId,
            ]);
        } catch (\Throwable $e) {
            // Jika 404 dari Midtrans (transaksi belum dikonfirmasi di popup), anggap pending
            if ($this->isNotFoundError($e)) {
                return response()->json([
                    'success' => true,
                    'paid' => false,
                    'status_bayar' => 'belum_lunas',
                    'transaction_status' => 'pending',
                ]);
            }

            Log::warning('Midtrans cekStatus error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal cek status ke Midtrans: ' . $e->getMessage(),
            ], 502);
        }
    }

    /**
     * Webhook HTTP Notification dari Midtrans
     */
    public function notification(Request $request)
    {
        $this->initMidtrans();
        $notif = $request->all();

        Log::info('Midtrans notification received', $notif);

        $orderId = $notif['order_id'] ?? null;
        $statusCode = $notif['status_code'] ?? null;
        $grossAmount = $notif['gross_amount'] ?? null;
        $signatureKey = $notif['signature_key'] ?? null;

        if (!$orderId || !$statusCode || !$signatureKey) {
            return response()->json(['success' => false, 'message' => 'Invalid notification'], 400);
        }

        $serverKey = Config::$serverKey;
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($signatureKey !== $expectedSignature) {
            Log::warning('Midtrans signature mismatch for order: ' . $orderId);
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 403);
        }

        $pembayaran = Pembayaran::where('order_id', $orderId)->first();
        $transactionStatus = $notif['transaction_status'] ?? null;
        $fraudStatus = $notif['fraud_status'] ?? null;

        $update = [
            'transaction_status' => $transactionStatus,
            'payment_type' => $notif['payment_type'] ?? null,
            'transaction_id' => $notif['transaction_id'] ?? null,
        ];

        if ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {
            $update['status_bayar'] = 'lunas';

            // Webhook dapat dikirim Midtrans lebih dari sekali untuk order yang
            // sama; jangan ubah tanggal bayar yang sudah tercatat.
            if (!$pembayaran || $pembayaran->status_bayar !== 'lunas') {
                $update['tgl_bayar'] = now();
            }

            if ($pembayaran) {
                $this->markTagihanSppLunas($pembayaran);
            }
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'], true)) {
            $update['status_bayar'] = 'belum_lunas';
        }

        if ($pembayaran) {
            $pembayaran->update($update);
        }

        LogMidtrans::create([
            'order_id' => $orderId,
            'id_pembayaran' => $pembayaran?->id_pembayaran,
            'status_code' => $statusCode,
            'transaction_status' => $transactionStatus,
            'raw_response' => json_encode($notif, JSON_UNESCAPED_UNICODE),
        ]);

        return response()->json(['success' => true]);
    }
}
