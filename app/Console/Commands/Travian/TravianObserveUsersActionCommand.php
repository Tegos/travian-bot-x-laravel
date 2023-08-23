<?php

namespace App\Console\Commands\Travian;

use App\Support\Browser\BrowserMangerFactory;
use App\Travian\TravianGame;
use Exception;
use Illuminate\Console\Command;
use Laravel\Dusk\Browser;

final class TravianObserveUsersActionCommand extends Command
{
    protected $signature = 'travian:observe-users-action';

    protected $description = 'Perform observe users action';

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        $browserManager = BrowserMangerFactory::create();

        $browserManager->watch(function (Browser $browser) {

            $travianGame = new TravianGame($browser);

            $travianGame->performObserveUsersAction();

            rescue(function () use ($browser) {
                $browser->quit();
            });

        });

        return self::SUCCESS;
    }
}
