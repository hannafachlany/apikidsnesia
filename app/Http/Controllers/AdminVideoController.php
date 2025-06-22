<?php

namespace App\Http\Controllers;

use App\Models\Videos;
use Illuminate\Http\Request;
use App\Http\Requests\Videos\StoreVideoRequest;
use App\Http\Requests\Videos\UpdateVideoRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AdminVideoController extends Controller
{
    // Tampilkan semua video
    public function index()
    {
        $videos = Videos::all()->map(function ($video) {
            $video->file_path = asset('storage/videos/' . $video->file_path);
            return $video;
        });

        return response()->json([
            'error' => false,
            'message' => 'Video berhasil diambil',
            'listVideo' => $videos
        ]);
    }

    // Upload video baru
    public function store(StoreVideoRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('file_video')) {
            $file = $request->file('file_video');
            $path = $file->store('public/videos');
            $data['file_path'] = basename($path);
        }

        $video = Videos::create($data);

        return response()->json([
            'error' => false,
            'message' => 'Video berhasil diupload',
            'video' => [
                'id_video' => $video->id_video,
                'judul_video' => $video->judul_video,
                'deskripsi_video' => $video->deskripsi_video,
                'file_path' => asset('storage/videos/' . $video->file_path),
                'created_at' => $video->created_at,
                'updated_at' => $video->updated_at
            ]
        ], 201);
    }

    // Tampilkan detail video
    public function show($id_video)
    {
        $video = Videos::find($id_video);

        if (!$video) {
            return response()->json([
                'error' => true,
                'message' => 'Video tidak ditemukan',
                'data' => null
            ], 404);
        }

        $video->file_path = asset('storage/videos/' . $video->file_path);

        return response()->json([
            'error' => false,
            'message' => 'Detail video berhasil diambil',
            'deatilVideo' => $video
        ]);
    }

    // Update video
    

    public function update(UpdateVideoRequest $request, $id_video)
    {
        $video = Videos::findOrFail($id_video);
        $data = $request->validated();

        Log::info('UpdateVideoRequest data:', $data);
        Log::info('Video before update:', $video->toArray());

        if ($request->hasFile('file_video')) {
            if ($video->file_path && Storage::exists('public/videos/' . $video->file_path)) {
                Storage::delete('public/videos/' . $video->file_path);
                Log::info('Deleted old video file:', ['file_path' => $video->file_path]);
            }

            $file = $request->file('file_video');
            $path = $file->store('public/videos');
            $data['file_path'] = basename($path);

            Log::info('Stored new video file:', ['file_path' => $data['file_path']]);
        }

        $updated = $video->update($data);
        Log::info('Video update result:', ['success' => $updated]);

        $video->refresh();

        Log::info('Video after update:', $video->toArray());

        return response()->json([
            'error' => false,
            'message' => 'Video berhasil diupdate',
            'video' => [
                'id_video' => $video->id_video,
                'judul_video' => $video->judul_video,
                'deskripsi_video' => $video->deskripsi_video,
                'file_path' => asset('storage/videos/' . $video->file_path),
                'created_at' => $video->created_at,
                'updated_at' => $video->updated_at
            ]
        ]);
    }


    // Hapus video dan file-nya
    public function destroy($id_video)
    {
        $video = Videos::findOrFail($id_video);

        if ($video->file_path && Storage::exists('public/videos/' . $video->file_path)) {
            Storage::delete('public/videos/' . $video->file_path);
        }

        $video->delete();

        return response()->json([
            'error' => false,
            'message' => 'Video berhasil dihapus'
        ]);
    }
}
