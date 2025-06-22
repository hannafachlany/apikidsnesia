<?php
namespace App\Http\Controllers;

use App\Http\Requests\Event\PembayaranEventRequest;
use App\Http\Requests\Event\UploadBuktiBayarEventRequest;
use App\Services\PembayaranEventService;
use Illuminate\Http\Request;

class PembayaranEventController extends Controller
{
    protected $pembayaranEventService;

    public function __construct(PembayaranEventService $service)
    {
        $this->middleware('authtoken')->only([
            'getDetailBayar',
            'pilihBank',
            'notaPembelian',
            'uploadBukti'
        ]);

        $this->pembayaranEventService = $service;
    }

    public function getDetailBayar(Request $request, int $idPembelianEvent)
    {
        return $this->pembayaranEventService->getDetailBayar($request, $idPembelianEvent);
    }

    public function pilihBank(PembayaranEventRequest $request)
    {
        return $this->pembayaranEventService->pilihBank($request);
    }

    public function uploadBukti(UploadBuktiBayarEventRequest $request, $idPembayaranEvent)
    {
        return $this->pembayaranEventService->uploadBukti($request, $idPembayaranEvent);
    }

    public function notaPembelian(Request $request, int $idPembelianEvent)
    {
        return $this->pembayaranEventService->notaPembelian($request, $idPembelianEvent);
    }
}
