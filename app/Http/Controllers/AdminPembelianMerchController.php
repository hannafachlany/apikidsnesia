<?php

namespace App\Http\Controllers;

use App\Models\PembelianMerch;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
class AdminPembelianMerchController extends Controller
{
    public function index(): JsonResponse
    {
        $pembelian = PembelianMerch::with(['pelanggan', 'pembayaran'])
            ->where('is_checkout', 1)
            ->orderByDesc('created_at')
            ->get();

        $result = $pembelian->map(function ($item) {
            return [
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
            'data' => $result,
        ]);
    }

    public function show($id): JsonResponse
    {
       $pembelian = PembelianMerch::with(['detail.merchandise', 'pelanggan', 'pembayaran'])->find($id);

        if (!$pembelian) {
            return response()->json(['error' => true, 'message' => 'Data tidak ditemukan'], 404);
        }

        foreach ($pembelian->detail as $item) {
            Log::info('Merchandise:', ['merch' => $item->merchandise]);
        }


        $detail = $pembelian->detail->map(function ($item, $i) {
            return [
                
                'nama_merchandise' => optional($item->merchandise)->nama_merchandise,
                'jumlah' => $item->jumlah,
                'harga' => $item->harga,
                'subtotal' => $item->subtotal,
            ];
        });

        
        return response()->json([
            'error' => false,
            'detail' => $detail,  // langsung array detail merchandise
            'info_pembelian' => [
                'nama_merchandise' => optional($item->merchandise)->nama_merchandise,
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
