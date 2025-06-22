<?php

namespace App\Services;

use App\Models\Event;
use App\Models\PembelianEvent;
use App\Models\DetailPembelianEvent;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EventCartService
{
    public function createCart($idPelanggan, $items)
    {
        return DB::transaction(function () use ($idPelanggan, $items) {
            $cart = collect($items)->map(function ($item) {
                $event = Event::findOrFail($item['idEvent']);
                return [
                    'eventModel' => $event,
                    'idEvent' => $event->id_event,
                    'namaEvent' => $event->nama_event,
                    'hargaEvent' => $event->harga_event,
                    'jumlahTiket' => $item['jumlah'],
                    'subtotalEvent' => $event->harga_event * $item['jumlah'],
                ];
            });

            $total = $cart->sum('subtotalEvent');

            $pembelian = PembelianEvent::create([
                'id_pelanggan' => $idPelanggan,
                'tanggal_pembelian' => null,
                'total_pembelian' => $total,
                'status_pembelian' => 'Belum Checkout',
                'is_checkout' => false,
            ]);

            foreach ($cart as $item) {
                DetailPembelianEvent::create([
                    'id_pembelian' => $pembelian->id_pembelian,
                    'id_event' => $item['idEvent'],
                    'jumlah' => $item['jumlahTiket'],
                ]);
            }

            return [
                'idPembelian' => $pembelian->id_pembelian,
                'cart' => $cart->map(function ($i) {
                    return [
                        'idEvent' => $i['idEvent'],
                        'namaEvent' => $i['namaEvent'],
                        'hargaEvent' => $i['hargaEvent'],
                        'jumlahTiket' => $i['jumlahTiket'],
                        'subtotalEvent' => $i['subtotalEvent'],
                    ];
                }),
                'total' => $total,
            ];
        });
    }


    /**
     * List semua pembelian event yang belum checkout milik pelanggan
     */
    public function listCart($idPelanggan)
    {
        return PembelianEvent::where('id_pelanggan', $idPelanggan)
            ->where('is_checkout', false)
            ->get(['id_pembelian', 'total_pembelian', 'tanggal_pembelian', 'status_pembelian']);
    }


    /**
     * Ambil detail cart berdasarkan ID
     */
    public function getCartDetailById($idPembelianEvent)
    {
        $pembelian = PembelianEvent::with(['detailEvent.event'])
            ->where('id_pembelian', $idPembelianEvent)
            ->where('is_checkout', false)
            ->first();

        if (!$pembelian) {
            return null;
        }

        $items = $pembelian->detailEvent->map(function ($detail) {
            if (!$detail->event) {
                return [
                    'idEvent' => null,
                    'namaEvent' => 'Event sudah dihapus',
                    'hargaEvent' => 0,
                    'jumlahTiket' => $detail->jumlah,
                    'subtotalEvent' => 0,
                ];
            }

            return [
                'idEvent' => $detail->event->id_event,
                'namaEvent' => $detail->event->nama_event,
                'hargaEvent' => $detail->event->harga_event,
                'jumlahTiket' => $detail->jumlah,
                'subtotalEvent' => $detail->jumlah * $detail->event->harga_event,
            ];
        });

        return [
            'idPembelianEvent' => $pembelian->id_pembelian,
            'totalHargaEvent' => $pembelian->total_pembelian,
            'statusPembelianEvent' => $pembelian->status_pembelian,
            'detailEvent' => $items
        ];
    }


    /**
     * Hapus pembelian dan detail pembeliannya
     */
    public function deleteCart($idPembelianEvent)
    {
        $pembelian = PembelianEvent::where('id_pembelian', $idPembelianEvent)
            ->where('is_checkout', false)
            ->first();

        if (!$pembelian) {
            return false;
        }

        $pembelian->detailEvent()->delete();
        $pembelian->delete();

        return true;
    }

    public function checkoutCart($idPembelianEvent, $idPelanggan, $items)
    {
        return DB::transaction(function () use ($idPembelianEvent, $idPelanggan, $items) {
            $pembelian = PembelianEvent::where('id_pembelian', $idPembelianEvent)
                ->where('id_pelanggan', $idPelanggan)
                ->where('is_checkout', false)
                ->firstOrFail();

            $total = 0;
            $detailEvent = [];

            foreach ($items as $item) {
                $event = Event::findOrFail($item['idEvent']);
                $jumlah = $item['jumlah'];

                if ($event->kuota < $jumlah) {
                    throw new \Exception("Kuota event {$event->nama_event} tidak mencukupi.");
                }

                $event->kuota -= $jumlah;
                $event->save();

                $subtotal = $event->harga_event * $jumlah;
                $total += $subtotal;

                // Cek apakah sudah ada entri
                $detail = DetailPembelianEvent::where('id_pembelian', $pembelian->id_pembelian)
                    ->where('id_event', $event->id_event)
                    ->first();

                if ($detail) {
                    // Update jumlah jika berbeda
                    if ($detail->jumlah != $jumlah) {
                        $detail->jumlah = $jumlah;
                        $detail->save();
                    }
                } else {
                    // Belum ada, buat entri baru
                    $detail = DetailPembelianEvent::create([
                        'id_pembelian' => $pembelian->id_pembelian,
                        'id_event' => $event->id_event,
                        'jumlah' => $jumlah,
                    ]);
                }

                $detailEvent[] = [
                    'idDetailPembelianEvent' => $detail->id_pembelian_event,
                    'namaEvent' => $event->nama_event,
                    'jumlahTiket' => $jumlah,
                ];
            }

            // Update data pembelian
            $pembelian->update([
                'tanggal_pembelian' => Carbon::now(),
                'total_pembelian' => $total,
                'status_pembelian' => 'Belum bayar',
                'is_checkout' => true,
            ]);

            return [
                'idPembelianEvent' => $pembelian->id_pembelian,
                'totalEvent' => $total,
                'statusPembelianEvent' => $pembelian->status_pembelian,
                'tanggalPembelianEvent' => $pembelian->tanggal_pembelian,
                'detailEvent' => $detailEvent,
            ];
        });
    }
}
