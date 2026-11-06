<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menyiapkan daftar kelas standar SMP (7A - 9B).
 *
 * Versi lama migration ini melakukan TRUNCATE tbl_kelas sehingga berbahaya:
 * data siswa yang berelasi bisa ikut terhapus. Versi ini hanya menambahkan
 * kelas yang belum ada (idempotent) dan tidak pernah menghapus data.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('tbl_kelas')) {
            return;
        }

        $now = now();
        $kelasData = [
            ['nama_kelas' => '7A', 'tingkat' => '7'],
            ['nama_kelas' => '7B', 'tingkat' => '7'],
            ['nama_kelas' => '8A', 'tingkat' => '8'],
            ['nama_kelas' => '8B', 'tingkat' => '8'],
            ['nama_kelas' => '9A', 'tingkat' => '9'],
            ['nama_kelas' => '9B', 'tingkat' => '9'],
        ];

        foreach ($kelasData as $kelas) {
            $exists = DB::table('tbl_kelas')
                ->where('nama_kelas', $kelas['nama_kelas'])
                ->exists();

            if (!$exists) {
                DB::table('tbl_kelas')->insert($kelas + [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Tidak ada rollback: data kelas yang sudah dipakai siswa harus dipertahankan.
    }
};
