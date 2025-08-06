<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan;
use App\Models\PembayaranMembership;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use App\Mail\MembershipGagalVerifikasi;
use App\Mail\MembershipBerhasilVerifikasi;

class AdminPelangganController extends Controller
{
    // 1. Menghapus data pelanggan berdasarkan ID
    public function destroy($id): JsonResponse
    {
        $pelanggan = Pelanggan::find($id); //1.1 Cari pelanggan berdasarkan ID

        if (!$pelanggan) {
            return response()->json([
                'error' => true,
                'message' => 'Pelanggan tidak ditemukan.'
            ], 404); //1.2 Jika tidak ditemukan, kirim response error
        }

        $pelanggan->delete(); //1.3 Hapus pelanggan jika ditemukan

        return response()->json([
            'error' => false,
            'message' => 'Pelanggan berhasil dihapus.'
        ]); //1.4 Response sukses
    }

    // 2. Menampilkan daftar pelanggan dengan data membership & pembayaran (pagination)
    public function index(Request $request): JsonResponse
    {
        $limit = 5; //2.1 Jumlah data per halaman
        $page = $request->query('page', 1); //2.2 Ambil query page, default ke 1
        $offset = ($page - 1) * $limit; //2.3 Hitung offset

        $total = Pelanggan::count(); //2.4 Total data pelanggan

        $pelanggans = Pelanggan::with(['memberships.pembayaran']) //2.5 Ambil relasi membership dan pembayaran
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(function ($pelanggan) {
                $latestMembership = $pelanggan->memberships->sortByDesc('created_at')->first(); //2.6 Ambil membership terbaru

                return [
                    'id_pelanggan' => $pelanggan->id_pelanggan,
                    'nama_pelanggan' => $pelanggan->nama_pelanggan,
                    'email' => $pelanggan->email,
                    'no_hp_pelanggan' => $pelanggan->no_hp_pelanggan,
                    'membership_status' => $latestMembership->status ?? 'Nonaktif', //2.7 Status membership default 'Nonaktif'
                    'membership' => $latestMembership ? [ //2.8 Data membership jika ada
                        'id_membership' => $latestMembership->id_membership,
                        'status' => $latestMembership->status,
                        'tanggal_mulai' => $latestMembership->tanggal_mulai,
                        'tanggal_berakhir' => $latestMembership->tanggal_berakhir,
                        'pembayaran' => $latestMembership->pembayaran ? [ //2.9 Data pembayaran jika ada
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
            'pelanggans' => $pelanggans, //2.10 Data pelanggan dikembalikan sebagai array
            'totalPages' => ceil($total / $limit), //2.11 Hitung total halaman
            'currentPage' => (int) $page
        ]);
    }

    // 3. Memperbarui status pembayaran membership
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status_pembayaran' => 'required|in:Pending,Berhasil,Gagal',
        ]); //3.1 Validasi status pembayaran harus salah satu dari nilai ini

        $pembayaran = PembayaranMembership::with('membership.pelanggan')->find($id); //3.2 Ambil pembayaran dengan relasi membership & pelanggan

        if (!$pembayaran) {
            return response()->json([
                'error' => true,
                'message' => 'Data pembayaran tidak ditemukan.'
            ], 404); //3.3 Jika tidak ada, kirim error
        }

        $pembayaran->status_pembayaran = $request->status_pembayaran;
        $pembayaran->save(); //3.4 Update status pembayaran

        $membership = $pembayaran->membership;
        $pelanggan = $membership->pelanggan;

        if ($request->status_pembayaran === 'Berhasil') {
            $now = now();
            $membership->update([
                'status' => 'Aktif',
                'tanggal_mulai' => $now,
                'tanggal_berakhir' => $now->copy()->addMinutes(10) //3.5 Set masa aktif membership (sementara 10 menit)
            ]);

            $pelanggan->update([
                'is_membership' => true //3.6 Tandai pelanggan sebagai anggota aktif
            ]);

            try {
                Mail::to($pelanggan->email)->send(new MembershipBerhasilVerifikasi($pelanggan)); //3.7 Kirim email verifikasi berhasil
            } catch (\Exception $e) {
                // Optional: log error email
            }

        } elseif ($request->status_pembayaran === 'Gagal') {
            $membership->update(['status' => 'Pending']); //3.8 Jika gagal, kembalikan status membership ke Pending

            try {
                Mail::to($pelanggan->email)->send(new MembershipGagalVerifikasi($pelanggan)); //3.9 Kirim email gagal verifikasi
            } catch (\Exception $e) {
                // Optional: log error email
            }
        }

        return response()->json([
            'error' => false,
            'message' => 'Status pembayaran berhasil diperbarui.',
            'data' => $pembayaran //3.10 Kembalikan data pembayaran
        ]);
    }

    // 4. Menampilkan detail pembayaran berdasarkan ID membership
    public function showPembayaranByMembership($id): JsonResponse
    {
        $pembayaran = PembayaranMembership::with('membership')->where('id_membership', $id)->first(); //4.1 Ambil pembayaran berdasarkan id_membership

        if (!$pembayaran) {
            return response()->json([
                'error' => true,
                'message' => 'Data pembayaran tidak ditemukan.'
            ], 404); //4.2 Jika tidak ada, kirim error
        }

        return response()->json([
            'error' => false,
            'data' => $pembayaran //4.3 Kembalikan data pembayaran
        ]);
    }
}
