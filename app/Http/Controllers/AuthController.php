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

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    // Fungsi register
    public function register(RegisterRequest $request)
    {
        try {
            $result = $this->authService->register($request->validated());

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
            $this->authService->verifyEmail($request->validated());

            return response()->json([
                'message' => 'Verifikasi email berhasil!',
                'status' => 'sukses',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error',
            ], 422);
        }
    }
    public function resendOtp(Request $request)
    {
        $tokenVerifikasi = $request->bearerToken(); // ← ambil dari header Authorization

        if (!$tokenVerifikasi) {
            return response()->json([
                'message' => 'Token verifikasi wajib dikirim.',
                'status' => 'error',
            ], 400);
        }

        try {
            $result = $this->authService->resendOtp($tokenVerifikasi);

            return response()->json([
                'message' => 'Kode OTP berhasil dikirim ulang.',
                'status' => 'sukses',
                'resendResult' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error',
            ], 400);
        }
    }


    // Fungsi login
    public function login(LoginRequest $request)
    {
        $user = $this->authService->login($request->validated());

        if ($user === null) {
            return response()->json([
                'error' => true,
                'message' => 'Email atau password salah',
            ], 401);
        }

        if ($user === 'not_verified') {
            return response()->json([
                'error' => true,
                'message' => 'Email belum diverifikasi, silakan cek email Anda.',
            ], 403);
        }

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
            $otp = $this->authService->sendResetEmail($request->validated()); // 🟢 simpan hasil return service

            return response()->json([
                'message' => 'Kode OTP sudah dikirim ke email.',
                'status' => 'success',
                'otp' => $otp, // 🟢 tambahkan ke response
            ]);
        } catch (\Exception $e) {
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
            $result = $this->authService->verifyOtp($request->validated());

            return response()->json([
                'error' => false,
                'message' => 'success',
                'resetResult' => [
                    'email' => $result['email'],
                    'token_reset' => $result['token_reset'],
                ],
            ]);
        } catch (\Exception $e) {
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
            $data = array_merge($request->validated(), ['email' => $request->email]);
            $this->authService->resetPassword($data);

            return response()->json([
                'message' => 'Password berhasil diubah.',
                'status' => 'success',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error',
            ], 422);
        }
    }


    // Fungsi logout
    public function logout(LogoutRequest $request)
    {
        $pelanggan = $request->pelanggan;

        $pelanggan->token = null;
        $pelanggan->token_expired_at = null;
        $pelanggan->save();

        return response()->json([
            'message' => 'Logout berhasil',
            'status' => 'sukses',
        ]);
    }
}
