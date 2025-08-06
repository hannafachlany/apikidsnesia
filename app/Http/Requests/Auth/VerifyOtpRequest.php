<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    // 1. Izinkan semua user akses
     public function authorize()
    {
        return true;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    // 2. Validasi email dan OTP
    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:password_resets,email',
            'otp' => 'required|string',
        ];
    }

}
