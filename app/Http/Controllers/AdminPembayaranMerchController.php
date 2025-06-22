<?php

namespace App\Http\Controllers;

use App\Models\PembayaranMerch;
use Illuminate\Http\Request;
use App\Http\Requests\Merchandise\UpdateStatusBayarMerchRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\PembayaranMerchBerhasil;
use Exception;


class AdminPembayaranMerchController extends Controller
{
    public function show($id)
    {
        $pembayaran = PembayaranMerch::with('pembelian', 'pelanggan')->find($id);

        if (!$pembayaran) {
            return response()->json([
                'error' => true,
                'message' => 'Data pembayaran tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'error' => false,
            'data' => [
                'id_pembayaranMerch' => $pembayaran->id_pembayaranMerch,
                'id_pembelianMerch' => $pembayaran->id_pembelianMerch,
                'nama_pelanggan' => $pembayaran->pelanggan->nama_pelanggan ?? '-',
                'bank' => $pembayaran->bank,
                'tanggal_bayar' => $pembayaran->created_at->format('d-m-Y H:i'),
                'status_pembayaran' => $pembayaran->status_pembayaran,
                'bukti_bayar_merch' => $pembayaran->bukti_bayar_merch,
            ]
        ]);
    }
     // PATCH update status bayar
    public function updateStatus(UpdateStatusBayarMerchRequest $request, int $idPembayaran)
{
    $pembayaran = PembayaranMerch::find($idPembayaran);
    if (!$pembayaran) {
        return response()->json([
            'error' => true,
            'message' => 'Pembayaran tidak ditemukan',
        ], 404);
    }

    Log::info("ADMIN - Update status pembayaran ID {$idPembayaran} menjadi {$request->status_pembayaran}");

        $pembayaran->update([
            'status_pembayaran' => $request->status_pembayaran,
            'tanggal_bayar' => $request->tanggal_bayar,
        ]);

        if ($request->status_pembayaran === 'Berhasil') {
            $pembelian = $pembayaran->pembelian;
            if ($pembelian) {
                $pembelian->update(['status_pembelian' => 'Berhasil']);
                Log::info("ADMIN - Status pembelian ID {$pembelian->id_pembelianMerch} juga diubah menjadi Berhasil");

                // Kirim email notifikasi
                try {
                    Log::info("Pelanggan: " . json_encode($pembelian->pelanggan));
                    Log::info("Detail merchandise: " . json_encode($pembelian->detailMerchandise));
                    Mail::to($pembelian->pelanggan->email)->send(new PembayaranMerchBerhasil($pembelian));
                    Log::info("Email merchandise berhasil dikirim ke {$pembelian->pelanggan->email}");
                } catch (Exception $e) {
                    Log::error("Gagal kirim email merchandise: " . $e->getMessage());
                }
            }
        }

        return response()->json([
            'error' => false,
            'message' => 'Status pembayaran berhasil diperbarui',
            'data' => $pembayaran,
        ]);
    }

}
