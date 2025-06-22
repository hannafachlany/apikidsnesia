<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\PasswordReset;

class ResetPasswordToken
{
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['message' => 'Token reset tidak ditemukan'], 401);
        }

        $token = substr($authHeader, 7); // hapus "Bearer "

        // Cek token di tabel password_resets
        $reset = PasswordReset::where('token_reset', $token)
            ->where('created_at', '>', now()->subMinutes(30)) // misalnya token valid 30 menit
            ->first();

        if (!$reset) {
            return response()->json(['message' => 'Token reset tidak valid atau kedaluwarsa'], 401);
        }

        // Inject email ke request supaya bisa dipakai langsung
        $request->merge(['email' => $reset->email]);
        return $next($request);
    }
}
