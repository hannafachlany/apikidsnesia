<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LogoutRequest extends FormRequest
{
    public function authorize()
    {
        // Pastikan hanya user yang sudah login (token valid) yang bisa akses
        return $this->user() != null;
    }

    public function rules()
    {
        return [
            // logout biasanya gak butuh body, jadi kosong
        ];
    }

    // Bisa juga validasi header token pakai method prepareForValidation() jika mau
}
