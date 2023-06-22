<?php

namespace App\Console\Commands\Travian;

use App\Support\Browser\BrowserMangerFactory;
use App\Travian\TravianGame;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Laravel\Dusk\Browser;

final class TravianRunFarmListActionCommand extends Command
{
    protected $signature = 'travian:run-farm-list-action';

    protected $description = 'Perform start farm list action';

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        $start = Carbon::now();

        $browserManager = BrowserMangerFactory::create();

        $browserManager->watch(function (Browser $browser) {

            $travianGame = new TravianGame($browser);

            $travianGame->performRunFarmListAction();

            $browser->quit();

        });

        $time = $start->diffInSeconds(Carbon::now());
        Log::channel('travian')->debug("Processed in $time seconds");

        return self::SUCCESS;
    }
}
