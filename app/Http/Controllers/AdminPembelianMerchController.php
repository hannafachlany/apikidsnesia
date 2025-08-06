<?php

namespace App\Http\Controllers;

use App\Models\PembelianMerch;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AdminPembelianMerchController extends Controller
{
    // 1. Menampilkan semua data pembelian merchandise (yang sudah checkout)
    public function index(): JsonResponse
    {
        $pembelian = PembelianMerch::with(['pelanggan', 'pembayaran']) // 1.1 Ambil data pembelian beserta relasi pelanggan dan pembayaran
            ->where('is_checkout', 1) // 1.2 Hanya yang sudah checkout
            ->orderByDesc('created_at') // 1.3 Urutkan dari yang terbaru
            ->get();

        $result = $pembelian->map(function ($item) {
            return [ // 1.4 Format data untuk response ringkas
                'id_pembelianMerch' => $item->id_pembelianMerch,
                'nama_pelanggan' => optional($item->pelanggan)->nama_pelanggan ?? 'Tidak diketahui',
                'tanggal_pembelian' => Carbon::parse($item->created_at)->format('Y-m-d H:i:s'),
                'total_pembelian' => $item->total_pembelian,
                'status_pembelian' => $item->status_pembelian ?? 'Belum Bayar',
                'id_pembayaranMerch' => optional($item->pembayaran)->id_pembayaranMerch,
            ];
        });

        return response()->json([
            'error' => false,
            'data' => $result, // 1.5 Kembalikan data JSON hasil format
        ]);
    }

    // 2. Menampilkan detail pembelian merchandise berdasarkan ID
    public function show($id): JsonResponse
    {
        $pembelian = PembelianMerch::with(['detail.merchandise', 'pelanggan', 'pembayaran'])->find($id); // 2.1 Ambil pembelian beserta detail, pelanggan, dan pembayaran

        if (!$pembelian) {
            return response()->json(['error' => true, 'message' => 'Data tidak ditemukan'], 404); // 2.2 Jika tidak ada, kembalikan 404
        }

        // 2.3 Logging detail merchandise (opsional, bisa dihapus jika tidak dibutuhkan)
        foreach ($pembelian->detail as $item) {
            Log::info('Merchandise:', ['merch' => $item->merchandise]);
        }

        // 2.4 Mapping detail barang
        $detail = $pembelian->detail->map(function ($item, $i) {
            return [
                'nama_merchandise' => optional($item->merchandise)->nama_merchandise,
                'jumlah' => $item->jumlah,
                'harga' => $item->harga,
                'subtotal' => $item->subtotal,
            ];
        });

        // 2.5 Kembalikan JSON response dengan detail + info pembelian utama
        return response()->json([
            'error' => false,
            'detail' => $detail, // Daftar produk yang dibeli
            'info_pembelian' => [
                'id_pembelianMerch' => $pembelian->id_pembelianMerch,
                'id_pembayaranMerch' => optional($pembelian->pembayaran)->id_pembayaranMerch,
                'tanggal' => Carbon::parse($pembelian->created_at)->format('Y-m-d H:i:s'),
                'nama_pelanggan' => optional($pembelian->pelanggan)->nama_pelanggan ?? 'Tidak diketahui',
                'total' => $pembelian->total_pembelian,
                'status_pembelian' => $pembelian->status_pembelian ?? 'Belum Bayar',
            ],
        ]);
    }
}
