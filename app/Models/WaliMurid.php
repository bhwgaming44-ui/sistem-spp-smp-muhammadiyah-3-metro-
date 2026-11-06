<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaliMurid extends Model
{
    protected $table = 'tbl_wali_murid';
    protected $primaryKey = 'id_wali_murid';
    public $timestamps = true;

    protected $fillable = [
        'id_siswa',
        'nama_wali',
        'no_telp',
        'email',
    ];

    /**
     * Satu wali murid mewakili satu siswa (relasi 1:1).
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }
}
