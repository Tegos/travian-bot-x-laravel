<?php

namespace App\Travian\Helpers;

use App\Exceptions\Travian\GameRandomBreakException;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Lottery;
use Throwable;

final class TravianGameHelper
{
    /**
     * @param int $minWaitSeconds
     * @param float $probability
     * @throws Exception
     */
    public static function waitRandomizer(int $minWaitSeconds = 20, float $probability = 0.5): void
    {
        $chances = 10;
        $outOf = intval(ceil($chances / $probability));
        $probabilityResult = Lottery::odds($chances, $outOf)->choose();

        $maxWaitSeconds = intval(ceil($minWaitSeconds + ($minWaitSeconds * 0.2)));

        $seconds = $probabilityResult ? random_int($minWaitSeconds, $maxWaitSeconds) : 1;
        Log::channel('travian')->debug("Delay: $seconds sec");
        sleep($seconds);
    }

    /**
     * @throws Exception
     * @throws Throwable
     */
    public static function randomBreak(float $probability = 0.1): void
    {
        $chances = 10;
        $outOf = intval(ceil($chances / $probability));

        $probabilityResult = Lottery::odds($chances, $outOf)->choose();

        throw_if($probabilityResult, new GameRandomBreakException());
    }

    /**
     * Declare own callable function which could be passed to `$driver->wait()->until()`.
     * @return callable
     */
    public static function jqueryAjaxFinished(): callable
    {
        return static function ($driver): bool {
            return $driver->executeScript('return jQuery.active === 0;');
        };
    }
}
