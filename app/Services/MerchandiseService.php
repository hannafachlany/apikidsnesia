<?php

namespace App\Services;

use App\Models\Merchandise;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MerchandiseService
{
    // 1. Menyimpan data merchandise baru ke database
    public function createMerchandise(array $data, $fotoFile = null)
    {
        // 1.1 Jika ada file foto, simpan ke storage dan masukkan ke data
        if ($fotoFile) {
            $filename = uniqid() . '.' . $fotoFile->getClientOriginalExtension();
            $fotoFile->storeAs('public/merchandise', $filename);
            $data['foto_merchandise'] = $filename;
        }

        // 1.2 Simpan data merchandise ke database
        $merchandise = Merchandise::create($data);

        // 1.3 Kembalikan response JSON dengan data merchandise yang baru dibuat
        return response()->json([
            'error' => false,
            'message' => 'Merchandise berhasil ditambahkan',
            'data' => [
                'idMerchandise'   => $merchandise->id_merchandise,
                'namaMerchandise' => $merchandise->nama_merchandise,
                'hargaMerchandise' => $merchandise->harga_merchandise,
                'stok' => $merchandise->stok,
                'fotoMerchandise' => asset('storage/merchandise/' . $merchandise->foto_merchandise),
            ]
        ], 201);
    }

    // 2. Memperbarui data merchandise berdasarkan ID
    public function updateMerchandise($idMerchandise, array $data, $fotoFile = null)
    {
        // 2.1 Cari merchandise berdasarkan ID
        $item = Merchandise::find($idMerchandise);
        if (!$item) {
            return response()->json([
                'error' => true,
                'message' => 'Merchandise tidak ditemukan',
            ], 404);
        }

        // 2.2 Log data sebelum diupdate
        Log::info('🛠 Data sebelum update merchandise:', $item->toArray());

        // 2.3 Jika ada file baru, hapus foto lama lalu simpan yang baru
        if ($fotoFile) {
            if ($item->foto_merchandise && Storage::exists('public/merchandise/' . $item->foto_merchandise)) {
                Storage::delete('public/merchandise/' . $item->foto_merchandise);
            }

            $filename = uniqid() . '.' . $fotoFile->getClientOriginalExtension();
            $fotoFile->storeAs('public/merchandise', $filename);
            $data['foto_merchandise'] = $filename;

        }

        // 2.4 Update data merchandise di database
        $item->update($data);

        // 2.5 Kembalikan response dengan data yang telah diperbarui
        return response()->json([
            'success' => true,
            'message' => 'Merchandise berhasil diperbarui',
            'data' => [
                'idMerchandise'   => $item->id_merchandise,
                'namaMerchandise' => $item->nama_merchandise,
                'hargaMerchandise' => $item->harga_merchandise,
                'stok' => $item->stok,
                'fotoMerchandise' => asset('storage/merchandise/' . $item->foto_merchandise),
            ]
        ]);
    }

    // 3. Menghapus merchandise berdasarkan ID
    public function deleteMerchandise($idMerchandise)
    {
        // 3.1 Cari merchandise berdasarkan ID
        $item = Merchandise::find($idMerchandise);
        if (!$item) {
            return response()->json([
                'error' => true,
                'message' => 'Merchandise tidak ditemukan',
            ], 404);
        }

        // 3.2 Hapus foto dari storage jika ada
        if ($item->foto_merchandise && Storage::exists('public/merchandise/' . $item->foto_merchandise)) {
            Storage::delete('public/merchandise/' . $item->foto_merchandise);
        }

        // 3.3 Hapus data merchandise dari database
        $item->delete();

        // 3.4 Kembalikan response berhasil
        return response()->json([
            'error' => false,
            'message' => 'Merchandise berhasil dihapus',
        ]);
    }

    // 4. Menampilkan semua merchandise
    public function showAll()
    {
        // 4.1 Ambil semua merchandise dan ubah struktur responsenya
        $items = Merchandise::all()->map(function ($item) {
            return [
                'idMerchandise'   => $item->id_merchandise,
                'namaMerchandise' => $item->nama_merchandise,
                'hargaMerchandise' => $item->harga_merchandise,
                'deskripsiMerchandise' => $item->deskripsi_merchandise,
                'stok' => $item->stok,
                'fotoMerchandise' => asset('storage/merchandise/' . $item->foto_merchandise),
            ];
        });

        // 4.2 Kembalikan response dengan semua data
        return response()->json([
            'error' => false,
            'message' => 'Daftar merchandise berhasil diambil',
            'listMerchandise' => $items,
            'status' => 'sukses'
        ]);
    }

    // 5. Menampilkan detail satu merchandise berdasarkan ID
    public function show($idMerchandise)
    {
        // 5.1 Cari merchandise berdasarkan ID
        $item = Merchandise::find($idMerchandise);

        if (!$item) {
            return response()->json([
                'error' => true,
                'message' => 'Merchandise tidak ditemukan'
            ], 404);
        }

        // 5.2 Kembalikan response dengan detail merchandise
        return response()->json([
            'error' => false,
            'message' => 'Detail merchandise berhasil diambil',
            'detailMerchandise' => [
                'idMerchandise' => $item->id_merchandise,
                'namaMerchandise' => $item->nama_merchandise,
                'hargaMerchandise' => $item->harga_merchandise,
                'deskripsiMerchandise' => $item->deskripsi_merchandise,
                'stok' => $item->stok,
                'fotoMerchandise' => asset('storage/merchandise/' . $item->foto_merchandise),
            ]
        ]);
    }
}
