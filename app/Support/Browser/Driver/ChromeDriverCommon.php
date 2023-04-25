<?php

namespace App\Support\Browser\Driver;

use Closure;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\Chrome\SupportsChrome;
use NunoMaduro\LaravelConsoleDusk\Contracts\Drivers\DriverContract;

class ChromeDriverCommon implements DriverContract
{
    use SupportsChrome;

    public function open(): void
    {
        static::startChromeDriver();
    }

    public function close(): void
    {
        static::stopChromeDriver();
    }

    public static function afterClass(Closure $callback): void
    {
        // ..
    }

    public function getDriver(): RemoteWebDriver
    {
        $options = (new ChromeOptions())->addArguments(
            array_filter(
                [
                    '--no-sandbox',
                    '--disable-gpu',
                    '--window-size=1920,1080',
                    '--ignore-certificate-errors',
                    '--ignore-ssl-errors',
                    '--allow-running-insecure-content',
                    '--user-data-dir=' . config('laravel-console-dusk.paths.data'),
                    $this->runHeadless(),
                ]
            )
        );

        return RemoteWebDriver::create(
            'http://localhost:9515',
            DesiredCapabilities::chrome()
                ->setCapability(
                    ChromeOptions::CAPABILITY,
                    $options
                )
        );
    }

    /**
     * Running around headless, or not..
     */
    protected function runHeadless(): ?string
    {
        return !config('laravel-console-dusk.headless', true) && !app()->isProduction() ? null : '--headless';
    }

    public function __destruct()
    {
        $this->close();
    }
}
