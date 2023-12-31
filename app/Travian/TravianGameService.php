<?php

namespace App\Travian;

use App\Exceptions\BusinessException;
use App\Support\Helpers\FileHelper;
use App\Support\Helpers\NumberHelper;
use App\Support\Helpers\StringHelper;
use App\Travian\Enums\TravianAuctionBid;
use App\Travian\Enums\TravianAuctionCategoryPrice;
use App\Travian\Enums\TravianTroopSelector;
use App\Travian\Helpers\TravianGameHelper;
use Exception;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Throwable;

final class TravianGameService
{
    private Browser $browser;

    public function __construct(Browser $browser)
    {
        $this->browser = $browser;
    }

    /**
     * @throws Exception
     * @throws Throwable
     */
    public function getAuctionData()
    {
        $this->browser->visit(TravianRoute::auctionSellRoute());

        $scripts = $this->browser->driver->findElements(WebDriverBy::tagName('script'));

        $auctionDataScript = '';
        foreach ($scripts as $script) {
            $scriptContent = $script->getDomProperty('innerHTML');

            if (Str::contains($scriptContent, ['checkSum', 'HeroAuction'])) {
                $auctionDataScript = $scriptContent;
                break;
            }
        }

        $dataJsonString = StringHelper::getStringBetween($auctionDataScript, 'render(', ', {}');

        return json_decode($dataJsonString, true) ?? [];
    }

    /**
     * @throws Throwable
     */
    public function getSilverAmount(): int
    {
        $auctionData = $this->getAuctionData();
        return intval($auctionData['common']['silver'] ?? 0);
    }

    /**
     * @throws Exception
     * @throws Throwable
     */
    public function performBids(): void
    {
        $limit = 3;
        $auctionTable = $this->browser->driver->findElement(WebDriverBy::cssSelector('#auction .currentBid'));
        $auctionBidRows = $auctionTable->findElements(WebDriverBy::cssSelector('tbody tr'));

        throw_if(empty($auctionTable), new BusinessException('You must open auction page'));

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
                if (Str::contains($name, array_map([StringHelper::class, 'normalizeString'], $ignoredItems))) {
                    continue;
                }

                if (!$auctionBidRow->isDisplayed()) {
                    continue;
                }

                $timerSecondsLeft = rescue(function () use ($auctionBidRow) {
                    return $auctionBidRow->findElement(WebDriverBy::cssSelector('td.time .timer'))->getAttribute('value');
                }, 0);


                $amount = filter_var($name, FILTER_SANITIZE_NUMBER_INT);
                $itemCategoryElement = $auctionBidRow->findElement(WebDriverBy::cssSelector('td img.itemCategory'));
                $itemCategoryClasses = explode(' ', $itemCategoryElement->getAttribute('class'));
                $itemCategoryClass = Arr::first($itemCategoryClasses, function ($cssClass) {
                    return $cssClass !== 'itemCategory';
                });

                $itemCategory = Str::replace('itemCategory_', '', $itemCategoryClass);

                $price = TravianAuctionCategoryPrice::getPrice($itemCategory);

                $bidPrice = $amount * $price;


                $smallPriceRand = random_int(2, 9);
                $bidPrice += $smallPriceRand;

                $bidPrice = intval(NumberHelper::numberRandomizer($bidPrice, 3, 20));

                if ($bidPrice > $currentBidPrice && $timerSecondsLeft > 5) {
                    $bidButton->click();
                    TravianGameHelper::waitRandomizer(1);

                    /** @var RemoteWebElement $bidInput */
                    $bidInput = Arr::first($auctionTable->findElements(WebDriverBy::name('maxBid')));
                    if (empty($bidInput)) {
                        continue;
                    }

                    $bidInput->clear()->sendKeys($bidPrice);

                    TravianGameHelper::waitRandomizer(3);

                    $this->browser->driver->getKeyboard()->pressKey(WebDriverKeys::ENTER);
                    $bidCount++;

                    Log::channel('travian_auction')->info($name . ' ' . $itemCategory . ' ' . $price . ' ' . $bidPrice);
                }

                if ($bidCount > $limit) {
                    break;
                }
            }
        }

        Log::channel('travian_auction')->info($bidCount . ' bids made');
    }

    /**
     * @throws Exception
     */
    public function getHorsesAmount(): int
    {
        $horsesData = 0;

        retry(3, function () use (&$horsesData) {
            $village = 19289;
            $this->browser->visit(TravianRoute::mainRoute('?newdid=' . $village));
            TravianGameHelper::waitRandomizer(3);

            $troopsTable = $this->browser->driver->findElement(WebDriverBy::cssSelector('#troops'));

            $tableText = $troopsTable->getText();

            $troops = preg_split('/\s*\R/', trim($tableText));

            $horsesData = Arr::first($troops, function ($v) {
                return Str::contains($v, TravianTroopSelector::THEUTATES_THUNDERS_TITLE, true);
            });

        }, 2000);

        return intval($horsesData);
    }

    /**
     * @throws Exception
     */
    public function clickRandomLink(array $links, $times = 1): void
    {
        $ignoredUrls = [
            'mark=unread',
            'forum'
        ];
        for ($i = 0; $i < $times; $i++) {
            /** @var RemoteWebElement $link */
            $link = Arr::random($links);
            $href = $link->getAttribute('href');
            if ($href) {

                if (Str::contains($href, $ignoredUrls)) {
                    return;
                }

                Log::channel('travian')->debug('Clicked random element:');
                Log::channel('travian')->debug($href);

                $link->click();
                TravianGameHelper::waitRandomizer(2);

                $this->browser->screenshot(FileHelper::getScreenshotFileName($href));
            }
        }
    }
}
