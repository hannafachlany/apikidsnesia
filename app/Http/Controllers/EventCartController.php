<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EventCartService;

class EventCartController extends Controller
{
    // 1. Inisialisasi service cart event melalui dependency injection
    protected $cartService;

    public function __construct(EventCartService $cartService)
    {
        $this->cartService = $cartService;
    }

    // 2. Menambahkan item ke cart event
    public function store(Request $request)
    {
        $user = $request->user(); // 2.1 Autentikasi user
        if (!$user) return $this->unauthorizedResponse();

        $items = $request->input('itemsEvent'); // 2.2 Ambil daftar item

        // 2.3 Validasi format array
        if (!is_array($items)) {
            return response()->json([
                'error' => true,
                'message' => 'Format itemsEvent tidak valid. Harus array.'
            ], 422);
        }

        // 2.4 Ubah struktur data untuk service
        $transformedItems = collect($items)->map(fn($item) => [
            'idEvent' => $item['idEvent'],
            'jumlah' => $item['jumlahTiket'],
        ])->toArray();

        // 2.5 Kirim ke service
        try {
            $result = $this->cartService->createCart($user->id_pelanggan, $transformedItems);
            return response()->json([
                'error' => false,
                'message' => 'Cart berhasil dibuat',
                'pembelianEventResponse' => $result['pembelianEventResponse'],
            ]);
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    // 3. Menampilkan semua cart yang dimiliki user
    public function listCart(Request $request)
    {
        $user = $request->user();
        if (!$user) return $this->unauthorizedResponse();

        try {
            $result = $this->cartService->listCart($user->id_pelanggan);
            return response()->json([
                'error' => false,
                'listEventCart' => $result
            ]);
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    // 4. Menampilkan detail cart tertentu
    public function detailCart(Request $request, $idPembelianEvent)
    {
        $user = $request->user();
        if (!$user) return $this->unauthorizedResponse();

        try {
            $result = $this->cartService->getCartDetailById($idPembelianEvent, $user->id_pelanggan);
            if (!$result) {
                return response()->json([
                    'error' => true,
                    'message' => 'Data pembelian tidak ditemukan atau bukan milik anda.',
                ], 404);
            }

            return response()->json([
                'error' => false,
                'cartEventDetail' => $result
            ]);
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    // 5. Menghapus cart
    public function deleteCart(Request $request, $idPembelianEvent)
    {
        $user = $request->user();
        if (!$user) return $this->unauthorizedResponse();

        try {
            $deleted = $this->cartService->deleteCart($idPembelianEvent, $user->id_pelanggan);

            if (!$deleted) {
                return response()->json([
                    'error' => true,
                    'message' => 'Data pembelian tidak ditemukan atau bukan milik anda.',
                ], 404);
            }

            return response()->json([
                'error' => false,
                'message' => 'Cart berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    // 6. Melakukan checkout cart (ubah status + simpan ke pembelian)
    public function checkout(Request $request, $idPembelianEvent)
    {
        $user = $request->user();
        if (!$user) return $this->unauthorizedResponse();

        $items = $request->input('itemsEvent');

        if (!is_array($items)) {
            return response()->json([
                'error' => true,
                'message' => 'Format itemsEvent tidak valid.'
            ], 422);
        }

        // 6.1 Transformasi data
        $transformedItems = collect($items)->map(fn($item) => [
            'idEvent' => $item['idEvent'],
            'jumlah' => $item['jumlahTiket'],
        ])->toArray();

        try {
            // 6.2 Kirim ke service untuk proses checkout
            $result = $this->cartService->checkoutCart($idPembelianEvent, $user->id_pelanggan, $transformedItems);

            return response()->json([
                'error' => false,
                'message' => 'Checkout berhasil',
                'pembelianEventResponse' => [
                    'idPembelianEvent' => $result['idPembelianEvent'],
                    'totaHargalEvent' => $result['totalEvent'],
                    'statusPembelianEvent' => $result['statusPembelianEvent'],
                    'tanggalPembelianEvent' => $result['tanggalPembelianEvent'],
                    'cartEventItem' => $result['cartEventItem'],
                ],
            ]);
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    // 7. Response jika user tidak login atau token invalid
    protected function unauthorizedResponse()
    {
        return response()->json([
            'error' => true,
            'message' => 'Tidak memiliki izin atau token tidak valid.'
        ], 401);
    }

    // 8. Response jika terjadi error server
    protected function serverErrorResponse($message = 'Terjadi kesalahan pada server.')
    {
        return response()->json([
            'error' => true,
            'message' => $message
        ], 500);
    }
}
