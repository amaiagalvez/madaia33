<?php

// Feature: community-web, Task 11: Admin panel — Message inbox
// Validates: Requirements 14.1–14.8

use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;
use App\Models\ContactMessage;

it('allows general admin to access messages route', function () {
    Role::query()->firstOrCreate(['name' => Role::GENERAL_ADMIN]);

    $generalAdmin = User::factory()->create();
    $generalAdmin->assignRole(Role::GENERAL_ADMIN);

    test()->actingAs($generalAdmin)
        ->get(route('admin.messages'))
        ->assertOk();
});

it('forbids message deletion for general admin users', function () {
    Role::query()->firstOrCreate(['name' => Role::GENERAL_ADMIN]);

    $generalAdmin = User::factory()->create();
    $generalAdmin->assignRole(Role::GENERAL_ADMIN);

    $message = ContactMessage::factory()->create();

    Livewire::actingAs($generalAdmin)
        ->test('admin-message-inbox')
        ->assertDontSeeHtml('data-admin-action="delete"')
        ->call('confirmDelete', $message->id)
        ->assertForbidden();
});

// ─────────────────────────────────────────────────────────────────────────────
// Property 14: Message inbox completeness
// Validates: Requirement 14.1
// ─────────────────────────────────────────────────────────────────────────────

it('all messages appear in inbox when all filter is active', function () {
    $user = adminUser();
    $read = ContactMessage::factory()->read()->create();
    $unread = ContactMessage::factory()->unread()->create();

    $component = Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->call('setReadFilter', 'all');

    expect($component->messages->pluck('id'))
        ->toContain($read->id)
        ->toContain($unread->id);
});

// ─────────────────────────────────────────────────────────────────────────────
// Property 16: Read status toggle is reversible with visual differentiation
// Validates: Requirements 14.4, 14.5
// ─────────────────────────────────────────────────────────────────────────────

it('read toggle is reversible', function () {
    $user = adminUser();
    $message = ContactMessage::factory()->unread()->create();

    $component = Livewire::actingAs($user)->test('admin-message-inbox');

    // mark as read
    $component->call('toggleRead', $message->id);
    expect(ContactMessage::find($message->id)?->is_read)->toBeTrue();

    // mark as unread
    $component->call('toggleRead', $message->id);
    expect(ContactMessage::find($message->id)?->is_read)->toBeFalse();
});

// ─────────────────────────────────────────────────────────────────────────────
// Example tests
// ─────────────────────────────────────────────────────────────────────────────

it('abrir un mensaje lo marca automáticamente como leído', function () {
    $user = adminUser();
    $message = ContactMessage::factory()->unread()->create();

    Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->call('openMessage', $message->id);

    $updated = ContactMessage::find($message->id);
    expect($updated->is_read)->toBeTrue();
    expect($updated->read_at)->not->toBeNull();
});

it('abrir un mensaje ya leído no cambia read_at', function () {
    $user = adminUser();
    $readAt = now()->subHour();
    $message = ContactMessage::factory()->read()->create(['read_at' => $readAt]);

    Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->call('openMessage', $message->id);

    expect(ContactMessage::find($message->id)->read_at->timestamp)
        ->toBe($readAt->timestamp);
});

it('opening the same message twice closes detail', function () {
    $user = adminUser();
    $message = ContactMessage::factory()->unread()->create();

    Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->call('openMessage', $message->id)
        ->assertSet('openMessageId', $message->id)
        ->call('openMessage', $message->id)
        ->assertSet('openMessageId', null);
});

it('delete with confirmation removes message', function () {
    $user = adminUser();
    $message = ContactMessage::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->call('confirmDelete', $message->id)
        ->call('deleteMessage');

    expect(ContactMessage::find($message->id))->toBeNull();
});

it('renders reusable delete row action', function () {
    $user = adminUser();
    ContactMessage::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->assertSeeHtml('data-admin-action="delete"');
});

it('renders reusable filter input and filter toggle group', function () {
    $user = adminUser();
    ContactMessage::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->assertSeeHtml('data-admin-table-header')
        ->assertSeeHtml('data-admin-action-link="confirm"')
        ->assertSeeHtml('data-admin-filter-input')
        ->assertSeeHtml('data-admin-filter-group')
        ->assertSeeHtml('data-admin-filter-button="read"')
        ->assertSeeHtml('data-admin-filter-button="unread"')
        ->assertSeeHtml('data-admin-filter-button="all"');
});

