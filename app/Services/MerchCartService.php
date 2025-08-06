<?php

namespace App\Services;

use App\Models\PembelianMerch;
use App\Models\DetailPembelianMerch;
use App\Models\Merchandise;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MerchCartService
{
    // 1. Membuat keranjang (cart) baru untuk pelanggan.
    //    Melalui transaksi database agar penyimpanan header dan detail berjalan aman.
    public function createCart($idPelanggan, $items)
    {
        if (!is_array($items)) {
            return [
                'error' => true,
                'message' => 'Format itemsMerch tidak valid. Harus array.'
            ];
        }

        return DB::transaction(function () use ($idPelanggan, $items) {
            $total = 0;

            // Simpan pembelian utama (belum checkout)
            $pembelian = PembelianMerch::create([
                'id_pelanggan' => $idPelanggan,
                'tanggal_pembelian' => null,
                'status_pembelian' => 'Belum Checkout',
                'is_checkout' => 0,
                'total_pembelian' => 0,
            ]);

            $cartMerchItem = [];

            // Simpan detail item satu per satu
            foreach ($items as $item) {
                $merch = Merchandise::findOrFail($item['idMerch']);

                $jumlah = $item['jumlah'];

                if ($jumlah > $merch->stok) {
                    throw new \Exception("Jumlah pembelian untuk merchandise '{$merch->nama_merchandise}' melebihi stok ({$merch->stok}).");
                }

                $subtotal = $merch->harga_merchandise * $jumlah;

                $detail = DetailPembelianMerch::create([
                    'id_pembelianMerch' => $pembelian->id_pembelianMerch,
                    'id_merchandise' => $merch->id_merchandise,
                    'jumlah' => $jumlah,
                    'harga' => $merch->harga_merchandise,
                    'subtotal' => $subtotal,
                ]);

                $cartMerchItem[] = [
                    'idDetailPembelianMerch' => $detail->id_detail_pembelianMerch,
                    'idMerch' => $merch->id_merchandise,
                    'namaMerch' => $merch->nama_merchandise,
                    'jumlahMerch' => $jumlah,
                    'hargaMerch' => $merch->harga_merchandise,
                    'subtotalMerch' => $subtotal,
                ];

                $total += $subtotal;
            }

            $pembelian->update(['total_pembelian' => $total]);

            return [
                'idPembelianMerch' => $pembelian->id_pembelianMerch,
                'tanggalPembelianMerch' => $pembelian->tanggal_pembelian,
                'totalHargaMerch' => $total,
                'statusPembelianMerch' => $pembelian->status_pembelian,
                'cartMerchItem' => $cartMerchItem,
            ];
        });
    }

    // 2. Menampilkan semua cart yang belum di-checkout milik pelanggan.
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
                    'totalHargaMerch' => $pembelian->total_pembelian,
                    'statusPembelianMerch' => $pembelian->status_pembelian,
                    'cartMerchItem' => $pembelian->detail->map(function ($item) {
                        return [
                            'idDetailPembelianMerch' => $item->id_detail_pembelianMerch,
                            'fotoMerchandise' => $item->merchandise?->foto_merchandise 
                                ? asset('storage/merchandise/' . $item->merchandise->foto_merchandise)
                                : null,
                            'idMerch' => $item->merchandise->id_merchandise,
                            'namaMerch' => $item->merchandise->nama_merchandise,
                            'jumlahMerch' => $item->jumlah,
                            'hargaMerch' => $item->harga,
                            'subtotalMerch' => $item->subtotal,
                        ];
                    }),
                ];
            });
    }

    // 3. Menampilkan detail cart spesifik (jika milik user & belum checkout)
    public function getCartDetail($idPembelian, $idPelanggan)
    {
        $pembelian = PembelianMerch::with(['detail.merchandise'])
            ->where('id_pembelianMerch', $idPembelian)
            ->where('id_pelanggan', $idPelanggan)
            ->where('is_checkout', 0)
            ->first();

        if (!$pembelian) {
            return null;
        }

        return [
            'idPembelianMerch' => $pembelian->id_pembelianMerch,
            'tanggalPembelianMerch' => $pembelian->tanggal_pembelian,
            'totalHargaMerch' => $pembelian->total_pembelian,
            'statusPembelianMerch' => $pembelian->status_pembelian,
            'cartMerchItem' => $pembelian->detail->map(function ($item) {
                return [
                    'idDetailPembelianMerch' => $item->id_detail_pembelianMerch,
                    'fotoMerchandise' => $item->merchandise?->foto_merchandise 
                        ? asset('storage/merchandise/' . $item->merchandise->foto_merchandise)
                        : null,
                    'idMerch' => $item->merchandise->id_merchandise,
                    'namaMerch' => $item->merchandise->nama_merchandise,
                    'jumlahMerch' => $item->jumlah,
                    'hargaMerch' => $item->harga,
                    'subtotalMerch' => $item->subtotal,
                ];
            }),
        ];
    }

    // 4. Menghapus cart (dan detailnya) jika belum di-checkout
    public function deleteCart($idPembelian, $idPelanggan)
    {
        $pembelian = PembelianMerch::where('id_pembelianMerch', $idPembelian)
            ->where('is_checkout', 0)
            ->first();

        if (!$pembelian) return false;

        $pembelian->detail()->delete();
        $pembelian->delete();

        return true;
    }

    // 5. Melakukan proses checkout: update status, simpan tanggal, dan kurangi stok
    public function checkout($idPembelian, $idPelanggan, $items)
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

                if ($jumlah > $merch->stok) {
                    throw new \Exception("Jumlah pembelian untuk merchandise '{$merch->nama_merchandise}' melebihi stok ({$merch->stok}).");
                }

                $subtotal = $merch->harga_merchandise * $jumlah;

                // Update detail pembelian
                DetailPembelianMerch::where('id_pembelianMerch', $idPembelian)
                    ->where('id_merchandise', $merch->id_merchandise)
                    ->update([
                        'jumlah' => $jumlah,
                        'subtotal' => $subtotal,
                    ]);

                $total += $subtotal;
            }

            // Update header pembelian jadi sudah checkout
            $pembelian->update([
                'is_checkout' => 1,
                'status_pembelian' => 'Belum Bayar',
                'tanggal_pembelian' => Carbon::now(),
                'total_pembelian' => $total,
            ]);

            return [
                'idPembelianMerch' => $pembelian->id_pembelianMerch,
                'tanggalPembelianMerch' => $pembelian->tanggal_pembelian,
                'totalHargaMerch' => $total,
                'statusPembelianMerch' => $pembelian->status_pembelian,
                'cartMerchItem' => $pembelian->detail->map(function ($item) {
                    return [
                        'idDetailPembelianMerch' => $item->id_detail_pembelianMerch,
                        'fotoMerchandise' => $item->merchandise?->foto_merchandise 
                            ? asset('storage/merchandise/' . $item->merchandise->foto_merchandise)
                            : null,
                        'idMerch' => $item->merchandise->id_merchandise,
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
