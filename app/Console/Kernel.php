<?php

namespace App\Console;

use App\Console\Commands\Travian\TravianInitLoginActionCommand;
use App\Travian\TravianScheduler;
use Exception;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

final class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     * @throws Exception
     */
    protected function schedule(Schedule $schedule): void
    {
        // daily at [8-19]
        $schedule->command(TravianInitLoginActionCommand::class)->cron(TravianScheduler::actionLoginScheduleCronExpression());
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
