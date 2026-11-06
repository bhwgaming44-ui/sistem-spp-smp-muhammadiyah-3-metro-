<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tagihan SPP adalah "invoice" per siswa per bulan.
 *
 * Satu tagihan dapat dibayar melalui satu atau beberapa pembayaran
 * (relasi many-to-many lewat tbl_detail_pembayaran).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_tagihan_spp', function (Blueprint $table) {
            $table->id('id_tagihan_spp');
            $table->unsignedInteger('id_kelas')->nullable();
            $table->unsignedInteger('id_siswa')->nullable();
            $table->unsignedInteger('id_tahun_ajaran')->nullable();
            $table->unsignedInteger('id_spp')->nullable();
            $table->unsignedInteger('id_user')->nullable();
            $table->string('bulan', 20);
            $table->unsignedTinyInteger('bulan_ke')->nullable();
            $table->decimal('nominal', 12, 2);
            $table->date('jatuh_tempo')->nullable();
            $table->enum('status', ['belum_lunas', 'lunas', 'dihapus'])->default('belum_lunas');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('id_kelas')->references('id_kelas')->on('tbl_kelas')->onDelete('restrict');
            $table->foreign('id_siswa')->references('id_siswa')->on('tbl_siswa')->onDelete('restrict');
            $table->foreign('id_tahun_ajaran')->references('id_tahun_ajaran')->on('tbl_tahun_ajaran')->onDelete('restrict');
            $table->foreign('id_spp')->references('id_spp')->on('tbl_spp')->onDelete('restrict');
            $table->foreign('id_user')->references('id_user')->on('tbl_user')->onDelete('set null');

            // Anti tagihan ganda: satu siswa hanya boleh punya satu tagihan
            // untuk tahun ajaran + bulan yang sama.
            $table->unique(['id_siswa', 'id_tahun_ajaran', 'bulan_ke'], 'uq_tagihan_siswa_periode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_tagihan_spp');
    }
};
