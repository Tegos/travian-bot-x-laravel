<?php

namespace App\Support\Helpers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

final class FileHelper
{
    public static function getScreenshotFileName(string $url = ''): string
    {
        $urlPath = (trim(parse_url($url, PHP_URL_PATH), '/'));
        $urlQuery = parse_url($url, PHP_URL_QUERY);
        parse_str($urlQuery, $urlQueries);

        $urlQueriesParam = urldecode(http_build_query($urlQueries));

        $date = Carbon::now();

        $nameParts = [
            'screen',
            $date->toDateString(),
            $urlPath,
            StringHelper::normalizeString($urlQueriesParam) . '_' . $date->format('H_i')
        ];

        $name = implode('/', array_filter($nameParts));

        return Str::snake($name);
    }

    public static function getPlayerObserveScreenshotPath(string $playerLogin = ''): string
    {
        $date = Carbon::now();

        $nameParts = [
            'profile-observe',
            $date->toDateString(),
            $playerLogin,
            'player-details' . '-' . $date->format('H-i')
        ];

        $name = implode('/', $nameParts);
        $name = Str::snake($name);

        return sprintf('%s/%s.png', rtrim(config('laravel-console-dusk.paths.screenshots'), '/'), $name);
    }
}
