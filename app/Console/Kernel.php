<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\NonaktifkanMembership;
use App\Console\Commands\UpdateMembershipStatus;


class Kernel extends ConsoleKernel
{
    /**
     * Daftarkan command secara manual (penting!)
     */
    protected $commands = [
        NonaktifkanMembership::class,
        UpdateMembershipStatus::class, // ⬅️ Tambahkan ini
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // Jalankan setiap menit untuk testing
        $schedule->command('membership:nonaktifkan')->everyMinute();
        $schedule->command('membership:update-status')->everyMinute();
    }


    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
