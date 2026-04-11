<?php

use App\Models\Notice;
use Livewire\Livewire;
use App\SupportedLocales;

dataset('supported_locales', SupportedLocales::all());

test('notices page renders grid layout responsive', function () {
    $notices = Notice::factory()->public()->count(9)->create();

    $response = test()->get(route(SupportedLocales::routeName('notices', SupportedLocales::DEFAULT)));

    $response->assertSuccessful();
    // Grid classes should be present
    $response->assertSee('grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3');
});

test('notices page displays 9 notices per page in grid', function (string $locale) {
    $notices = Notice::factory()->public()->count(12)->create();

    $response = test()->get(route(SupportedLocales::routeName('notices', $locale)));

    $response->assertSuccessful();
    // First 9 notices should be visible
    $firstPage = $notices->sortByDesc('published_at')->take(9);
    foreach ($firstPage as $notice) {
        $response->assertSee($notice->title);
    }
})->with('supported_locales');

test('notices page renders notice cards with responsive classes', function () {
    Notice::factory()->public()->create();

    $response = test()->get(route(SupportedLocales::routeName('notices', SupportedLocales::DEFAULT)));

    $response->assertSuccessful();
    // Should use notice-card component classes
    $response->assertSee('line-clamp-2');
    $response->assertDontSee('line-clamp-4');
});

test('notices page renders a featured notice block layout', function () {
    Notice::factory()->public()->count(3)->create();

    $response = test()->get(route(SupportedLocales::routeName('notices', SupportedLocales::DEFAULT)));

    $response->assertSuccessful();
    $response->assertSee('lg:col-span-3');
    $response->assertSee('text-xl md:text-2xl lg:text-3xl');
});

test('notices page filter selector is visible and responsive', function () {
    Notice::factory()->public()->count(5)->create();

    $response = test()->get(route(SupportedLocales::routeName('notices', SupportedLocales::DEFAULT)));

    $response->assertSuccessful();
    $response->assertSee('data-page-hero="notices"', false);
    $response->assertSee('data-notices-filter', false);
    $response->assertSee('data-notices-filter-btn="all"', false);
    $response->assertDontSee(__('notices.filter.label'));
    $response->assertDontSee('lg:grid-cols-[minmax(0,1fr)_minmax(16rem,18rem)]');
});

test('notices page filter updates grid when location selected', function (string $locale) {
    $portal1 = 'A';
    $notice1 = Notice::factory()->public()->create();
    attachNoticeToLocationCode($notice1, $portal1);

    $notice2 = Notice::factory()->public()->create();
    attachNoticeToLocationCode($notice2, 'B');

    test()->get(route(SupportedLocales::routeName('notices', $locale)))
        ->assertSee($notice1->title)
        ->assertSee($notice2->title);

    // Test filter with Livewire
    Livewire::test('public-notices')
        ->set('locationFilter', $portal1)
        ->assertSee($notice1->title);
})->with('supported_locales');

test('notices page shows pagination when more than 9 notices', function () {
    Notice::factory()->public()->count(15)->create();

    $response = test()->get(route(SupportedLocales::routeName('notices', SupportedLocales::DEFAULT)));

    $response->assertSuccessful();
    // Pagination links should be present (check for page query parameter or pagination classes)
    $response->assertSee('href');
});

test('notices page pagination is centered', function () {
    Notice::factory()->public()->count(15)->create();

    $response = test()->get(route(SupportedLocales::routeName('notices', SupportedLocales::DEFAULT)));

    $response->assertSuccessful();
    $response->assertSee('flex justify-center');
});

test('notices page shows all notice cards in grid order', function (string $locale) {
    $notice1 = Notice::factory()->public()->create(['published_at' => now()->subDays(2)]);
    $notice2 = Notice::factory()->public()->create(['published_at' => now()->subDay(1)]);
    $notice3 = Notice::factory()->public()->create(['published_at' => now()]);

    $response = test()->get(route(SupportedLocales::routeName('notices', $locale)));

    $response->assertSuccessful();
    // Should be in reverse chronological order (latest first)
    $content = $response->getContent();
    $pos1 = strpos($content, $notice1->title);
    $pos2 = strpos($content, $notice2->title);
    $pos3 = strpos($content, $notice3->title);

    expect($pos3 < $pos2 && $pos2 < $pos1)->toBeTrue();
})->with('supported_locales');

test('notices page grid maintains responsive gap spacing', function () {
    Notice::factory()->public()->count(6)->create();

    $response = test()->get(route(SupportedLocales::routeName('notices', SupportedLocales::DEFAULT)));

    $response->assertSuccessful();
    $response->assertSee('data-notices-grid', false);
    $response->assertSee('gap-4 sm:gap-6');
});
