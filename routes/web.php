<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\WaliMuridController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SystemController;

// Route Halaman Utama E-SPP SMP Muhammadiyah 3 Metro
Route::get('/', function () {
    return view('welcome');
});

// Health check untuk hosting / uptime monitor
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'time' => now()->toDateTimeString(),
    ]);
});

// Status & konfigurasi publikasi online
Route::get('/api/system/status', [SystemController::class, 'status']);
Route::get('/api/system/public-url', [SystemController::class, 'getPublicUrl']);
Route::post('/api/system/public-url', [SystemController::class, 'savePublicUrl']);

// Midtrans API Endpoints
Route::get('/api/midtrans/config', [MidtransController::class, 'getConfig']);
Route::post('/api/midtrans/config', [MidtransController::class, 'saveConfig']);
Route::get('/api/midtrans/test-connection', [MidtransController::class, 'testConnection']);
Route::post('/api/midtrans/snap-token', [MidtransController::class, 'createSnapToken']);
Route::get('/api/midtrans/snap-token/{id?}', [MidtransController::class, 'snapToken']);
Route::get('/api/midtrans/cek-status/{orderId}', [MidtransController::class, 'cekStatus']);
Route::post('/api/midtrans/notification', [MidtransController::class, 'notification']);

// Tagihan SPP Dynamic API Endpoint (Untuk Bayar SPP Online Siswa/Ortu)
Route::get('/api/tagihan/search', [TagihanController::class, 'search']);
Route::get('/api/tagihan/unpaid/{identifier?}', [TagihanController::class, 'getUnpaidByStudent']);

// Daftar tahun ajaran dari tabel tbl_tahun_ajaran (dropdown form tagihan)
Route::get('/api/tahun-ajaran', [TagihanController::class, 'tahunAjaran']);

// Tagihan SPP Web Routes
Route::prefix('tagihan')->group(function () {
    Route::get('/', [TagihanController::class, 'index'])->middleware('auth');
    Route::get('/create', [TagihanController::class, 'create'])->middleware('auth');
    Route::post('/', [TagihanController::class, 'store'])->middleware(['auth', 'role:admin,petugas']);
    Route::put('/edit/{id}', [TagihanController::class, 'update'])->middleware(['auth', 'role:admin,petugas']);
    Route::delete('/delete/{id}', [TagihanController::class, 'destroy'])->middleware(['auth', 'role:admin,petugas']);
});

// Staff Management Web Routes
Route::prefix('staff')->group(function () {
    Route::get('/', [StaffController::class, 'index'])->middleware('auth');
    Route::get('/create', [StaffController::class, 'create'])->middleware(['auth', 'role:admin,kepala_sekolah']);
    Route::post('/', [StaffController::class, 'store'])->middleware(['auth', 'role:admin,kepala_sekolah']);
    Route::get('/kelas', [StaffController::class, 'kelasIndex'])->middleware('auth');
    Route::get('/role', [StaffController::class, 'getByRole'])->middleware('auth');
});

// Student Registration Routes
Route::prefix('register')->group(function () {
    Route::get('/', [RegisterController::class, 'show'])->name('register.show');
    Route::post('/', [RegisterController::class, 'register'])->name('register.store');
});

// Student Data Routes (for staff)
Route::prefix('siswa')->group(function () {
    Route::get('/', [SiswaController::class, 'index'])->middleware('auth');
    Route::get('/api', [SiswaController::class, 'apiList'])->middleware('auth');
    Route::get('/{nisn}', [SiswaController::class, 'show'])->middleware('auth');
});

// Parent/Wali Murid Data Routes (for staff)
Route::prefix('wali-murid')->group(function () {
    Route::get('/', [WaliMuridController::class, 'index'])->middleware('auth');
    Route::get('/api', [WaliMuridController::class, 'apiList'])->middleware('auth');
    Route::get('/{nisn}', [WaliMuridController::class, 'show'])->middleware('auth');
});

// Staff Router Resource (alternative approach)
Route::resource('tagihan', TagihanController::class)->except(['index', 'create', 'store', 'update', 'destroy']);
Route::resource('petugas', StaffController::class)->except(['index', 'create', 'store', 'update', 'destroy']);