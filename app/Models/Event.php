<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    public $timestamps = false; // gak pakai created_at updated_at
     protected $table = 'event';
     protected $primaryKey = 'id_event';
    protected $fillable = [
        'nama_event',
        'harga_event',
        'jadwal_event',
        'foto_event',
        'deskripsi_event',
        'kuota',
    ];
    public function detailFoto()
    {
        return $this->hasMany(DetailEvent::class, 'id_event', 'id_event');
    }

}
