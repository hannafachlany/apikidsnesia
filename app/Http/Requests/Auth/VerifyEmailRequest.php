<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    // 1. Izinkan semua user
    public function authorize()
    {
        return true;
    }

    // 2. Validasi OTP (harus 6 digit string)
    public function rules()
    {
        return [
            'otp' => 'required|string|size:6',
        ];
    }

    // 3. Pesan kustom
    public function messages()
    {
        return [
            'otp.size' => 'Kode OTP harus 6 digit',
        ];
    }


}
