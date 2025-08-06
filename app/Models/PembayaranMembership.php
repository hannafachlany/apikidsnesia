<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranMembership extends Model
{
    use HasFactory;
    protected $table = 'pembayaran_membership';
    protected $primaryKey = 'id_pembayaranMembership';

    protected $fillable = [
        'id_membership',
        'nama_pelanggan',
        'bank_pengirim',
        'waktu_transfer',
        'jumlah_transfer',
        'status_pembayaran',
        'bukti_transfer',
    ];

    public function membership()
    {
        return $this->belongsTo(Membership::class, 'id_membership', 'id_membership');
    }
    public function pelanggan()
    {
        // Bisa lewat relasi hasOneThrough, atau helper
        return $this->hasOneThrough(
            Pelanggan::class,
            Membership::class,
            'id_membership', // Foreign key di Membership
            'id_pelanggan',  // Foreign key di Pelanggan
            'id_membership', // Local key di PembayaranMembership
            'id_pelanggan'   // Local key di Membership
        );
    }
}
