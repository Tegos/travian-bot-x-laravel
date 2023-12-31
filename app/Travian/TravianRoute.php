<?php

namespace App\Travian;

final class TravianRoute
{
    public static function mainRoute($query = ''): string
    {
        return self::buildUrl('dorf1.php' . $query);
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

    public static function reportRouteSurrounding(): string
    {
        $reportRoute = self::reportRoute();
        return $reportRoute . '/surrounding';
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

    public static function messagesInboxRoute(): string
    {
        return self::buildUrl('messages/inbox');
    }

    public static function villageStatisticsRoute(): string
    {
        return self::buildUrl('village/statistics');
    }

    public static function stableRoute(): string
    {
        return self::buildUrl('build.php?gid=20');
    }

    public static function mapRoute(): string
    {
        return self::buildUrl('karte.php');
    }

    public static function mapCoordinateRoute(int $x = 0, $y = 0): string
    {
        $mapRoute = self::mapRoute();
        return $mapRoute . '?' . http_build_query(['x' => $x, 'y' => $y]);
    }

    public static function positionDetailsRoute(int $x = 0, $y = 0): string
    {
        $positionDetailsRoute = self::buildUrl('position_details.php');
        return $positionDetailsRoute . '?' . http_build_query(['x' => $x, 'y' => $y]);
    }

    public static function profileRoute(int $userId = 0): string
    {
        $profileRoute = self::buildUrl('profile');
        return $profileRoute . '/' . $userId;
    }

    public static function statisticsPlayerTop10Route(): string
    {
        return self::buildUrl('statistics/player/top10');
    }

    public static function statisticsWoWRoute(): string
    {
        return self::buildUrl('statistics/wonderoftheworld');
    }

    protected static function buildUrl(string $path = ''): string
    {
        $domain = trim(config('services.travian.domain'), '/');

        return implode('/', [$domain, $path]);
    }
}
