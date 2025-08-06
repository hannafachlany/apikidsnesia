<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
class ProfilService
{
    // 1. Mengambil data profil pelanggan
    public function getProfil($pelanggan)
    {
        return [
            // 1.1 Menyusun informasi yang akan ditampilkan
            'namaPelanggan' => $pelanggan->nama_pelanggan,
            'email' => $pelanggan->email,
            'noHpPelanggan' => $pelanggan->no_hp_pelanggan,
            'fotoProfil' => $pelanggan->foto_profil
                ? asset('storage/foto_profil/' . $pelanggan->foto_profil) // jika ada foto, tampilkan URL
                : null, // jika tidak ada, null
        ];
    }

    // 2. Menyimpan foto profil baru pelanggan
    public function uploadFoto($pelanggan, $file)
    {
        // 2.1 Membuat nama file unik dengan ekstensi asli
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();

        // 2.2 Simpan file ke folder public/foto_profil
        $file->storeAs('public/foto_profil', $filename);

        // 2.3 Update nama file di database pelanggan
        $pelanggan->foto_profil = $filename;
        $pelanggan->save();

        // 2.4 Kembalikan nama file untuk digunakan di response
        return $filename;
    }

    // 3. Menghapus foto profil pelanggan
    public function hapusFoto($pelanggan)
    {
        // 3.1 Cek apakah pelanggan memiliki foto yang tersimpan
        if ($pelanggan->foto_profil) {
            // 3.2 Hapus file dari storage
            Storage::delete('public/foto_profil/' . $pelanggan->foto_profil);

            // 3.3 Hapus referensi dari database
            $pelanggan->foto_profil = null;
            $pelanggan->save();

            // 3.4 Berhasil dihapus
            return true;
        }

        // 3.5 Tidak ada foto untuk dihapus
        return false;
    }
}
