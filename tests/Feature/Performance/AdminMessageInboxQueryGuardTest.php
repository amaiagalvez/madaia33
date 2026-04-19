<?php

// Feature: quality-db-query-guard
// Validates: Requisitos 2.1-2.4, 3.1-3.3, 6.3

use Livewire\Livewire;
use App\Models\ContactMessage;
use Tests\Feature\Performance\QueryGuardHelpers;

it('admin message inbox stays within query budget', function () {
    // Budget: 10 queries — simple list with pagination, no nested relations expected
    // Margin: ~20% over expected baseline for 5 mixed read/unread messages
    $user = adminUser();

    ContactMessage::factory(3)->unread()->create();
    ContactMessage::factory(2)->read()->create();

    $log = QueryGuardHelpers::capture(function () use ($user) {
        Livewire::actingAs($user)
            ->test('admin-message-inbox');
    });

    QueryGuardHelpers::assertMaxQueries($log, 10, 'AdminMessageInbox render');
});

it('admin message inbox has no runaway duplicate statements', function () {
    // Duplicate limit: 2 — inbox es simple; duplicadas indican regresión evidente
    // Se excluyen checks de autorización de roles (overhead de framework Livewire)
    $user = adminUser();

    ContactMessage::factory(3)->unread()->create();
    ContactMessage::factory(2)->read()->create();

    $log = QueryGuardHelpers::capture(function () use ($user) {
        Livewire::actingAs($user)
            ->test('admin-message-inbox');
    });

    QueryGuardHelpers::assertMaxDuplicates($log, 2, 'AdminMessageInbox duplicates', [
        'role_user', // checks de autorización de framework
    ]);
});
