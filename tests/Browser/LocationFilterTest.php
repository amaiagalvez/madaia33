<?php

/**
 * Validates: Requirements 4.5, 4.6
 */

use App\Models\Notice;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\NoticeLocation;

test('location filter on notices page filters results in real time', function () {
    $ts = now()->timestamp;

    // Create a notice for portal 33-A only
    $noticeA = Notice::create([
        'slug' => 'dusk-portal-a-'.$ts,
        'title_eu' => 'Portal 33-A Iragarkia '.$ts,
        'title_es' => 'Aviso Portal 33-A '.$ts,
        'content_eu' => 'Portal 33-A edukia.',
        'content_es' => 'Contenido Portal 33-A.',
        'is_public' => true,
        'published_at' => now(),
    ]);
    NoticeLocation::create([
        'notice_id' => $noticeA->id,
        'location_type' => 'portal',
        'location_code' => '33-A',
    ]);

    // Create a notice for portal 33-B only
    $noticeB = Notice::create([
        'slug' => 'dusk-portal-b-'.$ts,
        'title_eu' => 'Portal 33-B Iragarkia '.$ts,
        'title_es' => 'Aviso Portal 33-B '.$ts,
        'content_eu' => 'Portal 33-B edukia.',
        'content_es' => 'Contenido Portal 33-B.',
        'is_public' => true,
        'published_at' => now()->subSecond(),
    ]);
    NoticeLocation::create([
        'notice_id' => $noticeB->id,
        'location_type' => 'portal',
        'location_code' => '33-B',
    ]);

    $titleA = 'Portal 33-A Iragarkia '.$ts;
    $titleB = 'Portal 33-B Iragarkia '.$ts;

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($titleA, $titleB) {
        $browser->visit('/avisos')
            ->waitForText($titleA, 5)
            ->assertSee($titleA)
            ->assertSee($titleB);

        // Filter by portal 33-A — wait for Livewire to update
        $browser->select('#location-filter', '33-A')
            ->pause(1500)
            ->assertSee($titleA)
            ->assertDontSee($titleB);

        // Filter by portal 33-B
        $browser->select('#location-filter', '33-B')
            ->pause(1500)
            ->assertSee($titleB)
            ->assertDontSee($titleA);

        // Reset filter
        $browser->select('#location-filter', '')
            ->pause(1500)
            ->assertSee($titleA)
            ->assertSee($titleB);
    });

    $noticeA->delete();
    $noticeB->delete();
});
