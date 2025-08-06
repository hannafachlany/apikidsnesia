<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\PembelianMerch;

class PembayaranMerchGagal extends Mailable
{
    use Queueable, SerializesModels;

    public $pembelian;

    /**
     * Create a new message instance.
     */
    public function __construct(PembelianMerch $pembelian)
    {
        $this->pembelian = $pembelian;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Pembayaran Merchandise Gagal')
                    ->view('pembayaran_merch_gagal')
                    ->with([
                        'pembelian' => $this->pembelian,
                    ]);
    }
}
