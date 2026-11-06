<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_log_midtrans', function (Blueprint $table) {
            $table->increments('id_log_midtrans');
            $table->string('order_id', 50)->index();
            $table->string('status_code', 10);
            $table->string('transaction_status', 50);
            $table->longText('raw_response');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_log_midtrans');
    }
};
