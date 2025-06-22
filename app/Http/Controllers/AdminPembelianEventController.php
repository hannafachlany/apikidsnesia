<?php

namespace App\Http\Controllers;

use App\Models\PembelianEvent;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminPembelianEventController extends Controller
{
   public function index()
    {
        $data = PembelianEvent::with(['pelanggan', 'pembayaran']) // tambahkan pembayaran
            ->where('is_checkout', true)
            ->orderByDesc('tanggal_pembelian')
            ->get();

        $result = $data->map(function ($pembelian) {
            return [
                'id_pembelian' => $pembelian->id_pembelian,
                'nama_pelanggan' => $pembelian->pelanggan->nama_pelanggan,
                'tanggal_pembelian' => $pembelian->tanggal_pembelian,
                'total_pembelian' => $pembelian->total_pembelian,
                'status_pembelian' => $pembelian->status_pembelian,
                'id_pembayaran' => optional($pembelian->pembayaran)->id_pembayaran,
            ];
        });

        return response()->json([
            'error' => false,
            'data' => $result
        ]);
    }


    public function show($id)
    {
        $pembelian = PembelianEvent::with(['detailEvent.event', 'pelanggan'])
            ->find($id);

        if (!$pembelian) {
            return response()->json([
                'error' => true,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $detail = $pembelian->detailEvent->map(function ($item, $i) {
            return [
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
            'detail' => [
                'id_pembelian' => $pembelian->id_pembelian,
                'id_pembayaran' => optional($pembelian->pembayaran)->id_pembayaran,
                'tanggal' => $pembelian->tanggal_pembelian,
                'nama_pelanggan' => $pembelian->pelanggan->nama_pelanggan,
                'total' => $pembelian->total_pembelian,
                'status' => $pembelian->status_pembelian,
                'detail_produk' => $detail
            ]
        ]);
    }
}
