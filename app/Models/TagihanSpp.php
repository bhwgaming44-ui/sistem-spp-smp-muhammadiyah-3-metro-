<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagihanSpp extends Model
{
    protected $table = 'tbl_tagihan_spp';
    protected $primaryKey = 'id_tagihan_spp';
    public $timestamps = true;

    protected $fillable = [
        'id_kelas',
        'id_siswa',
        'id_tahun_ajaran',
        'id_spp',
        'id_user',
        'bulan',
        'bulan_ke',
        'nominal',
        'jatuh_tempo',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'bulan_ke' => 'integer',
        'jatuh_tempo' => 'date',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    public function spp()
    {
        return $this->belongsTo(Spp::class, 'id_spp', 'id_spp');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function detailPembayaran()
    {
        return $this->hasMany(DetailPembayaran::class, 'id_tagihan_spp', 'id_tagihan_spp');
    }

    public function pembayaran()
    {
        return $this->belongsToMany(
            Pembayaran::class,
            'tbl_detail_pembayaran',
            'id_tagihan_spp',
            'id_pembayaran'
        )->withPivot('nominal')->withTimestamps();
    }
}
