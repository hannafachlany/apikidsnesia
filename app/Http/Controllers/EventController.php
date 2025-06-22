<?php

namespace App\Http\Controllers;

use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Services\EventService;

class EventController extends Controller
{
    protected $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function store(StoreEventRequest $request)
    {
        $validated = $request->validated();
        $response = $this->eventService->storeEvent($validated, $request->file('foto_event'));

        return response()->json($response, $response['statusCode']);
    }

    public function update(UpdateEventRequest $request, $idEvent)
    {
        $validated = $request->validated();
        $response = $this->eventService->updateEvent($idEvent, $validated, $request->file('foto_event'));

        return response()->json($response, $response['statusCode']);
    }

    public function showAll()
    {
        $response = $this->eventService->showAll();
        return response()->json($response);
    }

    public function show($idEvent)
    {
        $response = $this->eventService->show($idEvent);
        return response()->json($response, $response['statusCode']);
    }

    public function destroy($idEvent)
    {
        $response = $this->eventService->deleteEvent($idEvent);
        return response()->json($response, $response['statusCode']);
    }
}
