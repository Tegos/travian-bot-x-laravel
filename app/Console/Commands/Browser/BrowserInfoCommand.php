<?php

namespace App\Console\Commands\Browser;

use App\Support\Browser\BrowserMangerFactory;
use Exception;
use Illuminate\Console\Command;
use Laravel\Dusk\Browser;

final class BrowserInfoCommand extends Command
{
    protected $signature = 'browser:info';

    protected $description = 'Check browser information';

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        $browserManager = BrowserMangerFactory::create();

        $browserManager->watch(function (Browser $browser) {

            $userAgentInfoLink = 'https://www.whatismybrowser.com/detect/what-is-my-user-agent/';
            $browser->visit($userAgentInfoLink);

            $browser->driver->manage()->timeouts()->implicitlyWait(5);

            $browser->screenshot('user-agent-info');

            $browser->visit('http://howbigismybrowser.com/');

            $browser->driver->manage()->timeouts()->implicitlyWait(5);

            $browser->screenshot('browser-size-info');

        });

        return self::SUCCESS;
    }
}
