<?php

namespace App\Travian;

use App\Support\Helpers\NumberHelper;
use Carbon\CarbonInterface;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

final class TravianScheduler
{
    const LOGIN_ACTION = 'LOGIN_ACTION';

    const FARM_LIST_ACTION = 'FARM_LIST_ACTION';

    const CHECK_FARM_LIST_ACTION = 'CHECK_FARM_LIST_ACTION';

    const FARM_LIST_CONFIRM_ACTION = 'FARM_LIST_CONFIRM_ACTION';

    const AUCTION_SELLING = 'AUCTION_SELLING';

    const AUCTION_BIDS = 'AUCTION_BIDS';

    /**
     * @throws Exception
     */
    public static function actionLoginScheduleCronExpression(): string
    {
        $key = self::LOGIN_ACTION;

        $randomMinutePart = Cache::remember($key . 'minute-part', Carbon::now()->addHour(), function () {
            return random_int(0, 59);
        });

        $expressionEndPart = Cache::remember($key . 'end-part', Carbon::now()->endOfDay(), function () {

            // hour between 5:00 and 23:00
            $hourRange = range(5, 23);
            shuffle($hourRange);
            // execute 5 times per day
            $times = 5;
            $randomHours = array_slice($hourRange, 0, $times);
            sort($randomHours);

            return implode(',', $randomHours) . ' * * *';
        });

        return $randomMinutePart . ' ' . $expressionEndPart;
    }

    /**
     * @throws Exception
     */
    public static function actionRunFarmListCronExpression(): string
    {
        $key = self::FARM_LIST_ACTION;
        $now = Carbon::now();
        $currentHour = $now->hour;

        // limits minutes
        $minDiff = 11;
        $maxDiff = 19;

        $expressionEndPart = '* * * *';

        $hours = CarbonInterface::HOURS_PER_DAY;

        $cacheKeyHour = "$key-$currentHour";

        if (!Cache::has($cacheKeyHour)) {

            for ($h = 0; $h < $hours; $h++) {
                $perHour = random_int(3, 4);

                $minuteRange = [random_int(1, 57)];
                $count = 1000;
                $i = 0;
                while (count($minuteRange) < $perHour) {
                    $i++;
                    $num = random_int(1, 57);

                    if (NumberHelper::minDist($minuteRange, $num) > $minDiff && NumberHelper::minDist($minuteRange, $num) < $maxDiff) {
                        $minuteRange[] = $num;
                    }

                    if ($i > $count) {
                        break;
                    }

                }

                sort($minuteRange);

                Cache::put("$key-$h", implode(',', $minuteRange) . ' ' . $expressionEndPart, Carbon::now()->endOfDay());

            }
        }

        return Cache::get($cacheKeyHour);
    }

    public static function actionAuctionBidsCronExpression(): string
    {
        $key = self::AUCTION_BIDS;

        $randomMinutePart = Cache::remember($key . 'minute-part', Carbon::now()->addHour(), function () {
            return random_int(0, 59);
        });

        $expressionEndPart = '* * * *';

        return $randomMinutePart . ' ' . $expressionEndPart;
    }
}
