<?php

namespace App\Travian\Actions;

use App\Support\Helpers\FileHelper;
use App\Travian\Helpers\TravianGameHelper;
use App\Travian\TravianGameService;
use App\Travian\TravianRoute;
use Exception;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
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
            TravianRoute::reportRouteSurrounding(),
            TravianRoute::allianceReportRoute(),
            TravianRoute::heroInventoryRoute(),
            TravianRoute::auctionRoute(),
            TravianRoute::messagesInboxRoute(),
            TravianRoute::villageStatisticsRoute(),
            TravianRoute::stableRoute(),
            TravianRoute::mapRoute(),
        ];

        TravianGameHelper::waitRandomizer(3);

        if ($this->isAuthenticated()) {

            Log::channel('travian')->debug(__FUNCTION__);

            $routes = Arr::random($listRoutes, random_int(2, 3));

            foreach ($routes as $route) {
                $this->browser->visit($route);
                TravianGameHelper::waitRandomizer(3);
                $this->browser->script('window.scrollBy(0,"+random+");');

                $mapContainer = $this->browser->element('#mapContainer');
                if (!empty($mapContainer)) {
                    $randomDivs = $this->browser->driver->findElements(WebDriverBy::cssSelector('div'));
                    shuffle($randomDivs);

                    $div = Arr::first($randomDivs, function ($randomDiv) {
                        /** @var RemoteWebElement $randomDiv */
                        return $randomDiv->isDisplayed() && $randomDiv->isEnabled();
                    });

                    $this->browser->driver->getMouse()
                        ->mouseDown($mapContainer->getCoordinates())
                        ->mouseMove($div->getCoordinates())
                        ->mouseUp($div->getCoordinates());

                    TravianGameHelper::waitRandomizer(3);
                }

                $this->browser->driver->getKeyboard()->pressKey(WebDriverKeys::DOWN);

                $this->browser->screenshot(FileHelper::getScreenshotFileName($route));

                $randomLinks = $this->browser->driver->findElements(WebDriverBy::cssSelector('#center a:not([href="#"])'));

                $this->travianGameService->clickRandomLink($randomLinks);
            }

            TravianGameHelper::waitRandomizer(5);

            $this->browser->screenshot(Str::snake(__FUNCTION__));
        }
    }
}
