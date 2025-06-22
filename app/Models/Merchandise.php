<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merchandise extends Model
{
    protected $table = 'merchandise';
    protected $primaryKey = 'id_merchandise';
    public $timestamps = false;

    protected $fillable = [
        'nama_merchandise',
        'harga_merchandise',
        'foto_merchandise',
        'deskripsi_merchandise',
        'stok'
    ];

    public function detailPembelian()
    {
        return $this->hasMany(DetailPembelianMerch::class, 'id_merchandise');
    }

}
