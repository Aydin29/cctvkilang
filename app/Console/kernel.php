<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The console command classes that are registered.
     *
     * @var array
     */
    protected $commands = [
        Commands\StartCctvStreams::class,
        Commands\ServeWithStreamsCommand::class,
        Commands\StartStream::class,
        Commands\RunFfmpeg::class,
        Commands\UpdateCctvStatuses::class,
        Commands\MonitorSystemStatus::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Menjalankan command untuk memastikan semua CCTV aktif distreaming
        $schedule->command('cctv:start-streams')->everyMinute();

        // Update CCTV statuses by checking IP connectivity every 5 minutes
        $schedule->command('cctv:update-statuses')->everyFiveMinutes();

        // Monitor system status every 2 minutes
        $schedule->command('system:monitor')->everyTwoMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
