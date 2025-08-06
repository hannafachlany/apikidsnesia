<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    // 1. Aktifkan factory
    use HasFactory;

    // 2. Matikan timestamps (gunakan created_at manual)
    public $timestamps = false;

    // 3. Atur kolom yang bisa diisi
    protected $fillable = ['email', 'otp', 'created_at'];
}

