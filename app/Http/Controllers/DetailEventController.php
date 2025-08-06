<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DetailEvent;
use App\Http\Requests\DetailEvent\StoreDetailEventRequest;
use App\Http\Requests\DetailEvent\UpdateDetailEventRequest;
use App\Services\DetailEventService;

class DetailEventController extends Controller
{
    protected $service;

    // Konstruktor: inject service untuk handle logika detail event
    public function __construct(DetailEventService $service)
    {
        $this->service = $service;
    }

    // 1. Menyimpan data foto kegiatan baru (POST /api/detail-event)
    public function store(StoreDetailEventRequest $request)
    {
        return $this->service->store($request->validated(), $request->file('foto_kegiatan')); // 1.1 Kirim data & file ke service
    }

    // 2. Menampilkan semua data foto kegiatan (GET /api/detail-event/all)
    public function listAll()
    {
        return $this->service->listAll(); // 2.1 Ambil semua data dari service
    }

    // 3. Menampilkan foto kegiatan berdasarkan event tertentu (GET /api/detail-event/{idEvent})
    public function index($idEvent)
    {
        return $this->service->getByEvent($idEvent); // 3.1 Ambil berdasarkan ID event
    }

    // 4. Mengupdate data foto kegiatan (PUT/PATCH /api/detail-event/{id_detail_event})
    public function update(UpdateDetailEventRequest $request, $id_detail_event)
    {
        $detail = DetailEvent::find($id_detail_event); // 4.1 Cari detail event berdasarkan ID

        if (!$detail) {
            return response()->json(['error' => true, 'message' => 'Detail event tidak ditemukan.'], 404); // 4.2 Jika tidak ditemukan
        }

        return $this->service->update($detail, $request); // 4.3 Kirim data ke service untuk update
    }

    // 5. Menghapus foto kegiatan (DELETE /api/detail-event/{id_detail_event})
    public function destroy($id_detail_event)
    {
        $detail = DetailEvent::find($id_detail_event); // 5.1 Cari detail event

        if (!$detail) {
            return response()->json(['error' => true, 'message' => 'Foto kegiatan tidak ditemukan.'], 404); // 5.2 Jika tidak ditemukan
        }

        return $this->service->destroy($detail); // 5.3 Hapus via service
    }
}
