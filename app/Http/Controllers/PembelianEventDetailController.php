<?php

namespace App\Http\Controllers;

use App\Models\DetailPembelianEvent;
use Illuminate\Http\Request;

// Controller ini khusus untuk ADMIN melihat data detail pembelian event
class PembelianEventDetailController extends Controller
{
    // 1. Menampilkan semua data detail pembelian event (beserta relasi)
    public function index()
    {
        // Mengambil semua data dari tabel detail_pembelian_event
        // Disertai dengan relasi ke tabel pembelian_event (induk pembelian) dan event (nama, jadwal, dll)
        return DetailPembelianEvent::with(['pembelian', 'event'])->get();
    }

    // 2. Menampilkan detail pembelian event berdasarkan ID pembelian tertentu
    public function show($id)
    {
        // Mengambil semua data detail dari 1 pembelian event (berdasarkan id_pembelian)
        // Disertai juga dengan relasi pembelian dan event agar lengkap
        return DetailPembelianEvent::with(['pembelian', 'event'])
            ->where('id_pembelian', $id) // Filter berdasarkan ID pembelian
            ->get(); // Kembalikan hasil dalam bentuk collection
    }
}
