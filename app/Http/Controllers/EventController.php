<?php
namespace App\Http\Controllers;

use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Services\EventService;

class EventController extends Controller
{
    protected $eventService;

    // 1. Injeksi service melalui konstruktor
    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    // 2. Menyimpan event baru
    public function store(StoreEventRequest $request)
    {
        $validated = $request->validated(); // 2.1 Validasi input
        $response = $this->eventService->storeEvent($validated, $request->file('foto_event')); // 2.2 Panggil service

        return response()->json($response, $response['statusCode']); // 2.3 Kembalikan response sesuai status
    }

    // 3. Memperbarui data event
    public function update(UpdateEventRequest $request, $idEvent)
    {
        $validated = $request->validated(); // 3.1 Validasi input
        $response = $this->eventService->updateEvent($idEvent, $validated, $request->file('foto_event'));

        return response()->json($response, $response['statusCode']);
    }

    // 4. Menampilkan semua event
    public function showAll()
    {
        $response = $this->eventService->showAll(); // 4.1 Ambil semua data dari service
        return response()->json($response); // 4.2 Return JSON tanpa custom status code
    }

    // 5. Menampilkan detail event berdasarkan ID
    public function show($idEvent)
    {
        $response = $this->eventService->show($idEvent);
        return response()->json($response, $response['statusCode']);
    }

    // 6. Menghapus event
    public function destroy($idEvent)
    {
        $response = $this->eventService->deleteEvent($idEvent);
        return response()->json($response, $response['statusCode']);
    }
}
