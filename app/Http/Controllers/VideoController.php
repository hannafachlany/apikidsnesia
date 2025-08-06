<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\VideoService;

class VideoController extends Controller
{
    protected $service;

    // 1. Konstruktor: mengikat service video ke controller
    public function __construct(VideoService $service)
    {
        $this->service = $service;
    }

    // 2. Menampilkan seluruh list video (umum, tanpa validasi token/membership)
    public function index(Request $request)
    {
        // 2.1 Ambil data list video dari service
        $data = $this->service->getAllVideos();

        // 2.2 Kembalikan response dalam format JSON
        return response()->json([
            'error' => false,
            'message' => 'List video berhasil diambil.',
            'data' => $data
        ]);
    }

    // 3. Menampilkan detail video tertentu berdasarkan id (dengan validasi membership)
    public function show(Request $request, $idVideo)
    {
        // 3.1 Kirim user dan id video ke service
        return $this->service->getVideoDetail($request->user(), $idVideo);
    }
}
