<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Membership;
use Carbon\Carbon;

class UpdateMembershipStatus extends Command
{
    // Nama command yang bisa dipanggil di terminal
    protected $signature = 'membership:update-status';

    // Deskripsi command (optional)
    protected $description = 'Cek dan update status membership jika sudah expired';

    public function handle()
    {
        $now = Carbon::now();

        $updated = Membership::where('status', 'Aktif')
            ->where('tanggal_berakhir', '<', $now)
            ->update(['status' => 'Tidak Aktif']);

        $this->info("Berhasil mengupdate $updated membership menjadi 'Tidak Aktif'.");
    }
}
