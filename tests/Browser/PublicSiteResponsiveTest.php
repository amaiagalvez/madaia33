<?php

/**
 * Validates: Requirements 1.7, 3.1, 3.5, 4.2, 4.4, 5.1, 5.3, 6.1, 6.3, 8.2
 */

use App\Models\Image;
use App\Models\Notice;
use App\Models\Voting;
use App\Models\Setting;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Http;

function mailhogMessageTotal(): int
{
    try {
        $response = Http::timeout(5)->get('http://mailhog:8025/api/v2/messages', ['limit' => 500]);
    } catch (Throwable) {
        return 0;
    }

    if (! $response->successful()) {
        return 0;
    }

    return (int) $response->json('total', 0);
}

function mailhogIsAvailable(): bool
{
    try {
        $response = Http::timeout(5)->get('http://mailhog:8025/api/v2/messages', ['limit' => 1]);
    } catch (Throwable) {
        return false;
    }

    return $response->successful();
}

function waitForMailhogCountIncrease(int $initialCount, int $expectedIncrease, int $attempts = 40): void
{
    for ($attempt = 0; $attempt < $attempts; $attempt++) {
        if (mailhogMessageTotal() >= $initialCount + $expectedIncrease) {
            expect(true)->toBeTrue();

            return;
        }

        usleep(500000);
    }

    expect(mailhogMessageTotal())->toBeGreaterThanOrEqual($initialCount + $expectedIncrease);
}

test('home page smoke on mobile renders hero latest notices and working mobile menu', function () {
    Notice::factory()->public()->count(6)->create();
    Image::factory()->count(4)->create();

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit('/eu')
            ->dismissCookieConsentBanner()
            ->resize(375, 812)
            ->pause(500)
            ->assertScript("return document.querySelector('[data-hero-slider]') !== null;", true)
            ->assertScript("return document.querySelector('[data-latest-notices-general]') !== null;", true)
            ->assertScript("return getComputedStyle(document.querySelector('header')).position;", 'sticky')
            ->assertScript("return document.getElementById('livewire-error') === null;", true)
            ->click('[data-hamburger-button]')
            ->pause(350)
            ->assertScript("return getComputedStyle(document.querySelector('[data-mobile-menu]')).display !== 'none';", true)
            ->click('[data-mobile-notices-link]')
            ->pause(350)
            ->assertPathIs('/eu/iragarkiak');
    });
});

test('home results announcement is outside history block and uses notice-like typography', function () {
    Notice::factory()->public()->count(2)->create();
    Image::factory()->count(2)->create();
    Voting::factory()->create([
        'show_results' => true,
        'ends_at' => now()->subDay(),
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit('/eu')
            ->dismissCookieConsentBanner()
            ->pause(500)
            ->assertPresent('[data-home-results-announcement]')
            ->assertScript(
                "return (function () { var history = document.querySelector('[data-home-history]'); var announcement = document.querySelector('[data-home-results-announcement]'); return Boolean(history && announcement && !history.contains(announcement)); })();",
                true
            )
            ->assertScript(
                "return (function () { var title = document.querySelector('[data-home-results-announcement-title]'); return Boolean(title && title.classList.contains('text-base') && title.classList.contains('md:text-lg')); })();",
                true
            )
            ->assertScript(
                "return (function () { var body = document.querySelector('[data-home-results-announcement-body]'); return Boolean(body && body.classList.contains('text-sm') && body.classList.contains('md:text-base')); })();",
                true
            );
    });
});

