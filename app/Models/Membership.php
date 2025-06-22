<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    use HasFactory;
     protected $table = 'membership';
    protected $primaryKey = 'id_membership';

    protected $fillable = [
        'id_pelanggan',
        'tanggal_pembelian',
        'tanggal_mulai',
        'tanggal_berakhir',
        'status',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function pembayaran()
    {
        return $this->hasOne(PembayaranMembership::class, 'id_membership', 'id_membership');
    }
}
