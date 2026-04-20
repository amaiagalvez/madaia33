<?php

use App\Models\User;
use App\Models\Owner;
use App\Models\Notice;
use App\Models\NoticeRead;
use App\Models\Construction;

it('requires authentication and lists only active constructions', function () {
    $activeConstruction = Construction::factory()->create([
        'title' => 'Aktibo dagoen obra',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(10),
        'is_active' => true,
    ]);

    $inactiveConstruction = Construction::factory()->create([
        'title' => 'Amaitutako obra',
        'starts_at' => now()->subDays(10),
        'ends_at' => now()->subDay(),
        'is_active' => true,
    ]);

    test()->get(route('constructions.eu'))->assertRedirect(route('login'));

    $user = User::factory()->create();

    test()->actingAs($user)
        ->get(route('constructions.eu'))
        ->assertOk()
        ->assertSee($activeConstruction->title)
        ->assertDontSee($inactiveConstruction->title);
});

it('shows notices bound to the construction tag only', function () {
    $user = User::factory()->create();
    $construction = Construction::factory()->create([
        'slug' => 'patioa',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(3),
        'is_active' => true,
    ]);

    $otherConstruction = Construction::factory()->create([
        'slug' => 'teilatua',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(5),
        'is_active' => true,
    ]);

    $matchingNotice = Notice::factory()->create([
        'title_eu' => 'Patioko aurrerapena',
        'title_es' => 'Avance del patio',
        'notice_tag_id' => $construction->tag->id,
        'is_public' => true,
        'published_at' => now(),
    ]);

    $otherNotice = Notice::factory()->create([
        'title_eu' => 'Teilatuko aurrerapena',
        'title_es' => 'Avance del tejado',
        'notice_tag_id' => $otherConstruction->tag->id,
        'is_public' => true,
        'published_at' => now(),
    ]);

    test()->actingAs($user)
        ->get(route('constructions.show.eu', ['slug' => $construction->slug]))
        ->assertOk()
        ->assertSee($matchingNotice->title)
        ->assertDontSee($otherNotice->title);
});

it('returns 404 for inactive or missing construction slugs', function () {
    $user = User::factory()->create();
    $inactiveConstruction = Construction::factory()->create([
        'slug' => 'itxita',
        'starts_at' => now()->subDays(10),
        'ends_at' => now()->subDay(),
        'is_active' => true,
    ]);

    test()->actingAs($user)
        ->get(route('constructions.show.eu', ['slug' => $inactiveConstruction->slug]))
        ->assertNotFound();

    test()->actingAs($user)
        ->get(route('constructions.show.eu', ['slug' => 'ez-dago']))
        ->assertNotFound();
});

it('orders construction notices by published date descending and exposes unread markers', function () {
    $owner = Owner::factory()->create();
    $construction = Construction::factory()->create([
        'slug' => 'fatxada',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(5),
        'is_active' => true,
    ]);

    $older = Notice::factory()->create([
        'title_eu' => 'Obra zaharra',
        'title_es' => 'Aviso viejo',
        'notice_tag_id' => $construction->tag->id,
        'is_public' => true,
        'published_at' => now()->subDays(2),
    ]);

    $newer = Notice::factory()->create([
        'title_eu' => 'Obra berria',
        'title_es' => 'Aviso nuevo',
        'notice_tag_id' => $construction->tag->id,
        'is_public' => true,
        'published_at' => now()->subDay(),
    ]);

    NoticeRead::query()->create([
        'notice_id' => $newer->id,
        'owner_id' => $owner->id,
        'user_id' => $owner->user_id,
        'ip_address' => '127.0.0.1',
        'opened_at' => now(),
    ]);

    $response = test()->actingAs($owner->user)
        ->get(route('constructions.show.eu', ['slug' => $construction->slug]));

    $response->assertOk()
        ->assertSee('data-construction-notice-unread="' . $older->id . '"', false)
        ->assertSee('data-construction-notice-read="' . $newer->id . '"', false);

    $content = $response->getContent();

    expect(strpos($content, 'Obra berria'))->toBeLessThan(strpos($content, 'Obra zaharra'));
});