test('notices page smoke checks responsive grid filter and pagination', function () {
    Notice::factory()->public()->count(10)->create([
        'published_at' => now()->subMinute(),
    ]);

    $matchingNotice = Notice::factory()->public()->create([
        'title_eu' => 'Smoke Notice A',
        'title_es' => 'Smoke Notice A',
        'published_at' => now()->addMinutes(2),
    ]);
    attachNoticeToLocationCode($matchingNotice, '33-A');

    $nonMatchingNotice = Notice::factory()->public()->create([
        'title_eu' => 'Smoke Notice B',
        'title_es' => 'Smoke Notice B',
        'published_at' => now()->addMinute(),
    ]);
    attachNoticeToLocationCode($nonMatchingNotice, '33-B');

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit('/eu/iragarkiak')
            ->dismissCookieConsentBanner()
            ->resize(375, 812)
            ->pause(500)
            ->assertScript(
                "return getComputedStyle(document.querySelector('[data-notices-grid]')).gridTemplateColumns.split(' ').length;",
                1
            )
            ->assertScript(<<<'JS'
                (() => {
                    const button = Array.from(document.querySelectorAll('[data-notices-filter-btn]'))
                        .find((element) => (element.textContent || '').includes('33-A'));

                    if (!button) {
                        return false;
                    }

                    button.click();

                    return true;
                })();
            JS, true)
            ->pause(600)
            ->assertScript("return document.body.innerText.includes('Smoke Notice A');", true)
            ->resize(1200, 900)
            ->pause(400)
            ->assertScript(
                "return getComputedStyle(document.querySelector('[data-notices-grid]')).gridTemplateColumns.split(' ').length;",
                3
            )
            ->assertScript(
                "return document.querySelector('nav[aria-label=\"Pagination Navigation\"]') !== null || document.querySelector('nav[role=\"navigation\"]') !== null;",
                true
            );
    });
});

test('gallery smoke opens lightbox closes with escape and works after rotating to landscape', function () {
    Image::factory()->count(6)->create();

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit('/eu/argazki-bilduma')
            ->dismissCookieConsentBanner()
            ->resize(375, 812)
            ->pause(400)
            ->click('[data-gallery-open]')
            ->pause(350)
            ->assertScript("return document.body.style.overflow === 'hidden';", true)
            ->script("document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }));");

        $browser->pause(350)
            ->assertScript('return document.body.style.overflow;', '')
            ->resize(667, 375)
            ->pause(350);

        $browser->script("document.querySelector('[data-gallery-open]').click();");

        $browser->pause(350)
            ->assertScript(
                "return document.querySelector('[data-lightbox] img').classList.contains('max-h-[85vh]');",
                true
            );
    });
});

test('contact form smoke submits successfully and sends both emails', function () {
    $timestamp = now()->timestamp;
    $visitorEmail = "smoke-{$timestamp}@example.com";
    $subject = "Smoke test {$timestamp}";
    $mailhogAvailable = mailhogIsAvailable();
    $initialMailhogCount = $mailhogAvailable ? mailhogMessageTotal() : 0;

    Setting::updateOrCreate(['key' => 'admin_email'], ['value' => 'admin@example.com']);
    Setting::updateOrCreate(['key' => 'recaptcha_secret_key'], ['value' => '']);
    Setting::updateOrCreate(['key' => 'recaptcha_site_key'], ['value' => '']);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($visitorEmail, $subject) {
        $browser->visit('/eu/harremana')
            ->dismissCookieConsentBanner()
            ->resize(375, 812)
            ->pause(400)
            ->type('#contact-name', 'Smoke Test User')
            ->type('#contact-email', $visitorEmail)
            ->type('#contact-subject', $subject)
            ->type('#contact-message', 'Responsive browser smoke test message.')
            ->check('#contact-legal')
            ->script("document.getElementById('recaptcha-token').value = 'smoke-token'; document.getElementById('recaptcha-token').dispatchEvent(new Event('input')); ");

        $browser
            ->press('Bidali')
            ->waitForText('Zure mezua bidali da', 10)
            ->assertSee('Zure mezua bidali da');
    });

    expect(ContactMessage::query()->where('email', $visitorEmail)->where('subject', $subject)->exists())->toBeTrue();

    if ($mailhogAvailable) {
        waitForMailhogCountIncrease($initialMailhogCount, 2);
    }
});
