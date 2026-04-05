<?php

/**
 * Validates: Requirements 1.7, 3.1, 3.5, 4.2, 4.4, 5.1, 5.3, 6.1, 6.3, 8.2
 */

use App\Models\Image;
use App\Models\Notice;
use App\Models\Setting;
use Laravel\Dusk\Browser;
use App\Models\ContactMessage;
use App\Models\NoticeLocation;
use Illuminate\Support\Facades\Http;

function mailhogDeliveredMessages(string $subject, array $recipients): bool
{
  try {
    $response = Http::timeout(2)->get('http://mailhog:8025/api/v2/messages');
  } catch (Throwable) {
    return false;
  }

  if (! $response->successful()) {
    return false;
  }

  $messages = collect($response->json('items', []));

  return collect($recipients)->every(function (string $recipient) use ($messages, $subject): bool {
    return $messages->contains(function (array $message) use ($recipient, $subject): bool {
      $headers = $message['Content']['Headers'] ?? [];
      $subjects = $headers['Subject'] ?? [];
      $toHeader = implode(',', $headers['To'] ?? []);

      return in_array($subject, $subjects, true)
        && str_contains($toHeader, $recipient);
    });
  });
}

function waitForMailhogDelivery(string $subject, array $recipients, int $attempts = 10): void
{
  for ($attempt = 0; $attempt < $attempts; $attempt++) {
    if (mailhogDeliveredMessages($subject, $recipients)) {
      expect(true)->toBeTrue();

      return;
    }

    usleep(500000);
  }

  expect(mailhogDeliveredMessages($subject, $recipients))->toBeTrue();
}

test('home page smoke on mobile renders hero latest notices and working mobile menu', function () {
  Notice::factory()->public()->count(6)->create();
  Image::factory()->count(4)->create();

  $this->browse(function (Browser $browser) {
    $browser->visit('/')
      ->resize(375, 812)
      ->pause(500)
      ->assertScript("return document.querySelector('[data-hero-slider]') !== null;", true)
      ->assertScript("return document.querySelector('[data-latest-notices]') !== null;", true)
      ->assertScript("return getComputedStyle(document.querySelector('header')).position;", 'sticky')
      ->assertScript("return document.getElementById('livewire-error') === null;", true)
      ->click('[data-hamburger-button]')
      ->pause(350)
      ->assertScript("return getComputedStyle(document.querySelector('[data-mobile-menu]')).display !== 'none';", true)
      ->click('[data-first-menu-item]')
      ->pause(350)
      ->assertPathIs('/avisos');
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
  NoticeLocation::create([
    'notice_id' => $matchingNotice->id,
    'location_type' => 'portal',
    'location_code' => 'A',
  ]);

  $nonMatchingNotice = Notice::factory()->public()->create([
    'title_eu' => 'Smoke Notice B',
    'title_es' => 'Smoke Notice B',
    'published_at' => now()->addMinute(),
  ]);
  NoticeLocation::create([
    'notice_id' => $nonMatchingNotice->id,
    'location_type' => 'portal',
    'location_code' => 'B',
  ]);

  $this->browse(function (Browser $browser) {
    $browser->visit('/avisos')
      ->resize(375, 812)
      ->pause(500)
      ->assertScript(
        "return getComputedStyle(document.querySelector('[data-notices-grid]')).gridTemplateColumns.split(' ').length;",
        1
      )
      ->select('#location-filter', 'A')
      ->pause(600)
      ->assertScript("return document.body.innerText.includes('Smoke Notice A');", true)
      ->resize(1024, 768)
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

  $this->browse(function (Browser $browser) {
    $browser->visit('/galeria')
      ->resize(375, 812)
      ->pause(400)
      ->click('[data-gallery-open]')
      ->pause(350)
      ->assertScript("return getComputedStyle(document.querySelector('[data-lightbox]')).display !== 'none';", true)
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

  Setting::updateOrCreate(['key' => 'admin_email'], ['value' => 'admin@example.com']);
  Setting::updateOrCreate(['key' => 'recaptcha_secret_key'], ['value' => '']);
  Setting::updateOrCreate(['key' => 'recaptcha_site_key'], ['value' => '']);

  $this->browse(function (Browser $browser) use ($visitorEmail, $subject) {
    $browser->visit('/contacto')
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

  waitForMailhogDelivery($subject, [$visitorEmail, 'admin@example.com']);
});
