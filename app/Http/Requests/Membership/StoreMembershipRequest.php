<?php

namespace App\Http\Requests\Membership;

use Illuminate\Foundation\Http\FormRequest;

class StoreMembershipRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Pastikan sudah pakai auth guard jika perlu
    }

    public function rules(): array
    {
        return [
            'bank_pengirim' => 'required|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'bank_pengirim.required' => 'Bank pengirim wajib diisi.',
        ];
    }
}
