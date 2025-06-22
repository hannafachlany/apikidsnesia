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
    public function getDetailBayar(Request $request, int $idPembelianEvent)
    {
        $pelanggan = $request->user();

        $pembelian = PembelianEvent::with(['detailEvent.event', 'pembayaran'])
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->find($idPembelianEvent);

        if (!$pembelian) {
            return response()->json([
                'error' => true,
                'message' => 'Data pembelian tidak ditemukan atau bukan milik Anda',
            ], 404);
        }

        $pembayaran = $pembelian->pembayaran;

        $response = [
            'idPembayaranEvent' => $pembayaran->id_pembayaran ?? null,
            'idPembelianEvent' => $pembelian->id_pembelian,
            'totalHargaEvent' => $pembelian->total_pembelian,
            'tanggalBayarEvent' => $pembayaran?->tanggal_bayar,
            'statusPembayaranEvent' => $pembayaran->status_pembayaran ?? 'Menunggu Pilihan Bank',
            'bankEvent' => $pembayaran->bank ?? null,
            'detailEvent' => $pembelian->detailEvent->map(function ($item) {
                return [
                    'namaEvent' => $item->event->nama_event,
                    'jumlahTiket' => $item->jumlah,
                    'hargaEvent' => $item->event->harga_event ?? $item->event->harga ?? 0,
                ];
            }),
        ];

        return response()->json([
            'error' => false,
            'detailBayarEvent' => $response,
        ]);
    }

    public function pilihBank(PembayaranEventRequest $request)
    {
        $pelanggan = $request->user();
        $namaBank = $request->input('bankPengirim');

        $pembelian = PembelianEvent::where('id_pelanggan', $pelanggan->id_pelanggan)
            ->where('is_checkout', true)
            ->whereDoesntHave('pembayaran')
            ->oldest()
            ->first();

        if (!$pembelian) {
            return response()->json([
                'error' => true,
                'message' => 'Tidak ada pembelian event aktif atau pembayaran sudah dibuat.',
            ], 404);
        }

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
                'statusPembayaranEvent' => $pembayaran->status_pembayaran,
                'bankPengirim' => $pembayaran->bank,
                'totalHargaEvent' => $pembayaran->total_harga,
            ],
        ]);
    }

    public function uploadBukti(UploadBuktiBayarEventRequest $request, $idPembayaranEvent)
    {
        $pembayaran = PembayaranEvent::find($idPembayaranEvent);
        if (!$pembayaran) {
            return response()->json([
                'error' => true,
                'message' => 'Data pembayaran tidak ditemukan atau bukan milik Anda.',
            ], 404);
        }

        $file = $request->file('buktiBayarEvent');
        $filename = 'buktiBayar_event' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('public/bukti-event', $filename);

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

    public function notaPembelian(Request $request, int $idPembelianEvent)
    {
        $pelanggan = $request->user();

        $pembelian = PembelianEvent::with(['detailEvent.event'])
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->find($idPembelianEvent);

        if (!$pembelian) {
            return response()->json([
                'error' => true,
                'message' => 'Data pembelian tidak ditemukan atau bukan milik Anda',
            ], 404);
        }

        $response = [
            'noPembelianEvent' => $pembelian->id_pembelian,
            'tanggalPembelianEvent' => $pembelian->tanggal_pembelian,
            'namaPelanggan' => $pelanggan->nama_pelanggan,
            'teleponPelanggan' => $pelanggan->no_hp_pelanggan,
            'emailPelanggan' => $pelanggan->email,
            'totalPembelianEvent' => $pembelian->total_pembelian,
            'statusPembelianEvent' => $pembelian->status_pembelian,
            'statusPembayaranEvent' => $pembelian->pembayaran?->status_pembayaran ?? 'Belum memilih metode pembayaran',
            'detailEvent' => $pembelian->detailEvent->map(function ($item, $i) {
                return [
                    'no' => $i + 1,
                    'namaEvent' => $item->event->nama_event,
                    'hargaEvent' => $item->event->harga_event ?? 0,
                    'jadwalEvent' => Carbon::parse($item->event->tanggal_event)->format('d-m-Y'),
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
