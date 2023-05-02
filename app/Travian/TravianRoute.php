<?php

namespace App\Travian;

final class TravianRoute
{
    public static function mainRoute(): string
    {
        return self::buildUrl('dorf1.php');
    }

    public static function buildingsRoute(): string
    {
        return self::buildUrl('dorf2.php');
    }

    public static function rallyPointRoute(): string
    {
        return self::buildUrl('build.php?id=39&gid=16');
    }

    public static function rallyPointFarmListRoute(): string
    {
        $rallyPointRoute = self::rallyPointRoute();
        return $rallyPointRoute . '&tt=99';
    }

    public static function allianceRoute(): string
    {
        return self::buildUrl('alliance');
    }

    public static function reportRoute(): string
    {
        return self::buildUrl('report');
    }

    public static function allianceReportRoute(): string
    {
        $allianceRoute = self::allianceRoute();
        return $allianceRoute . '/reports';
    }

    public static function heroInventoryRoute(): string
    {
        return self::buildUrl('hero/inventory');
    }

    public static function auctionRoute(): string
    {
        return self::buildUrl('hero/auction?tab=buy');
    }

    public static function auctionSellRoute(): string
    {
        return self::buildUrl('hero/auction?tab=sell');
    }

    protected static function buildUrl(string $path = ''): string
    {
        $domain = trim(config('services.travian.domain'), '/');

        return implode('/', [$domain, $path]);
    }
}
