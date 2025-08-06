<?php

namespace App\Http\Controllers;

use App\Models\PembayaranMerch;
use App\Http\Requests\Merchandise\UpdateStatusBayarMerchRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\PembayaranMerchBerhasil;
use App\Mail\PembayaranMerchGagal;
use Exception;

class AdminPembayaranMerchController extends Controller
{
    // 1. Menampilkan detail pembayaran merchandise berdasarkan ID
    public function show($id)
    {
        $pembayaran = PembayaranMerch::with('pembelian', 'pelanggan')->find($id); //1.1 Ambil data pembayaran beserta relasi pembelian dan pelanggan

        if (!$pembayaran) {
            return response()->json([
                'error' => true,
                'message' => 'Data pembayaran tidak ditemukan'
            ], 404); //1.2 Jika tidak ditemukan, kirim error 404
        }

        return response()->json([
            'error' => false,
            'data' => [ //1.3 Kembalikan data pembayaran merchandise
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

    // 2. Memperbarui status pembayaran merchandise
    public function updateStatus(UpdateStatusBayarMerchRequest $request, int $idPembayaran)
    {
        $pembayaran = PembayaranMerch::find($idPembayaran); //2.1 Cari data pembayaran berdasarkan ID

        if (!$pembayaran) {
            return response()->json([
                'error' => true,
                'message' => 'Pembayaran tidak ditemukan',
            ], 404); //2.2 Jika tidak ditemukan, kirim response error
        }

        // 2.3 Tidak boleh ubah status jika sudah "Berhasil"
        if ($pembayaran->status_pembayaran === 'Berhasil') {
            return response()->json([
                'error' => true,
                'message' => 'Status tidak dapat diubah karena sudah berhasil.',
            ], 400);
        }

        $pembayaran->update([
            'status_pembayaran' => $request->status_pembayaran,
            'tanggal_bayar' => $request->tanggal_bayar,
        ]); //2.5 Simpan status pembayaran dan tanggal bayar

        $pembelian = $pembayaran->pembelian; //2.6 Ambil data pembelian yang terkait

        if ($pembelian) {
            if ($request->status_pembayaran === 'Berhasil') {
                $pembelian->update(['status_pembelian' => 'Berhasil']); //2.7 Jika status Berhasil, update status pembelian
                Log::info("ADMIN - Status pembelian ID {$pembelian->id_pembelianMerch} diubah menjadi Berhasil");

                try {
                    Mail::to($pembelian->pelanggan->email)->send(new PembayaranMerchBerhasil($pembelian)); //2.10 Kirim email berhasil
                } catch (Exception $e) {
                    Log::error("Gagal kirim email merchandise: " . $e->getMessage()); //2.11 Log jika email gagal dikirim
                }

            } elseif ($request->status_pembayaran === 'Gagal') {
                $pembelian->update(['status_pembelian' => 'Gagal']); //2.12 Jika status Gagal, update status pembelian jadi Gagal

                try {
                    Mail::to($pembelian->pelanggan->email)->send(new PembayaranMerchGagal($pembelian)); //2.13 Kirim email pemberitahuan gagal bayar
                } catch (Exception $e) {
                    Log::error("Gagal kirim email gagal merch: " . $e->getMessage()); //2.14 Log jika gagal kirim email
                }
            }
        }

        return response()->json([
            'error' => false,
            'message' => 'Status pembayaran berhasil diperbarui',
            'data' => $pembayaran, //2.15 Kembalikan data pembayaran setelah diperbarui
        ]);
    }
}
