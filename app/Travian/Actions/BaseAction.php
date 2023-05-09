<?php

namespace App\Travian\Actions;

use App\Exceptions\Travian\GameRandomBreakException;
use App\Support\Helpers\NumberHelper;
use App\Support\Helpers\StringHelper;
use App\Travian\Enums\TravianAuctionBid;
use App\Travian\Enums\TravianAuctionCategoryPrice;
use App\Travian\Enums\TravianTroopSelector;
use App\Travian\TravianGameService;
use App\Travian\TravianRoute;
use App\View\Table\ConsoleBaseTable;
use Carbon\Carbon;
use Exception;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Exception\UnsupportedOperationException;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Lottery;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Throwable;

abstract class BaseAction
{
    protected Browser $browser;

    protected TravianGameService $travianGameService;

    public function __construct(Browser $browser)
    {
        $this->browser = $browser;
        $this->travianGameService = new TravianGameService($this->browser);
    }

    /**
     * @throws TimeoutException
     * @throws Exception
     */
    public function performLoginAction(): void
    {
        $this->waitRandomizer(10);

        Log::channel('travian')->info(__FUNCTION__);

        if (!$this->isAuthenticated()) {
            Log::channel('travian')->info('Input login/password');
            $link = TravianRoute::mainRoute();
            $this->browser->visit($link);

            $this->browser
                ->type('name', config('services.travian.login'))
                ->type('password', config('services.travian.password'));

            $buttonLogin = $this->browser->driver->findElement(WebDriverBy::cssSelector('button[type=submit]'));

            $buttonLogin->click();

            $this->browser->waitForReload();
            $this->browser->screenshot(Str::snake(__FUNCTION__));
        }

        $this->waitRandomizer(3);

        $this->browser->screenshot(Str::snake(__FUNCTION__));
    }

    public function isAuthenticated(): bool
    {
        $link = TravianRoute::mainRoute();
        $this->browser->visit($link);

        $loginForm = $this->browser->resolver->find('#loginForm');

        return empty($loginForm);
    }

    /**
     * @throws TimeoutException
     * @throws Exception
     */
    public function performRandomAction(): void
    {
        $this->performLoginAction();

        $listRoutes = [
            TravianRoute::mainRoute(),
            TravianRoute::rallyPointRoute(),
            TravianRoute::allianceRoute(),
            TravianRoute::reportRoute(),
            TravianRoute::allianceReportRoute(),
            TravianRoute::heroInventoryRoute(),
            TravianRoute::auctionRoute(),
        ];

        $this->waitRandomizer(5);

        if ($this->isAuthenticated()) {

            Log::channel('travian')->info(__FUNCTION__);

            $routes = Arr::random($listRoutes, 3);

            foreach ($routes as $route) {
                $this->browser->visit($route);
                $this->waitRandomizer(5);
            }

            $this->browser->screenshot(Str::snake(__FUNCTION__));
        }
    }

    /**
     * @throws Exception
     */
    protected function waitRandomizer(int $minWaitSeconds = 20, float $probability = 0.5): void
    {
        $chances = 10;
        $outOf = intval(ceil($chances / $probability));
        $probabilityResult = Lottery::odds($chances, $outOf)->choose();

        $maxWaitSeconds = intval(ceil($minWaitSeconds + ($minWaitSeconds * 0.2)));

        $seconds = $probabilityResult ? random_int($minWaitSeconds, $maxWaitSeconds) : 1;
        Log::channel('travian')->info("Delay: $seconds sec");
        sleep($seconds);
    }

    /**
     * @throws Exception
     * @throws Throwable
     */
    protected function randomBreak(float $probability = 0.1): void
    {
        $chances = 10;
        $outOf = intval(ceil($chances / $probability));

        $probabilityResult = Lottery::odds($chances, $outOf)->choose();

        throw_if($probabilityResult, new GameRandomBreakException());
    }
}
