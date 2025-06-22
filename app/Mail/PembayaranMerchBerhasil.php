<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\PembelianMerch;

class PembayaranMerchBerhasil extends Mailable
{
    use Queueable, SerializesModels;

    public $pembelian;

    public function __construct(PembelianMerch $pembelian)
    {
        $this->pembelian = $pembelian;
    }

    public function build()
    {
        return $this->subject('Pembayaran Merchandise Berhasil - Kidsnesia')
                    ->view('pembayaran_merch_berhasil');
    }
}
