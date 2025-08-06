<?php

namespace App\Services;

use App\Models\PembelianMerch;
use App\Models\PembayaranMerch;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PembayaranMerchService
{
    // 1. Ambil detail pembayaran untuk pembelian merchandise tertentu
    public function getDetailBayar($pelanggan, int $idPembelianMerch)
    {
        // Ambil data pembelian lengkap dengan relasi detail & pembayaran
        $pembelian = PembelianMerch::with(['detail.merchandise', 'pembayaran'])
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->find($idPembelianMerch);

        // Jika pembelian tidak ditemukan atau bukan milik pelanggan, kirim error
        if (!$pembelian) {
            return response()->json([
                'error' => true,
                'message' => 'Data pembelian tidak ditemukan atau bukan milik Anda',
            ], 404);
        }

        // Ambil data pembayaran dari relasi
        $pembayaran = $pembelian->pembayaran;

        // Susun response JSON
        $response = [
            'idPembayaranMerch' => $pembayaran->id_pembayaranMerch ?? null,
            'idPembelianMerch' => $pembelian->id_pembelianMerch,
            'totalHargaMerch' => $pembelian->total_pembelian,
            'tanggalBayarMerch' => $pembayaran?->tanggal_bayar,
            'statusPembayaranMerch' => $pembayaran->status_pembayaran ?? 'Menunggu Pilihan Bank',
            'bankPengirim' => $pembayaran->bank ?? null,
            // Loop setiap detail merchandise
            'detailMerch' => $pembelian->detail->map(fn($item) => [
                'idMerchandise' => $item->merchandise->id_merchandise,
                'idDetailPembelianMerchandise' => $item->id_detail_pembelianMerch,
                'namaMerch' => $item->merchandise->nama_merchandise,
                'jumlahMerch' => $item->jumlah,
                'hargaMerch' => $item->harga,
                'subtotalMerch'=> $item->subtotal,
            ]),
        ];

        // Kembalikan response
        return response()->json([
            'error' => false,
            'detailBayarMerch' => $response,
        ]);
    }

    // 2. Proses memilih bank untuk pembayaran manual
    public function pilihBank($pelanggan, string $namaBank, int $idPembelianMerch)
    {
        // Ambil pembelian yang valid: milik pelanggan, sudah checkout, belum bayar
        $pembelian = PembelianMerch::where('id_pelanggan', $pelanggan->id_pelanggan)
            ->where('id_pembelianMerch', $idPembelianMerch)
            ->where('is_checkout', true)
            ->whereDoesntHave('pembayaran')
            ->first();

        // Jika tidak valid, kirim error
        if (!$pembelian) {
            return response()->json([
                'error' => true,
                'message' => 'Pembelian tidak ditemukan, belum checkout, atau sudah memiliki pembayaran.',
            ], 404);
        }

        // Simpan data pembayaran baru
        $pembayaran = PembayaranMerch::create([
            'id_pembelianMerch' => $pembelian->id_pembelianMerch,
            'nama_pelanggan' => $pelanggan->nama_pelanggan,
            'bank' => $namaBank,
            'total_harga' => $pembelian->total_pembelian,
            'status_pembayaran' => 'Menunggu Pembayaran',
        ]);

        // Kirim response sukses
        return response()->json([
            'error' => false,
            'message' => 'Bank berhasil dipilih. Silakan transfer manual.',
            'dataPembayaranMerch' => [
                'idPembayaranMerch' => $pembayaran->id_pembayaranMerch,
                'idPembelianMerch' => $pembayaran->id_pembelianMerch,
                'statusPembayaranMerch' => $pembayaran->status_pembayaran,
                'bankPengirim' => $pembayaran->bank,
                'totalHargaMerch' => $pembayaran->total_harga,
            ],
        ]);
    }

    // 3. Upload bukti transfer pembayaran
    public function uploadBukti($pelanggan, $file, int $idPembayaranMerch)
    {
        // Cari pembayaran berdasarkan ID dan pastikan milik pelanggan
        $pembayaran = PembayaranMerch::with('pembelian')->find($idPembayaranMerch);
        if (!$pembayaran || $pembayaran->pembelian->id_pelanggan !== $pelanggan->id_pelanggan) {
            return response()->json([
                'error' => true,
                'message' => 'Pembayaran tidak ditemukan atau bukan milik Anda.',
            ], 404);
        }

        // Simpan file ke folder 'public/bukti-merch' dengan nama unik
        $filename = 'buktiBayar_merch_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('public/bukti-merch', $filename);

        // Update data pembayaran dengan bukti, tanggal, dan status
        $pembayaran->update([
            'bukti_bayar_merch' => $filename,
            'tanggal_bayar' => now(),
            'status_pembayaran' => 'Menunggu Verifikasi',
        ]);

        // Kirim response berhasil
        return response()->json([
            'error' => false,
            'message' => 'Bukti pembayaran berhasil diupload.',
            'urlBuktiBayarMerch' => asset('storage/bukti-merch/' . $filename),
        ]);
    }

    // 4. Menampilkan daftar nota yang belum memilih bank
    public function listNotaBelumPilihBank($pelanggan)
    {
        // Ambil pembelian yang sudah checkout tapi belum memilih bank
        $pembelianList = PembelianMerch::with('pembayaran')
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->where('is_checkout', 1)
            ->whereDoesntHave('pembayaran')
            ->orderByDesc('tanggal_pembelian')
            ->get();

        // Format datanya untuk response
        $result = $pembelianList->map(function ($pembelian) {
            return [
                'idPembelianMerch' => $pembelian->id_pembelianMerch,
                'tanggalPembelianMerch' => $pembelian->tanggal_pembelian,
                'totalPembelianMerch' => $pembelian->total_pembelian,
                'statusPembelianMerch' => $pembelian->status_pembelian,
                'statusPembayaranMerch' => 'Belum memilih bank',
            ];
        });

        return response()->json([
            'error' => false,
            'listNotaBelumPilihBankMerch' => $result,
        ]);
    }

    // 5. Menampilkan detail nota pembelian yang belum memilih bank
    public function notaBelumPilihBank($pelanggan, int $idPembelianMerch)
    {
        // Cari data pembelian yang belum dibayar
        $pembelian = PembelianMerch::with(['detail.merchandise'])
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->whereDoesntHave('pembayaran')
            ->find($idPembelianMerch);

        if (!$pembelian) {
            return response()->json([
                'error' => true,
                'message' => 'Data pembelian tidak ditemukan atau sudah memiliki pembayaran.',
            ], 404);
        }

        // Format detail nota
        return response()->json([
            'error' => false,
            'notaBelumPilihBankMerch' => [
                'idPembelianMerch' => $pembelian->id_pembelianMerch,
                'tanggalPembelianMerch' => $pembelian->tanggal_pembelian,
                'namaPelanggan' => $pelanggan->nama_pelanggan,
                'teleponPelanggan' => $pelanggan->no_hp_pelanggan,
                'emailPelanggan' => $pelanggan->email,
                'totalPembelianMerch' => $pembelian->total_pembelian,
                'statusPembelianMerch' => $pembelian->status_pembelian,
                'statusPembayaranMerch' => 'Belum memilih bank',
                'detailMerch' => $pembelian->detail->map(function ($item) {
                    return [
                        'idDetailPembelianMerch' => $item->id_detail_pembelianMerch,
                        'idMerch' => $item->merchandise->id_merchandise,
                        'namaMerch' => $item->merchandise->nama_merchandise,
                        'hargaMerch' => $item->harga,
                        'jumlahMerch' => $item->jumlah,
                        'subtotalMerch' => $item->subtotal,
                    ];
                }),
            ]
        ]);
    }

    // 6. Menampilkan daftar semua nota pembelian yang sudah memilih bank
    public function listNotaPembelian($pelanggan)
    {
        // Ambil semua pembelian yang sudah bayar
        $pembelianList = PembelianMerch::with('pembayaran')
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->where('is_checkout', 1)
            ->whereHas('pembayaran')
            ->orderByDesc('tanggal_pembelian')
            ->get();

        // Format data
        $result = $pembelianList->map(function ($pembelian) {
            return [
                'idPembelianMerch' => $pembelian->id_pembelianMerch,
                'tanggalPembelianMerch' => $pembelian->tanggal_pembelian,
                'totalPembelianMerch' => $pembelian->total_pembelian,
                'statusPembelianMerch' => $pembelian->status_pembelian,
                'statusPembayaranMerch' => optional($pembelian->pembayaran)->status_pembayaran ?? 'Belum memilih metode pembayaran',
            ];
        });

        return response()->json([
            'error' => false,
            'listNotaPembelianMerch' => $result,
        ]);
    }

    // 7. Menampilkan detail lengkap nota pembelian + status pembayaran
    public function notaPembelian($pelanggan, int $idPembelianMerch)
    {
        // Ambil data pembelian beserta relasi detail & pembayaran
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

        // Format dan kembalikan data nota
        return response()->json([
            'error' => false,
            'notaPembelianMerch' => [
                'idPembelianMerch' => $pembelian->id_pembelianMerch,
                'idPembayaranMerch' => $pembayaran?->id_pembayaranMerch ?? null,
                'tanggalPembelianMerch' => $pembelian->tanggal_pembelian,
                'namaPelanggan' => $pelanggan->nama_pelanggan,
                'teleponPelanggan' => $pelanggan->no_hp_pelanggan,
                'emailPelanggan' => $pelanggan->email,
                'totalPembelianMerch' => $pembelian->total_pembelian,
                'statusPembelianMerch' => $pembelian->status_pembelian,
                'statusPembayaranMerch' => $pembayaran?->status_pembayaran ?? 'Belum memilih bank untuk transfer',
                'detailMerch' => $pembelian->detail->map(function ($item, $i) {
                    return [
                        'idDetailPembelianMerch' => $item->id_pembelianMerch,
                        'idMerch' => $item->merchandise->id_merchandise,
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
