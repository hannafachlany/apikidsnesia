<?php
namespace App\Http\Controllers;

use App\Http\Requests\Event\PembayaranEventRequest;
use App\Http\Requests\Event\UploadBuktiBayarEventRequest;
use App\Services\PembayaranEventService;
use Illuminate\Http\Request;

class PembayaranEventController extends Controller
{
    protected $pembayaranEventService;

    // Konstruktor: Menyimpan dependency service dan menerapkan middleware token
    public function __construct(PembayaranEventService $service)
    {
        // Middleware authtoken hanya untuk endpoint yang digunakan oleh pelanggan login
        $this->middleware('authtoken')->only([
            'getDetailBayar',
            'pilihBank',
            'notaPembayaran',
            'uploadBukti'
        ]);

        $this->pembayaranEventService = $service;
    }

    // 1. Menampilkan detail pembayaran berdasarkan ID pembelian (hanya milik pelanggan login)
    public function getDetailBayar(Request $request, int $idPembelianEvent)
    {
        return $this->pembayaranEventService->getDetailBayar($request, $idPembelianEvent);
    }

    // 2. Digunakan pelanggan untuk memilih bank saat ingin membayar event
    public function pilihBank(PembayaranEventRequest $request, int $idPembelianEvent)
    {
        return $this->pembayaranEventService->pilihBank($request, $idPembelianEvent);
    }

    // 3. Mengunggah bukti pembayaran (form-data gambar) untuk pembayaran event
    public function uploadBukti(UploadBuktiBayarEventRequest $request, $idPembayaranEvent)
    {
        return $this->pembayaranEventService->uploadBukti($request, $idPembayaranEvent);
    }

    // 4. Menampilkan nota yang belum memilih bank (biasanya digunakan di tahap awal setelah checkout)
    public function notaBelumPilihBank(Request $request, int $idPembelianEvent)
    {
        return $this->pembayaranEventService->notaBelumPilihBank($request, $idPembelianEvent);
    }

    // 5. Menampilkan semua daftar nota yang belum memilih bank, milik pelanggan login
    public function listNotaBelumPilihBank(Request $request)
    {
        return $this->pembayaranEventService->listNotaBelumPilihBank($request);
    }

    // 6. Menampilkan detail nota pembayaran (sudah pilih bank) berdasarkan ID pembelian event
    public function notaPembelian(Request $request, int $idPembelianEvent)
    {
        return $this->pembayaranEventService->notaPembelian($request, $idPembelianEvent);
    }

    // 7. Menampilkan semua nota pembelian yang sudah memilih bank, milik pelanggan login
    public function listNotaPembelian(Request $request)
    {
        return app(PembayaranEventService::class)->listNotaPembelian($request);
    }
}
