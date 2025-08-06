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

    // Constructor
    public function __construct(PembayaranMerchService $service)
    {
        // Middleware auth token hanya untuk endpoint tertentu
        $this->middleware('authtoken')->only([
            'getDetailBayar', 'pilihBank', 'uploadBukti', 'notaPembelian',
        ]);

        // Inject service ke controller
        $this->service = $service;
    }

    // 1. Menampilkan detail pembayaran berdasarkan ID pembelian merchandise
    public function getDetailBayar(Request $request, int $idPembelianMerch)
    {
        // Ambil data user dari request, teruskan ke service
        return $this->service->getDetailBayar($request->user(), $idPembelianMerch);
    }

    // 2. Proses memilih bank untuk transfer manual
    public function pilihBank(PembayaranMerchRequest $request, int $idPembelianMerch)
    {
        // Kirim data user, nama bank, dan ID pembelian ke service
        return $this->service->pilihBank(
            $request->user(),
            $request->input('bankPengirim'),
            $idPembelianMerch
        );
    }

    // 3. Upload bukti transfer manual
    public function uploadBukti(UploadBuktiBayarMerchRequest $request, $id)
    {
        // Kirim file dan user ke service berdasarkan ID pembayaran
        return $this->service->uploadBukti(
            $request->user(),
            $request->file('buktiBayarMerch'),
            $id
        );
    }

    // 4. Menampilkan daftar nota pembelian yang belum memilih bank
    public function listNotaBelumPilihBank(Request $request)
    {
        // Ambil semua nota pelanggan yang sudah checkout tapi belum pilih bank
        return $this->service->listNotaBelumPilihBank($request->user());
    }

    // 5. Menampilkan 1 nota yang belum pilih bank berdasarkan ID
    public function notaBelumPilihBank(Request $request, int $idPembelianMerch)
    {
        // Kirim user dan ID pembelian ke service untuk ambil detail nota
        return $this->service->notaBelumPilihBank($request->user(), $idPembelianMerch);
    }

    // 6. Menampilkan semua nota pembelian (yang sudah memilih bank)
    public function listNotaPembelian(Request $request)
    {
        // Kirim user ke service untuk ambil list nota pembelian lengkap
        return $this->service->listNotaPembelian($request->user());
    }

    // 7. Menampilkan detail nota pembelian lengkap berdasarkan ID
    public function notaPembelian(Request $request, int $idPembelianMerch)
    {
        // Kirim user dan ID pembelian ke service untuk ambil detail nota
        return $this->service->notaPembelian($request->user(), $idPembelianMerch);
    }
}
