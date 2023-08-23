<?php

namespace App\Console\Commands\Travian\Auction;

use App\Support\Browser\BrowserMangerFactory;
use App\Travian\TravianGame;
use Exception;
use Illuminate\Console\Command;
use Laravel\Dusk\Browser;

final class TravianDetectOwnAuctionItemsActionCommand extends Command
{
    protected $signature = 'travian:action-detect-own-items-action';

    protected $description = 'Perform detect own auction items on sell';

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        $browserManager = BrowserMangerFactory::create();

        $browserManager->watch(function (Browser $browser) {

            $travianGame = new TravianGame($browser);

            $travianGame->performDetectOwnAuctionItemsAction();

            rescue(function () use ($browser) {
                $browser->quit();
            });

        });

        return self::SUCCESS;
    }
}
