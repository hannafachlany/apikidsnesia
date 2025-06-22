<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\DetailEvent;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\DetailEvent\StoreDetailEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Http\Requests\DetailEvent\UpdateDetailEventRequest;
use App\Services\DetailEventService;

class DetailEventController extends Controller
{
    protected $service;
    public function __construct(DetailEventService $service)
    {
        $this->service = $service;
    }

    public function store(StoreDetailEventRequest $request)
    {
        return $this->service->store($request->validated(), $request->file('foto_kegiatan'));
    }

    public function listAll()
    {
        return $this->service->listAll();
    }

    public function index($idEvent)
    {
        return $this->service->getByEvent($idEvent);
    }

    public function update(UpdateDetailEventRequest $request, $id_detail_event)
    {
        $detail = DetailEvent::find($id_detail_event);
        if (!$detail) {
            return response()->json(['error' => true, 'message' => 'Detail event tidak ditemukan.'], 404);
        }

        return $this->service->update($detail, $request);
    }

    public function destroy($id_detail_event)
    {
        $detail = DetailEvent::find($id_detail_event);
        if (!$detail) {
            return response()->json(['error' => true, 'message' => 'Foto kegiatan tidak ditemukan.'], 404);
        }

        return $this->service->destroy($detail);
    }

}
