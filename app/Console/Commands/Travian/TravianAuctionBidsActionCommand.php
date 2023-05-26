<?php

namespace App\Console\Commands\Travian;

use App\Support\Browser\BrowserMangerFactory;
use App\Travian\TravianGame;
use Exception;
use Illuminate\Console\Command;
use Laravel\Dusk\Browser;

final class TravianAuctionBidsActionCommand extends Command
{
    protected $signature = 'travian:auction-bids-action';

    protected $description = 'Perform auction bids action';

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        $browserManager = BrowserMangerFactory::create();

        $browserManager->watch(function (Browser $browser) {

            $travianGame = new TravianGame($browser);

            $travianGame->performAuctionBidsAction();

            $browser->quit();

        });

        return self::SUCCESS;
    }
}
