<?php

namespace App\Support\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

final class FileHelper
{
    public static function getScreenshotFileName(string $url = ''): string
    {
        $urlPath = (trim(parse_url($url, PHP_URL_PATH), '/'));
        $urlQuery = parse_url($url, PHP_URL_QUERY);
        parse_str($urlQuery, $urlQueries);

        $urlQueries = Arr::flatten($urlQueries);

        $nameParts = [
            'screen',
            Carbon::now()->toDateString(),
            $urlPath,
            StringHelper::normalizeString(implode('-', $urlQueries))
        ];

        $name = implode('/', array_filter($nameParts));

        return Str::snake($name);
    }
}
