<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;
    protected $table = 'pelanggan'; // 👈 supaya tidak jadi "pelanggans"

    protected $primaryKey = 'id_pelanggan';

    public $timestamps = false; // matikan created_at dan updated_at jika tidak digunakan

    protected $fillable = [
        'email',
        'password',
        'nama_pelanggan',
        'no_hp_pelanggan',
        'token',
        'token_expired_at',
        'foto_profil',
        'is_membership'
    ];
    public function memberships()
    {
        return $this->hasMany(Membership::class, 'id_pelanggan', 'id_pelanggan');
    }
    
    public function getMembershipAktifAttribute()
    {
        return $this->is_membership == 1;
    }

}