it('confirm delete stores id and deleting clears selection and open detail', function () {
    $user = adminUser();
    $message = ContactMessage::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->call('openMessage', $message->id)
        ->call('confirmDelete', $message->id)
        ->assertSeeHtml('data-admin-form-footer-actions')
        ->assertSet('confirmingDeleteId', $message->id)
        ->call('deleteMessage')
        ->assertSet('confirmingDeleteId', null)
        ->assertSet('openMessageId', null);

    expect(ContactMessage::find($message->id))->toBeNull();
});

it('cancel delete does not remove message', function () {
    $user = adminUser();
    $message = ContactMessage::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->call('confirmDelete', $message->id)
        ->call('cancelDelete');

    expect(ContactMessage::find($message->id))->not->toBeNull();
});

it('ordenar por created_at desc produce orden correcto', function () {
    $user = adminUser();
    $old = ContactMessage::factory()->create(['created_at' => now()->subDays(2)]);
    $new = ContactMessage::factory()->create(['created_at' => now()]);

    $component = Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->call('setReadFilter', 'all');

    // default is created_at desc
    $ids = $component->messages->pluck('id')->toArray();
    expect(array_search($new->id, $ids))->toBeLessThan(array_search($old->id, $ids));
});

it('uses created_at desc as fallback when requested order is invalid', function () {
    $user = adminUser();
    $old = ContactMessage::factory()->create(['created_at' => now()->subDays(2)]);
    $new = ContactMessage::factory()->create(['created_at' => now()]);

    $component = Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->call('setReadFilter', 'all')
        ->set('sortColumn', 'invalid-column')
        ->set('sortDir', 'sideways');

    $ids = $component->messages->pluck('id')->toArray();
    expect(array_search($new->id, $ids))->toBeLessThan(array_search($old->id, $ids));
});

it('unread messages have visual differentiation class in HTML', function () {
    $user = adminUser();
    ContactMessage::factory()->unread()->create(['subject' => 'Unread subject test']);

    Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->call('setReadFilter', 'unread')
        ->assertSeeHtml('bg-[#edd2c7]/20');
});

it('read status action uses modal confirmation flow', function () {
    $user = adminUser();
    $message = ContactMessage::factory()->unread()->create();

    Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->call('setReadFilter', 'all')
        ->call('confirmReadToggle', $message->id, true)
        ->assertSet('showReadModal', true)
        ->assertSet('readAction', 'read')
        ->call('doReadToggle')
        ->assertSet('showReadModal', false)
        ->assertSet('readAction', '');

    expect(ContactMessage::find($message->id)?->is_read)->toBeTrue();
});

it('toggles direction when sorting same column and resets when column changes', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->assertSet('sortColumn', 'created_at')
        ->assertSet('sortDir', 'desc')
        ->call('sortBy', 'created_at')
        ->assertSet('sortDir', 'asc')
        ->call('sortBy', 'created_at')
        ->assertSet('sortDir', 'desc')
        ->call('sortBy', 'is_read')
        ->assertSet('sortColumn', 'is_read')
        ->assertSet('sortDir', 'desc');
});

it('deleteMessage does nothing if there is no active confirmation', function () {
    $user = adminUser();
    $message = ContactMessage::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->assertSet('confirmingDeleteId', null)
        ->call('deleteMessage');

    expect(ContactMessage::find($message->id))->not->toBeNull();
});

it('inbox shows all messages by default', function () {
    $user = adminUser();
    ContactMessage::factory()->read()->create();
    $unreadMessage = ContactMessage::factory()->unread()->create();

    $component = Livewire::actingAs($user)->test('admin-message-inbox');

    expect($component->get('readFilter'))->toBe('all')
        ->and($component->messages->pluck('id')->toArray())->toContain($unreadMessage->id)
        ->and($component->messages->pluck('id')->toArray())->toContain(ContactMessage::query()->where('is_read', true)->firstOrFail()->id);
});

it('allows searching by any textual message field', function () {
    $user = adminUser();
    ContactMessage::factory()->read()->create([
        'name' => 'Ane Iruretagoiena',
        'email' => 'ane-search@example.com',
        'subject' => 'Consulta de trastero',
        'message' => 'Necesito ayuda con la llave del trastero',
    ]);
    ContactMessage::factory()->read()->create([
        'name' => 'Otro nombre',
        'email' => 'otro@example.com',
        'subject' => 'Sin coincidencia',
        'message' => 'Texto sin la palabra buscada',
    ]);

    Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->call('setReadFilter', 'all')
        ->set('search', 'trastero')
        ->assertSee('Ane Iruretagoiena')
        ->assertDontSee('Otro nombre');
});
