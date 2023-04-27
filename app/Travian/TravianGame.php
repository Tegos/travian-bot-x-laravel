<?php

namespace App\Travian;

use App\Support\Browser\BrowserHelper;
use Carbon\Carbon;
use DateTime;
use Exception;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Lottery;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;

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
     */
    public function performRunFarmListAction(): void
    {
        $this->performLoginAction();

        $this->waitRandomizer(5);

        $this->performRandomAction();

        if ($this->isAuthenticated()) {

            Log::channel('travian')->info(__FUNCTION__);

            $driver = $this->browser->driver;
            $this->browser->visit(TravianRoute::mainRoute());

            $this->browser->visit(TravianRoute::rallyPointRoute());
            $driver->wait()->until(BrowserHelper::jqueryAjaxFinished());

            $this->browser->visit(TravianRoute::rallyPointFarmListRoute());
            $driver->wait()->until(BrowserHelper::jqueryAjaxFinished());

            $buttonStartAllFarmList = $this->browser->driver->findElement(WebDriverBy::cssSelector('#raidList button.startAll'));
            $buttonStartAllFarmList->click();

            // wait until cancel button is hidden
            $driver->wait(10, 1000)->until(
                WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('#raidList button.cancelDispatch'))
            );

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

            $driver = $this->browser->driver;

            foreach ($routes as $route) {
                $this->browser->visit($route);
                $driver->wait()->until(BrowserHelper::jqueryAjaxFinished());
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

    public function getServerDate(): DateTime
    {
        return Carbon::now(getenv('TIME_ZONE'));
    }

    public function getAuctionData(): array
    {
        $auction_url = '/hero/auction?action=sell';

        $auction_page = $this->makeRequest(
            [
                'method' => 'get',
                'url' => $auction_url
            ]
        );

        $crawler = new Crawler($auction_page->getBody()->getContents());
        $scripts = $crawler->filter('script')
            ->reduce(function (Crawler $node) {
                return strpos($node->text(), 'checkSum') !== false;
            });

        $check_sum_script = $scripts->first()->html();


        $lines = explode("\n", str_replace(["\r\n", "\n\r", "\r"], "\n", $check_sum_script));


        $check_sum_string = '';
        foreach ($lines as $line) {
            if (strpos($line, 'checkSum') !== false) {
                $check_sum_string = $line;
            }
        }

        $check_sum_string = trim($check_sum_string);
        $check_sum_string = rtrim($check_sum_string, ', {');
        $check_sum_string = rtrim($check_sum_string, '}');
        $check_sum_string = str_replace('data:', '', $check_sum_string);
        $check_sum_string = trim($check_sum_string);

        $result = [];
        $data = json_decode($check_sum_string, true);
        if (!empty($data)) {
            $result = $data ?? [];
        }

        return $result;
    }

    public function notifySellingAuction(): string
    {
        $auction_js_data = $this->getAuctionData();
        $auction_sell_data = $auction_js_data['sell'];
        $selling = $auction_sell_data['currentlySelling'];

        $game_server_date = $this->getServerDate();

        foreach ($selling as $k => $item) {
            unset($selling[$k]['description']);
            unset($selling[$k]['couldBeDeleted']);
            unset($selling[$k]['identifier']);
            unset($selling[$k]['obfuscatedId']);

            $date_end = Carbon::createFromTimestamp($item['time_end'])->timezone(getenv('TIME_ZONE'));
            $date_end_local = Carbon::createFromTimestamp($item['time_end'])->timezone(getenv('TIME_ZONE_LOCAL'));
            $selling[$k]['time_end_date'] = $date_end->format('d.m.Y H:i:s');
            $selling[$k]['left_time'] = $date_end->diff($game_server_date)->format('%H:%I:%S');

            $selling[$k]['time_end_date_local'] = $date_end_local->format('d.m.Y H:i:s');

        }

        if (count($selling) > 0) {
            $table = HtmlTable::build($selling);

            SendMail::send([
                'subject' => 'Selling Auction',
                'body' => $table,
                'is_body_html' => true,
            ]);

            return $table;
        }
        return '';
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
}
