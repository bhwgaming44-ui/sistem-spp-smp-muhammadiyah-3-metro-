<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Perbaikan relasi & normalisasi database SPP.
 *
 * Migration ini bersifat idempotent: aman dijalankan pada database yang sudah
 * berisi data (Laragon) maupun pada instalasi baru (fresh migrate), karena
 * setiap perubahan dicek terlebih dahulu.
 *
 * Ringkasan perubahan:
 *  - tbl_user          : perluas enum role (petugas, guru, wali_kelas).
 *  - tbl_wali_murid    : relasi ke siswa (id_siswa unik), hapus id_kelas/mapel_wali.
 *  - tbl_siswa         : pindahkan nama/no_telp/email orang tua ke tbl_wali_murid.
 *  - tbl_petugas       : UNIQUE(id_user).
 *  - tbl_tagihan_spp   : tambah id_spp, id_user, bulan_ke, jatuh_tempo + unique periode.
 *  - tbl_detail_pembayaran : tabel pivot pembayaran <-> tagihan (baru).
 *  - tbl_log_midtrans  : tambah id_pembayaran (FK) dan backfill dari order_id.
 *  - tbl_pembayaran    : nama_bulan VARCHAR(100), validasi bulan_ke 1-12.
 *  - tbl_spp           : koreksi nominal 175.000 / 185.000 / 195.000.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->perluasRoleUser();
        $this->perbaikiWaliMurid();
        $this->perbaikiPetugas();
        $this->perbaikiTagihanSpp();
        $this->buatDetailPembayaran();
        $this->perbaikiLogMidtrans();
        $this->perbaikiPembayaran();
        $this->koreksiNominalSpp();
        $this->sinkronkanIdSppPembayaran();
        $this->backfillTagihanDanDetail();
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_detail_pembayaran');

        if (Schema::hasTable('tbl_log_midtrans') && Schema::hasColumn('tbl_log_midtrans', 'id_pembayaran')) {
            $this->dropForeignIfExists('tbl_log_midtrans', 'tbl_log_midtrans_id_pembayaran_foreign');
            Schema::table('tbl_log_midtrans', function (Blueprint $table) {
                $table->dropColumn('id_pembayaran');
            });
        }

        if (Schema::hasTable('tbl_tagihan_spp')) {
            foreach ([
                'tbl_tagihan_spp_id_kelas_foreign',
                'tbl_tagihan_spp_id_siswa_foreign',
                'tbl_tagihan_spp_id_tahun_ajaran_foreign',
                'tbl_tagihan_spp_id_spp_foreign',
                'tbl_tagihan_spp_id_user_foreign',
            ] as $fk) {
                $this->dropForeignIfExists('tbl_tagihan_spp', $fk);
            }

            $this->dropIndexIfExists('tbl_tagihan_spp', 'uq_tagihan_siswa_periode');

            Schema::table('tbl_tagihan_spp', function (Blueprint $table) {
                foreach (['id_spp', 'id_user', 'bulan_ke', 'jatuh_tempo'] as $column) {
                    if (Schema::hasColumn('tbl_tagihan_spp', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('tbl_petugas')) {
            $this->dropIndexIfExists('tbl_petugas', 'tbl_petugas_id_user_unique');
        }
    }

    /* ------------------------------------------------------------------ */

    private function perluasRoleUser(): void
    {
        if (!Schema::hasTable('tbl_user')) {
            return;
        }

        DB::statement(
            "ALTER TABLE tbl_user MODIFY role ENUM('admin','bendahara','kepala_sekolah','petugas','guru','wali_kelas') "
            . "CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bendahara'"
        );
    }

    private function perbaikiWaliMurid(): void
    {
        if (!Schema::hasTable('tbl_wali_murid')) {
            return;
        }

        // Pastikan kolom id_siswa sudah ada sebelum dipakai untuk backfill.
        if (!Schema::hasColumn('tbl_wali_murid', 'id_siswa')) {
            Schema::table('tbl_wali_murid', function (Blueprint $table) {
                $table->unsignedInteger('id_siswa')->nullable()->after('id_wali_murid');
            });
        }

        // 1. Pindahkan data orang tua dari tbl_siswa ke tbl_wali_murid.
        if (Schema::hasColumn('tbl_wali_murid', 'id_kelas') && Schema::hasColumn('tbl_siswa', 'nama_ortu')) {
            $siswaList = DB::table('tbl_siswa')
                ->select(['id_siswa', 'nama_siswa', 'nama_ortu', 'no_telp_ortu', 'email_ortu'])
                ->get();

            foreach ($siswaList as $siswa) {
                $sudahAda = DB::table('tbl_wali_murid')
                    ->where('id_siswa', $siswa->id_siswa)
                    ->exists();

                if ($sudahAda) {
                    continue;
                }

                $payload = [
                    'id_siswa' => $siswa->id_siswa,
                    'nama_wali' => $siswa->nama_ortu ?: ('Wali dari ' . $siswa->nama_siswa),
                    'no_telp' => $siswa->no_telp_ortu,
                    'email' => $siswa->email_ortu,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Skema lama mewajibkan id_kelas.
                if (Schema::hasColumn('tbl_wali_murid', 'id_kelas')) {
                    $idKelas = DB::table('tbl_siswa')
                        ->where('id_siswa', $siswa->id_siswa)
                        ->value('id_kelas');
                    $payload['id_kelas'] = $idKelas;
                }

                DB::table('tbl_wali_murid')->insert($payload);
            }
        }

        // 2. Jika tabel masih memakai skema lama (id_kelas), lepaskan relasinya.
        if (Schema::hasColumn('tbl_wali_murid', 'id_kelas')) {
            $this->dropForeignIfExists('tbl_wali_murid', 'tbl_wali_murid_id_kelas_foreign');
            $this->dropIndexIfExists('tbl_wali_murid', 'tbl_wali_murid_id_kelas_unique');

            // Isi id_siswa untuk baris lama berdasarkan kelas (fallback).
            $waliTanpaSiswa = DB::table('tbl_wali_murid')->whereNull('id_siswa')->get();
            foreach ($waliTanpaSiswa as $wali) {
                $siswa = DB::table('tbl_siswa')
                    ->where('id_kelas', $wali->id_kelas)
                    ->orderBy('id_siswa')
                    ->first();

                if (!$siswa) {
                    continue;
                }

                $sudahAda = DB::table('tbl_wali_murid')
                    ->where('id_siswa', $siswa->id_siswa)
                    ->where('id_wali_murid', '!=', $wali->id_wali_murid)
                    ->exists();

                if ($sudahAda) {
                    continue;
                }

                DB::table('tbl_wali_murid')
                    ->where('id_wali_murid', $wali->id_wali_murid)
                    ->update(['id_siswa' => $siswa->id_siswa]);
            }

            Schema::table('tbl_wali_murid', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_wali_murid', 'mapel_wali')) {
                    $table->dropColumn('mapel_wali');
                }
                $table->dropColumn('id_kelas');
            });
        }

        // 3. Pastikan unique + FK id_siswa ada.
        if (!$this->hasIndex('tbl_wali_murid', 'tbl_wali_murid_id_siswa_unique')) {
            Schema::table('tbl_wali_murid', function (Blueprint $table) {
                $table->unique('id_siswa');
            });
        }

        if (!$this->hasForeignKey('tbl_wali_murid', 'tbl_wali_murid_id_siswa_foreign')) {
            Schema::table('tbl_wali_murid', function (Blueprint $table) {
                $table->foreign('id_siswa')
                    ->references('id_siswa')
                    ->on('tbl_siswa')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            });
        }
    }

    private function perbaikiPetugas(): void
    {
        if (Schema::hasTable('tbl_petugas')
            && Schema::hasColumn('tbl_petugas', 'id_user')
            && !$this->hasIndex('tbl_petugas', 'tbl_petugas_id_user_unique')) {
            Schema::table('tbl_petugas', function (Blueprint $table) {
                $table->unique('id_user');
            });
        }
    }

    private function perbaikiTagihanSpp(): void
    {
        if (!Schema::hasTable('tbl_tagihan_spp')) {
            return;
        }

        // Lepas FK lama (ON DELETE CASCADE) agar tidak menghapus data tagihan
        // secara tidak sengaja saat kelas/tahun ajaran dihapus.
        foreach ([
            'tbl_tagihan_spp_id_kelas_foreign',
            'tbl_tagihan_spp_id_siswa_foreign',
            'tbl_tagihan_spp_id_tahun_ajaran_foreign',
        ] as $fk) {
            $this->dropForeignIfExists('tbl_tagihan_spp', $fk);
        }

        $kolomBaru = [
            'id_spp' => function (Blueprint $table) {
                $table->unsignedInteger('id_spp')->nullable()->after('id_tahun_ajaran');
            },
            'id_user' => function (Blueprint $table) {
                $table->unsignedInteger('id_user')->nullable()->after('id_spp');
            },
            'bulan_ke' => function (Blueprint $table) {
                $table->unsignedTinyInteger('bulan_ke')->nullable()->after('bulan');
            },
            'jatuh_tempo' => function (Blueprint $table) {
                $table->date('jatuh_tempo')->nullable()->after('nominal');
            },
        ];

        foreach ($kolomBaru as $kolom => $callback) {
            if (!Schema::hasColumn('tbl_tagihan_spp', $kolom)) {
                Schema::table('tbl_tagihan_spp', $callback);
            }
        }

        // Lengkapi bulan_ke dari kolom bulan bila masih kosong.
        $this->backfillBulanKe('tbl_tagihan_spp');

        if (!$this->hasForeignKey('tbl_tagihan_spp', 'tbl_tagihan_spp_id_kelas_foreign')) {
            Schema::table('tbl_tagihan_spp', function (Blueprint $table) {
                $table->foreign('id_kelas')->references('id_kelas')->on('tbl_kelas')->onDelete('restrict');
            });
        }

        if (!$this->hasForeignKey('tbl_tagihan_spp', 'tbl_tagihan_spp_id_siswa_foreign')) {
            Schema::table('tbl_tagihan_spp', function (Blueprint $table) {
                $table->foreign('id_siswa')->references('id_siswa')->on('tbl_siswa')->onDelete('restrict');
            });
        }

        if (!$this->hasForeignKey('tbl_tagihan_spp', 'tbl_tagihan_spp_id_tahun_ajaran_foreign')) {
            Schema::table('tbl_tagihan_spp', function (Blueprint $table) {
                $table->foreign('id_tahun_ajaran')->references('id_tahun_ajaran')->on('tbl_tahun_ajaran')->onDelete('restrict');
            });
        }

        if (!$this->hasForeignKey('tbl_tagihan_spp', 'tbl_tagihan_spp_id_spp_foreign')) {
            Schema::table('tbl_tagihan_spp', function (Blueprint $table) {
                $table->foreign('id_spp')->references('id_spp')->on('tbl_spp')->onDelete('restrict');
            });
        }

        if (!$this->hasForeignKey('tbl_tagihan_spp', 'tbl_tagihan_spp_id_user_foreign')) {
            Schema::table('tbl_tagihan_spp', function (Blueprint $table) {
                $table->foreign('id_user')->references('id_user')->on('tbl_user')->onDelete('set null');
            });
        }

        if (!$this->hasIndex('tbl_tagihan_spp', 'uq_tagihan_siswa_periode')) {
            Schema::table('tbl_tagihan_spp', function (Blueprint $table) {
                $table->unique(['id_siswa', 'id_tahun_ajaran', 'bulan_ke'], 'uq_tagihan_siswa_periode');
            });
        }
    }

    private function buatDetailPembayaran(): void
    {
        if (Schema::hasTable('tbl_detail_pembayaran')) {
            return;
        }

        Schema::create('tbl_detail_pembayaran', function (Blueprint $table) {
            $table->id('id_detail_pembayaran');
            $table->unsignedInteger('id_pembayaran');
            $table->unsignedBigInteger('id_tagihan_spp');
            $table->decimal('nominal', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('id_pembayaran')
                ->references('id_pembayaran')
                ->on('tbl_pembayaran')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('id_tagihan_spp')
                ->references('id_tagihan_spp')
                ->on('tbl_tagihan_spp')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->unique(['id_pembayaran', 'id_tagihan_spp'], 'uq_detail_pembayaran_tagihan');
        });
    }

    private function perbaikiLogMidtrans(): void
    {
        if (!Schema::hasTable('tbl_log_midtrans') || !Schema::hasTable('tbl_pembayaran')) {
            return;
        }

        if (!Schema::hasColumn('tbl_log_midtrans', 'id_pembayaran')) {
            Schema::table('tbl_log_midtrans', function (Blueprint $table) {
                $table->unsignedInteger('id_pembayaran')->nullable()->after('order_id');
                $table->index('id_pembayaran');
            });
        }

        // Backfill hanya untuk log yang order_id-nya benar-benar ada di pembayaran.
        DB::statement(
            'UPDATE tbl_log_midtrans l '
            . 'JOIN tbl_pembayaran p ON p.order_id = l.order_id '
            . 'SET l.id_pembayaran = p.id_pembayaran '
            . 'WHERE l.id_pembayaran IS NULL'
        );

        if (!$this->hasForeignKey('tbl_log_midtrans', 'tbl_log_midtrans_id_pembayaran_foreign')) {
            Schema::table('tbl_log_midtrans', function (Blueprint $table) {
                $table->foreign('id_pembayaran')
                    ->references('id_pembayaran')
                    ->on('tbl_pembayaran')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            });
        }
    }

    private function perbaikiPembayaran(): void
    {
        if (!Schema::hasTable('tbl_pembayaran')) {
            return;
        }

        DB::statement('ALTER TABLE tbl_pembayaran MODIFY nama_bulan VARCHAR(100) NOT NULL');

        // Validasi rentang bulan 1-12 (MySQL 8 mendukung CHECK constraint).
        if (!$this->hasCheck('tbl_pembayaran', 'chk_pembayaran_bulan_ke')) {
            DB::statement(
                'ALTER TABLE tbl_pembayaran '
                . 'ADD CONSTRAINT chk_pembayaran_bulan_ke CHECK (bulan_ke BETWEEN 1 AND 12)'
            );
        }
    }

    private function koreksiNominalSpp(): void
    {
        if (!Schema::hasTable('tbl_spp')) {
            return;
        }

        $tarif = ['7' => 175000, '8' => 185000, '9' => 195000];

        foreach ($tarif as $tingkat => $nominal) {
            DB::table('tbl_spp')
                ->where('tingkat', (string) $tingkat)
                ->update(['nominal' => $nominal, 'updated_at' => now()]);
        }
    }

    private function sinkronkanIdSppPembayaran(): void
    {
        if (!Schema::hasTable('tbl_pembayaran')) {
            return;
        }

        // Samakan id_spp pada pembayaran lama dengan tingkat kelas siswa.
        DB::statement(
            'UPDATE tbl_pembayaran p '
            . 'JOIN tbl_siswa s ON s.id_siswa = p.id_siswa '
            . 'JOIN tbl_kelas k ON k.id_kelas = s.id_kelas '
            . 'JOIN tbl_spp sp ON sp.id_tahun_ajaran = p.id_tahun_ajaran AND sp.tingkat = k.tingkat '
            . 'SET p.id_spp = sp.id_spp'
        );
    }

    /**
     * Untuk setiap pembayaran lama yang belum punya detail, buatkan tagihan
     * (bila belum ada) lalu hubungkan lewat tbl_detail_pembayaran.
     */
    private function backfillTagihanDanDetail(): void
    {
        if (!Schema::hasTable('tbl_detail_pembayaran')
            || !Schema::hasTable('tbl_tagihan_spp')
            || !Schema::hasTable('tbl_pembayaran')) {
            return;
        }

        $pembayaranList = DB::table('tbl_pembayaran')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('tbl_detail_pembayaran as d')
                    ->whereColumn('d.id_pembayaran', 'tbl_pembayaran.id_pembayaran');
            })
            ->get();

        foreach ($pembayaranList as $pembayaran) {
            $bulanKe = (int) ($pembayaran->bulan_ke ?: 1);
            if ($bulanKe < 1 || $bulanKe > 12) {
                $bulanKe = 1;
            }

            $idSiswa = $pembayaran->id_siswa;
            $idTahunAjaran = $pembayaran->id_tahun_ajaran;
            $nominal = (float) $pembayaran->nominal_tagihan;

            $idTagihan = DB::table('tbl_tagihan_spp')
                ->where('id_siswa', $idSiswa)
                ->where('id_tahun_ajaran', $idTahunAjaran)
                ->where('bulan_ke', $bulanKe)
                ->value('id_tagihan_spp');

            if (!$idTagihan) {
                $idTagihan = DB::table('tbl_tagihan_spp')->insertGetId([
                    'id_kelas' => $pembayaran->id_kelas,
                    'id_siswa' => $idSiswa,
                    'id_tahun_ajaran' => $idTahunAjaran,
                    'id_spp' => $pembayaran->id_spp,
                    'id_user' => $pembayaran->id_user,
                    'bulan' => mb_substr((string) $pembayaran->nama_bulan, 0, 20),
                    'bulan_ke' => $bulanKe,
                    'nominal' => $nominal,
                    'jatuh_tempo' => $pembayaran->tgl_bayar
                        ? date('Y-m-d', strtotime($pembayaran->tgl_bayar . ' +10 days'))
                        : null,
                    'status' => $pembayaran->status_bayar === 'lunas' ? 'lunas' : 'belum_lunas',
                    'keterangan' => 'Tagihan dibuat otomatis dari pembayaran ' . $pembayaran->order_id,
                    'created_at' => $pembayaran->created_at ?: now(),
                    'updated_at' => now(),
                ], 'id_tagihan_spp');
            }

            $sudahTerhubung = DB::table('tbl_detail_pembayaran')
                ->where('id_pembayaran', $pembayaran->id_pembayaran)
                ->where('id_tagihan_spp', $idTagihan)
                ->exists();

            if (!$sudahTerhubung) {
                DB::table('tbl_detail_pembayaran')->insert([
                    'id_pembayaran' => $pembayaran->id_pembayaran,
                    'id_tagihan_spp' => $idTagihan,
                    'nominal' => $nominal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /* ------------------------------------------------------------------ */

    private function backfillBulanKe(string $table): void
    {
        if (!Schema::hasColumn($table, 'bulan_ke') || !Schema::hasColumn($table, 'bulan')) {
            return;
        }

        $bulanMap = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
            'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
            'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
        ];

        $rows = DB::table($table)
            ->whereNull('bulan_ke')
            ->get(['bulan']);

        foreach ($rows as $row) {
            $raw = trim((string) $row->bulan);

            if ($raw === '') {
                continue;
            }

            if (is_numeric($raw)) {
                $bulanKe = (int) $raw;
            } else {
                $kata = strtolower(explode(' ', $raw)[0]);
                $bulanKe = $bulanMap[$kata] ?? 0;
            }

            if ($bulanKe >= 1 && $bulanKe <= 12) {
                DB::table($table)
                    ->where('bulan', $row->bulan)
                    ->whereNull('bulan_ke')
                    ->update(['bulan_ke' => $bulanKe]);
            }
        }
    }

    private function hasForeignKey(string $table, string $constraint): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraint)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }

    private function hasIndex(string $table, string $index): bool
    {
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $index)
            ->exists();
    }

    private function hasCheck(string $table, string $constraint): bool
    {
        try {
            return DB::table('information_schema.CHECK_CONSTRAINTS as cc')
                ->join('information_schema.TABLE_CONSTRAINTS as tc', function ($join) {
                    $join->on('tc.CONSTRAINT_NAME', '=', 'cc.CONSTRAINT_NAME')
                        ->on('tc.CONSTRAINT_SCHEMA', '=', 'cc.CONSTRAINT_SCHEMA');
                })
                ->where('cc.CONSTRAINT_SCHEMA', DB::getDatabaseName())
                ->where('tc.TABLE_NAME', $table)
                ->where('cc.CONSTRAINT_NAME', $constraint)
                ->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function dropForeignIfExists(string $table, string $constraint): void
    {
        if (!$this->hasForeignKey($table, $constraint)) {
            return;
        }

        DB::statement(sprintf('ALTER TABLE `%s` DROP FOREIGN KEY `%s`', $table, $constraint));
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (!$this->hasIndex($table, $index)) {
            return;
        }

        DB::statement(sprintf('ALTER TABLE `%s` DROP INDEX `%s`', $table, $index));
    }
};
