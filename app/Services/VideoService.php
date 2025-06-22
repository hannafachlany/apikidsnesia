<?php

namespace App\Services;

use App\Models\Videos;

class VideoService
{
    public function getAllVideos()
    {
        $videos = Videos::select('id_video', 'judul_video', 'deskripsi_video', 'file_path')->get();

        return $videos->map(function ($video) {
            return [
                'idVideo' => $video->id_video,
                'judulVideo' => $video->judul_video,
                'deskripsiVideo' => $video->deskripsi_video,
                'filePath' => $video->file_path,
            ];
        });
    }

    public function getVideoDetail($user, $id_video)
    {
        if (!$user) {
            return response()->json([
                'error' => true,
                'message' => 'Token tidak valid',
            ], 401);
        }

        if (!$user->membership_aktif) {
            return response()->json([
                'error' => true,
                'message' => 'Akses ditolak. Membership tidak aktif.',
            ], 403);
        }

        $video = Videos::find($id_video);

        if (!$video) {
            return response()->json([
                'error' => true,
                'message' => 'Video tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'error' => false,
            'message' => 'Detail video berhasil diambil.',
            'data' => [
                'idVideo' => $video->id_video,
                'judulVideo' => $video->judul_video,
                'deskripsiVideo' => $video->deskripsi_video,
                'filePath' => asset('storage/' . ltrim(str_replace('storage/', '', $video->file_path), '/')),
            ]
        ]);
    }
}
