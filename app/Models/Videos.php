<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Videos extends Model
{
    protected $primaryKey = 'id_video';

    protected $fillable = [
        'judul_video',
        'deskripsi_video',
        'file_path',
    ];

    public $timestamps = true;
}
