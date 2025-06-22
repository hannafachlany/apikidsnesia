<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EventCartService;
use App\Models\PembelianEvent;


class EventCartController extends Controller
{
    protected $cartService;

    public function __construct(EventCartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Simpan cart ke database (belum checkout)
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'error' => true,
                'message' => 'Token tidak valid',
            ], 401);
        }

        $idPelanggan = $user->id_pelanggan;
        $items = $request->input('itemsEvent');

        if (!is_array($items)) {
            return response()->json([
                'error' => true,
                'message' => 'Format itemsEvent tidak valid. Harus array.'
            ], 422);
        }

        $transformedItems = collect($items)->map(function ($item) {
            return [
                'idEvent' => $item['idEvent'],
                'jumlah' => $item['jumlahTiket'],
            ];
        })->toArray();

        try {
            $result = $this->cartService->createCart($idPelanggan, $transformedItems);

            return response()->json([
                'error' => false,
                'message' => 'Cart berhasil dibuat',
                'idPembelianEvent' => $result['idPembelian'],
                'totalHargaEvent' => $result['total'],
                'cartEventItem' => $result['cart'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Gagal membuat cart: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lihat daftar cart pelanggan yang belum checkout
     */
    public function listCart(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'error' => true,
                'message' => 'Token tidak valid.',
            ], 401);
        }

        $result = $this->cartService->listCart($user->id_pelanggan);

        return response()->json([
            'error' => false,
            'listCart' => $result
        ]);
    }


    /**
     * Tampilkan detail isi cart berdasarkan ID pembelian event
     */
    public function detailCart($idPembelianEvent)
    {
        try {
            $result = $this->cartService->getCartDetailById($idPembelianEvent);

            if (!$result) {
                return response()->json([
                    'error' => true,
                    'message' => 'Cart tidak ditemukan atau kosong.',
                ], 404);
            }

            return response()->json([
                'error' => false,
                'cartDetail' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Gagal mengambil detail cart: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Hapus cart (belum checkout) beserta semua detail
     */
    public function deleteCart($idPembelianEvent)
    {
        try {
            $deleted = $this->cartService->deleteCart($idPembelianEvent);

            if (!$deleted) {
                return response()->json([
                    'error' => true,
                    'message' => 'Cart tidak ditemukan atau sudah checkout.',
                ], 404);
            }

            return response()->json([
                'error' => false,
                'message' => 'Cart berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Gagal menghapus cart: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Checkout dan kurangi kuota event berdasarkan ID pembelian
     */
    public function checkout(Request $request, $idPembelianEvent)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'error' => true,
                'message' => 'Token tidak valid',
            ], 401);
        }

        $idPelanggan = $user->id_pelanggan;
        $items = $request->input('itemsEvent');

        if (!is_array($items)) {
            return response()->json([
                'error' => true,
                'message' => 'Format itemsEvent tidak valid.'
            ], 422);
        }

        $transformedItems = collect($items)->map(function ($item) {
            return [
                'idEvent' => $item['idEvent'],
                'jumlah' => $item['jumlahTiket'],
            ];
        })->toArray();

        try {
            $result = $this->cartService->checkoutCart($idPembelianEvent, $idPelanggan, $transformedItems);

            return response()->json([
                'error' => false,
                'message' => 'Checkout berhasil',
                'pembelianEventResponse' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Checkout gagal: ' . $e->getMessage(),
            ], 500);
        }
    }
}
