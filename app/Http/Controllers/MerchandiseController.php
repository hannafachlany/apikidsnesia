<?php

namespace App\Http\Controllers;

use App\Http\Requests\Merchandise\StoreMerchandiseRequest;
use App\Http\Requests\Merchandise\UpdateMerchandiseRequest;
use App\Services\MerchandiseService;
class MerchandiseController extends Controller
{
    // 1. Simpan instance MerchandiseService ke dalam properti class
    protected $merchandiseService;

    // 2. Dependency injection untuk MerchandiseService
    public function __construct(MerchandiseService $merchandiseService)
    {
        $this->merchandiseService = $merchandiseService;
    }

    // 3. Menampilkan semua merchandise
    public function index()
    {
        return $this->merchandiseService->showAll();
    }

    // 4. Menampilkan detail satu merchandise berdasarkan ID
    public function show($idMerchandise)
    {
        return $this->merchandiseService->show($idMerchandise);
    }

    // 5. Menyimpan data merchandise baru
    public function store(StoreMerchandiseRequest $request)
    {
        $data = $request->validated(); // 5.1 Validasi input
        $foto = $request->file('foto_merchandise'); // 5.2 Ambil file foto (jika ada)

        return $this->merchandiseService->createMerchandise($data, $foto);
    }

    // 6. Mengupdate data merchandise
    public function update(UpdateMerchandiseRequest $request, $idMerchandise)
    {
        $data = $request->validated(); // 6.1 Validasi input
        $foto = $request->file('foto_merchandise'); // 6.2 Ambil file foto (jika ada)

        return $this->merchandiseService->updateMerchandise($idMerchandise, $data, $foto);
    }

    // 7. Menghapus data merchandise berdasarkan ID
    public function destroy($idMerchandise)
    {
        return $this->merchandiseService->deleteMerchandise($idMerchandise);
    }
}

