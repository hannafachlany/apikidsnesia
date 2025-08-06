<?php
namespace App\Services;

use App\Models\PembelianEvent;
use App\Models\PembayaranEvent;
use App\Http\Requests\Event\UploadBuktiBayarEventRequest;
use App\Http\Requests\Event\PembayaranEventRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PembayaranEventService
{
    // 1. Mengambil detail pembayaran untuk pembelian event tertentu milik pelanggan
    public function getDetailBayar(Request $request, int $idPembelianEvent)
    {
        $pelanggan = $request->user(); // Ambil data pelanggan dari token

        // Ambil pembelian berdasarkan ID dan ID pelanggan, termasuk relasi detail event dan pembayaran
        $pembelian = PembelianEvent::with(['detailEvent.event', 'pembayaran'])
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->find($idPembelianEvent);

        if (!$pembelian) {
            // Jika tidak ditemukan atau bukan milik pelanggan, kirim error
            return response()->json([
                'error' => true,
                'message' => 'Data pembelian tidak ditemukan atau bukan milik Anda',
            ], 404);
        }

        // Ambil data pembayaran jika ada
        $pembayaran = $pembelian->pembayaran;

        // Susun data respons lengkap
        $response = [
            'idPembayaranEvent' => $pembayaran->id_pembayaran ?? null,
            'idPembelianEvent' => $pembelian->id_pembelian,
            'totalHargaEvent' => $pembelian->total_pembelian,
            'tanggalBayarEvent' => $pembayaran?->tanggal_bayar,
            'statusPembayaranEvent' => $pembayaran->status_pembayaran ?? 'Menunggu Pilihan Bank',
            'bankEvent' => $pembayaran->bank ?? null,
            'detailEvent' => $pembelian->detailEvent->map(function ($item) {
                return [
                    'idEvent' => $item->event->id_event,
                    'idDetailPembelianEvent' => $item->id_pembelian_event,
                    'namaEvent' => $item->event->nama_event,
                    'tanggalEvent' => Carbon::parse($item->event->jadwal_event)->format('d-m-Y'),
                    'jadwalEvent' => Carbon::parse($item->event->jadwal_event)->format('H:i'),
                    'jumlahTiket' => $item->jumlah,
                    'hargaEvent' => $item->event->harga_event ?? 0,
                    'subtotalEvent' => $item->subtotal_event,
                ];
            }),
        ];

        return response()->json([
            'error' => false,
            'detailBayarEvent' => $response,
        ]);
    }

    // 2. Pelanggan memilih bank sebelum upload bukti pembayaran
    public function pilihBank(PembayaranEventRequest $request, int $idPembelianEvent)
    {
        $pelanggan = $request->user(); // Ambil data pelanggan
        $namaBank = $request->input('bankPengirim'); // Ambil nama bank dari input

        // Validasi: hanya bisa pilih bank jika sudah checkout dan belum pernah bayar
        $pembelian = PembelianEvent::where('id_pelanggan', $pelanggan->id_pelanggan)
            ->where('is_checkout', true)
            ->doesntHave('pembayaran')
            ->find($idPembelianEvent);

        if (!$pembelian) {
            return response()->json([
                'error' => true,
                'message' => 'Pembelian tidak ditemukan atau sudah memiliki pembayaran.',
            ], 404);
        }

        // Simpan data pembayaran baru ke database
        $pembayaran = PembayaranEvent::create([
            'id_pembelian' => $pembelian->id_pembelian,
            'nama_pelanggan' => $pelanggan->nama_pelanggan,
            'bank' => $namaBank,
            'total_harga' => $pembelian->total_pembelian,
            'tanggal_bayar' => null,
            'status_pembayaran' => 'Menunggu Pembayaran',
        ]);

        return response()->json([
            'error' => false,
            'message' => 'Bank berhasil dipilih. Silakan lakukan transfer manual.',
            'dataPembayaranEvent' => [
                'idPembayaranEvent' => $pembayaran->id_pembayaran,
                'idPembelianEvent' => $pembayaran->id_pembelian,
                'statusPembayaranEvent' => $pembayaran->status_pembayaran,
                'bankPengirim' => $pembayaran->bank,
                'totalHargaEvent' => $pembayaran->total_harga,
            ],
        ]);
    }

    // 3. Upload bukti pembayaran berupa file gambar
    public function uploadBukti(UploadBuktiBayarEventRequest $request, $idPembayaranEvent)
    {
        $pelanggan = $request->user(); // Ambil data pelanggan dari token

        // Pastikan pembayaran milik pelanggan
        $pembayaran = PembayaranEvent::where('id_pembayaran', $idPembayaranEvent)
            ->whereHas('pembelianEvent', function ($query) use ($pelanggan) {
                $query->where('id_pelanggan', $pelanggan->id_pelanggan);
            })->first();

        if (!$pembayaran) {
            return response()->json([
                'error' => true,
                'message' => 'Data pembayaran tidak ditemukan atau bukan milik Anda.',
            ], 404);
        }

        // Simpan file bukti pembayaran ke storage
        $file = $request->file('buktiBayarEvent');
        $filename = 'buktiBayar_event' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('public/bukti-event', $filename);

        // Update data pembayaran
        $pembayaran->bukti_bayar_event = $filename;
        $pembayaran->tanggal_bayar = now();
        $pembayaran->status_pembayaran = 'Menunggu Verifikasi';
        $pembayaran->save();

        return response()->json([
            'error' => false,
            'message' => 'Bukti pembayaran berhasil diupload.',
            'urlBuktiBayarEvent' => asset('storage/bukti-event/' . $filename),
        ]);
    }

    // 4. List pembelian event yang sudah checkout tapi belum pilih bank
    public function listNotaBelumPilihBank(Request $request)
    {
        $pelanggan = $request->user();

        // Ambil semua pembelian yang belum ada pembayaran
        $list = PembelianEvent::with('detailEvent.event')
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->where('is_checkout', true)
            ->whereDoesntHave('pembayaran')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($p) {
                return [
                    'idPembelianEvent' => $p->id_pembelian,
                    'statusPembelianEvent' => $p->status_pembelian,
                    'tanggalPembelianEvent' => $p->tanggal_pembelian,
                    'totalPembelianEvent' => $p->total_pembelian,
                    'statusPembayaranEvent' => 'Belum memilih bank',
                ];
            });

        return response()->json([
            'error' => false,
            'listNotaBelumPilihBank' => $list,
        ]);
    }

    // 5. Tampilkan detail nota yang belum pilih bank berdasarkan ID
    public function notaBelumPilihBank(Request $request, int $idPembelianEvent)
    {
        $pelanggan = $request->user();

        // Ambil pembelian yang belum memiliki pembayaran
        $pembelian = PembelianEvent::with('detailEvent.event')
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->where('is_checkout', true)
            ->whereDoesntHave('pembayaran')
            ->find($idPembelianEvent);

        if (!$pembelian) {
            return response()->json([
                'error' => true,
                'message' => 'Data pembelian tidak ditemukan atau sudah memilih bank.',
            ], 404);
        }

        return response()->json([
            'error' => false,
            'notaPembelianEvent' => [
                'idPembelianEvent' => $pembelian->id_pembelian,
                'statusPembelianEvent' => $pembelian->status_pembelian,
                'tanggalPembelianEvent' => $pembelian->tanggal_pembelian,
                'totalPembelianEvent' => $pembelian->total_pembelian,
                'statusPembayaranEvent' => 'Belum memilih bank',
                'detailEvent' => $pembelian->detailEvent->map(function ($item) {
                    return [
                        'idEvent' => $item->event->id_event,
                        'namaEvent' => $item->event->nama_event,
                        'jumlahTiket' => $item->jumlah,
                        'hargaEvent' => $item->event->harga_event,
                        'subtotalEvent' => $item->subtotal_event,
                    ];
                }),
            ],
        ]);
    }

    // 6. Menampilkan semua nota pembelian yang sudah memiliki pembayaran
    public function listNotaPembelian(Request $request)
    {
        $pelanggan = $request->user();

        if (!$pelanggan) {
            return response()->json([
                'error' => true,
                'message' => 'Token tidak valid atau kadaluarsa',
            ], 401);
        }

        // Ambil semua pembelian yang sudah ada pembayaran
        $notaList = PembelianEvent::with(['pembayaran'])
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->whereHas('pembayaran')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($pembelian) {
                return [
                    'idPembelianEvent' => $pembelian->id_pembelian,
                    'tanggalPembelianEvent' => $pembelian->tanggal_pembelian,
                    'totalPembelianEvent' => $pembelian->total_pembelian,
                    'statusPembelianEvent' => $pembelian->status_pembelian,
                    'statusPembayaranEvent' => $pembelian->pembayaran->status_pembayaran
                ];
            });

        return response()->json([
            'error' => false,
            'listNotaPembelianEvent' => $notaList,
        ]);
    }

    // 7. Menampilkan detail lengkap nota yang sudah dibayar
    public function notaPembelian(Request $request, int $idPembelianEvent)
    {
        $pelanggan = $request->user();

        // Ambil data pembelian + detail + pembayaran
        $pembelian = PembelianEvent::with(['detailEvent.event', 'pembayaran'])
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->find($idPembelianEvent);

        if (!$pembelian) {
            return response()->json([
                'error' => true,
                'message' => 'Data pembelian tidak ditemukan atau bukan milik Anda',
            ], 404);
        }

        // Susun data lengkap untuk nota
        $response = [
            'idPembelianEvent' => $pembelian->id_pembelian,
            'idPembayaranEvent' => $pembelian->pembayaran?->id_pembayaran ?? null,
            'tanggalPembelianEvent' => $pembelian->tanggal_pembelian,
            'namaPelanggan' => $pelanggan->nama_pelanggan,
            'teleponPelanggan' => $pelanggan->no_hp_pelanggan,
            'emailPelanggan' => $pelanggan->email,
            'totalPembelianEvent' => $pembelian->total_pembelian,
            'statusPembelianEvent' => $pembelian->status_pembelian,
            'statusPembayaranEvent' => $pembelian->pembayaran?->status_pembayaran,
            'detailEvent' => $pembelian->detailEvent->map(function ($item, $i) {
                return [
                    'idDetailPembelianEvent' => $item->id_pembelian_event,
                    'idEvent' => $item->event->id_event,
                    'namaEvent' => $item->event->nama_event,
                    'hargaEvent' => $item->event->harga_event ?? 0,
                    'tanggalEvent' => Carbon::parse($item->event->jadwal_event)->format('d-m-Y'),
                    'jadwalEvent' => Carbon::parse($item->event->jadwal_event)->format('H:i'),
                    'jumlahTiket' => $item->jumlah,
                    'subtotalEvent' => $item->jumlah * ($item->event->harga_event ?? 0),
                ];
            }),
        ];

        return response()->json([
            'error' => false,
            'notaPembelianEvent' => $response,
        ]);
    }
}
