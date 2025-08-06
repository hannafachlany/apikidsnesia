<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembelianEvent extends Model
{
    protected $table = 'pembelian_event';
    protected $primaryKey = 'id_pembelian';
    public $timestamps = true;

    protected $fillable = [
        'id_pelanggan',
        'total_pembelian',
        'tanggal_pembelian',
        'status_pembelian',
        'is_checkout',
        'nama_pelanggan',
    ];

    public function detailEvent()
    {
        return $this->hasMany(DetailPembelianEvent::class, 'id_pembelian');
    }

    public function pembayaran()
    {
        return $this->hasOne(PembayaranEvent::class, 'id_pembelian', 'id_pembelian');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }
    

}
