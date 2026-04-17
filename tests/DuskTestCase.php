<?php

namespace Tests;

use Illuminate\Support\Collection;
use Laravel\Dusk\TestCase as BaseTestCase;
use Laravel\Dusk\Browser;
use Facebook\WebDriver\Chrome\ChromeOptions;
use PHPUnit\Framework\Attributes\BeforeClass;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

abstract class DuskTestCase extends BaseTestCase
{
    protected function driverUrl(): string
    {
        return $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515';
    }

    protected static function shouldUseRemoteDriver(): bool
    {
        $driverUrl = $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? '';

        return filled($driverUrl) && ! str_contains($driverUrl, 'localhost:9515');
    }

    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! Browser::hasMacro('dismissCookieConsentBanner')) {
            Browser::macro('dismissCookieConsentBanner', function () {
                /** @var Browser $this */
                $this->script("document.querySelector('[data-cookie-consent-understood], [data-cookie-consent-accept]')?.click();");
                $this->pause(120);

                return $this;
            });
        }

        if (! static::runningInSail() && ! static::shouldUseRemoteDriver()) {
            static::startChromeDriver(['--port=9515']);
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
            '--no-sandbox',
            '--disable-dev-shm-usage',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        if (! static::shouldUseRemoteDriver()) {
            $options->setBinary('/usr/bin/chromium');
        }

        return RemoteWebDriver::create(
            $this->driverUrl(),
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY,
                $options
            )
        );
    }
}
