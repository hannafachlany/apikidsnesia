<?php

namespace App\Http\Controllers;

use App\Http\Requests\Membership\StoreMembershipRequest;
use App\Http\Requests\Membership\UploadBuktiMembershipRequest;
use Illuminate\Http\JsonResponse;
use App\Services\MembershipService;

class MembershipController extends Controller
{
    // 1. Properti service untuk menangani logika bisnis
    protected $service;

    // 2. Middleware authtoken untuk membatasi akses endpoint hanya untuk user yang login
    public function __construct(MembershipService $service)
    {
        $this->middleware('authtoken')->only([
            'store',
            'uploadBuktiTransfer',
            'history',
            'current',
        ]);

        $this->service = $service;
    }

    // 3. Endpoint untuk membuat pembelian membership baru
    public function store(StoreMembershipRequest $request): JsonResponse
    {
        return $this->service->store($request->user(), $request->bank_pengirim);
    }

    // 4. Endpoint untuk upload bukti pembayaran membership
    public function uploadBuktiTransfer(UploadBuktiMembershipRequest $request, $id): JsonResponse
    {
        return $this->service->uploadBuktiTransfer(
            $request->user(),
            $request->file('bukti_transfer'),
            $id
        );
    }

    // 5. Menampilkan histori pembayaran membership pelanggan
    public function history(): JsonResponse
    {
        return $this->service->history(request()->user());
    }

    // 6. Admin: Melihat detail pembayaran membership berdasarkan id
    public function showPayment($id): JsonResponse
    {
        return $this->service->showPayment($id);
    }

    // 7. Menampilkan membership aktif saat ini milik user login
    public function current(): JsonResponse
    {
        return $this->service->current(request()->user());
    }
}
