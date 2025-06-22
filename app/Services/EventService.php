<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class EventService
{
    public function storeEvent(array $data, $fotoFile = null)
    {
        if ($fotoFile) {
            $filename = uniqid() . '.' . $fotoFile->getClientOriginalExtension();
            $fotoFile->storeAs('public/event', $filename);
            $data['foto_event'] = $filename;
        }

        $event = Event::create($data);

        return [
            'statusCode' => 201,
            'message' => 'Event berhasil dibuat',
            'event' => [
                'id_event' => $event->id_event,
                'nama_event' => $event->nama_event,
                'foto_event' => asset('storage/event/' . $event->foto_event),
                'harga_event' => $event->harga_event,
                'jadwal_event' => $event->jadwal_event,
                'deskripsi_event' => $event->deskripsi_event,
                'kuota' => $event->kuota,
            ]
        ];
    }

    public function updateEvent($idEvent, array $data, $fotoFile = null)
    {
        $event = Event::find($idEvent);
        if (!$event) {
            return [
                'statusCode' => 404,
                'message' => 'Event tidak ditemukan'
            ];
        }

        if ($fotoFile) {
            if ($event->foto_event && Storage::exists('public/event/' . $event->foto_event)) {
                Storage::delete('public/event/' . $event->foto_event);
            }

            $filename = uniqid() . '.' . $fotoFile->getClientOriginalExtension();
            $fotoFile->storeAs('public/event', $filename);
            $data['foto_event'] = $filename;

            Log::info("Foto event diperbarui:", ['filename' => $filename]);
        }

        $event->update($data);

        return [
            'statusCode' => 200,
            'success' => true,
            'message' => 'Event berhasil diperbarui',
            'data' => $event
        ];
    }

    public function deleteEvent($idEvent)
    {
        $event = Event::find($idEvent);
        if (!$event) {
            return [
                'statusCode' => 404,
                'message' => 'Event tidak ditemukan'
            ];
        }

        if ($event->foto_event && Storage::exists('public/event/' . $event->foto_event)) {
            Storage::delete('public/event/' . $event->foto_event);
        }

        $event->delete();

        return [
            'statusCode' => 200,
            'message' => 'Event berhasil dihapus'
        ];
    }

    public function showAll()
    {
        $events = Event::with('detailFoto')->get()->map(function ($event) {
            return [
                'idEvent' => $event->id_event,
                'namaEvent' => $event->nama_event,
                'jadwalEvent' => $event->jadwal_event,
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
                'jadwalEvent' => $event->jadwal_event,
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
