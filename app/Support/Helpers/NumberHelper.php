<?php

namespace App\Support\Helpers;

final class NumberHelper
{
    public static function numberRandomizer(int $number, int $minPercent = 10, $maxPercent = 30): float
    {
        $percent = rand($minPercent, $maxPercent);
        $num = min($number * $percent / 100, 1);

        return ceil($number + $num);
    }

    public static function minDist($array, $n)
    {
        $distances = [];

        foreach ($array as $item) {
            $distances[] = abs($item - $n);
        }

        return min($distances);
    }
}
