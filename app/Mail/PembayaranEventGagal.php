<?php

namespace App\Mail;

use App\Models\PembelianEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PembayaranEventGagal extends Mailable
{
    use Queueable, SerializesModels;

    public $pembelian;

    public function __construct(PembelianEvent $pembelian)
    {
        $this->pembelian = $pembelian;
    }

    public function build()
    {
        return $this->subject('Pembayaran Tiket Event Gagal')
                    ->view('pembayaran_event_gagal')
                    ->with([
                        'nama' => $this->pembelian->pelanggan->nama,
                        'nama_event' => $this->pembelian->event->nama_event ?? 'Event',
                    ]);
    }
}
