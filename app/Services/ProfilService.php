<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfilService
{
    public function getProfil($pelanggan)
    {
        return [
            'namaPelanggan' => $pelanggan->nama_pelanggan,
            'email' => $pelanggan->email,
            'noHpPelanggan' => $pelanggan->no_hp_pelanggan,
            'fotoProfil' => $pelanggan->foto_profil
                ? asset('storage/foto_profil/' . $pelanggan->foto_profil)
                : null,
        ];
    }

    public function uploadFoto($pelanggan, $file)
    {
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('public/foto_profil', $filename);

        $pelanggan->foto_profil = $filename;
        $pelanggan->save();

        return $filename;
    }

    public function hapusFoto($pelanggan)
    {
        if ($pelanggan->foto_profil) {
            Storage::delete('public/foto_profil/' . $pelanggan->foto_profil);
            $pelanggan->foto_profil = null;
            $pelanggan->save();
            return true;
        }

        return false;
    }

}
