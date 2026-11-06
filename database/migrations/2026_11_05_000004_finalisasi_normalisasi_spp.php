<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Finalisasi normalisasi:
 *  - Data orang tua benar-benar hanya disimpan di tbl_wali_murid.
 *  - Nominal SPP dipastikan sesuai tarif sekolah.
 *  - Relasi petugas <-> user dan log Midtrans <-> pembayaran dipastikan lengkap.
 *
 * Migration ini idempotent dan aman untuk database yang sudah berisi data.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->pastikanWaliSetiapSiswa();
        $this->hapusKolomOrtuDariSiswa();
        $this->tetapkanNominalSpp();
        $this->pastikanPetugasUnik();
        $this->pastikanLogMidtransTerhubung();
    }

    public function down(): void
    {
        if (Schema::hasTable('tbl_siswa')) {
            Schema::table('tbl_siswa', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_siswa', 'nama_ortu')) {
                    $table->string('nama_ortu', 100)->nullable();
                }
                if (!Schema::hasColumn('tbl_siswa', 'no_telp_ortu')) {
                    $table->string('no_telp_ortu', 15)->nullable();
                }
                if (!Schema::hasColumn('tbl_siswa', 'email_ortu')) {
                    $table->string('email_ortu', 100)->nullable();
                }
            });
        }
    }

    private function pastikanWaliSetiapSiswa(): void
    {
        if (!Schema::hasTable('tbl_siswa') || !Schema::hasTable('tbl_wali_murid')) {
            return;
        }

        $siswaTanpaWali = DB::table('tbl_siswa as s')
            ->leftJoin('tbl_wali_murid as w', 'w.id_siswa', '=', 's.id_siswa')
            ->whereNull('w.id_wali_murid')
            ->select(['s.id_siswa', 's.nama_siswa'])
            ->get();

        $punyaKolomOrtu = Schema::hasColumn('tbl_siswa', 'nama_ortu');

        foreach ($siswaTanpaWali as $siswa) {
            $payload = [
                'id_siswa' => $siswa->id_siswa,
                'nama_wali' => 'Wali dari ' . $siswa->nama_siswa,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($punyaKolomOrtu) {
                $detail = DB::table('tbl_siswa')
                    ->where('id_siswa', $siswa->id_siswa)
                    ->first(['nama_ortu', 'no_telp_ortu', 'email_ortu']);

                if ($detail) {
                    $payload['nama_wali'] = $detail->nama_ortu ?: $payload['nama_wali'];
                    $payload['no_telp'] = $detail->no_telp_ortu;
                    $payload['email'] = $detail->email_ortu;
                }
            }

            DB::table('tbl_wali_murid')->insert($payload);
        }
    }

    private function hapusKolomOrtuDariSiswa(): void
    {
        if (!Schema::hasTable('tbl_siswa')) {
            return;
        }

        $kolom = array_values(array_filter(
            ['nama_ortu', 'no_telp_ortu', 'email_ortu'],
            fn ($name) => Schema::hasColumn('tbl_siswa', $name)
        ));

        if ($kolom === []) {
            return;
        }

        Schema::table('tbl_siswa', function (Blueprint $table) use ($kolom) {
            $table->dropColumn($kolom);
        });
    }

    private function tetapkanNominalSpp(): void
    {
        if (!Schema::hasTable('tbl_spp')) {
            return;
        }

        foreach (['7' => 175000, '8' => 185000, '9' => 195000] as $tingkat => $nominal) {
            DB::table('tbl_spp')
                ->where('tingkat', (string) $tingkat)
                ->update(['nominal' => $nominal, 'updated_at' => now()]);
        }
    }

    private function pastikanPetugasUnik(): void
    {
        if (!Schema::hasTable('tbl_petugas')) {
            return;
        }

        $exists = DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'tbl_petugas')
            ->where('INDEX_NAME', 'tbl_petugas_id_user_unique')
            ->exists();

        if (!$exists) {
            Schema::table('tbl_petugas', function (Blueprint $table) {
                $table->unique('id_user');
            });
        }
    }

    private function pastikanLogMidtransTerhubung(): void
    {
        if (!Schema::hasTable('tbl_log_midtrans')
            || !Schema::hasTable('tbl_pembayaran')
            || !Schema::hasColumn('tbl_log_midtrans', 'id_pembayaran')) {
            return;
        }

        DB::statement(
            'UPDATE tbl_log_midtrans l '
            . 'JOIN tbl_pembayaran p ON p.order_id = l.order_id '
            . 'SET l.id_pembayaran = p.id_pembayaran '
            . 'WHERE l.id_pembayaran IS NULL'
        );
    }
};
