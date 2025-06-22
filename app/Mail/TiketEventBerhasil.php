<?php
namespace App\Mail;

use App\Models\PembelianEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TiketEventBerhasil extends Mailable
{
    use Queueable, SerializesModels;

    public $pembelian;

    public function __construct(PembelianEvent $pembelian)
    {
        $this->pembelian = $pembelian;
    }

    public function build()
    {
        return $this->subject('Tiket Event Kidsnesia Berhasil Dibeli')
            ->view('tiket_event_berhasil');
    }
}
