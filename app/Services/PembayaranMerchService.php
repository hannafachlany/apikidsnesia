<?php

namespace App\Services;

use App\Models\PembelianMerch;
use App\Models\PembayaranMerch;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PembayaranMerchService
{
    public function getDetailBayar($pelanggan, int $idPembelianMerch)
    {
        $pembelian = PembelianMerch::with(['detail.merchandise', 'pembayaran'])
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->find($idPembelianMerch);

        if (!$pembelian) {
            return response()->json([
                'error' => true,
                'message' => 'Data pembelian tidak ditemukan atau bukan milik Anda',
            ], 404);
        }

        $pembayaran = $pembelian->pembayaran;

        $response = [
            'idPembayaranMerch' => $pembayaran->id_pembayaranMerch ?? null,
            'idPembelianMerch' => $pembelian->id_pembelianMerch,
            'totalHargaMerch' => $pembelian->total_pembelian,
            'tanggalBayarMerch' => $pembayaran?->tanggal_bayar,
            'statusPembayaranMerch' => $pembayaran->status_pembayaran ?? 'Menunggu Pilihan Bank',
            'bankPengirim' => $pembayaran->bank?? null,
            'buktiBayarMerch' => $pembayaran?->bukti_bayar_merch 
                ? asset('storage/bukti-merch/' . $pembayaran->bukti_bayar_merch)
                : null,
            'detailMerch' => $pembelian->detail->map(fn($item) => [
                'namaMerch' => $item->merchandise->nama_merchandise,
                'jumlahMerch' => $item->jumlah,
                'hargaMerch' => $item->harga,
            ]),
        ];

        return response()->json([
            'error' => false,
            'detailBayarMerch' => $response,
        ]);
    }

    public function pilihBank($pelanggan, string $namaBank)
    {
        $pembelian = PembelianMerch::where('id_pelanggan', $pelanggan->id_pelanggan)
            ->where('is_checkout', true)
            ->whereDoesntHave('pembayaran')
            ->oldest()
            ->first();

        if (!$pembelian) {
            return response()->json([
                'error' => true,
                'message' => 'Tidak ada pembelian aktif atau pembayaran sudah dibuat.',
            ], 404);
        }

        $pembayaran = PembayaranMerch::create([
            'id_pembelianMerch' => $pembelian->id_pembelianMerch,
            'nama_pelanggan' => $pelanggan->nama_pelanggan,
            'bank' => $namaBank,
            'total_harga' => $pembelian->total_pembelian,
            'status_pembayaran' => 'Menunggu Pembayaran',
        ]);

        return response()->json([
            'error' => false,
            'message' => 'Bank berhasil dipilih. Silakan transfer manual.',
            'dataPembayaranMerch' => [
                'idPembayaranMerch' => $pembayaran->id_pembayaranMerch,
                'statusPembayaranMerch' => $pembayaran->status_pembayaran,
                'bankPengirim' => $pembayaran->bank,
                'totalHargaMerch' => $pembayaran->total_harga,
            ],
        ]);
    }

    public function uploadBukti($pelanggan, $file, int $idPembayaranMerch)
    {
        $pembayaran = PembayaranMerch::with('pembelian')->find($idPembayaranMerch);

        if (!$pembayaran || $pembayaran->pembelian->id_pelanggan !== $pelanggan->id_pelanggan) {
            return response()->json([
                'error' => true,
                'message' => 'Pembayaran tidak ditemukan atau bukan milik Anda.',
            ], 404);
        }

        $filename = 'buktiBayar_merch_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('public/bukti-merch', $filename);

        $pembayaran->update([
            'bukti_bayar_merch' => $filename,
            'tanggal_bayar' => now(),
            'status_pembayaran' => 'Menunggu Verifikasi',
        ]);

        return response()->json([
            'error' => false,
            'message' => 'Bukti pembayaran berhasil diupload.',
            'urlBuktiBayarMerch' => asset('storage/bukti-merch/' . $filename),
        ]);
    }

    public function notaPembelian($pelanggan, int $idPembelianMerch)
    {
        $pembelian = PembelianMerch::with(['detail.merchandise', 'pembayaran'])
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->find($idPembelianMerch);

        if (!$pembelian) {
            return response()->json([
                'error' => true,
                'message' => 'Nota tidak ditemukan',
            ], 404);
        }

        $pembayaran = $pembelian->pembayaran;

        return response()->json([
            'error' => false,
            'notaPembelianMerch' => [
                'noPembelianMerch' => $pembelian->id_pembelianMerch,
                'tanggalPembelianMerch' => $pembelian->tanggal_pembelian,
                'namaPelanggan' => $pelanggan->nama_pelanggan,
                'teleponPelanggan' => $pelanggan->no_hp_pelanggan,
                'emailPelanggan' => $pelanggan->email,
                'totalPembelianMerch' => $pembelian->total_pembelian,
                'statusPembelianMerch' => $pembelian->status_pembelian,
                'statusPembayaranMerch' => $pembayaran?->status_pembayaran ?? 'Belum memilih metode pembayaran',
                'detailMerch' => $pembelian->detail->map(function ($item, $i) {
                    return [
                        'no' => $i + 1,
                        'namaMerch' => $item->merchandise->nama_merchandise,
                        'hargaMerch' => $item->harga,
                        'jumlahMerch' => $item->jumlah,
                        'subtotalMerch' => $item->subtotal,
                    ];
                }),
            ],
        ]);
    }
}
