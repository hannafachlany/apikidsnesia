<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Pelanggan;

class MembershipGagalVerifikasi extends Mailable
{
    use Queueable, SerializesModels;

    public $pelanggan;

    public function __construct(Pelanggan $pelanggan)
    {
        $this->pelanggan = $pelanggan;
    }

    public function build()
    {
        return $this->subject('Pembayaran Membership Tidak Dapat Diverifikasi')
                    ->view('membership_gagal_verifikasi')
                    ->with([
                        'pelanggan' => $this->pelanggan,
                    ]);
    }
}
