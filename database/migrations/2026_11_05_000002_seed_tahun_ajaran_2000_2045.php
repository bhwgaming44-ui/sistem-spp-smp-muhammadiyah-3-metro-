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

        // Jangan mengubah tahun ajaran yang sedang aktif pada database yang
        // sudah berjalan (misalnya 2024/2025). Hanya tentukan tahun aktif
        // apabila belum ada satu pun tahun ajaran yang aktif.
        $hasActive = DB::table('tbl_tahun_ajaran')
            ->where('status_aktif', 'aktif')
            ->exists();

        if (!$hasActive) {
            $preferred = DB::table('tbl_tahun_ajaran')
                ->where('tahun_ajaran', '2024/2025')
                ->exists()
                ? '2024/2025'
                : $activeName;

            DB::table('tbl_tahun_ajaran')
                ->where('tahun_ajaran', $preferred)
                ->update(['status_aktif' => 'aktif', 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        DB::table('tbl_tahun_ajaran')
            ->whereBetween('tahun_ajaran', ['2000/2001', '2045/2046'])
            ->whereNotIn('tahun_ajaran', ['2023/2024', '2024/2025'])
            ->delete();
    }
};
