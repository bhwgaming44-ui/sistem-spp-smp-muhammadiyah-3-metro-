<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_tahun_ajaran', function (Blueprint $table) {
            $table->increments('id_tahun_ajaran');
            $table->string('tahun_ajaran', 10)->unique();
            $table->enum('status_aktif', ['aktif', 'nonaktif'])->default('nonaktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_tahun_ajaran');
    }
};
