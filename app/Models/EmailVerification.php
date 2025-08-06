<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model
{
    // 1. Gunakan HasFactory untuk seeding
    use HasFactory;

    // 2. Matikan timestamps (karena hanya pakai created_at custom)
    public $timestamps = false;

    // 3. Atur kolom-kolom yang bisa diisi
    protected $fillable = [
        'email',
        'otp',
        'created_at',
        'token_verifikasi'
    ];
}
