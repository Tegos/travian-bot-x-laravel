<?php

namespace App\Travian;

final class TravianRoute
{
    public static function mainRoute(): string
    {
        return self::buildUrl('dorf1.php');
    }

    protected static function buildUrl(string $path = ''): string
    {
        $domain = trim(config('services.travian.domain'), '/');

        return implode('/', [$domain, $path]);
    }
}
