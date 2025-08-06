<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailVerification;
use App\Models\Pelanggan;
use App\Models\PasswordReset;


class AuthService
{
    //fungsi register
    public function register(array $data)
    {
        // 1. Simpan pelanggan ke tabel pelanggan
        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => $data['nama_pelanggan'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'no_hp_pelanggan' => $data['no_hp_pelanggan'],
            //null krn blm login
            'token' => null,
            'token_expired_at' => null,
            'email_verified_at' => null,
        ]);

        // 2. Generate OTP dan token verifikasi
        $otp = rand(100000, 999999); // OTP 6 digit
        $tokenVerifikasi = Str::random(60); // Token random 60 karakter

        // 3. Simpan ke tabel email_verifications
        EmailVerification::updateOrCreate(
            ['email' => $pelanggan->email],
            [
                'otp' => $otp,
                'token_verifikasi' => $tokenVerifikasi,
                'created_at' => Carbon::now(),
            ]
        );

        // 4. Kirim OTP via email (bukan token_verifikasi)
        Mail::raw("Kode verifikasi email Anda adalah: $otp", function ($message) use ($pelanggan) {
            $message->to($pelanggan->email)
                    ->subject('Kode Verifikasi Email Kidsnesia');
        });

        // 5. Return data ke controller
        return [
            'email' => $pelanggan->email,
            'nama_pelanggan' => $pelanggan->nama_pelanggan,
            'otp' => $otp,
            'token_verifikasi' => $tokenVerifikasi,
        ];
    }

    //fungsi verifikasi email
    public function verifyEmail(array $data)
    {
        // 1. Ambil token dari Authorization Bearer Token
        $token = request()->bearerToken();

        // 2. Cari record verifikasi berdasarkan token
        $record = EmailVerification::where('token_verifikasi', $token)->first();

        // 3. Jika tidak ditemukan, lempar exception
        if (!$record) {
            throw new \Exception('Token verifikasi tidak valid.');
        }

        // 4. Cek apakah OTP sudah kadaluarsa (5 menit)
        $expired = Carbon::parse($record->created_at)->addMinutes(5);
        if (Carbon::now()->gt($expired)) {
            throw new \Exception('Kode OTP sudah kadaluarsa.');
        }

        // 5. Cek kesesuaian OTP
        if ($record->otp !== $data['otp']) {
            throw new \Exception('Kode OTP salah.');
        }

        // 6. Tandai email sebagai sudah diverifikasi
        Pelanggan::where('email', $record->email)->update([
            'email_verified_at' => now(),
        ]);

        // 7. Hapus record verifikasi setelah berhasil
        $record->delete();

        // 8. Return sukses
        return true;
    }
   
    //resend otp utk verifikasi email
    public function resendOtp(string $tokenVerifikasi): array
    {
        // 1. Cari record verifikasi berdasarkan token
        $verifikasi = EmailVerification::where('token_verifikasi', $tokenVerifikasi)->first();

        // 2. Jika tidak ditemukan, lempar exception
        if (!$verifikasi) {
            throw new \Exception('Token verifikasi tidak ditemukan.');
        }

        // 3. Generate OTP baru
        $otpBaru = rand(100000, 999999);

        // 4. Update OTP dan waktu pembuatan
        $verifikasi->update([
            'otp' => $otpBaru,
            'created_at' => now(),
        ]);

        // 5. Kirim OTP baru via email
        Mail::raw("Kode OTP verifikasi baru Anda adalah: $otpBaru", function ($message) use ($verifikasi) {
            $message->to($verifikasi->email)
                    ->subject('Kode Verifikasi Ulang Kidsnesia');
        });

        // 6. Return data ke controller
        return [
            'email' => $verifikasi->email,
            'otp' => $otpBaru,
            'token_verifikasi' => $verifikasi->token_verifikasi,
        ];
    }

    // fungsi login
    public function login(array $data)
    {
        // 1. Cari pelanggan berdasarkan email
        $pelanggan = Pelanggan::where('email', $data['email'])->first();

        // 2. Cek apakah user ditemukan dan password cocok
        if (!$pelanggan || !Hash::check($data['password'], $pelanggan->password)) {
            return null; // Login gagal
        }

        // 3. Cek apakah email sudah diverifikasi
        if (!$pelanggan->email_verified_at) {
            return 'not_verified'; // Email belum diverifikasi
        }

        // 4. Generate token baru dan atur masa berlaku 30 hari
        $token = Str::random(60);
        $expiredAt = Carbon::now()->addDays(30);

        // 5. Simpan token ke pelanggan
        $pelanggan->token = $token;
        $pelanggan->token_expired_at = $expiredAt;
        $pelanggan->save();

        // 6. Return data login
        return [
            'email' => $pelanggan->email,
            'nama_pelanggan' => $pelanggan->nama_pelanggan,
            'token' => $token,
            'token_expired_at' => $expiredAt->toDateTimeString(),
        ];
    }

    //kode otp dikirm ke email
    public function sendResetEmail(array $data)
    {
        // 1. Cek apakah email terdaftar
        $pelanggan = Pelanggan::where('email', $data['email'])->first();
        if (!$pelanggan) {
            throw new \Exception('Email tidak ditemukan.');
        }

        // 2. Generate OTP untuk reset password
        $otp = rand(100000, 999999);

        // 3. Simpan atau update OTP di tabel password_resets
        PasswordReset::updateOrCreate(
            ['email' => $data['email']],
            [
                'otp' => $otp,
                'created_at' => now(),
            ]
        );

        // 4. Kirim OTP ke email
        Mail::raw("Kode OTP reset password Anda: $otp", function ($message) use ($data) {
            $message->to($data['email'])
                    ->subject('Reset Password Kidsnesia');
        });

        // 5. Return OTP (opsional, untuk testing)
        return $otp;
    }

    //verifikasi otp utk reset password
    public function verifyOtp(array $data)
    {
        // 1. Ambil record berdasarkan email
        $record = PasswordReset::where('email', $data['email'])->first();

        // 2. Jika tidak ditemukan, lempar error
        if (!$record) {
            throw new \Exception('OTP tidak ditemukan.');
        }

        // 3. Cek apakah OTP sesuai
        if ($record->otp !== $data['otp']) {
            throw new \Exception('OTP salah.');
        }

        // 4. Cek apakah OTP sudah kadaluarsa (5 menit)
        $expiredAt = Carbon::parse($record->created_at)->addMinutes(5);
        if (Carbon::now()->gt($expiredAt)) {
            throw new \Exception('OTP sudah kadaluarsa.');
        }

        // 5. Cari data pelanggan
        $pelanggan = Pelanggan::where('email', $data['email'])->first();
        if (!$pelanggan) {
            throw new \Exception('Data pelanggan tidak ditemukan.');
        }

        // 6. Generate token reset password dan simpan
        $tokenReset = Str::random(60);
        $record->token_reset = $tokenReset;
        $record->save();

        // 7. Return token reset
        return [
            'email' => $pelanggan->email,
            'token_reset' => $tokenReset,
        ];
    }

    //reset password
    public function resetPassword(array $data)
    {
        // 1. Cari user berdasarkan email
        $user = Pelanggan::where('email', $data['email'])->first();
        if (!$user) {
            throw new \Exception('Email tidak ditemukan.');
        }

        // 2. Cek apakah password baru sama dengan yang lama
        if (Hash::check($data['password'], $user->password)) {
            throw new \Exception('Password baru tidak boleh sama dengan password lama.');
        }

        // 3. Simpan password baru
        $user->password = Hash::make($data['password']);
        $user->save();

        // 4. Hapus record OTP dan token reset
        PasswordReset::where('email', $data['email'])->delete();

        // 5. Return sukses
        return true;
    }
}
