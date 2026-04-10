<?php

// Feature: community-web, Tarea 9: Panel de administración — Gestión de avisos
// Valida: Requisitos 6.1, 6.3, 6.4

use App\Models\User;
use App\Models\Notice;
use Livewire\Livewire;
use App\Models\Location;
use App\Models\Property;

// ─────────────────────────────────────────────────────────────────────────────
// Propiedad 8: Toggle de publicación de avisos es reversible
// Valida: Requisito 6.4
// ─────────────────────────────────────────────────────────────────────────────

it('el toggle de publicación es reversible y nunca elimina el aviso', function () {
    $user = User::factory()->create();
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
// Tests de ejemplo — CRUD de avisos
// Valida: Requisitos 6.1, 6.4
// ─────────────────────────────────────────────────────────────────────────────

it('crear aviso aparece en la lista admin', function () {
    $user = User::factory()->create();

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

it('publicar aviso lo hace visible en la parte pública', function () {
    $user = User::factory()->create();
    $notice = Notice::factory()->private()->create();

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->call('publishNotice', $notice->id);

    expect(Notice::find($notice->id)?->is_public)->toBeTrue();

    $publicComponent = Livewire::test('public-notices');
    expect($publicComponent->notices->pluck('id'))->toContain($notice->id);
});

it('despublicar aviso lo oculta de la parte pública', function () {
    $user = User::factory()->create();
    $notice = Notice::factory()->public()->create();

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->call('unpublishNotice', $notice->id);

    expect(Notice::find($notice->id)?->is_public)->toBeFalse();

    $publicComponent = Livewire::test('public-notices');
    expect($publicComponent->notices->pluck('id'))->not->toContain($notice->id);
});

it('eliminar aviso lo quita de la lista admin', function () {
    $user = User::factory()->create();
    $notice = Notice::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->call('confirmDelete', $notice->id)
        ->call('deleteNotice');

    expect(Notice::find($notice->id))->toBeNull();
});

it('abre y cierra la confirmación de borrado', function () {
    $user = User::factory()->create();
    $notice = Notice::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->call('confirmDelete', $notice->id)
        ->assertSet('confirmingDeleteId', $notice->id)
        ->call('cancelDelete')
        ->assertSet('confirmingDeleteId', null);
});

it('abre la confirmación de publicación con la acción correcta', function () {
    $user = User::factory()->create();
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

it('editar aviso dispara evento de foco al formulario', function () {
    $user = User::factory()->create();
    $notice = Notice::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->call('editNotice', $notice->id)
        ->assertDispatched('admin-notice-form-focus');
});

it('la asociación de ubicaciones persiste correctamente', function () {
    $user = User::factory()->create();
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

it('createNotice muestra el formulario y cancelForm restablece el estado', function () {
    $user = User::factory()->create();

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

it('renderiza los campos multiidioma de avisos con pestañas por idioma', function () {
    $user = User::factory()->create();

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

it('muestra solo locations en el selector de ubicaciones del formulario', function () {
    $user = User::factory()->create();

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

it('edita un aviso existente y reemplaza sus ubicaciones al guardar', function () {
    $user = User::factory()->create();
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

it('crea slug UUID cuando el título no produce slug válido', function () {
    $user = User::factory()->create();

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

it('al editar y mantener privado conserva published_at existente', function () {
    $user = User::factory()->create();
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
