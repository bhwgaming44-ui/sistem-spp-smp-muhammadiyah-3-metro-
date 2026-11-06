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
