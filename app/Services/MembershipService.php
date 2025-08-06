<?php
namespace App\Services;

use App\Models\Membership;
use App\Models\PembayaranMembership;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Mail\BuktiTransferMembershipTerkirim;
use Carbon\Carbon;
use App\Models\Pelanggan;

class MembershipService
{
    // 1. Menyimpan data pembelian membership baru (belum upload bukti)
    public function store($user, $bankPengirim)
    {
        // 1.1 Ambil ID pelanggan dari user yang login
        $idPelanggan = $user->id_pelanggan;

        // 1.2 Mulai transaksi database
        DB::beginTransaction();
        try {
            // 1.3 Ambil waktu sekarang
            $now = Carbon::now();

            // 1.4 Buat record baru di tabel membership
            $membership = Membership::create([
                'id_pelanggan' => $idPelanggan,
                'tanggal_pembelian' => $now,
                'status' => 'Pending',
            ]);

            // 1.5 Ambil data pelanggan (untuk nama)
            $pelanggan = Pelanggan::find($idPelanggan);

            // 1.6 Buat data pembayaran awal (tanpa bukti)
            $pembayaranMembership = PembayaranMembership::create([
                'id_membership' => $membership->id_membership,
                'nama_pelanggan' => $pelanggan->nama_pelanggan,
                'bank_pengirim' => $bankPengirim,
                'jumlah_transfer' => 50000,
                'status_pembayaran' => 'Menunggu verifikasi',
            ]);

            // 1.7 Commit transaksi
            DB::commit();

            // 1.8 Kembalikan response JSON
            return response()->json([
                'error' => false,
                'message' => 'Pembelian membership berhasil dicatat. Silakan transfer ke rekening berikut.',
                'data' => [
                    'idMembership' => $membership->id_membership,
                    'idPembayaranMembership' => $pembayaranMembership->id_pembayaranMembership,
                    'tanggalPembelian' => $membership->tanggal_pembelian->format('Y-m-d H:i:s'),
                    'namaBankTujuan' => 'BSI',
                    'noRekeningTujuan' => '7123456789',
                    'atasNama' => 'PT KIDSNESIA EDUPARK KREASI',
                    'jumlahTransfer' => 50000,
                    'statusPembayaranMembership' => $pembayaranMembership->status_pembayaran,
                ]
            ]);
        } catch (\Exception $e) {
            // 1.9 Rollback jika error
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Gagal membuat pembelian membership',
                'debug' => $e->getMessage()
            ], 500);
        }
    }

    // 2. Upload bukti transfer untuk pembayaran membership
    public function uploadBuktiTransfer($pelanggan, $file, $idPembayaranMembership)
    {
        // 2.1 Logging untuk debug
        Log::info('Fungsi uploadBuktiTransfer dipanggil.');

        // 2.2 Cek apakah file dikirim
        if (!$file) {
            Log::warning('Tidak ada file yang diterima oleh API.');
        } else {
            Log::info('File diterima: ' . $file->getClientOriginalName());
        }

        // 2.3 Cari data pembayaran berdasarkan ID & ID pelanggan
        $pembayaran = PembayaranMembership::with('membership')
            ->whereHas('membership', function ($query) use ($pelanggan) {
                $query->where('id_pelanggan', $pelanggan->id_pelanggan);
            })
            ->find($idPembayaranMembership);

        // 2.4 Jika tidak ditemukan, return error
        if (!$pembayaran) {
            Log::warning('Data pembayaran tidak ditemukan atau bukan milik pelanggan. ID: ' . $idPembayaranMembership);
            return response()->json([
                'error' => true,
                'message' => 'Data pembayaran tidak ditemukan atau bukan milik Anda',
            ], 404);
        }

        // 2.5 Simpan file bukti transfer ke storage
        $filename = 'bukti_membership_' . Str::random(20) . '.' . $file->getClientOriginalExtension();

        try {
            $path = $file->storeAs('public/bukti-member', $filename);
            Log::info("File disimpan di path: $path");
        } catch (\Exception $e) {
            Log::error("Gagal menyimpan file: " . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Gagal menyimpan file.',
            ], 500);
        }

        // 2.6 Update data pembayaran dengan file bukti & waktu transfer
        $pembayaran->update([
            'bukti_transfer' => $filename,
            'status_pembayaran' => 'Menunggu Verifikasi',
            'waktu_transfer' => now(),
        ]);

        // 2.7 Kirim email notifikasi bukti transfer
        try {
            Mail::to($pelanggan->email)->send(new BuktiTransferMembershipTerkirim($pelanggan));
            Log::info("Email bukti transfer dikirim ke: " . $pelanggan->email);
        } catch (\Exception $e) {
            Log::error("Gagal kirim email bukti transfer membership: " . $e->getMessage());
        }

        // 2.8 Response sukses ke user
        return response()->json([
            'error' => false,
            'message' => 'Bukti transfer berhasil diupload. Menunggu verifikasi.',
            'urlBuktiTransferMembership' => asset('storage/bukti-member/' . $filename),
            'waktuTransfer' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    // 3. Menampilkan riwayat semua membership milik pelanggan
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

    // 4. Menampilkan detail pembayaran berdasarkan ID membership
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

    // 5. Ambil data membership aktif (jika ada)
    public function current($user)
    {
        $idPelanggan = $user->id_pelanggan ?? null;

        // 5.1 Validasi login
        if (!$idPelanggan) {
            return response()->json([
                'error' => true,
                'message' => 'User tidak ditemukan atau belum login.',
                'data' => null
            ], 401);
        }

        // 5.2 Ambil data membership aktif terakhir
        $latestMembership = Membership::with('pembayaran')
            ->where('id_pelanggan', $idPelanggan)
            ->where('status', 'Aktif')
            ->orderByDesc('created_at')
            ->first();

        // 5.3 Jika tidak ada yang aktif, balikan info
        if (!$latestMembership) {
            return response()->json([
                'error' => false,
                'message' => 'Tidak ada membership aktif.',
                'data' => null
            ]);
        }

        $pembayaran = $latestMembership->pembayaran;

        // 5.4 Kembalikan data lengkap membership aktif
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
