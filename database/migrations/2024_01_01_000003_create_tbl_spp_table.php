<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_spp', function (Blueprint $table) {
            $table->increments('id_spp');
            $table->unsignedInteger('id_tahun_ajaran');
            $table->enum('tingkat', ['7', '8', '9']);
            $table->decimal('nominal', 12, 2);
            $table->string('keterangan', 100)->nullable();
            $table->timestamps();

            $table->foreign('id_tahun_ajaran')->references('id_tahun_ajaran')->on('tbl_tahun_ajaran')->onUpdate('cascade')->onDelete('restrict');
            $table->unique(['id_tahun_ajaran', 'tingkat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_spp');
    }
};
