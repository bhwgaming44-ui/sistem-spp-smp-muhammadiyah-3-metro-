<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Petugas extends Model
{
    protected $table = 'tbl_petugas';
    protected $primaryKey = 'id_petugas';
    public $timestamps = true;

    protected $fillable = [
        'id_user',
        'jabatan',
        'no_telp',
        'email',
        'status_petugas',
    ];

    protected $casts = [
        'status_petugas' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}