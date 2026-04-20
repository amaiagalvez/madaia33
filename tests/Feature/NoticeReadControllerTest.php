<?php

use App\Models\User;
use App\Models\Owner;
use App\Models\Notice;
use App\SupportedLocales;
use App\Models\NoticeRead;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

it('stores notice read once per owner', function () {
    $owner = Owner::factory()->create();
    $notice = Notice::factory()->create();

    test()->actingAs($owner->user)
        ->withoutMiddleware(PreventRequestForgery::class)
        ->postJson(route(SupportedLocales::routeName('notices.read', 'eu'), $notice))
        ->assertOk()
        ->assertJson(['ok' => true]);

    test()->actingAs($owner->user)
        ->withoutMiddleware(PreventRequestForgery::class)
        ->postJson(route(SupportedLocales::routeName('notices.read', 'eu'), $notice))
        ->assertOk()
        ->assertJson(['ok' => true]);

    expect(NoticeRead::query()->count())->toBe(1)
        ->and(NoticeRead::query()->first()?->notice_id)->toBe($notice->id)
        ->and(NoticeRead::query()->first()?->owner_id)->toBe($owner->id);
});

it('forbids read tracking when authenticated user has no owner profile', function () {
    $user = User::factory()->create();
    $notice = Notice::factory()->create();

    test()->actingAs($user)
        ->withoutMiddleware(PreventRequestForgery::class)
        ->postJson(route(SupportedLocales::routeName('notices.read', 'eu'), $notice))
        ->assertForbidden();

    expect(NoticeRead::query()->count())->toBe(0);
});
