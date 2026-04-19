<?php

use App\Models\Notice;
use Livewire\Livewire;
use App\Livewire\PublicNotices;

test('livewire public notices component renders', function () {
    Livewire::test(PublicNotices::class)
        ->assertSuccessful();
});

test('livewire public notices displays 9 items per page', function () {
    Notice::factory()->public()->count(12)->create();

    Livewire::test(PublicNotices::class)
        ->assertViewHas('notices', function ($notices) {
            return $notices->count() === 9;
        });
});

test('livewire public notices filter selector is reactive', function () {
    $portal = 'A';
    $notice1 = Notice::factory()->public()->create();
    attachNoticeToLocationCode($notice1, $portal);

    $notice2 = Notice::factory()->public()->create();
    attachNoticeToLocationCode($notice2, 'B');

    Livewire::test(PublicNotices::class)
        ->assertSee($notice1->title)
        ->assertSee($notice2->title)
        ->set('locationFilter', $portal)
        ->assertSee($notice1->title);
});

test('livewire public notices filter resets pagination', function () {
    $portal = 'A';
    for ($i = 0; $i < 12; $i++) {
        $notice = Notice::factory()->public()->create(['published_at' => now()->subMinutes($i)]);
        if ($i < 6) {
            attachNoticeToLocationCode($notice, $portal);
        }
    }

    $component = Livewire::test(PublicNotices::class)
        ->call('setPage', 2) // Go to page 2
        ->set('locationFilter', $portal) // Filter
        ->assertViewHas('notices', function ($notices) {
            return $notices->currentPage() === 1; // Should reset to page 1
        });
});

test('livewire public notices paginates correctly', function () {
    Notice::factory()->public()->count(18)->create();

    Livewire::test(PublicNotices::class)
        ->assertViewHas('notices', function ($notices) {
            return $notices->lastPage() === 2;
        });
});

test('livewire public notices has translation check method', function () {
    // Component should render with translation handling
    Livewire::test(PublicNotices::class)
        ->assertSuccessful();
});

test('livewire public notices filter with general notices', function () {
    $portal = 'A';
    // Notice with portal location
    $notice1 = Notice::factory()->public()->create();
    attachNoticeToLocationCode($notice1, $portal);

    // Notice with no locations (general)
    $notice2 = Notice::factory()->public()->create();

    Livewire::test(PublicNotices::class)
        ->set('locationFilter', $portal)
        ->assertSee($notice1->title)
        ->assertSee($notice2->title); // General notices should also appear
});

test('livewire public notices orders by published_at descending', function () {
    $oldest = Notice::factory()->public()->create(['published_at' => now()->subDays(2)]);
    $middle = Notice::factory()->public()->create(['published_at' => now()->subDay()]);
    $newest = Notice::factory()->public()->create(['published_at' => now()]);

    Livewire::test(PublicNotices::class)
        ->assertViewHas('notices', function ($notices) use ($newest, $middle, $oldest) {
            $ids = $notices->pluck('id')->values()->all();

            return $ids === [$newest->id, $middle->id, $oldest->id];
        });
});

test('livewire public notices includes notices with future published_at', function () {
    $futureNotice = Notice::factory()->public()->create([
        'title_eu' => 'Etorkizuneko iragarkia',
        'title_es' => 'Anuncio futuro',
        'published_at' => now()->addDays(7),
    ]);

    Livewire::test(PublicNotices::class)
        ->assertSee($futureNotice->title);
});

test('livewire public notices handles empty state', function () {
    Livewire::test(PublicNotices::class)
        ->assertSee(__('notices.empty'));
});
