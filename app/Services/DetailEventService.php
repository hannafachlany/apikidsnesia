<?php

namespace App\Services;

use App\Models\DetailEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DetailEventService
{
    public function store($validated, $file)
    {
        try {
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/foto_kegiatan', $filename);

            $detail = DetailEvent::create([
                'id_event' => $validated['id_event'],
                'foto_kegiatan' => $filename,
            ]);

            if (!$detail) {
                Log::error('Gagal menyimpan data DetailEvent ke database.');
                return response()->json([
                    'error' => true,
                    'message' => 'Gagal menyimpan data ke database.',
                ], 500);
            }

            Log::info('DetailEvent berhasil disimpan', ['id' => $detail->id_detail_event]);

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
            Log::error('Gagal upload foto kegiatan: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => true,
                'message' => 'Terjadi kesalahan saat mengupload foto.',
            ], 500);
        }
    }

    public function listAll()
    {
        $data = DetailEvent::with('event')->get()->map(function ($item) {
            return [
                'idDetailEvent' => $item->id_detail_event,
                'idEvent' => $item->id_event,
                'namaEvent' => $item->event->nama_event ?? '-',
                'fotoKegiatan' => url('storage/foto_kegiatan/' . $item->foto_kegiatan),
            ];
        });

        return response()->json([
            'error' => false,
            'message' => 'List foto kegiatan',
            'detailEventList' => $data
        ]);
    }

    public function getByEvent($idEvent)
    {
        $fotos = DetailEvent::where('id_event', $idEvent)->get();

        if ($fotos->isEmpty()) {
            return response()->json([
                'error' => true,
                'message' => 'Tidak ada foto kegiatan untuk event ini.',
                'fotoKegiatan' => [],
            ], 404);
        }

        $data = $fotos->map(function ($item) {
            return [
                'idDetailEvent' => $item->id_detail_event,
                'idEvent' => $item->id_event,
                'fotoKegiatan' => url('storage/foto_kegiatan/' . $item->foto_kegiatan),
            ];
        });

        return response()->json([
            'error' => false,
            'message' => 'Foto kegiatan berhasil diambil',
            'fotoKegiatan' => $data,
        ]);
    }

    public function update($detail, $request)
    {
        if ($request->has('id_event')) {
            $detail->id_event = $request->input('id_event');
        }

        if ($request->hasFile('foto_kegiatan')) {
            if ($detail->foto_kegiatan && Storage::exists('public/foto_kegiatan/' . $detail->foto_kegiatan)) {
                Storage::delete('public/foto_kegiatan/' . $detail->foto_kegiatan);
            }

            $file = $request->file('foto_kegiatan');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/foto_kegiatan', $filename);
            $detail->foto_kegiatan = $filename;
        }

        $detail->save();

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

    public function destroy($detail)
    {
        if ($detail->foto_kegiatan && Storage::exists('public/foto_kegiatan/' . $detail->foto_kegiatan)) {
            Storage::delete('public/foto_kegiatan/' . $detail->foto_kegiatan);
        }

        $detail->delete();

        return response()->json([
            'error' => false,
            'message' => 'Foto kegiatan berhasil dihapus.'
        ]);
    }
}
