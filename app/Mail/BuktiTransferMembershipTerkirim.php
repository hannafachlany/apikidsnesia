<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BuktiTransferMembershipTerkirim extends Mailable
{
    use Queueable, SerializesModels;
    public $pelanggan;

    public function __construct($pelanggan)
    {
        $this->pelanggan = $pelanggan;
    }

    public function build()
    {
        return $this->subject('Bukti Transfer Diterima')
                    ->view('bukti_transfer_terkirim')
                    ->with([
                        'pelanggan' => $this->viewData['pelanggan'] ?? null
                    ]);
    }

}
