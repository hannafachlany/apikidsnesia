<?php

namespace App\Http\Controllers;

use App\Models\Videos;
use App\Http\Requests\Videos\StoreVideoRequest;
use App\Http\Requests\Videos\UpdateVideoRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AdminVideoController extends Controller
{
    // 1. Ambil semua video
    public function index()
    {
        $videos = Videos::all()->map(function ($video) {
            // 1.1 Tambahkan URL lengkap ke video dan thumbnail
            $video->file_path = asset('storage/videos/' . $video->file_path);
            $video->thumbnail_url = $video->thumbnail_video 
                ? asset('storage/thumbnails/' . $video->thumbnail_video)
                : null;
            return $video;
        });

        // 1.2 Kembalikan response JSON
        return response()->json([
            'error' => false,
            'message' => 'Video berhasil diambil',
            'listVideo' => $videos
        ]);
    }

    // 2. Upload video baru
    public function store(StoreVideoRequest $request)
    {
        $data = $request->validated(); // 2.1 Validasi request

        // 2.2 Simpan file video ke storage
        if ($request->hasFile('file_video')) {
            $file = $request->file('file_video');
            $path = $file->store('public/videos');
            $data['file_path'] = basename($path);
        }

        // 2.3 Simpan thumbnail jika ada
        if ($request->hasFile('thumbnail')) {
            $thumb = $request->file('thumbnail');
            $thumbPath = $thumb->store('public/thumbnails');
            $data['thumbnail_video'] = basename($thumbPath);
        }

        // 2.4 Simpan data ke DB
        $video = Videos::create($data);

        // 2.5 Kembalikan response dengan data video
        return response()->json([
            'error' => false,
            'message' => 'Video berhasil diupload',
            'video' => [
                'id_video' => $video->id_video,
                'judul_video' => $video->judul_video,
                'deskripsi_video' => $video->deskripsi_video,
                'file_path' => asset('storage/videos/' . $video->file_path),
                'thumbnail' => asset('storage/thumbnails/' . $video->thumbnail_video),
                'created_at' => $video->created_at,
                'updated_at' => $video->updated_at
            ]
        ], 201);
    }

    // 3. Ambil detail 1 video
    public function show($id_video)
    {
        $video = Videos::find($id_video); // 3.1 Cari video berdasarkan ID

        if (!$video) {
            // 3.2 Jika tidak ditemukan
            return response()->json([
                'error' => true,
                'message' => 'Video tidak ditemukan',
                'data' => null
            ], 404);
        }

        // 3.3 Tambahkan URL ke file video
        $video->file_path = asset('storage/videos/' . $video->file_path);

        return response()->json([
            'error' => false,
            'message' => 'Detail video berhasil diambil',
            'deatilVideo' => $video
        ]);
    }

    // 4. Update video
    public function update(UpdateVideoRequest $request, $id_video)
    {
        $video = Videos::findOrFail($id_video); // 4.1 Cari video
        $data = $request->validated(); // 4.2 Validasi data

        Log::info('UpdateVideoRequest data:', $data); // 4.3 Logging untuk debugging
        Log::info('Video before update:', $video->toArray());

        // 4.4 Ganti file video jika ada yang baru
        if ($request->hasFile('file_video')) {
            if ($video->file_path && Storage::exists('public/videos/' . $video->file_path)) {
                Storage::delete('public/videos/' . $video->file_path); // Hapus video lama
                Log::info('Deleted old video file:', ['file_path' => $video->file_path]);
            }

            $file = $request->file('file_video');
            $path = $file->store('public/videos');
            $data['file_path'] = basename($path);

            Log::info('Stored new video file:', ['file_path' => $data['file_path']]);
        }

        // 4.5 Ganti thumbnail jika ada yang baru
        if ($request->hasFile('thumbnail')) {
            if ($video->thumbnail_video && Storage::exists('public/thumbnails/' . $video->thumbnail_video)) {
                Storage::delete('public/thumbnails/' . $video->thumbnail_video); // Hapus thumbnail lama
            }

            $thumb = $request->file('thumbnail');
            $thumbPath = $thumb->store('public/thumbnails');
            $data['thumbnail_video'] = basename($thumbPath);
        }

        $updated = $video->update($data); // 4.6 Simpan perubahan
        Log::info('Video update result:', ['success' => $updated]);

        $video->refresh(); // 4.7 Ambil ulang data setelah update
        Log::info('Video after update:', $video->toArray());

        return response()->json([
            'error' => false,
            'message' => 'Video berhasil diupdate',
            'video' => [
                'id_video' => $video->id_video,
                'judul_video' => $video->judul_video,
                'deskripsi_video' => $video->deskripsi_video,
                'file_path' => asset('storage/videos/' . $video->file_path),
                'thumbnail' => $video->thumbnail_video 
                    ? asset('storage/thumbnails/' . $video->thumbnail_video)
                    : null,
                'created_at' => $video->created_at,
                'updated_at' => $video->updated_at
            ]
        ]);
    }

    // 5. Hapus video
    public function destroy($id_video)
    {
        $video = Videos::findOrFail($id_video); // 5.1 Cari video

        // 5.2 Hapus file video jika ada
        if ($video->file_path && Storage::exists('public/videos/' . $video->file_path)) {
            Storage::delete('public/videos/' . $video->file_path);
        }

        $video->delete(); // 5.3 Hapus record di database

        return response()->json([
            'error' => false,
            'message' => 'Video berhasil dihapus'
        ]);
    }
}

