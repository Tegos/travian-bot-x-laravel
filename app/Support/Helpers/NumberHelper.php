<?php

namespace App\Support\Helpers;

final class NumberHelper
{
    public static function numberRandomizer(int $number, int $minPercent = 10, $maxPercent = 30): string
    {
        $percent = rand($minPercent, $maxPercent);
        $num = $number * $percent / 100;

        return ceil($number + $num);
    }
}
