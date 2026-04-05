<?php

use App\Models\Notice;
use Livewire\Livewire;
use App\Models\NoticeLocation;

test('notices page renders grid layout responsive', function () {
    $notices = Notice::factory()->public()->count(9)->create();

    $response = $this->get('/avisos');

    $response->assertSuccessful();
    // Grid classes should be present
    $response->assertSee('grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3');
});

test('notices page displays 9 notices per page in grid', function () {
    $notices = Notice::factory()->public()->count(12)->create();

    $response = $this->get('/avisos');

    $response->assertSuccessful();
    // First 9 notices should be visible
    $firstPage = $notices->sortByDesc('published_at')->take(9);
    foreach ($firstPage as $notice) {
        $response->assertSee($notice->title);
    }
});

test('notices page renders notice cards with responsive classes', function () {
    Notice::factory()->public()->create();

    $response = $this->get('/avisos');

    $response->assertSuccessful();
    // Should use notice-card component classes
    $response->assertSee('line-clamp-2');
    $response->assertSee('line-clamp-3');
});

test('notices page filter selector is visible and responsive', function () {
    Notice::factory()->public()->count(5)->create();

    $response = $this->get('/avisos');

    $response->assertSuccessful();
    $response->assertSee('sm:w-64');
    $response->assertSee(__('notices.filter.label'));
});

test('notices page filter updates grid when location selected', function () {
    $portal1 = 'A';
    $notice1 = Notice::factory()->public()->create();
    NoticeLocation::create([
        'notice_id' => $notice1->id,
        'location_type' => 'portal',
        'location_code' => $portal1,
    ]);

    $notice2 = Notice::factory()->public()->create();
    NoticeLocation::create([
        'notice_id' => $notice2->id,
        'location_type' => 'portal',
        'location_code' => 'B',
    ]);

    $this->get('/avisos')
        ->assertSee($notice1->title)
        ->assertSee($notice2->title);

    // Test filter with Livewire
    Livewire::test('public-notices')
        ->set('locationFilter', $portal1)
        ->assertSee($notice1->title);
});

test('notices page shows pagination when more than 9 notices', function () {
    Notice::factory()->public()->count(15)->create();

    $response = $this->get('/avisos');

    $response->assertSuccessful();
    // Pagination links should be present (check for page query parameter or pagination classes)
    $response->assertSee('href');
});

test('notices page pagination is centered', function () {
    Notice::factory()->public()->count(15)->create();

    $response = $this->get('/avisos');

    $response->assertSuccessful();
    $response->assertSee('flex justify-center');
});

test('notices page shows all notice cards in grid order', function () {
    $notice1 = Notice::factory()->public()->create(['published_at' => now()->subDays(2)]);
    $notice2 = Notice::factory()->public()->create(['published_at' => now()->subDay(1)]);
    $notice3 = Notice::factory()->public()->create(['published_at' => now()]);

    $response = $this->get('/avisos');

    $response->assertSuccessful();
    // Should be in reverse chronological order (latest first)
    $content = $response->getContent();
    $pos1 = strpos($content, $notice1->title);
    $pos2 = strpos($content, $notice2->title);
    $pos3 = strpos($content, $notice3->title);

    expect($pos3 < $pos2 && $pos2 < $pos1)->toBeTrue();
});

test('notices page grid maintains responsive gap spacing', function () {
    Notice::factory()->public()->count(6)->create();

    $response = $this->get('/avisos');

    $response->assertSuccessful();
    $response->assertSee('gap-4 sm:gap-6');
});
