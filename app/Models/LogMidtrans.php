<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogMidtrans extends Model
{
    use HasFactory;

    protected $table = 'tbl_log_midtrans';
    protected $primaryKey = 'id_log_midtrans';

    protected $fillable = [
        'order_id',
        'id_pembayaran',
        'status_code',
        'transaction_status',
        'raw_response',
    ];

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class, 'id_pembayaran', 'id_pembayaran');
    }
}
