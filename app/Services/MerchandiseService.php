<?php

namespace App\Services;

use App\Models\Merchandise;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MerchandiseService
{
    public function createMerchandise(array $data, $fotoFile = null)
    {
        if ($fotoFile) {
            $filename = uniqid() . '.' . $fotoFile->getClientOriginalExtension();
            $fotoFile->storeAs('public/merchandise', $filename);
            $data['foto_merchandise'] = $filename;
        }

        $merchandise = Merchandise::create($data);

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

    public function updateMerchandise($idMerchandise, array $data, $fotoFile = null)
    {
        $item = Merchandise::find($idMerchandise);
        if (!$item) {
            return response()->json([
                'error' => true,
                'message' => 'Merchandise tidak ditemukan',
            ], 404);
        }

        Log::info('🛠 Data sebelum update merchandise:', $item->toArray());

        if ($fotoFile) {
            if ($item->foto_merchandise && Storage::exists('public/merchandise/' . $item->foto_merchandise)) {
                Storage::delete('public/merchandise/' . $item->foto_merchandise);
                Log::info('🗑️ Foto lama dihapus:', ['file' => $item->foto_merchandise]);
            }

            $filename = uniqid() . '.' . $fotoFile->getClientOriginalExtension();
            $fotoFile->storeAs('public/merchandise', $filename);
            $data['foto_merchandise'] = $filename;

            Log::info('📥 Foto baru disimpan:', ['filename' => $filename]);
        }

        $item->update($data);

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

    public function deleteMerchandise($idMerchandise)
    {
        $item = Merchandise::find($idMerchandise);
        if (!$item) {
            return response()->json([
                'error' => true,
                'message' => 'Merchandise tidak ditemukan',
            ], 404);
        }

        if ($item->foto_merchandise && Storage::exists('public/merchandise/' . $item->foto_merchandise)) {
            Storage::delete('public/merchandise/' . $item->foto_merchandise);
        }

        $item->delete();

        return response()->json([
            'error' => false,
            'message' => 'Merchandise berhasil dihapus',
        ]);
    }

    public function showAll()
    {
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

        return response()->json([
            'error' => false,
            'message' => 'Daftar merchandise berhasil diambil',
            'listMerchandise' => $items,
            'status' => 'sukses'
        ]);
    }

    public function show($idMerchandise)
    {
        $item = Merchandise::find($idMerchandise);

        if (!$item) {
            return response()->json([
                'error' => true,
                'message' => 'Merchandise tidak ditemukan'
            ], 404);
        }

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
