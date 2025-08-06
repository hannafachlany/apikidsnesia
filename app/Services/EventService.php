<?php
namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EventService
{
    // 1. Menyimpan event baru ke database
    public function storeEvent(array $data, $fotoFile = null)
    {
        // 1.1 Simpan foto jika ada
        if ($fotoFile) {
            $filename = uniqid() . '.' . $fotoFile->getClientOriginalExtension();
            $fotoFile->storeAs('public/event', $filename);
            $data['foto_event'] = $filename;
        }

        // 1.2 Buat event baru
        $event = Event::create($data);

        // 1.3 Kembalikan response
        return [
            'statusCode' => 201,
            'message' => 'Event berhasil dibuat',
            'event' => [
                'id_event' => $event->id_event,
                'nama_event' => $event->nama_event,
                'foto_event' => asset('storage/event/' . $event->foto_event),
                'harga_event' => $event->harga_event,
                'tanggalEvent' => Carbon::parse($event->jadwal_event)->format('d-m-Y'),
                'jadwalEvent' => Carbon::parse($event->jadwal_event)->format('H:i'),
                'deskripsi_event' => $event->deskripsi_event,
                'kuota' => $event->kuota,
            ]
        ];
    }

    // 2. Memperbarui data event
    public function updateEvent($idEvent, array $data, $fotoFile = null)
    {
        $event = Event::find($idEvent);
        if (!$event) {
            return [
                'statusCode' => 404,
                'message' => 'Event tidak ditemukan'
            ];
        }

        // 2.1 Jika ada foto baru, hapus lama & simpan baru
        if ($fotoFile) {
            if ($event->foto_event && Storage::exists('public/event/' . $event->foto_event)) {
                Storage::delete('public/event/' . $event->foto_event);
            }

            $filename = uniqid() . '.' . $fotoFile->getClientOriginalExtension();
            $fotoFile->storeAs('public/event', $filename);
            $data['foto_event'] = $filename;

            Log::info("Foto event diperbarui:", ['filename' => $filename]);
        }

        // 2.2 Update data event
        $event->update($data);

        return [
            'statusCode' => 200,
            'success' => true,
            'message' => 'Event berhasil diperbarui',
            'data' => $event
        ];
    }

    // 3. Menghapus event
    public function deleteEvent($idEvent)
    {
        $event = Event::find($idEvent);
        if (!$event) {
            return [
                'statusCode' => 404,
                'message' => 'Event tidak ditemukan'
            ];
        }

        // 3.1 Hapus file foto dari storage jika ada
        if ($event->foto_event && Storage::exists('public/event/' . $event->foto_event)) {
            Storage::delete('public/event/' . $event->foto_event);
        }

        // 3.2 Hapus event dari database
        $event->delete();

        return [
            'statusCode' => 200,
            'message' => 'Event berhasil dihapus'
        ];
    }

    // 4. Menampilkan semua event (termasuk detail foto kegiatan)
    public function showAll()
    {
        $events = Event::with('detailFoto')->get()->map(function ($event) {
            return [
                'idEvent' => $event->id_event,
                'namaEvent' => $event->nama_event,
                'tanggalEvent' => Carbon::parse($event->jadwal_event)->format('d-m-Y'),
                'jadwalEvent' => Carbon::parse($event->jadwal_event)->format('H:i'),
                'fotoEvent' => url('storage/event/' . $event->foto_event),
                'deskripsiEvent' => $event->deskripsi_event,
                'kuota' => $event->kuota,
                'hargaEvent' => $event->harga_event,
                'fotoKegiatan' => $event->detailFoto->map(function ($detail) {
                    return url('storage/foto_kegiatan/' . $detail->foto_kegiatan);
                }),
            ];
        });

        return [
            'error' => false,
            'message' => 'Daftar event berhasil diambil',
            'listEvent' => $events,
            'status' => 'sukses'
        ];
    }

    // 5. Menampilkan detail event berdasarkan ID (dengan foto kegiatan)
    public function show($idEvent)
    {
        $event = Event::with('detailFoto')->find($idEvent);
        if (!$event) {
            return [
                'statusCode' => 404,
                'error' => true,
                'message' => 'Event tidak ditemukan',
                'detailEvent' => null,
                'status' => 'gagal'
            ];
        }

        return [
            'statusCode' => 200,
            'error' => false,
            'message' => 'Detail event berhasil diambil',
            'detailEvent' => [
                'idEvent' => $event->id_event,
                'namaEvent' => $event->nama_event,
                'tanggalEvent' => Carbon::parse($event->jadwal_event)->format('d-m-Y'),
                'jadwalEvent' => Carbon::parse($event->jadwal_event)->format('H:i'),
                'fotoEvent' => url('storage/event/' . $event->foto_event),
                'deskripsiEvent' => $event->deskripsi_event,
                'kuota' => $event->kuota,
                'hargaEvent' => $event->harga_event,
                'fotoKegiatan' => $event->detailFoto->map(function ($detail) {
                    return url('storage/foto_kegiatan/' . $detail->foto_kegiatan);
                }),
            ],
            'status' => 'sukses'
        ];
    }
}
