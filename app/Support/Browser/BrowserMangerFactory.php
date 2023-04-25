<?php

namespace App\Support\Browser;

use App\Support\Browser\Driver\ChromeDriverCommon;

final class BrowserMangerFactory
{
    public static function create(): BrowserManager
    {
        $driver = new ChromeDriverCommon();

        return new BrowserManager($driver);
    }
}
