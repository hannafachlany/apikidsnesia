<?php

namespace App\Services;

use App\Models\Videos;

class VideoService
{
    // 1. Mengambil semua data video untuk ditampilkan di daftar (list video)
    public function getAllVideos()
    {
        // 1.1 Ambil data dari tabel videos (judul, deskripsi, file path, thumbnail)
        $videos = Videos::select('id_video', 'judul_video', 'deskripsi_video', 'file_path', 'thumbnail_video')->get();

        // 1.2 Format response dengan mengubah nama field menjadi camelCase
        return $videos->map(function ($video) {
            return [
                'idVideo' => $video->id_video,
                'judulVideo' => $video->judul_video,
                'deskripsiVideo' => $video->deskripsi_video,
                'filePath' => secure_asset('storage/videos/' . ltrim($video->file_path, '/')),
                'thumbnail' => $video->thumbnail_video 
                    ? secure_asset('storage/thumbnails/' . ltrim($video->thumbnail_video, '/'))
                    : null,
            ];
        });
    }

    // 2. Mengambil detail 1 video berdasarkan ID, hanya jika pelanggan memiliki membership aktif
    public function getVideoDetail($user, $idVideo)
    {
        // 2.1 Validasi token: cek apakah user login
        if (!$user) {
            return response()->json([
                'error' => true,
                'message' => 'Token tidak valid',
            ], 401);
        }

        // 2.2 Cek apakah user memiliki membership aktif
        if (!$user->membership_aktif) {
            return response()->json([
                'error' => true,
                'message' => 'Akses ditolak. Membership tidak aktif.',
            ], 403);
        }

        // 2.3 Ambil data video berdasarkan ID
        $video = Videos::find($idVideo);

        // 2.4 Jika tidak ditemukan, kembalikan error
        if (!$video) {
            return response()->json([
                'error' => true,
                'message' => 'Video tidak ditemukan.',
            ], 404);
        }

        // 2.5 Jika ditemukan, kembalikan detail lengkap dalam format JSON
        return response()->json([
            'error' => false,
            'message' => 'Detail video berhasil diambil.',
            'detailVideo' => [
                'idVideo' => $video->id_video,
                'judulVideo' => $video->judul_video,
                'deskripsiVideo' => $video->deskripsi_video,
                'filePath' => secure_asset('storage/videos/' . ltrim($video->file_path, '/')),
                'thumbnail' => $video->thumbnail_video 
                    ? secure_asset('storage/thumbnails/' . ltrim($video->thumbnail_video, '/'))
                    : null,
            ]
        ]);
    }
}
