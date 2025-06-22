<?php
namespace App\Mail;

use App\Models\Pelanggan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MembershipBerhasilVerifikasi extends Mailable
{
    use Queueable, SerializesModels;

    public $pelanggan;

    public function __construct(Pelanggan $pelanggan)
    {
        $this->pelanggan = $pelanggan;
    }

    public function build()
    {
        return $this->subject('Membership Kidsnesia Kamu Telah Aktif!')
            ->view('membership_aktif');
    }
}
