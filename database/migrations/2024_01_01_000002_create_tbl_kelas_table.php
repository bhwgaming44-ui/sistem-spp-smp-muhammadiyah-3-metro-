<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_kelas', function (Blueprint $table) {
            $table->increments('id_kelas');
            $table->string('nama_kelas', 20)->unique();
            $table->enum('tingkat', ['7', '8', '9']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_kelas');
    }
};
