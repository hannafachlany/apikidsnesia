<?php

namespace App\Services;

use App\Models\DetailEvent;
use Illuminate\Support\Facades\Storage;

class DetailEventService
{
    // 1. Menyimpan foto kegiatan untuk event tertentu
    public function store($validated, $file)
    {
        try {
            // 1.1 Buat nama file unik berdasarkan uniqid + ekstensi file
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();

            // 1.2 Simpan file ke direktori 'storage/app/public/foto_kegiatan'
            $file->storeAs('public/foto_kegiatan', $filename);

            // 1.3 Simpan informasi ke database (id_event dan nama file)
            $detail = DetailEvent::create([
                'id_event' => $validated['id_event'],
                'foto_kegiatan' => $filename,
            ]);

            // 1.4 Jika gagal menyimpan ke database
            if (!$detail) {
                return response()->json([
                    'error' => true,
                    'message' => 'Gagal menyimpan data ke database.',
                ], 500);
            }

            // 1.5 Berhasil disimpan, kembalikan response JSON
            return response()->json([
                'error' => false,
                'message' => 'Foto kegiatan berhasil disimpan',
                'data' => [
                    'idDetailEvent' => $detail->id_detail_event,
                    'idEvent' => $detail->id_event,
                    'fotoKegiatan' => url('storage/foto_kegiatan/' . $filename),
                ]
            ], 201);
        } catch (\Exception $e) {
            // 1.6 Jika terjadi error saat proses simpan/upload file
            return response()->json([
                'error' => true,
                'message' => 'Terjadi kesalahan saat mengupload foto.',
            ], 500);
        }
    }

    // 2. Mengambil semua data foto kegiatan dari semua event
    public function listAll()
    {
        // 2.1 Ambil semua data detail event, termasuk relasi event
        $data = DetailEvent::with('event')->get()->map(function ($item) {
            return [
                'idDetailEvent' => $item->id_detail_event,
                'idEvent' => $item->id_event,
                'namaEvent' => $item->event->nama_event ?? '-',
                'fotoKegiatan' => url('storage/foto_kegiatan/' . $item->foto_kegiatan),
            ];
        });

        // 2.2 Kembalikan list dalam bentuk JSON
        return response()->json([
            'error' => false,
            'message' => 'List foto kegiatan',
            'detailEventList' => $data
        ]);
    }

    // 3. Mengambil foto kegiatan berdasarkan id_event tertentu
    public function getByEvent($idEvent)
    {
        // 3.1 Ambil semua foto berdasarkan id_event
        $fotos = DetailEvent::where('id_event', $idEvent)->get();

        // 3.2 Jika kosong, kembalikan response error
        if ($fotos->isEmpty()) {
            return response()->json([
                'error' => true,
                'message' => 'Tidak ada foto kegiatan untuk event ini.',
                'fotoKegiatan' => [],
            ], 404);
        }

        // 3.3 Format hasil menjadi array JSON
        $data = $fotos->map(function ($item) {
            return [
                'idDetailEvent' => $item->id_detail_event,
                'idEvent' => $item->id_event,
                'fotoKegiatan' => url('storage/foto_kegiatan/' . $item->foto_kegiatan),
            ];
        });

        // 3.4 Kembalikan respons JSON
        return response()->json([
            'error' => false,
            'message' => 'Foto kegiatan berhasil diambil',
            'fotoKegiatan' => $data,
        ]);
    }

    // 4. Memperbarui data detail event (foto kegiatan dan id_event)
    public function update($detail, $request)
    {
        // 4.1 Update ID event jika dikirim dari request
        if ($request->has('id_event')) {
            $detail->id_event = $request->input('id_event');
        }

        // 4.2 Jika ada file baru dikirim
        if ($request->hasFile('foto_kegiatan')) {
            // 4.2.1 Hapus file lama dari storage jika ada
            if ($detail->foto_kegiatan && Storage::exists('public/foto_kegiatan/' . $detail->foto_kegiatan)) {
                Storage::delete('public/foto_kegiatan/' . $detail->foto_kegiatan);
            }

            // 4.2.2 Simpan file baru
            $file = $request->file('foto_kegiatan');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/foto_kegiatan', $filename);
            $detail->foto_kegiatan = $filename;
        }

        // 4.3 Simpan perubahan ke database
        $detail->save();

        // 4.4 Kembalikan respons sukses
        return response()->json([
            'error' => false,
            'message' => 'Foto kegiatan berhasil diperbarui.',
            'data' => [
                'idDetailEvent' => $detail->id_detail_event,
                'idEvent' => $detail->id_event,
                'fotoKegiatan' => url('storage/foto_kegiatan/' . $detail->foto_kegiatan),
            ]
        ]);
    }

    // 5. Menghapus data dan file foto kegiatan
    public function destroy($detail)
    {
        // 5.1 Hapus file dari storage jika ada
        if ($detail->foto_kegiatan && Storage::exists('public/foto_kegiatan/' . $detail->foto_kegiatan)) {
            Storage::delete('public/foto_kegiatan/' . $detail->foto_kegiatan);
        }

        // 5.2 Hapus data dari database
        $detail->delete();

        // 5.3 Kembalikan response sukses
        return response()->json([
            'error' => false,
            'message' => 'Foto kegiatan berhasil dihapus.'
        ]);
    }
}
