<?php

namespace App\Travian;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

final class TravianScheduler
{
    const LOGIN_ACTION = 'LOGIN_ACTION';

    const FARM_LIST_ACTION = 'FARM_LIST_ACTION';

    const CHECK_FARM_LIST_ACTION = 'CHECK_FARM_LIST_ACTION';

    const AUCTION_SELLING = 'AUCTION_SELLING';

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

    public static function actionRunFarmListCronExpression(): string
    {
        $key = self::FARM_LIST_ACTION;

        $randomMinutePart = Cache::remember($key . 'minute-part', Carbon::now()->addHour(), function () {
            return random_int(0, 59);
        });

        $expressionEndPart = '* * * *';

        return $randomMinutePart . ' ' . $expressionEndPart;
    }

    public static function actionCheckRunFarmListCronExpression(): string
    {
        $key = self::CHECK_FARM_LIST_ACTION;

        $randomMinutePart = Cache::remember($key . 'minute-part', Carbon::now()->addHour(), function () {
            return random_int(0, 59);
        });

        $expressionEndPart = '* * * *';

        return $randomMinutePart . ' ' . $expressionEndPart;
    }

    public static function actionAuctionSellingCronExpression(): string
    {
        $key = self::AUCTION_SELLING;

        $randomMinutePart = Cache::remember($key . 'minute-part', Carbon::now()->addMinutes(30), function () {
            return random_int(0, 59);
        });

        $expressionEndPart = '7,9 * * *';

        return $randomMinutePart . ' ' . $expressionEndPart;
    }
}
