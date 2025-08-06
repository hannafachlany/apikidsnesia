<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Pelanggan;

class AuthToken
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Ambil header Authorization dari request
        $authHeader = $request->header('Authorization');

        // 2. Cek apakah header kosong atau bukan Bearer token
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['message' => 'Token tidak ditemukan'], 401);
        }

        // 3. Ambil token-nya aja, buang "Bearer "
        $token = substr($authHeader, 7);

        // 4. Cari pelanggan dengan token itu dan belum expired
        $pelanggan = Pelanggan::where('token', $token)
            ->where('token_expired_at', '>', now())
            ->first();

        // 5. Kalau tidak ketemu → token salah atau sudah kadaluarsa
        if (!$pelanggan) {
            return response()->json(['message' => 'Token tidak valid atau kedaluwarsa'], 401);
        }

        // 6. Masukkan data pelanggan ke request->user() kalau dibutuhkan di controller
        $request->setUserResolver(function () use ($pelanggan) {
            return $pelanggan;
        });

        // 7. Simpan juga ke $request->pelanggan kalau nanti butuh akses manual
        $request->merge(['pelanggan' => $pelanggan]);

        // 8. Lanjut ke proses selanjutnya (controller)
        return $next($request);
    }
}
