<?php

namespace App\Http\Controllers;

use App\Http\Requests\Merchandise\MerchCartRequest;
use Illuminate\Http\Request;
use App\Services\MerchCartService;

class MerchCartController extends Controller
{
    protected $cartService;

    public function __construct(MerchCartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function store(MerchCartRequest $request)
    {

        return $this->cartService->storeCart($request);
    }

    public function listCart(Request $request)
    {
        return $this->cartService->listCartByUser($request);
    }

    public function detailCart($idPembelianMerch)
    {
        return $this->cartService->getCartDetail($idPembelianMerch);
    }

    public function deleteCart($idPembelianMerch)
    {
        return $this->cartService->deleteCartById($idPembelianMerch);
    }

    public function checkout(MerchCartRequest $request, $idPembelianMerch)
    {


        return $this->cartService->checkoutCart($request, $idPembelianMerch);
    }
}
