<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembayaranEvent extends Model
{
    protected $table = 'pembayaran_event';
    protected $primaryKey = 'id_pembayaran';
    public $timestamps = true;

    protected $fillable = [
        'id_pembelian',
        'nama_pelanggan',
        'bank',
        'total_harga',
        'tanggal_bayar',
        'status_pembayaran',
        'bukti_bayar_event'
    ];

    public function pembelianEvent()
    {
        return $this->belongsTo(PembelianEvent::class, 'id_pembelian');
    }
    public function pembayaran()
    {
        return $this->hasOne(PembayaranEvent::class, 'id_pembelian');
    }


}
