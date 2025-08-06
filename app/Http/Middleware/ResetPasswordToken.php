<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\PasswordReset;

class ResetPasswordToken
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Ambil header Authorization
        $authHeader = $request->header('Authorization');

        // 2. Cek apakah token kosong atau formatnya salah
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'error' => true,
                'message' => 'Token reset tidak ditemukan'
            ], 401);
        }

        // 3. Ambil token-nya aja, buang "Bearer "
        $token = substr($authHeader, 7);

        // 4. Cari token_reset di tabel password_resets, dan belum expired (30 menit)
        $reset = PasswordReset::where('token_reset', $token)
            ->where('created_at', '>', now()->subMinutes(30))
            ->first();

        // 5. Kalau token salah atau sudah kadaluarsa
        if (!$reset) {
            return response()->json([
                'error' => true,
                'message' => 'Token reset tidak valid atau kedaluwarsa',
            ], 401);
        }

        // 6. Masukkan email ke request, biar nggak usah diketik lagi di body
        $request->merge(['email' => $reset->email]);

        // 7. Lanjut ke proses berikutnya (controller)
        return $next($request);
    }
}
