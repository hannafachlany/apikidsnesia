<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\PembayaranEvent;
use Illuminate\Http\Request;
use App\Mail\TiketEventBerhasil;
use App\Mail\PembayaranEventGagal;
use App\Http\Requests\Event\UpdateStatusBayarRequest;
use Exception;

class AdminPembayaranController extends Controller
{
    // 1. Menampilkan detail pembayaran event berdasarkan ID
    public function show($id)
    {
        $pembayaran = PembayaranEvent::with('pembelianEvent.pelanggan')->find($id); //1.1 Ambil pembayaran beserta pembelian & pelanggan

        if (!$pembayaran) {
            return response()->json([
                'error' => true,
                'message' => 'Data pembayaran tidak ditemukan'
            ], 404); //1.2 Jika tidak ditemukan, kirim error
        }

        return response()->json([
            'error' => false,
            'data' => [ //1.3 Kembalikan data pembayaran
                'id_pembayaran' => $pembayaran->id_pembayaran,
                'id_pembelian' => $pembayaran->id_pembelian,
                'nama_pelanggan' => $pembayaran->nama_pelanggan ?? '-', //1.4 Jika nama tidak tersedia, beri tanda '-'
                'bank' => $pembayaran->bank,
                'tanggal_bayar' => $pembayaran->created_at->format('d-m-Y H:i'),
                'status_pembayaran' => $pembayaran->status_pembayaran,
                'bukti_bayar_event' => $pembayaran->bukti_bayar_event, //1.5 Sertakan bukti transfer
            ]
        ]);
    }

    // 2. Memperbarui status pembayaran event (Berhasil/Gagal)
    public function updateStatusBayar(UpdateStatusBayarRequest $request, int $idPembayaran)
    {
        $pembayaran = PembayaranEvent::find($idPembayaran); //2.1 Cari data pembayaran

        if (!$pembayaran) {
            return response()->json([
                'error' => true,
                'message' => 'Pembayaran tidak ditemukan',
            ], 404); //2.2 Jika tidak ditemukan
        }

        if ($pembayaran->status_pembayaran === 'Berhasil') {
            return response()->json([
                'error' => true,
                'message' => 'Status tidak dapat diubah karena sudah berhasil.',
            ], 400); //2.3 Jika sudah berhasil, tidak boleh diubah lagi
        }

        $pembayaran->update([
            'status_pembayaran' => $request->status_pembayaran,
            'tanggal_bayar' => $request->tanggal_bayar,
        ]); //2.4 Update status dan tanggal bayar

        $pembelian = $pembayaran->pembelianEvent; //2.5 Ambil relasi pembelian event

        if ($pembelian) {
            if ($request->status_pembayaran === 'Berhasil') {
                $pembelian->update([
                    'status_pembelian' => 'Berhasil',
                ]); //2.6 Jika pembayaran berhasil, update status pembelian

                try {
                    Log::info("Proses pengiriman email dimulai...");
                    Mail::to($pembelian->pelanggan->email)->send(new TiketEventBerhasil($pembelian)); //2.7 Kirim email tiket berhasil
                    Log::info("Email berhasil dikirim ke: " . $pembelian->pelanggan->email);
                } catch (Exception $e) {
                    Log::error("Gagal kirim email: " . $e->getMessage()); //2.8 Log jika gagal kirim
                }

            } elseif ($request->status_pembayaran === 'Gagal') {
                $pembelian->update([
                    'status_pembelian' => 'Gagal',
                ]); //2.9 Jika gagal, update status pembelian

                try {
                    Mail::to($pembelian->pelanggan->email)->send(new PembayaranEventGagal($pembelian)); //2.10 Kirim email gagal bayar
                } catch (Exception $e) {
                    Log::error("Gagal kirim email gagal bayar: " . $e->getMessage()); //2.11 Log error
                }
            }
        }

        return response()->json([
            'error' => false,
            'message' => 'Status pembayaran berhasil diperbarui',
            'data' => [ //2.12 Response data ringkas
                'idPembayaranEvent' => $pembayaran->id_pembayaran,
                'statusPembayaranEvent' => $pembayaran->status_pembayaran,
                'tanggalBayarEvent' => $pembayaran->tanggal_bayar,
            ],
        ]);
    }
}
