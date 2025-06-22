<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // import Log

class AdminDashboardController extends Controller
{
    public function getDashboardStats()
    {
        try {
            $jumlahPelanggan = DB::table('pelanggan')->count();
            $pembayaranEvent = DB::table('pembayaran_event')
                ->where('status_pembayaran', 'Berhasil')
                ->selectRaw('COUNT(*) as jumlah, SUM(total_harga) as total')
                ->first();
            $pembayaranMerchIds = DB::table('pembayaran_merchandise')
                ->where('status_pembayaran', 'Berhasil')
                ->pluck('id_pembelianMerch');
            $detailMerch = DB::table('detail_pembelian_merchandise')
                ->whereIn('id_pembelianMerch', $pembayaranMerchIds)
                ->selectRaw('SUM(jumlah) as total_terjual, SUM(subtotal) as total_rupiah')
                ->first();
            $pembelianEventIds = DB::table('pembelian_event')
                ->where('is_checkout', 1)
                ->pluck('id_pembelian');
            $tiketTerjual = DB::table('detail_pembelian_event')
                ->whereIn('id_pembelian', $pembelianEventIds)
                ->sum('jumlah');

            return response()->json([
                'error' => false,
                'message' => 'Statistik dashboard berhasil diambil',
                'data' => [
                    'jumlahPelanggan' => $jumlahPelanggan,
                    'pembayaranTiketEvent' => [
                        'jumlahPembayaran' => $pembayaranEvent->jumlah ?? 0,
                        'totalRupiah' => $pembayaranEvent->total ?? 0,
                    ],
                    'pembayaranMerch' => [
                        'jumlahBarangDibeli' => $detailMerch->total_terjual ?? 0,
                        'totalRupiah' => $detailMerch->total_rupiah ?? 0,
                    ],
                    'jumlahTiketTerjual' => $tiketTerjual,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('getDashboardStats error: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Gagal mengambil statistik.',
                'debug' => $e->getMessage()
            ], 500);
        }
    }

    public function getFilteredStats(Request $request)
    {
        $tanggal = $request->query('tanggal');
        if (!$tanggal) {
            return response()->json(['error' => true, 'message' => 'Tanggal tidak ditemukan.'], 400);
        }

        try {
            $start = Carbon::parse($tanggal)->subDays(30)->startOfDay();
            $end = Carbon::parse($tanggal)->endOfDay();

            // Pembelian Event: hanya yang sudah checkout dan status_pembelian = 'Berhasil'
            $pembelianEvent = DB::table('pembelian_event')
                ->where('is_checkout', 1)
                ->where('status_pembelian', 'Berhasil')
                ->whereBetween('tanggal_pembelian', [$start, $end])
                ->selectRaw('COUNT(*) as jumlah, SUM(total_pembelian) as total')
                ->first();

            // Pembelian Merchandise: hanya yang sudah checkout dan status_pembelian = 'Berhasil'
            $pembelianMerch = DB::table('pembelian_merchandise')
                ->where('is_checkout', 1)
                ->where('status_pembelian', 'Berhasil')
                ->whereBetween('tanggal_pembelian', [$start, $end])
                ->selectRaw('COUNT(*) as jumlah, SUM(total_pembelian) as total')
                ->first();

            return response()->json([
                'error' => false,
                'message' => 'Data berhasil diambil.',
                'data' => [
                    'pembelianEvent' => [
                        'jumlahPembelian' => $pembelianEvent->jumlah ?? 0,
                        'totalRupiah' => $pembelianEvent->total ?? 0,
                    ],
                    'pembelianMerch' => [
                        'jumlahPembelian' => $pembelianMerch->jumlah ?? 0,
                        'totalRupiah' => $pembelianMerch->total ?? 0,
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('getFilteredStats error: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Gagal mengambil data terfilter.',
                'debug' => $e->getMessage()
            ], 500);
        }
    }

}
