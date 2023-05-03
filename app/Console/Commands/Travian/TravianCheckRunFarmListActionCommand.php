<?php

namespace App\Console\Commands\Travian;

use App\Support\Browser\BrowserMangerFactory;
use App\Travian\TravianGame;
use Exception;
use Illuminate\Console\Command;
use Laravel\Dusk\Browser;

final class TravianCheckRunFarmListActionCommand extends Command
{
    protected $signature = 'travian:check-run-farm-list-action';

    protected $description = 'Perform start check farm list action';

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        $browserManager = BrowserMangerFactory::create();

        $browserManager->watch(function (Browser $browser) {

            $travianGame = new TravianGame($browser);

            $travianGame->performcheckRunFarmListAction();

        });

        return self::SUCCESS;
    }
}
