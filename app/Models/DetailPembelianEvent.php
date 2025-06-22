<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPembelianEvent extends Model
{
    protected $table = 'detail_pembelian_event';
    protected $primaryKey = 'id_pembelian_event';
    public $timestamps = true;

    protected $fillable = [
        'id_event',
        'jumlah',
        'id_pembelian',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'id_event');
    }

    public function pembelian()
    {
        return $this->belongsTo(PembelianEvent::class, 'id_pembelian');
    }
}
