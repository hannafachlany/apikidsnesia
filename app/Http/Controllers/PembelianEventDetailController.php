<?php

namespace App\Http\Controllers;

use App\Models\DetailPembelianEvent;
use Illuminate\Http\Request;

//HANYA UTK ADMIN
class PembelianEventDetailController extends Controller
{
    public function index()
    {
        // misal hanya untuk admin
        return DetailPembelianEvent::with(['pembelian', 'event'])->get();
    }

    public function show($id)
    {
        return DetailPembelianEvent::with(['pembelian', 'event'])
            ->where('id_pembelian', $id)
            ->get();
    }
}
