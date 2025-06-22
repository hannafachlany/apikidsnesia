<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailEvent extends Model
{
    protected $table = 'detail_event';
    protected $primaryKey = 'id_detail_event';

    protected $fillable = ['id_event', 'foto_kegiatan'];

    public function event()
    {
        return $this->belongsTo(Event::class, 'id_event', 'id_event');
    }
}
