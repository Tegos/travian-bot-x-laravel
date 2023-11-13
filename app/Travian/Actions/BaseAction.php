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
        }

        TravianGameHelper::waitRandomizer(5);
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
            TravianRoute::statisticsPlayerTop10Route(),
            TravianRoute::profileRoute(Arr::first(Arr::random(
                [
                    734, 4970, 326, 8581, 5756, 5910, 529, 6264, 101, 5042, 2175, 4475, 1368, 2692,
                    2112, 4519, 4944, 284, 1706, 1467, 232, 2374, 3596, 1242, 10709, 1685,
                    7787, 5756, 12248, 101, 11730, 713, 6246, 10137, 11268, 78, 575, 14082, 1685
                ], 1))),
            ...[
                TravianRoute::mapCoordinateRoute(random_int(-150, 150), random_int(-150, 150)),
                TravianRoute::mapCoordinateRoute(random_int(-150, 150), random_int(-150, 150)),
            ],
            ...[
                TravianRoute::positionDetailsRoute(random_int(-150, 150), random_int(-150, 150)),
                TravianRoute::positionDetailsRoute(random_int(-150, 150), random_int(-150, 150)),
            ]
        ];

        TravianGameHelper::waitRandomizer(3);

        if ($this->isAuthenticated()) {

            $routes = Arr::random($listRoutes, random_int(3, 5));
            $routes[] = TravianRoute::statisticsWoWRoute();

            foreach ($routes as $route) {
                $this->browser->visit($route);

                TravianGameHelper::waitRandomizer(3);
                $this->browser->script('window.scrollBy(0,"+random+");');

                // check if it is the map page
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
        }
    }
}
