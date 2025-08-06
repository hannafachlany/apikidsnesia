<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    // 1. Izinkan semua user mengakses request ini
    public function authorize(): bool
    {
        return true;
    }

    // 2. Aturan validasi untuk login
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required'
        ];
    }

}
