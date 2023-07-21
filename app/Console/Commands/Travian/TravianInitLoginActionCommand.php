<?php

namespace App\Console\Commands\Travian;

use App\Support\Browser\BrowserMangerFactory;
use App\Travian\TravianGame;
use Exception;
use Illuminate\Console\Command;
use Laravel\Dusk\Browser;

final class TravianInitLoginActionCommand extends Command
{
    protected $signature = 'travian:login-action';

    protected $description = 'Perform login action';

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        $browserManager = BrowserMangerFactory::create();

        $browserManager->watch(function (Browser $browser) {

            $travianGame = new TravianGame($browser);

            $travianGame->performLoginAction();

            $travianGame->performRandomAction();

            $browser->quit();

        });

        return self::SUCCESS;
    }
}
