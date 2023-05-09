<?php

namespace App\Console\Commands\Travian;

use App\Support\Browser\BrowserMangerFactory;
use App\Travian\TravianGame;
use Exception;
use Illuminate\Console\Command;
use Laravel\Dusk\Browser;

final class TravianAuctionFullBidsActionCommand extends Command
{
    protected $signature = 'travian:auction-full-bids-action';

    protected $description = 'Perform auction full bids action';

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        $browserManager = BrowserMangerFactory::create();

        $browserManager->watch(function (Browser $browser) {

            $travianGame = new TravianGame($browser);

            $travianGame->performAuctionFullBidsAction();

        });

        return self::SUCCESS;
    }
}
