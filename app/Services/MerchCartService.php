<?php

namespace App\Services;

use App\Models\PembelianMerch;
use App\Models\DetailPembelianMerch;
use App\Models\Merchandise;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MerchCartService
{
    public function storeCart(Request $request)
    {
        $user = $request->user();
        $items = $request->input('itemsMerch');
        if (!is_array($items)) {
            return response()->json([
                'error' => true,
                'message' => 'Format itemsMerch tidak valid. Harus array.'
            ], 422);
        }

        try {
            $result = $this->createCart($user->id_pelanggan, $items);
            return response()->json([
                'error' => false,
                'message' => 'Cart berhasil dibuat',
                'pembelianMerchResponse' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Gagal membuat cart: ' . $e->getMessage()
            ], 500);
        }
    }

    public function listCartByUser(Request $request)
    {
        $user = $request->user();
        try {
            $result = $this->listCart($user->id_pelanggan);
            return response()->json([
                'error' => false,
                'listCartMerch' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCartDetail($idPembelian)
    {
        try {
            $result = $this->getCartDetailById($idPembelian);

            if (!$result) {
                return response()->json(['error' => true, 'message' => 'Cart tidak ditemukan'], 404);
            }

            return response()->json(['error' => false, 'cartDetail' => $result]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Gagal mengambil detail cart: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteCartById($idPembelian)
    {
        try {
            $deleted = $this->deleteCart($idPembelian);

            if (!$deleted) {
                return response()->json(['error' => true, 'message' => 'Cart tidak ditemukan atau sudah checkout'], 404);
            }

            return response()->json(['error' => false, 'message' => 'Cart berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => 'Gagal menghapus cart: ' . $e->getMessage()], 500);
        }
    }

    public function checkoutCart(Request $request, $idPembelian)
    {
        $user = $request->user();
        $items = $request->input('itemsMerch');

        if (!is_array($items)) {
            return response()->json([
                'error' => true,
                'message' => 'Format itemsMerch tidak valid. Harus array.'
            ], 422);
        }

        try {
            $result = $this->doCheckout($idPembelian, $user->id_pelanggan, $items);

            return response()->json([
                'error' => false,
                'message' => 'Checkout berhasil',
                'pembelianMerchResponse' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Checkout gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    // === Logic Murni ===

    public function createCart($idPelanggan, $items)
    {
        return DB::transaction(function () use ($idPelanggan, $items) {
            $total = 0;
            foreach ($items as $item) {
                $merch = Merchandise::findOrFail($item['idMerch']);
                $total += $merch->harga_merchandise * $item['jumlah'];
            }

            $pembelian = PembelianMerch::create([
                'id_pelanggan' => $idPelanggan,
                'tanggal_pembelian' => null,
                'status_pembelian' => 'Cart',
                'is_checkout' => 0,
                'total_pembelian' => $total,
            ]);

            $detail = [];
            foreach ($items as $item) {
                $merch = Merchandise::findOrFail($item['idMerch']);
                $jumlah = $item['jumlah'];
                $subtotal = $merch->harga_merchandise * $jumlah;

                $dp = DetailPembelianMerch::create([
                    'id_pembelianMerch' => $pembelian->id_pembelianMerch,
                    'id_merchandise' => $merch->id_merchandise,
                    'jumlah' => $jumlah,
                    'harga' => $merch->harga_merchandise,
                    'subtotal' => $subtotal,
                ]);

                $detail[] = [
                    'idDetailPembelianMerch' => $dp->id_detail_pembelianMerch,
                    'namaMerch' => $merch->nama_merchandise,
                    'jumlahMerch' => $jumlah,
                    'hargaMerch' => $merch->harga_merchandise,
                    'subtotalMerch' => $subtotal,
                ];
            }

            return [
                'idPembelianMerch' => $pembelian->id_pembelianMerch,
                'tanggalPembelianMerch' => $pembelian->tanggal_pembelian,
                'totalPembelianMerch' => $total,
                'statusPembelianMerch' => $pembelian->status_pembelian,
                'detailMerch' => $detail,
            ];
        });
    }

    public function listCart($idPelanggan)
    {
        return PembelianMerch::with(['detail.merchandise'])
            ->where('id_pelanggan', $idPelanggan)
            ->where('is_checkout', 0)
            ->get()
            ->map(function ($pembelian) {
                return [
                    'idPembelianMerch' => $pembelian->id_pembelianMerch,
                    'tanggalPembelianMerch' => $pembelian->tanggal_pembelian,
                    'totalPembelianMerch' => $pembelian->total_pembelian,
                    'statusPembelianMerch' => $pembelian->status_pembelian,
                    'detailMerch' => $pembelian->detail->map(function ($item) {
                        return [
                            'idDetailPembelianMerch' => $item->id_detail_pembelianMerch,
                            'namaMerch' => $item->merchandise->nama_merchandise,
                            'jumlahMerch' => $item->jumlah,
                            'hargaMerch' => $item->harga,
                            'subtotalMerch' => $item->subtotal,
                        ];
                    }),
                ];
            });
    }

    public function getCartDetailById($idPembelian)
    {
        $pembelian = PembelianMerch::with(['detail.merchandise'])->find($idPembelian);

        if (!$pembelian || $pembelian->is_checkout == 1) {
            return null;
        }

        return [
            'idPembelianMerch' => $pembelian->id_pembelianMerch,
            'tanggalPembelianMerch' => $pembelian->tanggal_pembelian,
            'totalPembelianMerch' => $pembelian->total_pembelian,
            'statusPembelianMerch' => $pembelian->status_pembelian,
            'detailMerch' => $pembelian->detail->map(function ($item) {
                return [
                    'idDetailPembelianMerch' => $item->id_detail_pembelianMerch,
                    'namaMerch' => $item->merchandise->nama_merchandise,
                    'jumlahMerch' => $item->jumlah,
                    'hargaMerch' => $item->harga,
                    'subtotalMerch' => $item->subtotal,
                ];
            }),
        ];
    }

    public function deleteCart($idPembelian)
    {
        $pembelian = PembelianMerch::where('id_pembelianMerch', $idPembelian)
            ->where('is_checkout', 0)
            ->first();

        if (!$pembelian) return false;

        $pembelian->detail()->delete();
        $pembelian->delete();

        return true;
    }

    public function doCheckout($idPembelian, $idPelanggan, $items)
    {
        return DB::transaction(function () use ($idPembelian, $idPelanggan, $items) {
            $pembelian = PembelianMerch::where('id_pembelianMerch', $idPembelian)
                ->where('id_pelanggan', $idPelanggan)
                ->where('is_checkout', 0)
                ->firstOrFail();

            $total = 0;

            foreach ($items as $item) {
                $merch = Merchandise::findOrFail($item['idMerch']);
                $jumlah = $item['jumlah'];
                $subtotal = $merch->harga_merchandise * $jumlah;

                DetailPembelianMerch::where('id_pembelianMerch', $idPembelian)
                    ->where('id_merchandise', $merch->id_merchandise)
                    ->update([
                        'jumlah' => $jumlah,
                        'subtotal' => $subtotal,
                    ]);

                $total += $subtotal;
            }

            $pembelian->total_pembelian = $total;
            $pembelian->is_checkout = 1;
            $pembelian->status_pembelian = 'Belum Bayar';
            $pembelian->tanggal_pembelian = Carbon::now();
            $pembelian->save();

            return [
                'idPembelianMerch' => $pembelian->id_pembelianMerch,
                'tanggalPembelianMerch' => $pembelian->tanggal_pembelian,
                'totalPembelianMerch' => $total,
                'statusPembelianMerch' => $pembelian->status_pembelian,
                'detailMerch' => $pembelian->detail->map(function ($item) {
                    return [
                        'idDetailPembelianMerch' => $item->id_detail_pembelianMerch,
                        'namaMerch' => $item->merchandise->nama_merchandise,
                        'jumlahMerch' => $item->jumlah,
                        'hargaMerch' => $item->harga,
                        'subtotalMerch' => $item->subtotal,
                    ];
                }),
            ];
        });
    }
}
