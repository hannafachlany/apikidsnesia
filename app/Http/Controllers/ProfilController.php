<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ProfilService;
use App\Http\Requests\Profil\UploadFotoRequest;
use Illuminate\Validation\ValidationException;

class ProfilController extends Controller
{
    protected $profilService;

    // 0. Konstruktor untuk menyuntikkan service yang menangani logika profil pelanggan
    public function __construct(ProfilService $profilService)
    {
        $this->profilService = $profilService;
    }

    // 1. Mengambil data profil pelanggan berdasarkan token yang dikirim
    public function profil(Request $request)
    {
        // 1.1 Ambil data pelanggan dari request (disediakan oleh middleware/token)
        $data = $this->profilService->getProfil($request->pelanggan);

        // 1.2 Kembalikan data profil dalam format JSON
        return response()->json([
            'message' => $data,
            'status' => 'sukses',
        ]);
    }

    // 2. Mengunggah atau mengganti foto profil pelanggan
    public function uploadFoto(UploadFotoRequest $request)
    {
        // 2.1 Validasi otomatis dijalankan oleh UploadFotoRequest (ukuran, format, dll)

        // 2.2 Kirim file yang sudah tervalidasi ke service untuk disimpan
        $filename = $this->profilService->uploadFoto(
            $request->pelanggan, 
            $request->file('foto_profil')
        );

        // 2.3 Kembalikan response sukses dan tampilkan URL foto
        return response()->json([
            'message' => 'Foto profil berhasil diunggah',
            'status' => 'sukses',
            'fotoProfil' => asset('storage/foto_profil/' . $filename),
        ]);
    }

    // 3. Menghapus foto profil pelanggan yang sedang login
    public function hapusFoto(Request $request)
    {
        // 3.1 Kirim pelanggan ke service untuk menghapus foto
        $berhasil = $this->profilService->hapusFoto($request->pelanggan);

        // 3.2 Jika berhasil menghapus (ada foto sebelumnya)
        if ($berhasil) {
            return response()->json([
                'message' => 'Foto profil berhasil dihapus',
                'status' => 'sukses',
            ]);
        }

        // 3.3 Jika pelanggan belum pernah upload foto atau tidak ditemukan
        return response()->json([
            'message' => 'Tidak ada foto untuk dihapus',
            'status' => 'error',
        ], 404);
    }
}
