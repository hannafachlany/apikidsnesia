<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembayaranMerch extends Model
{
    protected $table = 'pembayaran_merchandise';
    protected $primaryKey = 'id_pembayaranMerch';
    public $timestamps = true;

    protected $fillable = [
        'id_pembelianMerch',
        'nama_pelanggan',
        'bank',
        'status_pembayaran',
        'total_harga',
        'tanggal_bayar',
        'id_pelanggan',
        'bukti_bayar_merch'
    ];

    public function pembelian()
    {
        return $this->belongsTo(PembelianMerch::class, 'id_pembelianMerch', 'id_pembelianMerch');
    }
    public function pelanggan()
    {
        return $this->hasOneThrough(
            Pelanggan::class,
            PembelianMerch::class,
            'id_pembelianMerch', // FK di tabel pembelian_merch
            'id_pelanggan',      // FK di tabel pelanggan
            'id_pembelianMerch', // Local key di tabel pembayaran_merchandise
            'id_pelanggan'       // Local key di tabel pembelian_merch
        );
    }

}
