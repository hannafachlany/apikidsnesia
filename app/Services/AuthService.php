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
    public function register(array $data)
    {
        // 1. Simpan pelanggan
        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => $data['nama_pelanggan'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'no_hp_pelanggan' => $data['no_hp_pelanggan'],
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

   public function verifyEmail(array $data)
    {
        $token = request()->bearerToken();

        $record = EmailVerification::where('token_verifikasi', $token)->first();

        if (!$record) {
            throw new \Exception('Token verifikasi tidak valid.');
        }

        // Cek OTP dan masa aktif
        $expired = Carbon::parse($record->created_at)->addMinutes(15);
        if ($record->otp !== $data['otp']) {
            throw new \Exception('Kode OTP salah.');
        }

        if (Carbon::now()->gt($expired)) {
            throw new \Exception('Kode OTP sudah kadaluarsa.');
        }

        // Update pelanggan
        Pelanggan::where('email', $record->email)->update([
            'email_verified_at' => now(),
        ]);

        // Hapus token verifikasi dari database
        $record->delete();

        return true;
    }

   
    public function resendOtp(string $tokenVerifikasi): array
    {
        $verifikasi = EmailVerification::where('token_verifikasi', $tokenVerifikasi)->first();

        if (!$verifikasi) {
            throw new \Exception('Token verifikasi tidak ditemukan.');
        }

        $otpBaru = rand(100000, 999999);
       

        $verifikasi->update([
            'otp' => $otpBaru,
            'created_at' => now(),
        ]);

        Mail::raw("Kode OTP verifikasi baru Anda adalah: $otpBaru", function ($message) use ($verifikasi) {
            $message->to($verifikasi->email)
                    ->subject('Kode Verifikasi Ulang Kidsnesia');
        });

        return [
            'email' => $verifikasi->email,
            'otp' => $otpBaru,
            'token_verifikasi' => $verifikasi->token_verifikasi,
        ];
    }

    // fungsi login
    public function login(array $data)
    {
        $pelanggan = Pelanggan::where('email', $data['email'])->first();

        // Cek user dan password
        if (!$pelanggan || !Hash::check($data['password'], $pelanggan->password)) {
            return null; // login gagal
        }

        // Cek apakah email sudah diverifikasi
        if (!$pelanggan->email_verified_at) {
            return 'not_verified'; // khusus untuk email belum verifikasi
        }

        // Generate token dan set expired
        $token = Str::random(60);
        $expiredAt = Carbon::now()->addDays(30);

        $pelanggan->token = $token;
        $pelanggan->token_expired_at = $expiredAt;
        $pelanggan->save();

        return [
            'email' => $pelanggan->email,
            'nama_pelanggan' => $pelanggan->nama_pelanggan,
            'token' => $token,
            'token_expired_at' => $expiredAt->toDateTimeString(),
        ];
    }
//Str::random(60);
    //kode otp dikirm ke email
    public function sendResetEmail(array $data)
    {
        $otp = rand(100000, 999999);

        PasswordReset::updateOrCreate(
            ['email' => $data['email']],
            [
                'otp' => $otp,
                'created_at' => now(),
            ]
        );

        Mail::raw("Kode OTP reset password Anda: $otp", function ($message) use ($data) {
            $message->to($data['email'])
                    ->subject('Reset Password Kidsnesia');
        });

        return $otp; // 🟢 return OTP
    }

    //verifikasi otp utk reset password
    public function verifyOtp(array $data)
    {
        $record = PasswordReset::where('email', $data['email'])->first();

        if (!$record) {
            throw new \Exception('OTP tidak ditemukan.');
        }

        $expiredAt = Carbon::parse($record->created_at)->addMinutes(15);

        if ($record->otp !== $data['otp']) {
            throw new \Exception('OTP salah.');
        }

       if (Carbon::now()->gt($expiredAt)) {
            throw new \Exception('OTP sudah kadaluarsa.');
        }

        $pelanggan = Pelanggan::where('email', $data['email'])->first();

        if (!$pelanggan) {
            throw new \Exception('Data pelanggan tidak ditemukan.');
        }

        // Generate token reset password dan simpan di kolom token_reset
        $tokenReset = Str::random(60);

        $record->token_reset = $tokenReset;
        $record->save();

        return [
            'email' => $pelanggan->email,
            'token_reset' => $tokenReset,
        ];
    }


    //reset password
    public function resetPassword(array $data)
    {
        $user = Pelanggan::where('email', $data['email'])->first();

        if (!$user) {
            throw new \Exception('Email tidak ditemukan.');
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        // Hapus record OTP dan token reset setelah reset password berhasil
        PasswordReset::where('email', $data['email'])->delete();

        return true;
    }
}
