<?php

namespace App\Http\Controllers;

use App\Http\Requests\Merchandise\StoreMerchandiseRequest;
use App\Http\Requests\Merchandise\UpdateMerchandiseRequest;
use App\Services\MerchandiseService;

class MerchandiseController extends Controller
{
    protected $merchandiseService;

    public function __construct(MerchandiseService $merchandiseService)
    {
        $this->merchandiseService = $merchandiseService;
    }

    public function index()
    {
        return $this->merchandiseService->showAll();
    }

    public function show($idMerchandise)
    {
        return $this->merchandiseService->show($idMerchandise);
    }

    public function store(StoreMerchandiseRequest $request)
    {
        $data = $request->validated();
        $foto = $request->file('foto_merchandise');

        return $this->merchandiseService->createMerchandise($data, $foto);
    }

    public function update(UpdateMerchandiseRequest $request, $idMerchandise)
    {
        $data = $request->validated();
        $foto = $request->file('foto_merchandise');

        return $this->merchandiseService->updateMerchandise($idMerchandise, $data, $foto);
    }

    public function destroy($idMerchandise)
    {
        return $this->merchandiseService->deleteMerchandise($idMerchandise);
    }
}
