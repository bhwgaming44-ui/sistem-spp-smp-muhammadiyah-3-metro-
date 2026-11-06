<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_petugas', function (Blueprint $table) {
            $table->id('id_petugas');
            $table->unsignedInteger('id_user')->nullable();
            $table->string('jabatan', 50);
            $table->string('no_telp', 15)->nullable();
            $table->string('email', 100)->nullable();
            $table->enum('status_petugas', ['aktif', 'tidak_aktif'])->default('aktif');
            $table->timestamps();

            // Satu akun user hanya boleh memiliki satu profil petugas.
            $table->unique('id_user');
            $table->foreign('id_user')->references('id_user')->on('tbl_user')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_petugas');
    }
};
