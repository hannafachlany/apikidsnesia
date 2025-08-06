<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Http\Requests\Auth\SendResetEmailRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        // 1. Inisialisasi service
        $this->authService = $authService;
    }

    // Fungsi register
    public function register(RegisterRequest $request)
    {
        try {
            // 1. Panggil service register dengan data tervalidasi
            $result = $this->authService->register($request->validated());

            // 2. Return respons sukses
            return response()->json([
                'message' => 'Registrasi berhasil! Silakan cek email untuk kode verifikasi.',
                'status' => 'sukses',
                'registerResult' => [
                    'email' => $result['email'],
                    'namaPelanggan' => $result['nama_pelanggan'],
                    'token_verifikasi' => $result['token_verifikasi'],
                    'otp' => $result['otp']
                ],
            ], 201);
        } catch (ValidationException $e) {
            // 3. Tangani error validasi email/password
            $errors = $e->validator->errors();

            if ($errors->has('email')) {
                return response()->json([
                    'message' => 'Email sudah dipakai',
                    'status' => 'error'
                ], 401);
            }

            if ($errors->has('password')) {
                return response()->json([
                    'message' => implode('. ', $errors->get('password')),
                    'status' => 'error',
                ], 422);
            }

            return response()->json([
                'message' => $errors->all(),
                'status' => 'error',
            ], 400);
        }
    }

    // Fungsi verifikasi email
    public function verifyEmail(VerifyEmailRequest $request)
    {
        try {
            // 1. Verifikasi OTP dan token melalui service
            $this->authService->verifyEmail($request->validated());

            // 2. Return respons sukses
            return response()->json([
                'message' => 'Verifikasi email berhasil!',
                'status' => 'sukses',
            ]);
        } catch (\Exception $e) {
            // 3. Tangani kesalahan verifikasi
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error',
            ], 422);
        }
    }

    // Fungsi kirim ulang OTP
    public function resendOtp(Request $request)
    {
        // 1. Ambil token dari header Authorization
        $tokenVerifikasi = $request->bearerToken();

        // 2. Validasi keberadaan token
        if (!$tokenVerifikasi) {
            return response()->json([
                'message' => 'Token verifikasi wajib dikirim.',
                'status' => 'error',
            ], 400);
        }

        try {
            // 3. Kirim ulang OTP via service
            $result = $this->authService->resendOtp($tokenVerifikasi);

            // 4. Return response sukses
            return response()->json([
                'message' => 'Kode OTP berhasil dikirim ulang.',
                'status' => 'sukses',
                'resendResult' => $result
            ]);
        } catch (\Exception $e) {
            // 5. Tangani error
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error',
            ], 400);
        }
    }

    // Fungsi login
    public function login(LoginRequest $request)
    {
        // 1. Panggil service login dengan data tervalidasi
        $user = $this->authService->login($request->validated());

        // 2. Cek jika login gagal karena user/password salah
        if ($user === null) {
            return response()->json([
                'error' => true,
                'message' => 'Email atau password salah',
            ], 401);
        }

        // 3. Cek jika email belum diverifikasi
        if ($user === 'not_verified') {
            return response()->json([
                'error' => true,
                'message' => 'Email belum diverifikasi, silakan cek email Anda.',
            ], 403);
        }

        // 4. Return respons sukses
        return response()->json([
            'error' => false,
            'message' => 'success',
            'loginResult' => [
                'email' => $user['email'],
                'namaPelanggan' => $user['nama_pelanggan'],
                'token' => $user['token'],
            ],
        ]);
    }

    // Fungsi kirim email reset password
    public function sendResetEmail(SendResetEmailRequest $request)
    {
        try {
            // 1. Kirim OTP reset password ke email
            $otp = $this->authService->sendResetEmail($request->validated());

            // 2. Return response sukses
            return response()->json([
                'message' => 'Kode OTP sudah dikirim ke email.',
                'status' => 'success',
                'otp' => $otp,
            ]);
        } catch (\Exception $e) {
            // 3. Tangani error
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error',
            ], 422);
        }
    }

    // Fungsi verifikasi OTP reset password
    public function verifyOtp(VerifyOtpRequest $request)
    {
        try {
            // 1. Verifikasi OTP dan generate token reset
            $result = $this->authService->verifyOtp($request->validated());

            // 2. Return token reset untuk ubah password
            return response()->json([
                'error' => false,
                'message' => 'success',
                'resetResult' => [
                    'email' => $result['email'],
                    'token_reset' => $result['token_reset'],
                ],
            ]);
        } catch (\Exception $e) {
            // 3. Tangani error verifikasi OTP
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // Fungsi reset password
    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            // 1. Gabungkan email dari route/body dengan data password baru
            $data = array_merge($request->validated(), ['email' => $request->email]);

            // 2. Panggil service reset password
            $this->authService->resetPassword($data);

            // 3. Return response sukses
            return response()->json([
                'message' => 'Password berhasil diubah.',
                'status' => 'success',
            ]);
        } catch (ValidationException $e) {
            // 4. Tangani validasi gagal
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
                'status' => 'error',
            ], 422);
        } catch (\Exception $e) {
            // 5. Tangani error lain dari service
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error',
            ], 422);
        }
    }

    // Fungsi logout
    public function logout(LogoutRequest $request)
    {
        // 1. Ambil pelanggan dari request
        $pelanggan = $request->pelanggan;

        // 2. Kosongkan token dan expired-nya
        $pelanggan->token = null;
        $pelanggan->token_expired_at = null;
        $pelanggan->save();

        // 3. Return response sukses
        return response()->json([
            'message' => 'Logout berhasil',
            'status' => 'sukses',
        ]);
    }
}
