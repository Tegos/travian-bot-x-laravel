<?php

namespace App\Console\Commands\Travian;

use App\Support\Browser\BrowserMangerFactory;
use App\Travian\TravianGame;
use Exception;
use Illuminate\Console\Command;
use Laravel\Dusk\Browser;

final class TravianNotifyAuctionSellingActionCommand extends Command
{
    protected $signature = 'travian:notify-action-selling-action';

    protected $description = 'Perform notify auction selling action';

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        $browserManager = BrowserMangerFactory::create();

        $browserManager->watch(function (Browser $browser) {

            $travianGame = new TravianGame($browser);

            $travianGame->performNotifyAuctionSellingAction();

            $browser->quit();

        });

        return self::SUCCESS;
    }
}
