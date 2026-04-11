<?php

// Feature: community-web, Task 9: Admin panel — Notice management
// Validates: Requirements 6.1, 6.3, 6.4

use App\Models\Notice;
use Livewire\Livewire;
use App\Models\Location;
use App\Models\Property;

// ─────────────────────────────────────────────────────────────────────────────
// Property 8: Notice publish toggle is reversible
// Validates: Requirement 6.4
// ─────────────────────────────────────────────────────────────────────────────

it('publish toggle is reversible and never deletes notice', function () {
    $user = adminUser();
    $notice = Notice::factory()->private()->create();

    $component = Livewire::actingAs($user)->test('admin-notice-manager');

    // publish
    $component->call('publishNotice', $notice->id);
    expect(Notice::find($notice->id)?->is_public)->toBeTrue();

    // unpublish
    $component->call('unpublishNotice', $notice->id);
    expect(Notice::find($notice->id)?->is_public)->toBeFalse();

    // publish again
    $component->call('publishNotice', $notice->id);
    expect(Notice::find($notice->id)?->is_public)->toBeTrue();

    // notice still exists
    expect(Notice::find($notice->id))->not->toBeNull();
});

// ─────────────────────────────────────────────────────────────────────────────
// Example tests — Notice CRUD
// Validates: Requirements 6.1, 6.4
// ─────────────────────────────────────────────────────────────────────────────

it('creating notice appears in admin list', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->set('titleEu', 'Iragarki berria')
        ->set('titleEs', 'Nuevo aviso')
        ->set('contentEu', 'Edukia')
        ->set('contentEs', 'Contenido')
        ->call('saveNotice')
        ->assertSee(__('general.messages.saved'));

    expect(Notice::where('title_eu', 'Iragarki berria')->exists())->toBeTrue();
});

it('publishing notice makes it visible in public area', function () {
    $user = adminUser();
    $notice = Notice::factory()->private()->create();

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->call('publishNotice', $notice->id);

    expect(Notice::find($notice->id)?->is_public)->toBeTrue();

    $publicComponent = Livewire::test('public-notices');
    expect($publicComponent->notices->pluck('id'))->toContain($notice->id);
});

it('unpublishing notice hides it from public area', function () {
    $user = adminUser();
    $notice = Notice::factory()->public()->create();

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->call('unpublishNotice', $notice->id);

    expect(Notice::find($notice->id)?->is_public)->toBeFalse();

    $publicComponent = Livewire::test('public-notices');
    expect($publicComponent->notices->pluck('id'))->not->toContain($notice->id);
});

it('deleting notice removes it from admin list', function () {
    $user = adminUser();
    $notice = Notice::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->call('confirmDelete', $notice->id)
        ->call('deleteNotice');

    expect(Notice::find($notice->id))->toBeNull();
});

it('opens and closes delete confirmation', function () {
    $user = adminUser();
    $notice = Notice::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->call('confirmDelete', $notice->id)
        ->assertSet('confirmingDeleteId', $notice->id)
        ->call('cancelDelete')
        ->assertSet('confirmingDeleteId', null);
});

it('opens publish confirmation with correct action', function () {
    $user = adminUser();
    $notice = Notice::factory()->private()->create();

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->call('confirmPublish', $notice->id, true)
        ->assertSet('confirmingPublishId', $notice->id)
        ->assertSet('publishAction', 'publish')
        ->call('cancelPublish')
        ->assertSet('confirmingPublishId', null)
        ->assertSet('publishAction', '');
});

it('editing notice dispatches focus event to form', function () {
    $user = adminUser();
    $notice = Notice::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->call('editNotice', $notice->id)
        ->assertDispatched('admin-notice-form-focus');
});

it('location association persists correctly', function () {
    $user = adminUser();
    Location::factory()->create(['type' => 'portal', 'code' => '33-A', 'name' => 'Portal 33-A']);
    Location::factory()->create(['type' => 'garage', 'code' => 'P-1', 'name' => 'Garaje P-1']);

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->set('titleEu', 'Iragarki kokapenarekin')
        ->set('titleEs', 'Aviso con ubicación')
        ->set('contentEu', 'Edukia')
        ->set('contentEs', 'Contenido')
        ->set('selectedLocations', ['33-A', 'P-1'])
        ->call('saveNotice');

    $notice = Notice::where('title_eu', 'Iragarki kokapenarekin')->firstOrFail();

    expect($notice->locations->pluck('location_code')->sort()->values()->toArray())
        ->toBe(['33-A', 'P-1']);
});

