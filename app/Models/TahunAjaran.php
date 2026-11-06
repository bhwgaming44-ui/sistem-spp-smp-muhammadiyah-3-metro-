<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    use HasFactory;

    protected $table = 'tbl_tahun_ajaran';
    protected $primaryKey = 'id_tahun_ajaran';

    protected $fillable = [
        'tahun_ajaran',
        'status_aktif',
    ];

    public function spp()
    {
        return $this->hasMany(Spp::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    public function siswa()
    {
        return $this->hasMany(Siswa::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }
}
