<?php


namespace App\Models;

// 1. Gunakan trait bawaan Laravel
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    // 2. Aktifkan factory jika ingin generate dummy data
    use HasFactory;

    // 3. Atur nama tabel secara 
    protected $table = 'pelanggan';

    // 4. Tentukan primary key
    protected $primaryKey = 'id_pelanggan';

    // 5. Matikan timestamp karena tidak pakai created_at & updated_at
    public $timestamps = false;

    // 6. Atur kolom-kolom yang bisa diisi
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

    // 7. Relasi dengan tabel membership (hasMany)
    public function memberships()
    {
        return $this->hasMany(Membership::class, 'id_pelanggan', 'id_pelanggan');
    }

    // 8. Accessor untuk cek apakah membership aktif
    public function getMembershipAktifAttribute()
    {
        return $this->is_membership == 1;
    }
}
