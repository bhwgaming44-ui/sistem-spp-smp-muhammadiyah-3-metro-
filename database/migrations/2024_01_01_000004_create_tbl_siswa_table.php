<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_siswa', function (Blueprint $table) {
            $table->increments('id_siswa');
            $table->string('nisn', 10)->unique();
            $table->string('password', 255);
            $table->string('nis', 10)->unique();
            $table->string('nama_siswa', 100);
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->unsignedInteger('id_kelas');
            $table->unsignedInteger('id_tahun_ajaran');
            $table->text('alamat')->nullable();
            $table->enum('status_siswa', ['aktif', 'lulus', 'pindah', 'keluar'])->default('aktif');
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('id_kelas')->references('id_kelas')->on('tbl_kelas')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_tahun_ajaran')->references('id_tahun_ajaran')->on('tbl_tahun_ajaran')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_siswa');
    }
};
