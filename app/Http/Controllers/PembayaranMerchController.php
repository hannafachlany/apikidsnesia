<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PembelianMerch;
use App\Models\PembayaranMerch;
use App\Services\PembayaranMerchService;
use App\Http\Requests\Merchandise\UploadBuktiBayarMerchRequest;
use App\Http\Requests\Merchandise\PembayaranMerchRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PembayaranMerchController extends Controller
{
    protected $service;
    public function __construct(PembayaranMerchService $service)
    {
        $this->middleware('authtoken')->only([
            'getDetailBayar', 'pilihBank', 'uploadBukti', 'notaPembelian',
        ]);
        $this->service = $service;
    }

    public function getDetailBayar(Request $request, int $idPembelianMerch)
    {
        return $this->service->getDetailBayar($request->user(), $idPembelianMerch);
    }

    public function pilihBank(PembayaranMerchRequest $request)
    {
        return $this->service->pilihBank($request->user(), $request->bankPengirim);
    }

    public function uploadBukti(UploadBuktiBayarMerchRequest $request, $id)
    {
        return $this->service->uploadBukti($request->user(), $request->file('buktiBayarMerch'), $id);
    }

    public function notaPembelian(Request $request, int $idPembelianMerch)
    {
        return $this->service->notaPembelian($request->user(), $idPembelianMerch);
    }
}
