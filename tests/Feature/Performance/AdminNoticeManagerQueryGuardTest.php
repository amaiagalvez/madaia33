<?php

// Feature: quality-db-query-guard
// Validates: Requisitos 2.1-2.4, 3.1-3.3, 6.2

use App\Models\Notice;
use Livewire\Livewire;
use App\Models\Location;
use App\Models\NoticeLocation;
use Tests\Feature\Performance\QueryGuardHelpers;

it('admin notice manager list stays within query budget', function () {
    // Budget: 15 queries — notice list loads notices + locations via eager loading
    // Margin: ~20% over expected baseline for 5 notices with 2 locations each
    $user = adminUser();

    Notice::factory(5)->create()->each(function (Notice $notice) {
        $locations = Location::factory(2)->create(['type' => 'portal']);
        $locations->each(fn (Location $loc) => NoticeLocation::create([
            'notice_id' => $notice->id,
            'location_id' => $loc->id,
        ]));
    });

    $log = QueryGuardHelpers::capture(function () use ($user) {
        Livewire::actingAs($user)
            ->test('admin-notice-manager');
    });

    QueryGuardHelpers::assertMaxQueries($log, 15, 'AdminNoticeManager render');
});

it('admin notice manager list has no runaway duplicate statements', function () {
    // Duplicate limit: 3 — con eager loading no debería haber consultas N+1 por notice
    // Se excluyen checks de autorización de roles (overhead de framework Livewire, 1 por notice rendered)
    $user = adminUser();

    Notice::factory(5)->create()->each(function (Notice $notice) {
        $locations = Location::factory(2)->create(['type' => 'portal']);
        $locations->each(fn (Location $loc) => NoticeLocation::create([
            'notice_id' => $notice->id,
            'location_id' => $loc->id,
        ]));
    });

    $log = QueryGuardHelpers::capture(function () use ($user) {
        Livewire::actingAs($user)
            ->test('admin-notice-manager');
    });

    QueryGuardHelpers::assertMaxDuplicates($log, 3, 'AdminNoticeManager duplicates', [
        'role_user', // checks de autorización de framework
    ]);
});
