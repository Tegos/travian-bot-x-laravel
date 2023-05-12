<?php

namespace App\Travian\Actions;

use App\Travian\Helpers\TravianGameHelper;
use App\Travian\TravianGameService;
use App\Travian\TravianRoute;
use Exception;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;

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
        TravianGameHelper::waitRandomizer(10);

        Log::channel('travian')->debug(__FUNCTION__);

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

        TravianGameHelper::waitRandomizer(3);

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

        TravianGameHelper::waitRandomizer(5);

        if ($this->isAuthenticated()) {

            Log::channel('travian')->debug(__FUNCTION__);

            $routes = Arr::random($listRoutes, 3);

            foreach ($routes as $route) {
                $this->browser->visit($route);
                TravianGameHelper::waitRandomizer(5);
            }

            $this->browser->screenshot(Str::snake(__FUNCTION__));
        }
    }
}
