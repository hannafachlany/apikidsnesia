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

    public function getLaporan(Request $request)
    {
        $tglm = $request->query('tglm');
        $tgls = $request->query('tgls');

        if (!$tglm || !$tgls) {
            return response()->json([
                'error' => true,
                'message' => 'Tanggal mulai dan selesai wajib diisi.'
            ], 400);
        }

        try {
            $start = Carbon::parse($tglm)->startOfDay();
            $end = Carbon::parse($tgls)->endOfDay();

            // 1. Pembelian Event
            $event = DB::table('pembelian_event')
                ->select('nama_pelanggan', 'tanggal_pembelian', 'total_pembelian', 'status_pembelian')
                ->where('status_pembelian', 'Berhasil')
                ->whereBetween('tanggal_pembelian', [$start, $end])
                ->orderBy('tanggal_pembelian', 'desc')
                ->get();

            // 2. Pembelian Merchandise
            $merch = DB::table('pembelian_merchandise')
                ->join('pelanggan', 'pelanggan.id_pelanggan', '=', 'pembelian_merchandise.id_pelanggan')
                ->select('pelanggan.nama_pelanggan', 'pembelian_merchandise.tanggal_pembelian', 'pembelian_merchandise.total_pembelian', 'pembelian_merchandise.status_pembelian')
                ->where('pembelian_merchandise.status_pembelian', 'Berhasil')
                ->whereBetween('pembelian_merchandise.tanggal_pembelian', [$start, $end])
                ->orderBy('pembelian_merchandise.tanggal_pembelian', 'desc')
                ->get();

            // 3. Pembayaran Membership
           $membership = DB::table('pembayaran_membership')
                ->select(
                    'nama_pelanggan',
                    'waktu_transfer',
                    'jumlah_transfer',
                    'status_pembayaran'
                )
                ->where('pembayaran_membership.status_pembayaran', 'Berhasil')
                ->whereBetween('pembayaran_membership.waktu_transfer', [$start, $end])
                ->orderBy('pembayaran_membership.waktu_transfer', 'desc')
                ->get();


            return response()->json([
                'error' => false,
                'message' => 'Laporan berhasil diambil.',
                'data' => [
                    'event' => $event,
                    'merchandise' => $merch,
                    'membership' => $membership,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal ambil laporan: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Terjadi kesalahan saat mengambil data.',
                'debug' => $e->getMessage()
            ], 500);
        }
    }

}
