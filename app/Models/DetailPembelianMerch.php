<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPembelianMerch extends Model
{
    protected $table = 'detail_pembelian_merchandise';
    protected $primaryKey = 'id_detail_pembelianMerch';
    public $timestamps = false;

    protected $fillable = [
        'id_pembelianMerch',
        'id_merchandise',
        'jumlah',
        'harga',
        'subtotal'
    ];

    public function pembelian()
    {
        return $this->belongsTo(PembelianMerch::class, 'id_pembelianMerch');
    }

    public function merchandise()
    {
        return $this->belongsTo(Merchandise::class, 'id_merchandise', 'id_merchandise');
    }
}
