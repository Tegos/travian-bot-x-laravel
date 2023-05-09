<?php

namespace App\Travian;

use App\Support\Helpers\NumberHelper;
use App\Support\Helpers\StringHelper;
use App\Travian\Actions\BaseAction;
use App\Travian\Enums\TravianAuctionBid;
use App\Travian\Enums\TravianAuctionCategoryPrice;
use App\Travian\Enums\TravianTroopSelector;
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

        $this->waitRandomizer(5);

        $this->performRandomAction();

        $farmListEnabled = config('services.travian.farm_list_enabled');

        if ($this->isAuthenticated() && $farmListEnabled) {

            Log::channel('travian')->info(__FUNCTION__);

            $driver = $this->browser->driver;
            $this->browser->visit(TravianRoute::mainRoute());
            $this->waitRandomizer(5);

            $this->browser->visit(TravianRoute::rallyPointRoute());
            $this->waitRandomizer(5);

            $this->browser->visit(TravianRoute::rallyPointFarmListRoute());
            $this->waitRandomizer(5);

            $this->randomBreak();

            $buttonStartAllFarmList = $this->browser->driver->findElement(WebDriverBy::cssSelector('#raidList button.startAll'));
            $buttonStartAllFarmList->click();

            // wait until cancel button is hidden
            $driver->wait(10, 1000)->until(
                WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('#raidList button.cancelDispatch'))
            );

            $this->waitRandomizer(3);

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

        $this->waitRandomizer(5);

        $farmListEnabled = config('services.travian.farm_list_enabled');

        if ($this->isAuthenticated() && $farmListEnabled) {

            Log::channel('travian')->info(__FUNCTION__);

            $this->browser->visit(TravianRoute::mainRoute());
            $this->waitRandomizer(5);

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

            $this->waitRandomizer(3);

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

        $this->waitRandomizer(3);

        if ($this->isAuthenticated()) {

            Log::channel('travian')->info(__FUNCTION__);

            $this->browser->visit(TravianRoute::mainRoute());
            $this->waitRandomizer(3);

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

            $this->waitRandomizer(3);

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

        $this->waitRandomizer(5);

        if ($this->isAuthenticated()) {

            Log::channel('travian')->info(__FUNCTION__);

            $this->browser->visit(TravianRoute::mainRoute());
            $this->waitRandomizer(3);

            $auctionData = $this->travianGameService->getAuctionData();
            $silverAmount = $auctionData['common']['silver'];
            if ($silverAmount < 100) {
                return;
            }

            $this->browser->visit(TravianRoute::auctionRoute());
            $this->waitRandomizer(1);

            $auctionTable = $this->browser->driver->findElement(WebDriverBy::cssSelector('#auction .currentBid'));

            $auctionBidRows = $auctionTable->findElements(WebDriverBy::cssSelector('tbody tr'));

            $bidCount = 0;

            $ignoredItems = config('services.travian.auction_ignored_items');

            foreach ($auctionBidRows as $auctionBidRow) {
                /** @var RemoteWebElement $bidButton */
                $bidButton = Arr::first($auctionBidRow->findElements(WebDriverBy::className('bidButton')));

                if ($bidButton && $bidButton->getText() === TravianAuctionBid::BID) {
                    $currentBidPrice = $auctionBidRow->findElement(WebDriverBy::cssSelector('td.silver'))->getText();
                    $name = $auctionBidRow->findElement(WebDriverBy::cssSelector('td.name'))->getText();
                    $name = StringHelper::normalizeString($name);

                    // ignored items
                    if (Str::contains($name, $ignoredItems)) {
                        continue;
                    }

                    $timerSecondsLeft = $auctionBidRow->findElement(WebDriverBy::cssSelector('td.time .timer'))->getAttribute('value');

                    $amount = filter_var($name, FILTER_SANITIZE_NUMBER_INT);
                    $itemCategoryElement = $auctionBidRow->findElement(WebDriverBy::cssSelector('td img.itemCategory'));
                    $itemCategoryClasses = explode(' ', $itemCategoryElement->getAttribute('class'));
                    $itemCategoryClass = Arr::first($itemCategoryClasses, function ($cssClass) {
                        return $cssClass !== 'itemCategory';
                    });

                    $itemCategory = Str::replace('itemCategory_', '', $itemCategoryClass);

                    $price = TravianAuctionCategoryPrice::getPrice($itemCategory);
                    $price = NumberHelper::numberRandomizer($price, 3, 15);

                    $bidPrice = $amount * $price;

                    if ($bidPrice > $currentBidPrice && $timerSecondsLeft > 10) {
                        $bidButton->click();
                        $this->waitRandomizer(1);

                        /** @var RemoteWebElement $bidInput */
                        $bidInput = Arr::first($auctionTable->findElements(WebDriverBy::name('maxBid')));

                        $bidInput->clear()->sendKeys($bidPrice);
                        $this->waitRandomizer(0);
                        $this->browser->screenshot(Str::snake(__FUNCTION__));
                        $this->browser->driver->getKeyboard()->pressKey(WebDriverKeys::ENTER);
                        $bidCount++;

                        Log::channel('travian_auction')->info($name . ' ' . $itemCategory . ' ' . $price . ' ' . $bidPrice);
                    }
                }
            }

            $this->browser->screenshot(Str::snake(__FUNCTION__));

            Log::channel('travian')->info($bidCount . ' bids made');
        }
    }
}
