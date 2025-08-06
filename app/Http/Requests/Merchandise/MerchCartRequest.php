<?php

namespace App\Http\Requests\Merchandise;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class MerchCartRequest extends FormRequest
{
    protected $casts = [
        'itemsMerch' => 'array',
    ];

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'itemsMerch' => 'required|array|min:1',
            'itemsMerch.*.idMerch' => 'required|integer|exists:merchandise,id_merchandise',
            'itemsMerch.*.jumlah' => 'required|integer|min:1',
        ];
    }
    public function messages()
    {
        return [
            'itemsMerch.required' => 'Daftar merchandise tidak boleh kosong.',
            'itemsMerch.array' => 'Format itemsMerch harus berupa array.',
            'itemsMerch.*.idMerch.exists' => 'Merchandise dengan ID :input tidak ditemukan.',
            'itemsMerch.*.jumlah.min' => 'Jumlah merchandise minimal 1.',
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all(); // Ambil semua error sebagai array biasa (bukan keyed array)

        // Tangani khusus jika itemsMerch bukan array
        if (!is_array($this->input('itemsMerch'))) {
            throw new HttpResponseException(
                response()->json([
                    'error' => true,
                    'message' => 'Format itemsMerch tidak valid. Harus berupa array.'
                ], Response::HTTP_UNPROCESSABLE_ENTITY)
            );
        }

        // Tampilkan hanya pesan pertama
        throw new HttpResponseException(
            response()->json([
                'error' => true,
                'message' => $errors[0] ?? 'Validasi gagal.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }

}
