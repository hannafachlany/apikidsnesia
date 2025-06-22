<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Membership;
use Carbon\Carbon;

class NonaktifkanMembership extends Command
{
    protected $signature = 'membership:nonaktifkan';
    protected $description = 'Nonaktifkan membership dan is_membership pelanggan jika sudah expired';

    public function handle()
    {
        $now = Carbon::now();

        $expiredMemberships = Membership::where('status', 'Aktif')
            ->where('tanggal_berakhir', '<', $now)
            ->get();

        foreach ($expiredMemberships as $membership) {
            $membership->update(['status' => 'Nonaktif']);

            if ($membership->pelanggan) {
                $membership->pelanggan->update(['is_membership' => false]);
            }
        }

        $this->info('Membership yang expired berhasil dinonaktifkan.');
    }
}
