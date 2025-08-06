<?php
namespace App\Services;

use App\Models\Event;
use App\Models\Pelanggan;
use App\Models\PembelianEvent;
use App\Models\DetailPembelianEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class EventCartService
{
    // 1. Membuat cart baru
    public function createCart($idPelanggan, $items) 
    {
        return DB::transaction(function () use ($idPelanggan, $items) {
            $total = 0;

            // 1.1 Ambil data pelanggan
            $pelanggan = Pelanggan::findOrFail($idPelanggan);

            // 1.2 Buat data pembelian event (cart)
            $pembelian = PembelianEvent::create([
                'id_pelanggan' => $idPelanggan,
                'nama_pelanggan' => $pelanggan->nama_pelanggan,
                'tanggal_pembelian' => null,
                'total_pembelian' => 0,
                'status_pembelian' => 'Belum Checkout',
                'is_checkout' => false,
            ]);

            // 1.3 Iterasi setiap item, simpan ke detail pembelian event
            $cart = collect($items)->map(function ($item) use (&$total, $pembelian) {
                $event = Event::findOrFail($item['idEvent']);
                $jumlah = $item['jumlah'];

                // Validasi kuota
                if ($jumlah > $event->kuota) {
                    throw new \Exception("Jumlah tiket untuk event '{$event->nama_event}' melebihi kuota ({$event->kuota}).");
                }

                $subtotal = $event->harga_event * $jumlah;

                $detail = DetailPembelianEvent::create([
                    'id_pembelian' => $pembelian->id_pembelian,
                    'id_event' => $event->id_event,
                    'jumlah' => $jumlah,
                    'harga_event' => $event->harga_event,
                    'subtotal_event' => $subtotal,
                ]);

                $total += $subtotal;

                return [
                    'idDetailPembelianEvent' => $detail->id_pembelian_event,
                    'idEvent' => $event->id_event,
                    'namaEvent' => $event->nama_event,
                    'hargaEvent' => $event->harga_event,
                    'jumlahTiket' => $jumlah,
                    'subtotalEvent' => $subtotal,
                ];
            });

            // 1.4 Update total pembelian
            $pembelian->update(['total_pembelian' => $total]);

            return [
                'pembelianEventResponse' => [
                    'idPembelianEvent' => $pembelian->id_pembelian,
                    'tanggalPembelianEvent' => $pembelian->tanggal_pembelian,
                    'totalHargaEvent' => $total,
                    'statusPembelianEvent' => $pembelian->status_pembelian,
                    'cartEventItem' => $cart->values(),
                ]
            ];
        });
    }

    // 2. Menampilkan list cart (yang belum checkout) milik user
    public function listCart($idPelanggan)
    {
        $carts = PembelianEvent::with(['detailEvent.event'])
            ->where('id_pelanggan', $idPelanggan)
            ->where('is_checkout', false)
            ->get();

        return $carts->map(function ($item) {
            return [
                'idPembelianEvent' => $item->id_pembelian,
                'totalPembelianEvent' => $item->total_pembelian,
                'tanggalPembelianEvent' => $item->tanggal_pembelian,
                'statusPembelianEvent' => $item->status_pembelian,
                'cartEventItem' => $item->detailEvent->map(function ($detail) {
                    return [
                        'idDetailPembelianEvent' => $detail->id_pembelian_event,
                        'fotoEvent' => optional($detail->event) ? url('storage/event/' . $detail->event->foto_event) : null,
                        'idEvent'=> $detail->event->id_event,
                        'namaEvent' => $detail->event->nama_event,
                        'hargaEvent' => $detail->event->harga_event,
                        'tanggalEvent' => Carbon::parse($detail->event->jadwal_event)->format('d-m-Y'),
                        'jadwalEvent' => Carbon::parse($detail->event->jadwal_event)->format('H:i'),
                        'jumlahTiket' => $detail->jumlah,
                        'subtotalEvent' => $detail->jumlah * $detail->event->harga_event,
                    ];
                }),
            ];
        });
    }

    // 3. Menampilkan detail cart berdasarkan ID dan ID pelanggan
    public function getCartDetailById($idPembelianEvent, $idPelanggan)
    {
        $pembelian = PembelianEvent::with(['detailEvent.event'])
            ->where('id_pembelian', $idPembelianEvent)
            ->where('id_pelanggan', $idPelanggan)
            ->where('is_checkout', false)
            ->first();

        if (!$pembelian) return null;

        $items = $pembelian->detailEvent->map(function ($detail) {
            return [
                'idDetailPembelianEvent' => $detail->id_pembelian_event,
                'fotoEvent' => optional($detail->event) ? url('storage/event/' . $detail->event->foto_event) : null,
                'idEvent' => $detail->id_event,
                'namaEvent' => optional($detail->event)->nama_event ?? 'Event sudah dihapus',
                'hargaEvent' => $detail->harga_event,
                'tanggalEvent' => Carbon::parse($detail->event->jadwal_event)->format('d-m-Y'),
                'jadwalEvent' => Carbon::parse($detail->event->jadwal_event)->format('H:i'),
                'jumlahTiket' => $detail->jumlah,
                'subtotalEvent' => $detail->subtotal_event,
            ];
        });

        return [
            'idPembelianEvent' => $pembelian->id_pembelian,
            'tanggalPembelianEvent' => $pembelian->tanggal_pembelian,
            'totalHargaEvent' => $pembelian->total_pembelian,
            'statusPembelianEvent' => $pembelian->status_pembelian,
            'cartEventItem' => $items,
        ];
    }

    // 4. Menghapus cart beserta detailnya jika belum checkout
    public function deleteCart($idPembelianEvent, $idPelanggan)
    {
        $pembelian = PembelianEvent::where('id_pembelian', $idPembelianEvent)
            ->where('id_pelanggan', $idPelanggan)
            ->where('is_checkout', false)
            ->first();

        if (!$pembelian) return false;

        $pembelian->detailEvent()->delete(); // hapus detail
        $pembelian->delete(); // hapus utama

        return true;
    }

    // 5. Melakukan proses checkout cart
    public function checkoutCart($idPembelianEvent, $idPelanggan, $items)
    {
        try {
            return DB::transaction(function () use ($idPembelianEvent, $idPelanggan, $items) {
                $pembelian = PembelianEvent::where('id_pembelian', $idPembelianEvent)
                    ->where('id_pelanggan', $idPelanggan)
                    ->where('is_checkout', false)
                    ->firstOrFail();

                $total = 0;
                $cartEventItem = [];

                foreach ($items as $item) {
                    $event = Event::findOrFail($item['idEvent']);
                    $jumlah = $item['jumlah'];

                    // Validasi kuota
                    if ($jumlah > $event->kuota) {
                        throw new \Exception("Jumlah tiket untuk event '{$event->nama_event}' melebihi kuota ({$event->kuota}).");
                    }

                    // Kurangi kuota
                    $event->kuota -= $jumlah;
                    $event->save();

                    $subtotal = $event->harga_event * $jumlah;
                    $total += $subtotal;

                    // Update jika sudah ada, kalau tidak buat baru
                    $detail = DetailPembelianEvent::where('id_pembelian', $pembelian->id_pembelian)
                        ->where('id_event', $event->id_event)
                        ->first();

                    if ($detail) {
                        $detail->update([
                            'jumlah' => $jumlah,
                            'harga_event' => $event->harga_event,
                            'subtotal_event' => $subtotal,
                        ]);
                    } else {
                        $detail = DetailPembelianEvent::create([
                            'id_pembelian' => $pembelian->id_pembelian,
                            'id_event' => $event->id_event,
                            'jumlah' => $jumlah,
                            'harga_event' => $event->harga_event,
                            'subtotal_event' => $subtotal,
                        ]);
                    }

                    $cartEventItem[] = [
                        'idDetailPembelianEvent' => $detail->id_pembelian_event,
                        'fotoEvent' => optional($detail->event) ? url('storage/event/' . $detail->event->foto_event) : null,
                        'idEvent' => $event->id_event,
                        'namaEvent' => $event->nama_event,
                        'jumlahTiket' => $jumlah,
                        'tanggalEvent' => Carbon::parse($event->jadwal_event)->format('d-m-Y'),
                        'jadwalEvent' => Carbon::parse($event->jadwal_event)->format('H:i'),
                        'hargaEvent' => $event->harga_event,
                        'subtotalEvent' => $subtotal,
                    ];
                }

                $pelanggan = Pelanggan::findOrFail($idPelanggan);

                // Update data pembelian jadi checkout
                $pembelian->update([
                    'tanggal_pembelian' => Carbon::now(),
                    'nama_pelanggan' => $pelanggan->nama_pelanggan,
                    'total_pembelian' => $total,
                    'status_pembelian' => 'Belum bayar',
                    'is_checkout' => true,
                ]);

                return [
                    'idPembelianEvent' => $pembelian->id_pembelian,
                    'totalEvent' => $total,
                    'statusPembelianEvent' => $pembelian->status_pembelian,
                    'tanggalPembelianEvent' => $pembelian->tanggal_pembelian,
                    'cartEventItem' => $cartEventItem,
                ];
            });
        } catch (ModelNotFoundException $e) {
            throw new \Exception('Checkout gagal: Data pembelian atau event tidak ditemukan.');
        } catch (\Exception $e) {
            throw new \Exception('Checkout gagal: ' . $e->getMessage());
        }
    }
}
