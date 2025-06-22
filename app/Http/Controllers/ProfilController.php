<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ProfilService;
use App\Http\Requests\Profil\UploadFotoRequest;
use Illuminate\Validation\ValidationException;

class ProfilController extends Controller
{
    protected $profilService;

    public function __construct(ProfilService $profilService)
    {
        $this->profilService = $profilService;
    }

    public function profil(Request $request)
    {
        $data = $this->profilService->getProfil($request->pelanggan);

        return response()->json([
            'message' => $data,
            'status' => 'sukses',
        ]);
    }

    public function uploadFoto(UploadFotoRequest $request)
    {
        $filename = $this->profilService->uploadFoto($request->pelanggan, $request->file('foto_profil'));

        return response()->json([
            'message' => 'Foto profil berhasil diunggah',
            'status' => 'sukses',
            'fotoProfil' => $filename,
        ]);
    }

    public function hapusFoto(Request $request)
    {
        $berhasil = $this->profilService->hapusFoto($request->pelanggan);

        if ($berhasil) {
            return response()->json([
                'message' => 'Foto profil berhasil dihapus',
                'status' => 'sukses',
            ]);
        }

        return response()->json([
            'message' => 'Tidak ada foto untuk dihapus',
            'status' => 'error',
        ], 404);
    }

}