<?php

namespace App\Console;

use App\Console\Commands\Travian\TravianInitLoginActionCommand;
use App\Console\Commands\Travian\TravianNotifyAuctionSellingActionCommand;
use App\Console\Commands\Travian\TravianRunFarmListActionCommand;
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
        // login
        $schedule->command(TravianInitLoginActionCommand::class)
            ->cron(TravianScheduler::actionLoginScheduleCronExpression());

        // run farm lists
        $schedule->command(TravianRunFarmListActionCommand::class)
            ->cron(TravianScheduler::actionRunFarmListCronExpression());

        // auction selling
        $schedule->command(TravianNotifyAuctionSellingActionCommand::class)
            ->cron(TravianScheduler::actionAuctionSellingCronExpression());
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
