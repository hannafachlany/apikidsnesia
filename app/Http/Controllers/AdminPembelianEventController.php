<?php

namespace App\Http\Controllers;

use App\Models\PembelianEvent;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminPembelianEventController extends Controller
{
    // 1. Menampilkan semua data pembelian event (yang sudah checkout)
    public function index()
    {
        $data = PembelianEvent::with(['pelanggan', 'pembayaran']) // 1.1 Ambil data pembelian beserta pelanggan dan pembayaran
            ->where('is_checkout', true) // 1.2 Hanya ambil data yang sudah checkout
            ->orderByDesc('tanggal_pembelian') // 1.3 Urutkan dari yang terbaru
            ->get();

        $result = $data->map(function ($pembelian) {
            return [ // 1.4 Format data untuk response
                'id_pembelian' => $pembelian->id_pembelian,
                'nama_pelanggan' => $pembelian->nama_pelanggan,
                'tanggal_pembelian' => $pembelian->tanggal_pembelian,
                'total_pembelian' => $pembelian->total_pembelian,
                'status_pembelian' => $pembelian->status_pembelian,
                'id_pembayaran' => optional($pembelian->pembayaran)->id_pembayaran, // 1.5 Gunakan optional jika tidak ada relasi pembayaran
            ];
        });

        return response()->json([
            'error' => false,
            'data' => $result // 1.6 Kembalikan hasil dalam bentuk JSON
        ]);
    }

    // 2. Menampilkan detail pembelian event berdasarkan ID
    public function show($id)
    {
        $pembelian = PembelianEvent::with(['detailEvent.event', 'pelanggan']) // 2.1 Ambil relasi detail_event, event, dan pelanggan
            ->find($id);

        if (!$pembelian) {
            return response()->json([
                'error' => true,
                'message' => 'Data tidak ditemukan'
            ], 404); // 2.2 Jika tidak ditemukan, kembalikan error 404
        }

        $detail = $pembelian->detailEvent->map(function ($item, $i) {
            return [ // 2.3 Format data detail event
                'no' => $i + 1,
                'nama_event' => $item->event->nama_event,
                'jadwal_event' => Carbon::parse($item->event->tanggal_event)->format('d-m-Y'),
                'jumlah' => $item->jumlah,
                'harga' => $item->event->harga_event,
                'subtotal' => $item->jumlah * $item->event->harga_event
            ];
        });

        return response()->json([
            'error' => false,
            'detail' => [ // 2.4 Format data utama pembelian
                'id_pembelian' => $pembelian->id_pembelian,
                'id_pembayaran' => optional($pembelian->pembayaran)->id_pembayaran,
                'tanggal' => $pembelian->tanggal_pembelian,
                'nama_pelanggan' => $pembelian->nama_pelanggan,
                'total' => $pembelian->total_pembelian,
                'status' => $pembelian->status_pembelian,
                'detail_produk' => $detail // 2.5 Sertakan detail produk (event-event yang dibeli)
            ]
        ]);
    }
}
