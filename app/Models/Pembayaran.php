<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'tbl_pembayaran';
    protected $primaryKey = 'id_pembayaran';

    protected $fillable = [
        'order_id',
        'id_user',
        'id_siswa',
        'id_kelas',
        'id_spp',
        'id_tahun_ajaran',
        'tgl_bayar',
        'bulan_ke',
        'nama_bulan',
        'nominal_tagihan',
        'jumlah_bayar',
        'snap_token',
        'transaction_id',
        'payment_type',
        'va_number',
        'bank',
        'transaction_status',
        'pdf_url',
        'status_bayar',
        'metode_bayar',
        'keterangan',
    ];

    protected $casts = [
        'tgl_bayar' => 'datetime',
        'bulan_ke' => 'integer',
        'nominal_tagihan' => 'decimal:2',
        'jumlah_bayar' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    public function spp()
    {
        return $this->belongsTo(Spp::class, 'id_spp', 'id_spp');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    public function detailPembayaran()
    {
        return $this->hasMany(DetailPembayaran::class, 'id_pembayaran', 'id_pembayaran');
    }

    public function tagihan()
    {
        return $this->belongsToMany(
            TagihanSpp::class,
            'tbl_detail_pembayaran',
            'id_pembayaran',
            'id_tagihan_spp'
        )->withPivot('nominal')->withTimestamps();
    }

    public function logMidtrans()
    {
        return $this->hasMany(LogMidtrans::class, 'id_pembayaran', 'id_pembayaran');
    }
}
