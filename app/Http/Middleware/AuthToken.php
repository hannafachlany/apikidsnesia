<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Pelanggan;

class AuthToken
{
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['message' => 'Token tidak ditemukan'], 401);
        }

        $token = substr($authHeader, 7); // Buang 'Bearer '

        $pelanggan = Pelanggan::where('token', $token)
            ->where('token_expired_at', '>', now()) // gunakan now() bukan now()->timestamp
            ->first();

        if (!$pelanggan) {
            return response()->json(['message' => 'Token tidak valid atau kedaluwarsa'], 401);
        }

        // Jangan panggil auth()->setUser() karena model Pelanggan bukan Authenticatable

        // Set user resolver agar $request->user() tidak null
        $request->setUserResolver(function () use ($pelanggan) {
            return $pelanggan;
        });

        // Kalau kamu ingin akses user lewat $request->pelanggan, bisa juga:
        $request->merge(['pelanggan' => $pelanggan]);

        return $next($request);
    }

}
