<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembelianMerch extends Model
{
    protected $table = 'pembelian_merchandise';
    protected $primaryKey = 'id_pembelianMerch';
    public $timestamps = true;

    protected $fillable = [
        'id_pelanggan',
        'total_pembelian',
        'tanggal_pembelian',
        'status_pembelian',
        'is_checkout'
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function detail()
    {
        return $this->hasMany(DetailPembelianMerch::class, 'id_pembelianMerch', 'id_pembelianMerch');
    }

    public function pembayaran()
    {
        return $this->hasOne(PembayaranMerch::class, 'id_pembelianMerch', 'id_pembelianMerch');
    }
    // app/Models/PembelianMerch.php

    public function detailMerchandise()
    {
        return $this->hasMany(\App\Models\DetailPembelianMerch::class, 'id_pembelianMerch', 'id_pembelianMerch');
    }

}
