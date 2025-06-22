<?php

namespace App\Http\Controllers;

use App\Http\Requests\Membership\StoreMembershipRequest;
use App\Http\Requests\Membership\UploadBuktiMembershipRequest;
use App\Models\Membership;
use App\Models\PembayaranMembership;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\MembershipService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\BuktiTransferMembershipTerkirim;
use Illuminate\Support\Facades\Storage;
use App\Models\Pelanggan;

class MembershipController extends Controller
{
    protected $service;

    public function __construct(MembershipService $service)
    {
        $this->service = $service;
    }

    public function store(StoreMembershipRequest $request): JsonResponse
    {
        return $this->service->store($request->user(), $request->bank_pengirim);
    }

    public function uploadBuktiTransfer(UploadBuktiMembershipRequest $request, $id): JsonResponse
    {
        $token = $request->bearerToken();
        $pelanggan = Pelanggan::where('token', $token)->first();

        if (!$pelanggan) {
            return response()->json(['error' => true, 'message' => 'Token tidak valid'], 401);
        }

        return $this->service->uploadBuktiTransfer($pelanggan, $request->file('bukti_transfer'), $id);
    }

    public function history(): JsonResponse
    {
        return $this->service->history(request()->user());
    }

    public function showPayment($id): JsonResponse
    {
        return $this->service->showPayment($id);
    }

    public function current(): JsonResponse
    {
        return $this->service->current(request()->user());
    }
}