<?php

namespace App\Console;

use App\Console\Commands\Travian\TravianLoginActionCommand;
use App\Console\Commands\Travian\TravianObserveUsersActionCommand;
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
        $schedule->command(TravianLoginActionCommand::class)
            ->cron(TravianScheduler::actionLoginScheduleCronExpression());

        // run farm lists
        $schedule->command(TravianRunFarmListActionCommand::class)
            ->cron(TravianScheduler::actionRunFarmListCronExpression());

        // run observe users
        $schedule->command(TravianObserveUsersActionCommand::class)
            ->cron(TravianScheduler::observeUsersActionCronExpression());
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
