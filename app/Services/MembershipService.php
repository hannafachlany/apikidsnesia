<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\PembayaranMembership;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Mail\BuktiTransferMembershipTerkirim;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Pelanggan;

class MembershipService
{
    public function store($user, $bankPengirim)
    {
        $idPelanggan = $user->id_pelanggan;

        DB::beginTransaction();
        try {
            $now = Carbon::now();

            $membership = Membership::create([
                'id_pelanggan' => $idPelanggan,
                'tanggal_pembelian' => $now,
                'status' => 'Pending',
            ]);

            $pembayaranMembership = PembayaranMembership::create([
                'id_membership' => $membership->id_membership,
                'bank_pengirim' => $bankPengirim,
                'jumlah_transfer' => 50000,
                'status_pembayaran' => 'Pending',
            ]);

            DB::commit();

            return response()->json([
                'error' => false,
                'message' => 'Pembelian membership berhasil dicatat. Silakan transfer ke rekening berikut.',
                'data' => [
                    'idMembership' => $membership->id_membership,
                    'tanggalPembelian' => $membership->tanggal_pembelian->format('Y-m-d H:i:s'),
                    'namaBankTujuan' => 'BSI',
                    'noRekeningTujuan' => '7123456789',
                    'atasNama' => 'PT KIDSNESIA EDUPARK KREASI',
                    'jumlahTransfer' => 50000,
                    'statusPembayaranMembership' => $pembayaranMembership->status_pembayaran,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Gagal membuat pembelian membership',
                'debug' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadBuktiTransfer($pelanggan, $file, $idPembayaranMembership)
    {
        $pembayaran = PembayaranMembership::with('membership')
            ->whereHas('membership', function ($query) use ($pelanggan) {
                $query->where('id_pelanggan', $pelanggan->id_pelanggan);
            })
            ->find($idPembayaranMembership);

        if (!$pembayaran) {
            return response()->json([
                'error' => true,
                'message' => 'Data pembayaran tidak ditemukan atau bukan milik Anda',
            ], 404);
        }

        $filename = 'bukti_membership_' . Str::random(20) . '.' . $file->getClientOriginalExtension();
        $file->storeAs('public/bukti-member', $filename);

        $pembayaran->update([
            'bukti_transfer' => $filename,
            'status_pembayaran' => 'Menunggu Verifikasi',
            'waktu_transfer' => now(),
        ]);

        try {
            Mail::to($pelanggan->email)->send(new BuktiTransferMembershipTerkirim($pelanggan));
        } catch (\Exception $e) {
            Log::error("Gagal kirim email bukti transfer membership: " . $e->getMessage());
        }

        return response()->json([
            'error' => false,
            'message' => 'Bukti transfer berhasil diupload. Menunggu verifikasi.',
            'urlBuktiTransferMembership' => asset('storage/bukti-member/' . $filename),
            'waktuTransfer' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    public function history($user)
    {
        $idPelanggan = $user->id_pelanggan;

        $data = Membership::with('pembayaran')
            ->where('id_pelanggan', $idPelanggan)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'error' => false,
            'data' => $data
        ]);
    }

    public function showPayment($idPembayaranMembership)
    {
        $pembayaran = PembayaranMembership::with('membership')
            ->where('id_membership', $idPembayaranMembership)
            ->first();

        if (!$pembayaran) {
            return response()->json([
                'error' => true,
                'message' => 'Data pembayaran tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'error' => false,
            'data' => $pembayaran
        ]);
    }

    public function current($user)
    {
        $idPelanggan = $user->id_pelanggan ?? null;

        if (!$idPelanggan) {
            return response()->json([
                'error' => true,
                'message' => 'User tidak ditemukan atau belum login.',
                'data' => null
            ], 401);
        }

        $latestMembership = Membership::with('pembayaran')
            ->where('id_pelanggan', $idPelanggan)
            ->where('status', 'Aktif')
            ->orderByDesc('created_at')
            ->first();

        if (!$latestMembership) {
            return response()->json([
                'error' => false,
                'message' => 'Tidak ada membership aktif.',
                'data' => null
            ]);
        }

        $pembayaran = $latestMembership->pembayaran;

        return response()->json([
            'error' => false,
            'data' => [
                'idMembership' => $latestMembership->id_membership,
                'tanggalMulai' => $latestMembership->tanggal_mulai,
                'tanggalBerakhir' => $latestMembership->tanggal_berakhir,
                'statusMembership' => $latestMembership->status,
                'pembayaranMembership' => $pembayaran ? [
                    'idpembayaranMembership' => $pembayaran->id_pembayaranMembership,
                    'bankPengirim' => $pembayaran->bank_pengirim,
                    'jumlahTransfer' => $pembayaran->jumlah_transfer,
                    'statusPembayaranMembership' => $pembayaran->status_pembayaran,
                    'buktiTransfer' => $pembayaran->bukti_transfer,
                ] : null
            ]
        ]);
    }
}