it('createNotice shows form and cancelForm resets state', function () {
    $user = adminUser();

    $component = Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->set('titleEu', 'Temporal')
        ->set('titleEs', 'Temporal ES')
        ->set('contentEu', 'Temporal edukia')
        ->set('contentEs', 'Temporal contenido')
        ->set('isPublic', true)
        ->set('selectedLocations', ['33-A'])
        ->call('createNotice')
        ->assertSet('showForm', true)
        ->assertSet('titleEu', '')
        ->assertSet('titleEs', '')
        ->assertSet('contentEu', '')
        ->assertSet('contentEs', '')
        ->assertSet('isPublic', false)
        ->assertSet('selectedLocations', []);

    $component
        ->set('titleEu', 'Rellenado')
        ->set('selectedLocations', ['P-1'])
        ->call('cancelForm')
        ->assertSet('showForm', false)
        ->assertSet('titleEu', '')
        ->assertSet('selectedLocations', []);
});

it('renders multilingual notice fields with language tabs', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->call('createNotice')
        ->assertSeeHtml('data-bilingual-field="titleEu"')
        ->assertSeeHtml('data-bilingual-tab="eu"')
        ->assertSeeHtml('data-bilingual-tab="es"')
        ->assertSeeHtml('id="titleEu"')
        ->assertSeeHtml('id="titleEs"')
        ->assertSeeHtml('data-bilingual-field="contentEu"')
        ->assertSeeHtml('id="contentEu"')
        ->assertSeeHtml('id="contentEs"');
});

it('shows only locations in form location selector', function () {
    $user = adminUser();

    $location = Location::factory()->create([
        'type' => 'portal',
        'code' => '33-A',
        'name' => 'Portal 33-A',
    ]);

    Property::factory()->create([
        'location_id' => $location->id,
        'name' => '1A',
    ]);

    Location::factory()->create([
        'type' => 'storage',
        'code' => 'TR-99',
        'name' => 'Trastero TR-99',
    ]);

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->call('createNotice')
        ->assertSee('33-A')
        ->assertDontSee('1A')
        ->assertDontSee('TR-99');
});

it('edits an existing notice and replaces locations on save', function () {
    $user = adminUser();
    Location::factory()->create(['type' => 'portal', 'code' => '33-A', 'name' => 'Portal 33-A']);
    Location::factory()->create(['type' => 'garage', 'code' => 'P-1', 'name' => 'Garaje P-1']);

    $notice = Notice::factory()->public()->create([
        'title_eu' => 'Original EU',
        'title_es' => 'Original ES',
        'content_eu' => 'Original edukia',
        'content_es' => 'Original contenido',
        'published_at' => now()->subDay(),
    ]);

    attachNoticeToLocationCode($notice, '33-A');

    $originalPublishedAt = $notice->published_at;

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->call('editNotice', $notice->id)
        ->assertSet('editingId', $notice->id)
        ->assertSet('showForm', true)
        ->assertSet('titleEu', 'Original EU')
        ->assertSet('selectedLocations', ['33-A'])
        ->set('titleEu', 'Editado EU')
        ->set('titleEs', '')
        ->set('contentEu', 'Edukia eguneratua')
        ->set('contentEs', '')
        ->set('isPublic', true)
        ->set('selectedLocations', ['P-1'])
        ->call('saveNotice')
        ->assertSet('showForm', false)
        ->assertSet('editingId', null);

    $notice->refresh();

    expect($notice->title_eu)->toBe('Editado EU')
        ->and($notice->title_es)->toBeNull()
        ->and($notice->content_es)->toBeNull()
        ->and($notice->published_at?->toDateTimeString())->toBe($originalPublishedAt?->toDateTimeString())
        ->and($notice->locations->pluck('location_code')->values()->toArray())->toBe(['P-1']);
});

it('creates UUID slug when title does not produce valid slug', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->set('titleEu', '###')
        ->set('contentEu', 'Edukia')
        ->set('isPublic', false)
        ->call('saveNotice');

    $notice = Notice::latest('id')->firstOrFail();

    expect($notice->slug)->toMatch('/^[0-9a-fA-F-]{36}$/')
        ->and($notice->published_at)->toBeNull();
});

it('when editing and keeping private, it preserves existing published_at', function () {
    $user = adminUser();
    $notice = Notice::factory()->public()->create([
        'published_at' => now()->subHour(),
    ]);

    $originalPublishedAt = $notice->published_at?->toDateTimeString();

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->call('editNotice', $notice->id)
        ->set('titleEu', 'Editatua')
        ->set('contentEu', 'Edukia berria')
        ->set('isPublic', false)
        ->call('saveNotice');

    $notice->refresh();

    expect($notice->published_at?->toDateTimeString())->toBe($originalPublishedAt)
        ->and($notice->is_public)->toBeFalse();
});
