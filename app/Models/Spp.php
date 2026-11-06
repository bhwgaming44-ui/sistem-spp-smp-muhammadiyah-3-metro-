<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spp extends Model
{
    use HasFactory;

    protected $table = 'tbl_spp';
    protected $primaryKey = 'id_spp';

    protected $fillable = [
        'id_tahun_ajaran',
        'tingkat',
        'nominal',
        'keterangan',
    ];

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'id_spp', 'id_spp');
    }
}
