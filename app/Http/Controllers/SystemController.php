<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SystemController extends Controller
{
    private function publicUrlPath(): string
    {
        return storage_path('app/public_url.json');
    }

    private function defaultPublicUrl(): ?string
    {
        $appUrl = rtrim((string) config('app.url'), '/');

        if ($appUrl === '' || preg_match('/localhost|127\.0\.0\.1/i', $appUrl)) {
            return null;
        }

        return $appUrl;
    }

    /**
     * Status aplikasi: lokal/online, koneksi database, storage, versi.
     */
    public function status(Request $request)
    {
        $dbConnected = true;
        $dbError = null;

        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $dbConnected = false;
            $dbError = config('app.debug') ? $e->getMessage() : 'Koneksi database gagal.';
        }

        $publicUrl = null;
        if (File::exists($this->publicUrlPath())) {
            $data = json_decode(File::get($this->publicUrlPath()), true) ?: [];
            $publicUrl = $data['public_url'] ?? null;
        }
        if (!$publicUrl) {
            $publicUrl = $this->defaultPublicUrl();
        }

        $host = $request->getHost();
        $isLocal = in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || str_ends_with($host, '.local')
            || str_ends_with($host, '.test');

        return response()->json([
            'success' => true,
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'app_debug' => (bool) config('app.debug'),
            'app_url' => config('app.url'),
            'current_url' => $request->getSchemeAndHttpHost(),
            'host' => $host,
            'is_local' => $isLocal,
            'is_https' => $request->isSecure(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'db_connected' => $dbConnected,
            'db_error' => $dbError,
            'storage_writable' => is_writable(storage_path()),
            'public_url' => $publicUrl,
            'server_time' => now()->toDateTimeString(),
        ]);
    }

    public function getPublicUrl()
    {
        $publicUrl = null;
        $updatedAt = null;

        if (File::exists($this->publicUrlPath())) {
            $data = json_decode(File::get($this->publicUrlPath()), true) ?: [];
            $publicUrl = $data['public_url'] ?? null;
            $updatedAt = $data['updated_at'] ?? null;
        }

        if (!$publicUrl) {
            $publicUrl = $this->defaultPublicUrl();
        }

        return response()->json([
            'success' => true,
            'public_url' => $publicUrl,
            'updated_at' => $updatedAt,
        ]);
    }

    public function savePublicUrl(Request $request)
    {
        $url = trim((string) $request->input('public_url', ''));

        if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json([
                'success' => false,
                'message' => 'Format URL tidak valid. Contoh: https://spp.smpm3metro.sch.id',
            ], 422);
        }

        if ($url !== '' && !preg_match('/^https?:\/\//i', $url)) {
            return response()->json([
                'success' => false,
                'message' => 'URL harus diawali http:// atau https://',
            ], 422);
        }

        $url = rtrim($url, '/');
        $path = $this->publicUrlPath();
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode([
            'public_url' => $url,
            'updated_at' => date('Y-m-d H:i:s'),
        ], JSON_PRETTY_PRINT));

        return response()->json([
            'success' => true,
            'public_url' => $url,
            'message' => 'URL publikasi berhasil disimpan.',
        ]);
    }
}
