<?php

namespace Tests;

use Laravel\Fortify\Features;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $appKey = (string) config('app.key');

        if ($appKey === '') {
            $appKey = 'base64:' . base64_encode(random_bytes(32));
            config()->set('app.key', $appKey);
            putenv('APP_KEY=' . $appKey);
            $_ENV['APP_KEY'] = $appKey;
            $_SERVER['APP_KEY'] = $appKey;
        }

        app()->useStoragePath(sys_get_temp_dir() . '/madaia33-storage-tests-' . getmypid());

        config()->set('mail.from.address', 'noreply@example.test');
        config()->set('mail.from.name', 'Madaia 33 Test');
    }

    protected function skipUnlessFortifyFeature(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }
}
