<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_pembayaran', function (Blueprint $table) {
            $table->increments('id_pembayaran');
            $table->string('order_id', 50)->unique();
            $table->unsignedInteger('id_user')->nullable();
            $table->unsignedInteger('id_siswa');
            $table->unsignedInteger('id_kelas');
            $table->unsignedInteger('id_spp');
            $table->unsignedInteger('id_tahun_ajaran');
            $table->dateTime('tgl_bayar')->nullable();
            $table->unsignedTinyInteger('bulan_ke');
            $table->string('nama_bulan', 100);
            $table->decimal('nominal_tagihan', 12, 2);
            $table->decimal('jumlah_bayar', 12, 2)->default(0);
            
            // Kolom Khusus Midtrans Payment Gateway
            $table->string('snap_token', 255)->nullable();
            $table->string('transaction_id', 100)->nullable();
            $table->string('payment_type', 50)->nullable();
            $table->string('va_number', 50)->nullable();
            $table->string('bank', 20)->nullable();
            $table->enum('transaction_status', ['pending', 'settlement', 'expire', 'cancel', 'deny'])->default('pending');
            $table->string('pdf_url', 255)->nullable();

            $table->enum('status_bayar', ['lunas', 'belum_lunas'])->default('belum_lunas');
            $table->enum('metode_bayar', ['midtrans', 'tunai'])->default('midtrans');
            $table->string('keterangan', 150)->nullable();
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('tbl_user')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('id_siswa')->references('id_siswa')->on('tbl_siswa')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_kelas')->references('id_kelas')->on('tbl_kelas')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_spp')->references('id_spp')->on('tbl_spp')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_tahun_ajaran')->references('id_tahun_ajaran')->on('tbl_tahun_ajaran')->onUpdate('cascade')->onDelete('restrict');

            // Proteksi pembayaran ganda pada bulan yang sama
            $table->unique(['id_siswa', 'id_tahun_ajaran', 'bulan_ke']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_pembayaran');
    }
};
