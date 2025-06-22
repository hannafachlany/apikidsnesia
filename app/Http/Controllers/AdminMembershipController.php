<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan;
use App\Models\PembayaranMembership;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use App\Mail\MembershipBerhasilVerifikasi;

class AdminMembershipController extends Controller
{
    // GET: /api/admin/pelanggan?page=1
    
    public function index(Request $request): JsonResponse
    {
        $limit = 5;
        $page = $request->query('page', 1);
        $offset = ($page - 1) * $limit;

        $total = Pelanggan::count();

        $pelanggans = Pelanggan::with(['memberships.pembayaran'])
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(function ($pelanggan) {
                $latestMembership = $pelanggan->memberships->sortByDesc('created_at')->first();

                return [
                    'id_pelanggan' => $pelanggan->id_pelanggan,
                    'nama_pelanggan' => $pelanggan->nama_pelanggan,
                    'email' => $pelanggan->email,
                    'no_hp_pelanggan' => $pelanggan->no_hp_pelanggan,
                    'membership_status' => $latestMembership->status ?? 'Nonaktif',
                    'membership' => $latestMembership ? [
                        'id_membership' => $latestMembership->id_membership,
                        'status' => $latestMembership->status,
                        'tanggal_mulai' => $latestMembership->tanggal_mulai,
                        'tanggal_berakhir' => $latestMembership->tanggal_berakhir,
                        'pembayaran' => $latestMembership->pembayaran ? [
                            'id_pembayaranMembership' => $latestMembership->pembayaran->id_pembayaranMembership,
                            'bank_pengirim' => $latestMembership->pembayaran->bank_pengirim,
                            'jumlah_transfer' => $latestMembership->pembayaran->jumlah_transfer,
                            'status_pembayaran' => $latestMembership->pembayaran->status_pembayaran,
                            'bukti_transfer' => $latestMembership->pembayaran->bukti_transfer,
                        ] : null,
                    ] : null
                ];
            });

        return response()->json([
            'pelanggans' => $pelanggans,
            'totalPages' => ceil($total / $limit),
            'currentPage' => (int) $page
        ]);
    }

    // PUT: /api/admin/pembayaran-membership/{id}
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status_pembayaran' => 'required|in:Pending,Berhasil',
        ]);

        $pembayaran = PembayaranMembership::with('membership')->find($id);

        if (!$pembayaran) {
            return response()->json([
                'error' => true,
                'message' => 'Data pembayaran tidak ditemukan.'
            ], 404);
        }

        $pembayaran->status_pembayaran = $request->status_pembayaran;
        $pembayaran->save();

        if ($request->status_pembayaran === 'Berhasil') {
        $membership = $pembayaran->membership;

        $now = now();
        $membership->update([
            'status' => 'Aktif',
            'tanggal_mulai' => $now,
            'tanggal_berakhir' => $now->copy()->addMinutes(10)
        ]);

        // Ambil data pelanggan
        $pelanggan = $membership->pelanggan;

        // ✅ Update is_membership ke TRUE
        $pelanggan->update([
            'is_membership' => true
        ]);

        // Kirim email notifikasi membership berhasil
        try {
            Mail::to($pelanggan->email)->send(new MembershipBerhasilVerifikasi($pelanggan));
        } catch (\Exception $e) {
            // boleh log error jika perlu
        }
    }


        return response()->json([
            'error' => false,
            'message' => 'Status pembayaran berhasil diperbarui.',
            'data' => $pembayaran
        ]);
    }

    public function showPembayaranByMembership($id): JsonResponse
    {
        $pembayaran = PembayaranMembership::with('membership')->where('id_membership', $id)->first();

        if (!$pembayaran) {
            return response()->json([
                'error' => true,
                'message' => 'Data pembayaran tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'error' => false,
            'data' => $pembayaran
        ]);
    }


}
