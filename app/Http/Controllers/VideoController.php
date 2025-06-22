<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\VideoService;

class VideoController extends Controller
{
    protected $service;

    public function __construct(VideoService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data = $this->service->getAllVideos();

        return response()->json([
            'error' => false,
            'message' => 'List video berhasil diambil.',
            'data' => $data
        ]);
    }

    public function show(Request $request, $id_video)
    {
        return $this->service->getVideoDetail($request->user(), $id_video);
    }
}
