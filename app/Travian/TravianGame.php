<?php

namespace App\Travian;

use App\Support\Helpers\StringHelper;
use App\Travian\Actions\BaseAction;
use App\Travian\Enums\TravianAuctionCategory;
use App\Travian\Enums\TravianTroopSelector;
use App\Travian\Helpers\TravianGameHelper;
use App\View\Table\ConsoleBaseTable;
use Carbon\Carbon;
use Exception;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Exception\UnsupportedOperationException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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

            Log::channel('travian')->info(__FUNCTION__);

            $driver = $this->browser->driver;
            $this->browser->visit(TravianRoute::mainRoute());
            TravianGameHelper::waitRandomizer(5);

            $this->browser->visit(TravianRoute::rallyPointRoute());
            TravianGameHelper::waitRandomizer(5);

            $this->browser->visit(TravianRoute::rallyPointFarmListRoute());
            TravianGameHelper::waitRandomizer(5);

            TravianGameHelper::randomBreak();

            $buttonStartAllFarmList = $this->browser->driver->findElement(WebDriverBy::cssSelector('#raidList button.startAll'));
            $buttonStartAllFarmList->click();

            // wait until cancel button is hidden
            $driver->wait(10, 1000)->until(
                WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('#raidList button.cancelDispatch'))
            );

            TravianGameHelper::waitRandomizer(3);

            $this->browser->screenshot(Str::snake(__FUNCTION__));
        }
    }

    /**
     * @throws TimeoutException
     * @throws Exception
     * @throws Throwable
     */
    public function performCheckRunFarmListAction(): void
    {
        $limitHorses = 100;

        $this->performLoginAction();

        TravianGameHelper::waitRandomizer(5);

        $farmListEnabled = config('services.travian.farm_list_enabled');

        if ($this->isAuthenticated() && $farmListEnabled) {

            Log::channel('travian')->info(__FUNCTION__);

            $this->browser->visit(TravianRoute::mainRoute());
            TravianGameHelper::waitRandomizer(5);

            $troopsTable = $this->browser->driver->findElement(WebDriverBy::cssSelector('#troops'));

            $troopsTableRows = $troopsTable->findElements(WebDriverBy::cssSelector('tr'));
            $horsesCount = 0;

            foreach ($troopsTableRows as $troopsTableRow) {
                $theutatesThunders = $troopsTableRow->findElements(WebDriverBy::className(TravianTroopSelector::THEUTATES_THUNDERS));
                if ($theutatesThunders) {
                    $horsesCount = $troopsTableRow->findElement(WebDriverBy::className('num'))->getText();
                }
            }

            if ($horsesCount > $limitHorses) {
                Log::channel('travian')->info('Farm list start: horses limit');
                $this->performRunFarmListAction();
            }

            TravianGameHelper::waitRandomizer(3);

            $this->browser->screenshot(Str::snake(__FUNCTION__));
        }
    }

    /**
     * @throws TimeoutException
     * @throws Exception
     * @throws Throwable
     */
    public function performNotifyAuctionSellingAction(): void
    {
        $this->performLoginAction();

        TravianGameHelper::waitRandomizer(3);

        if ($this->isAuthenticated()) {

            Log::channel('travian')->info(__FUNCTION__);

            $this->browser->visit(TravianRoute::mainRoute());
            TravianGameHelper::waitRandomizer(3);

            $auctionData = $this->travianGameService->getAuctionData();

            if (!empty($auctionData)) {
                $selling = $auctionData['sell']['auctions'];
                $sellingItems = $selling['data'];

                foreach ($sellingItems as $k => $sellingItem) {

                    unset($sellingItems[$k]['description']);
                    unset($sellingItems[$k]['couldBeDeleted']);
                    unset($sellingItems[$k]['identifier']);
                    unset($sellingItems[$k]['obfuscatedId']);

                    // formatting
                    $name = StringHelper::normalizeString($sellingItems[$k]['nameFormatted']);
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

            $this->browser->screenshot(Str::snake(__FUNCTION__));
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

        TravianGameHelper::waitRandomizer(5);

        if ($this->isAuthenticated()) {

            Log::channel('travian')->info(__FUNCTION__);

            $this->browser->visit(TravianRoute::mainRoute());
            TravianGameHelper::waitRandomizer(3);

            $silverAmount = $this->travianGameService->getSilverAmount();
            if ($silverAmount < 100) {
                return;
            }

            $this->browser->visit(TravianRoute::auctionRoute());
            TravianGameHelper::waitRandomizer(1);

            $this->travianGameService->performBids();

            $this->browser->screenshot(Str::snake(__FUNCTION__));
        }
    }

    /**
     * @throws UnsupportedOperationException
     * @throws TimeoutException
     * @throws Exception
     * @throws Throwable
     */
    public function performAuctionFullBidsAction(): void
    {
        $this->performLoginAction();

        TravianGameHelper::waitRandomizer(5);

        if ($this->isAuthenticated()) {

            Log::channel('travian')->info(__FUNCTION__);

            $this->browser->visit(TravianRoute::mainRoute());
            TravianGameHelper::waitRandomizer(3);

            $silverAmount = $this->travianGameService->getSilverAmount();
            if ($silverAmount < 100) {
                return;
            }

            $this->browser->visit(TravianRoute::auctionRoute());
            TravianGameHelper::waitRandomizer(1);

            $categories = TravianAuctionCategory::getCategories();

            $filterContainer = $this->browser->driver->findElement(WebDriverBy::cssSelector('#auction #filter .filterContainer'));

            foreach ($categories as $category) {
                $buttonSelector = '[data-key="' . $category . '"]';
                $buttonFilter = $filterContainer->findElement(WebDriverBy::cssSelector($buttonSelector));

                TravianGameHelper::waitRandomizer(1);
                $buttonFilter->click();

                $this->browser->driver->wait()->until(TravianGameHelper::jqueryAjaxFinished());
                TravianGameHelper::waitRandomizer(1);

                $this->travianGameService->performBids();
            }

            $this->browser->screenshot(Str::snake(__FUNCTION__));
        }
    }
}
