<?php


namespace App\Support\Browser;

use Exception;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use NunoMaduro\LaravelConsoleDusk\Contracts\Drivers\DriverContract;

final class CommonBrowserFactory
{
    use ProvidesBrowser;

    protected DriverContract $driver;

    /**
     * @throws Exception
     */
    public function make(DriverContract $driver): Browser
    {
        $this->driver = $driver;

        return new Browser($this->createWebDriver());
    }

    protected function driver()
    {
        return $this->driver->getDriver();
    }
}
