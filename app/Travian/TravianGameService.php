<?php

namespace App\Travian;

use Exception;
use Facebook\WebDriver\WebDriverBy;
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

            if (Str::contains($scriptContent, ['checkSum', 'HeroAuction '])) {
                $auctionDataScript = $scriptContent;
                break;
            }
        }

        // get json from string
        preg_match('/(\{.+})/', $auctionDataScript, $result);
        $dataJsonString = $result[0] ?? '';

        return json_decode($dataJsonString, true) ?? [];
    }
}
