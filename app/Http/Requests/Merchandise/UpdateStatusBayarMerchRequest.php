<?php

namespace App\Http\Requests\Merchandise;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusBayarMerchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status_pembayaran' => 'required|in:Menunggu Pembayaran,Berhasil,Gagal',
            'tanggal_bayar' => 'required|date_format:Y-m-d H:i:s',
        ];
    }

    public function messages(): array
    {
        return [
            'status_pembayaran.required' => 'Status pembayaran wajib diisi.',
            'status_pembayaran.in' => 'Status pembayaran tidak valid.',
            'tanggal_bayar.required' => 'Tanggal bayar wajib diisi.',
            'tanggal_bayar.date_format' => 'Format tanggal harus Y-m-d H:i:s.',
        ];
    }
}
