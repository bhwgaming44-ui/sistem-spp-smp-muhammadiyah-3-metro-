<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabel pivot antara pembayaran dan tagihan SPP.
 *
 * Satu pembayaran dapat melunasi beberapa tagihan (multi-bulan), dan satu
 * tagihan dapat dibayar melalui beberapa transaksi (misalnya cicilan).
 */
class DetailPembayaran extends Model
{
    protected $table = 'tbl_detail_pembayaran';
    protected $primaryKey = 'id_detail_pembayaran';

    protected $fillable = [
        'id_pembayaran',
        'id_tagihan_spp',
        'nominal',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class, 'id_pembayaran', 'id_pembayaran');
    }

    public function tagihanSpp()
    {
        return $this->belongsTo(TagihanSpp::class, 'id_tagihan_spp', 'id_tagihan_spp');
    }
}
