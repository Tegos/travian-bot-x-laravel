<?php

namespace App\Travian;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

final class TravianScheduler
{
    const LOGIN_ACTION = 'LOGIN_ACTION';

    /**
     * @throws Exception
     */
    public static function actionLoginScheduleCronExpression(): string
    {
        $key = self::LOGIN_ACTION;

        $expression = Cache::get($key);
        if (!$expression) {
            $randomMinute = random_int(0, 59);

            // hour between 8:00 and 19:00
            $hourRange = range(8, 19);
            shuffle($hourRange);
            // execute twice a day
            $randomHours = array_slice($hourRange, 0, 2);

            $expression = $randomMinute . ' ' . implode(',', $randomHours) . ' * * *';
            Cache::put($key, $expression, Carbon::now()->endOfDay());
        }

        return $expression;
    }
}
