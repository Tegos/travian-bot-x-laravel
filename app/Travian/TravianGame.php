<?php

namespace App\Travian;

use Carbon\Carbon;
use DateTime;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\WebDriverBy;
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
     */
    public function performLoginAction(): void
    {
        if(!$this->isAuthenticated()){
            $link = TravianRoute::mainRoute();
            $this->browser->visit($link);

            $this->browser
                ->type('name', config('services.travian.login'))
                ->type('password', config('services.travian.password'));

            $buttonLogin = $this->browser->driver->findElement(WebDriverBy::cssSelector('button[type=submit]'));

            $buttonLogin->click();

            $this->browser->waitForReload();
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
    private function setAjaxToken($response_content)
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

    public function runFarmLists()
    {
        $rallyPointFarmList = $this->client->get('/build.php?tt=99&id=39');
        $crawler = new Crawler($rallyPointFarmList->getBody()->getContents());
        $raidList = $crawler->filter('#raidList .raidList');

        $sort_params = [
            [
                'field' => 'lastRaid',
                'direction' => 'desc'
            ],
            [
                'field' => 'lastRaid',
                'direction' => 'asc'
            ],
            [
                'field' => 'distance',
                'direction' => 'asc'
            ],
            [
                'field' => 'distance',
                'direction' => 'desc'
            ],
            [
                'field' => 'bounty',
                'direction' => 'desc'
            ],
            [
                'field' => 'ew',
                'direction' => 'asc'
            ],
            [
                'field' => 'ew',
                'direction' => 'desc'
            ],
        ];

        $sort_param = $sort_params[array_rand($sort_params)];


        Log::i(self::$tag, 'Sort: ' . json_encode($sort_param));

        if ($raidList->count() > 0) {
            $allowed_list_ids = Helper::getAllowedFarmList();
            Log::i(self::$tag, 'Farm lists: ' . json_encode($allowed_list_ids));

            $first_run = true;
            $raidListArray = [];

            foreach ($raidList as $list) {
                $element = new Crawler($list);
                $raidListArray[] = $element;
            }

            $rand = RandomBreak::getRand();
            if ($rand > .5) {
                shuffle($raidListArray);
            }

            foreach ($raidListArray as $element) {

                if ($first_run) {
                    $param_a = $element->filter("form input[name=a]")->attr('value');
                }

                $raid_list_id = $element->attr('data-listid');
                if (in_array($raid_list_id, $allowed_list_ids)) {
                    $raid_form_data = [
                        'method' => 'ActionStartRaid',
                        'listId' => $raid_list_id,
                        'slots' => [],
                        'sort' => $sort_param['field'],
                        'direction' => $sort_param['field'],
                        'captcha' => null,
                        'a' => $param_a ?? '',
                        'loadedLists' => [],
                    ];

                    $json = $this->runFarmList($raid_form_data);
                    $param_a = $json['a'];
                    $first_run = false;
                }
            }
        }
    }

    public function runFarmList($raidData)
    {
        $response = $this->makeRequest(
            [
                'method' => 'post',
                'url' => '/api/v1/raid-list',
                'json' => $raidData,
                'ajax' => true
            ]
        );

        return json_decode($response->getBody()->getContents(), true);
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

    public function getVillageName($village_id = 0): string
    {
        $response = $this->client->get('/dorf1.php?newdid=' . $village_id);
        $response_content = $response->getBody()->getContents();
        $crawler = new Crawler($response_content);
        return $crawler->filter('#villageName .villageInput')->attr('value');
    }

}
