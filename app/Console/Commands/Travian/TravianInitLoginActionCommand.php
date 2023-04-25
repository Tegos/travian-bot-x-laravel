<?php

namespace App\Console\Commands\Travian;

use App\Support\Browser\BrowserMangerFactory;
use App\Travian\TravianGame;
use Exception;
use Illuminate\Console\Command;
use Laravel\Dusk\Browser;

final class TravianInitLoginActionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'travian:login-action';

    /**
     * The console command description.
     *
     * @var string
     */
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

            $browser->screenshot('travian.auth.login');

        });

        return 0;
    }
}
