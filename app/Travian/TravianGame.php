<?php

namespace App\Travian;

use App\Exceptions\Travian\GameRandomBreakException;
use App\Support\Helpers\StringHelper;
use App\View\Table\ConsoleBaseTable;
use App\View\Table\HtmlTable;
use Carbon\Carbon;
use Exception;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Lottery;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Throwable;

final class TravianGame
{
    private Browser $browser;

    public function __construct(Browser $browser)
    {
        $this->browser = $browser;
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
     * @param $response_content
     */
    protected function setAjaxToken($response_content)
    {
        // set ajaxToken
        $html = $response_content;
        $crawler = new Crawler($html);

        $scripts = $crawler->filter('script')
            ->reduce(function (Crawler $node) {
                return strpos($node->text(), 'eval') !== false;
            });

        $eval_script = $scripts->first()->text();

        $lines = array_filter(explode(';', $eval_script));

        $eval_string = '';
        foreach ($lines as $line) {
            if (strpos($line, 'eval') !== false) {
                $eval_string = $line;
            }
        }

        $regex = "/.*\(([^)]*)\)/";
        preg_match($regex, $eval_string, $matches);

        $atob_content = end($matches);
        $atob_content = str_replace("'", '', $atob_content);
        $content = base64_decode($atob_content);

        $parts = explode('&&', $content);
        $token = trim($parts[1] ?? '');
        $token = str_replace("'", '', $token);

        $this->ajaxToken = $token;

    }

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
    public function performNotifyAuctionSellingAction(): void
    {
        $this->performLoginAction();

        $this->waitRandomizer(3);

        if ($this->isAuthenticated()) {

            Log::channel('travian')->info(__FUNCTION__);

            $driver = $this->browser->driver;

            $this->browser->visit(TravianRoute::mainRoute());
            $this->waitRandomizer(3);

            $this->browser->visit(TravianRoute::auctionSellRoute());
            $this->waitRandomizer(1);

            $scripts = $driver->findElements(WebDriverBy::tagName('script'));

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

            $auctionData = json_decode($dataJsonString, true) ?? [];

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
                    $tableHtml = new HtmlTable($sellingItems, $headers);
                    $consoleTable = new ConsoleBaseTable($sellingItems, $headers);

                    Log::channel('travian')->info($consoleTable);

                    //Mail::to(config('mail.to'))->send(new TravianAuctionSellingNotification($tableHtml));
                }
            }

            $this->waitRandomizer(3);

            $this->browser->screenshot(Str::snake(__FUNCTION__));
        }
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

    public function clearOffensiveReport(): int
    {
        // offensive - without losses
        $url_report = '/report/offensive?opt=AAABAA==';
        return $this->clearReport($url_report);
    }

    public function clearMerchantsReport(): int
    {
        // merchants
        $url_report = '/report/other?opt=AAALAAwADQAOAA==';
        return $this->clearReport($url_report);
    }

    public function clearReport($url_report): int
    {
        $total_messages = 0;

        $reportsPage = $this->makeRequest(
            [
                'method' => 'get',
                'url' => $url_report
            ]
        );

        $crawler = new Crawler($reportsPage->getBody()->getContents());
        $inputs = $crawler->filter('#reportsForm table tr td.sel input');

        $input_array = [];

        foreach ($inputs as $node) {
            $element = new Crawler($node);
            $id = $element->attr('value');
            $input_array[] = $id;
        }

        $total_inputs = count($input_array);
        $total_messages += $total_inputs;


        if ($total_inputs > 0) {
            $post_data = [
                'ids' => $input_array,
                'do' => 'delete',
            ];

            $this->makeRequest([
                'method' => 'post',
                'url' => '/report/offensive?page=1',
                'body' => $post_data
            ], true);
        }

        return $total_messages;
    }

    /**
     * @throws Exception
     */
    private function waitRandomizer(int $minWaitSeconds = 20, float $probability = 0.5): void
    {
        $chances = 10;
        $outOf = intval(ceil($chances / $probability));
        $probabilityResult = Lottery::odds($chances, $outOf)->choose();

        $maxWaitSeconds = intval($minWaitSeconds + ($minWaitSeconds * 0.2));

        $seconds = $probabilityResult ? random_int($minWaitSeconds, $maxWaitSeconds) : 1;
        Log::channel('travian')->info("Delay: $seconds sec");
        sleep($seconds);
    }

    /**
     * @throws Exception
     * @throws Throwable
     */
    private function randomBreak(float $probability = 0.1): void
    {
        $chances = 10;
        $outOf = intval(ceil($chances / $probability));

        $probabilityResult = Lottery::odds($chances, $outOf)->choose();

        throw_if($probabilityResult, new GameRandomBreakException());
    }
}
