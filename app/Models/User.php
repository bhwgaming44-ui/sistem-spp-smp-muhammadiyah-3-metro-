<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'tbl_user';
    protected $primaryKey = 'id_user';

    protected $fillable = [
        'username',
        'password',
        'nama_petugas',
        'role',
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

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'id_user', 'id_user');
    }
}
