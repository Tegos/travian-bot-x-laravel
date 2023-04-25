<?php

namespace App\Support\Browser;

use Closure;
use Exception;
use NunoMaduro\LaravelConsoleDusk\Manager;

final class BrowserManager extends Manager
{
    /**
     * @throws Exception
     */
    public function watch(Closure $callback = null): void
    {
        $this->driver->open();

        $browserFactory = new CommonBrowserFactory();

        $browser = $browserFactory->make($this->driver);

        try {
            $callback($browser);
        } catch (\Throwable $e) {
        }

        $browser->quit();

        $this->driver->close();

        if (!empty($e)) {
            throw $e;
        }
    }
}
