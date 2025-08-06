<?php

namespace App\Http\Controllers;

use App\Http\Requests\Merchandise\MerchCartRequest;
use App\Services\MerchCartService;
use Illuminate\Http\Request;

class MerchCartController extends Controller
{
    protected $cartService;

    // 1. Inisialisasi controller dan inject service keranjang merchandise
    public function __construct(MerchCartService $cartService)
    {
        $this->cartService = $cartService;
    }

    // 2. Menyimpan data keranjang baru (cart) dari user
    //    Validasi sudah dilakukan oleh MerchCartRequest
    public function store(MerchCartRequest $request)
    {
        $user = $request->user();
        if (!$user) return $this->unauthorizedResponse();

        try {
            $result = $this->cartService->createCart($user->id_pelanggan, $request->itemsMerch);

            return response()->json([
                'error' => false,
                'message' => 'Cart berhasil dibuat',
                'pembelianMerchResponse' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Gagal membuat cart: ' . $e->getMessage()
            ], 400); // gunakan 400 untuk error validasi logika
        }
    }

    // 3. Menampilkan seluruh daftar cart milik pelanggan yang sedang login
    public function listCart(Request $request)
    {
        $user = $request->user();
        if (!$user) return $this->unauthorizedResponse();

        try {
            $list = $this->cartService->listCart($user->id_pelanggan);

            return response()->json([
                'error' => false,
                'listCartMerch' => $list
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    // 4. Menampilkan detail satu cart berdasarkan id, hanya jika milik user tersebut
    public function detailCart(Request $request, $idPembelianMerch)
    {
        $user = $request->user();
        if (!$user) return $this->unauthorizedResponse();

        try {
            $result = $this->cartService->getCartDetail($idPembelianMerch, $user->id_pelanggan);

            if (!$result) {
                return response()->json([
                    'error' => true,
                    'message' => 'Detail pembelian tidak ditemukan atau bukan punya anda'
                ], 404);
            }

            return response()->json([
                'error' => false,
                'itemMerchCart' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Gagal mengambil detail cart: ' . $e->getMessage()
            ], 500);
        }
    }

    // 5. Menghapus cart jika belum di-checkout dan milik pelanggan
    public function deleteCart(Request $request, $idPembelianMerch)
    {
        $user = $request->user();
        if (!$user) return $this->unauthorizedResponse();

        try {
            $deleted = $this->cartService->deleteCart($idPembelianMerch, $user->id_pelanggan);

            if (!$deleted) {
                return response()->json([
                    'error' => true,
                    'message' => 'Cart tidak ditemukan atau sudah checkout'
                ], 404);
            }

            return response()->json([
                'error' => false,
                'message' => 'Cart berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Gagal menghapus cart: ' . $e->getMessage()
            ], 500);
        }
    }

    // 6. Checkout cart tertentu — menyelesaikan pembelian berdasarkan isi keranjang
    public function checkout(MerchCartRequest $request, $idPembelianMerch)
    {
        $user = $request->user();
        if (!$user) return $this->unauthorizedResponse();

        try {
            $result = $this->cartService->checkout(
                $idPembelianMerch,
                $user->id_pelanggan,
                $request->itemsMerch
            );

            return response()->json([
                'error' => false,
                'message' => 'Checkout berhasil',
                'pembelianMerchResponse' => $result
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => true,
                'message' => 'Data pembelian tidak ditemukan atau bukan milik anda.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Checkout gagal: ' . $e->getMessage()
            ], 400); // gunakan 400 karena masih error dari sisi klien
        }
    }

    // 7. Helper jika token tidak valid atau belum login
    private function unauthorizedResponse()
    {
        return response()->json([
            'error' => true,
            'message' => 'Token tidak valid atau belum login.',
        ], 401);
    }
}
