<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\PembayaranEvent;
use Illuminate\Http\Request;
use App\Mail\TiketEventBerhasil;
use App\Http\Requests\Event\UpdateStatusBayarRequest;
use Exception;

class AdminPembayaranController extends Controller
{
    public function show($id)
    {
        $pembayaran = PembayaranEvent::with('pembelianEvent.pelanggan')->find($id);

        if (!$pembayaran) {
            return response()->json([
                'error' => true,
                'message' => 'Data pembayaran tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'error' => false,
            'data' => [
                'id_pembayaran' => $pembayaran->id_pembayaran,
                'id_pembelian' => $pembayaran->id_pembelian,
                'nama_pelanggan' => $pembayaran->pembelianEvent->pelanggan->nama_pelanggan ?? '-',
                'bank' => $pembayaran->bank,
                'tanggal_bayar' => $pembayaran->created_at->format('d-m-Y H:i'),
                'status_pembayaran' => $pembayaran->status_pembayaran,
                'bukti_bayar_event' => $pembayaran->bukti_bayar_event, // ⬅️ Tambahkan ini

            ]
        ]);
    }

    

public function updateStatusBayar(UpdateStatusBayarRequest $request, int $idPembayaran)
{
    $pembayaran = PembayaranEvent::find($idPembayaran);
    if (!$pembayaran) {
        return response()->json([
            'error' => true,
            'message' => 'Pembayaran tidak ditemukan',
        ], 404);
    }

    // Cek jika status sebelumnya bukan 'Menunggu Verifikasi', tolak
    if ($pembayaran->status_pembayaran !== 'Menunggu Verifikasi') {
        return response()->json([
            'error' => true,
            'message' => 'Status hanya bisa diubah jika masih Menunggu Verifikasi.',
        ], 400);
    }

    // Update pembayaran
    $pembayaran->update([
        'status_pembayaran' => $request->status_pembayaran,
        'tanggal_bayar' => $request->tanggal_bayar,
    ]);

    // Jika status pembayaran menjadi "Berhasil", ubah status_pembelian juga
    if ($request->status_pembayaran === 'Berhasil') {
        $pembelian = $pembayaran->pembelianEvent;

        if ($pembelian) {
            $pembelian->update([
                'status_pembelian' => 'Berhasil',
            ]);

            // Kirim email notifikasi tiket
           try {
                Log::info("Proses pengiriman email dimulai...");

                Mail::to($pembelian->pelanggan->email)->send(new TiketEventBerhasil($pembelian));

                Log::info("Email berhasil dikirim ke: " . $pembelian->pelanggan->email);
            } catch (Exception $e) {
                Log::error("Gagal kirim email: " . $e->getMessage());
            }

            
        }
    }

    return response()->json([
        'error' => false,
        'message' => 'Status pembayaran berhasil diperbarui',
        'data' => [
            'idPembayaranEvent' => $pembayaran->id_pembayaran,
            'statusPembayaranEvent' => $pembayaran->status_pembayaran,
            'tanggalBayarEvent' => $pembayaran->tanggal_bayar,
        ],
    ]);
}



}
