<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel wali murid menyimpan data orang tua/wali dari seorang siswa.
 *
 * Relasi: tbl_siswa 1 : 1 tbl_wali_murid (id_siswa unik).
 * Kolom id_kelas dan mapel_wali dari skema lama tidak dipakai lagi karena
 * tbl_wali_murid bukan tabel wali kelas (homeroom teacher).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tbl_wali_murid')) {
            Schema::create('tbl_wali_murid', function (Blueprint $table) {
                $table->id('id_wali_murid');
                $table->unsignedInteger('id_siswa')->nullable()->unique();
                $table->string('nama_wali', 100);
                $table->string('no_telp', 15)->nullable();
                $table->string('email', 100)->nullable();
                $table->timestamps();

                $table->foreign('id_siswa')
                    ->references('id_siswa')
                    ->on('tbl_siswa')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_wali_murid');
    }
};
