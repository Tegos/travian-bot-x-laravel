<?php

namespace App\Support\Browser;

final class BrowserHelper
{
    public static function jqueryAjaxFinished(): callable
    {
        return static function ($driver): bool {
            return $driver->executeScript('return jQuery.active === 0;');
        };
    }
}
