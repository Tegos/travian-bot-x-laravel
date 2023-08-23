<?php

namespace App\Travian;

use App\Support\Helpers\FileHelper;
use App\Support\Helpers\StringHelper;
use App\Travian\Actions\BaseAction;
use App\Travian\Enums\TravianAuctionCategory;
use App\Travian\Helpers\TravianGameHelper;
use App\View\Table\ConsoleBaseTable;
use Carbon\Carbon;
use Exception;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Exception\UnsupportedOperationException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;

final class TravianGame extends BaseAction
{
    /**
     * @throws TimeoutException
     * @throws Exception
     * @throws Throwable
     */
    public function performRunFarmListAction(): void
    {
        $this->performLoginAction();

        TravianGameHelper::waitRandomizer(5);

        $this->performRandomAction();

        $farmListEnabled = config('services.travian.farm_list_enabled');

        if ($this->isAuthenticated() && $farmListEnabled) {

            $driver = $this->browser->driver;

            $horsesAmount = $this->travianGameService->getHorsesAmount();
            $minHorsesAmount = config('services.travian.min_horses_amount');

            $this->browser->visit(TravianRoute::rallyPointRoute());
            TravianGameHelper::waitRandomizer(3);

            if ($horsesAmount < $minHorsesAmount) {
                Log::channel('travian')
                    ->debug('Not enough horses, min: ' . $minHorsesAmount . ' current: ' . $horsesAmount);
                return;
            }

            Log::channel('travian')->debug($horsesAmount . ' horses');

            $this->browser->visit(TravianRoute::rallyPointFarmListRoute());
            TravianGameHelper::waitRandomizer(7);

            Log::channel('travian')->info(__FUNCTION__);

            $this->browser->script('window.scrollBy(0,"+random+");');

            $buttonStartAllFarmList = $this->browser->driver->findElement(WebDriverBy::cssSelector('#raidList button.startAll'));
            $buttonStartAllFarmList->click();

            // wait until cancel button is hidden
            $driver->wait(10, 1000)->until(
                WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('#raidList button.cancelDispatch'))
            );

            TravianGameHelper::waitRandomizer(15);

            // set another run delay
            $now = Carbon::now()->addMinutes(random_int(21, 33));
            Cache::set(TravianScheduler::CHECK_FARM_LIST_ACTION . 'minute-part', $now->minute);

            $this->browser->screenshot(FileHelper::getScreenshotFileName(TravianRoute::rallyPointFarmListRoute() . '&' . __FUNCTION__));

            TravianGameHelper::waitRandomizer(3);
        }
    }

    /**
     * @throws TimeoutException
     * @throws Exception
     * @throws Throwable
     */
    public function performDetectOwnAuctionItemsAction(): void
    {
        $this->performLoginAction();

        TravianGameHelper::waitRandomizer(10);

        if ($this->isAuthenticated()) {

            $this->browser->visit(TravianRoute::mainRoute());
            TravianGameHelper::waitRandomizer(3);

            $auctionData = $this->travianGameService->getAuctionData();

            if (!empty($auctionData)) {
                $selling = $auctionData['sell']['auctions'];
                $sellingItems = $selling['data'];

                foreach ($sellingItems as $k => $sellingItem) {

                    $sellingItems[$k] = Arr::only($sellingItem,
                        [
                            'id', 'nameFormatted', 'quality', 'slot',
                            'uid', 'item_type_id', 'amount', 'status',
                            'time_start', 'time_end', 'price', 'bids', 'uid_bidder'
                        ]
                    );

                    // formatting
                    $name = StringHelper::normalizeString($sellingItem['nameFormatted']);
                    $sellingItems[$k]['nameFormatted'] = $name;

                    $gameDateNow = Carbon::now()->timezone(config('services.travian.timezone'));

                    $timeEnd = Carbon::createFromTimestamp($sellingItem['time_end'])
                        ->timezone(config('services.travian.timezone'));

                    $sellingItems[$k]['time_end_date'] = $timeEnd->format('d.m.Y H:i:s');
                    $sellingItems[$k]['left_time'] = $timeEnd->diff($gameDateNow)->format('%H:%I:%S');
                }

                if (count($sellingItems) > 0) {
                    $headers = array_keys(Arr::first($sellingItems));
                    $consoleTable = new ConsoleBaseTable($sellingItems, $headers);

                    Log::channel('travian_auction')->info(PHP_EOL . $consoleTable);
                }
            }

            TravianGameHelper::waitRandomizer(3);
        }
    }

    /**
     * @throws UnsupportedOperationException
     * @throws TimeoutException
     * @throws Exception
     * @throws Throwable
     */
    public function performAuctionBidsAction(): void
    {
        $this->performLoginAction();

        TravianGameHelper::waitRandomizer(3);

        if ($this->isAuthenticated()) {

            Log::channel('travian_auction')->info(__FUNCTION__);

            $this->browser->visit(TravianRoute::mainRoute());
            TravianGameHelper::waitRandomizer(5);

            $silverAmount = $this->travianGameService->getSilverAmount();

            if ($silverAmount < 100) {
                return;
            }

            $this->browser->visit(TravianRoute::auctionRoute());
            TravianGameHelper::waitRandomizer(2);

            $categories = TravianAuctionCategory::getCategories();
            shuffle($categories);

            $filterContainer = $this->browser->driver->findElement(WebDriverBy::cssSelector('#auction #filter .filterContainer'));

            foreach ($categories as $category) {
                $buttonSelector = '[data-key="' . $category . '"]';
                $buttonFilter = $filterContainer->findElement(WebDriverBy::cssSelector($buttonSelector));

                TravianGameHelper::waitRandomizer(3);
                $buttonFilter->click();

                $this->browser->driver->wait()->until(TravianGameHelper::jqueryAjaxFinished());
                TravianGameHelper::waitRandomizer(3);

                $this->travianGameService->performBids();

                $this->browser->screenshot(FileHelper::getScreenshotFileName(TravianRoute::auctionRoute() . '&category=' . $category));
            }
        }
    }

    /**
     * @throws TimeoutException
     * @throws Exception
     * @throws Throwable
     */
    public function performObserveUsersAction(): void
    {
        $profileObserveEnabled = config('services.travian.profile_observe_enabled');

        if (!$profileObserveEnabled) {
            return;
        }

        $this->performLoginAction();

        TravianGameHelper::waitRandomizer(5);

        $now = Carbon::now();

        if ($this->isAuthenticated()) {

            $profileObserveList = config('services.travian.profile_observe_list');
            shuffle($profileObserveList);

            TravianGameHelper::waitRandomizer(3);

            if (empty($profileObserveList)) {
                Log::channel('travian')
                    ->debug('profileObserveList is empty');
                return;
            }

            $this->browser->visit(TravianRoute::statisticsPlayerTop10Route());
            TravianGameHelper::waitRandomizer(5);

            foreach ($profileObserveList as $itemUid) {

                $this->browser->visit(TravianRoute::profileRoute($itemUid));
                $this->browser->script('window.scrollBy(0,"+random+");');

                TravianGameHelper::waitRandomizer(3);

                $login = Str::lower($this->browser->driver->findElement(WebDriverBy::cssSelector('.titleInHeader'))->getText());

                $playerDetails = $this->browser->driver->findElement(WebDriverBy::cssSelector('#playerProfile .playerDetails'));
                $playerDetails->takeElementScreenshot(FileHelper::getPlayerObserveScreenshotPath($login));

                $crawler = new Crawler($playerDetails->getDomProperty('outerHTML'));

                $details = $crawler->filter('table')->filter('tr')->each(function ($tr) {
                    return $tr->filter('td')->each(function ($td) {
                        return trim($td->text());
                    });
                });

                $details = array_filter($details);

                $detailsData = Arr::dot($details);

                $userInfo = [
                    'population' => Arr::get($detailsData, '7.1'),
                    'attacker_point' => Arr::get($detailsData, '8.1'),
                    'defender_point' => Arr::get($detailsData, '9.1'),
                    'experience' => Arr::get($detailsData, '10.1'),
                ];

                //Storage::disk('travian')->createDirectory();
                //$stream = fopen(Storage::disk('travian')->path('players/' . $login . '/' . $now->toDateString() . '/details.csv'), 'a+');

                //fputcsv($stream, $userInfo);

                TravianGameHelper::waitRandomizer(3);
            }

            TravianGameHelper::waitRandomizer(5);
        }
    }
}
