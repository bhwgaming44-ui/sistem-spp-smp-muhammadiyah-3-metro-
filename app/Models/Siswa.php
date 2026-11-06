<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Siswa extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'tbl_siswa';
    protected $primaryKey = 'id_siswa';

    protected $fillable = [
        'nisn',
        'password',
        'nis',
        'nama_siswa',
        'jenis_kelamin',
        'id_kelas',
        'id_tahun_ajaran',
        'alamat',
        'status_siswa',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'id_siswa', 'id_siswa');
    }

    public function tagihanSpp()
    {
        return $this->hasMany(TagihanSpp::class, 'id_siswa', 'id_siswa');
    }

    public function waliMurid()
    {
        return $this->hasOne(WaliMurid::class, 'id_siswa', 'id_siswa');
    }
}
