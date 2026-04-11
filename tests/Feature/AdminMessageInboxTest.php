<?php

// Feature: community-web, Tarea 11: Panel de administración — Bandeja de mensajes
// Valida: Requisitos 14.1–14.8

use App\Models\User;
use Livewire\Livewire;
use App\Models\ContactMessage;

// ─────────────────────────────────────────────────────────────────────────────
// Propiedad 14: Completitud de la bandeja de mensajes
// Valida: Requisito 14.1
// ─────────────────────────────────────────────────────────────────────────────

it('todos los mensajes aparecen en la bandeja al activar el filtro de todos', function () {
    $user = User::factory()->create();
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
// Propiedad 16: Toggle de estado de lectura es reversible con diferenciación visual
// Valida: Requisitos 14.4, 14.5
// ─────────────────────────────────────────────────────────────────────────────

it('el toggle de lectura es reversible', function () {
    $user = User::factory()->create();
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
// Tests de ejemplo
// ─────────────────────────────────────────────────────────────────────────────

it('abrir un mensaje lo marca automáticamente como leído', function () {
    $user = User::factory()->create();
    $message = ContactMessage::factory()->unread()->create();

    Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->call('openMessage', $message->id);

    $updated = ContactMessage::find($message->id);
    expect($updated->is_read)->toBeTrue();
    expect($updated->read_at)->not->toBeNull();
});

it('abrir un mensaje ya leído no cambia read_at', function () {
    $user = User::factory()->create();
    $readAt = now()->subHour();
    $message = ContactMessage::factory()->read()->create(['read_at' => $readAt]);

    Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->call('openMessage', $message->id);

    expect(ContactMessage::find($message->id)->read_at->timestamp)
        ->toBe($readAt->timestamp);
});

it('abrir el mismo mensaje dos veces cierra el detalle', function () {
    $user = User::factory()->create();
    $message = ContactMessage::factory()->unread()->create();

    Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->call('openMessage', $message->id)
        ->assertSet('openMessageId', $message->id)
        ->call('openMessage', $message->id)
        ->assertSet('openMessageId', null);
});

it('eliminar con confirmación borra el mensaje', function () {
    $user = User::factory()->create();
    $message = ContactMessage::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->call('confirmDelete', $message->id)
        ->call('deleteMessage');

    expect(ContactMessage::find($message->id))->toBeNull();
});

it('confirmar eliminación guarda el id y borrar limpia selección y detalle abierto', function () {
    $user = User::factory()->create();
    $message = ContactMessage::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->call('openMessage', $message->id)
        ->call('confirmDelete', $message->id)
        ->assertSet('confirmingDeleteId', $message->id)
        ->call('deleteMessage')
        ->assertSet('confirmingDeleteId', null)
        ->assertSet('openMessageId', null);

    expect(ContactMessage::find($message->id))->toBeNull();
});

it('cancelar eliminación no borra el mensaje', function () {
    $user = User::factory()->create();
    $message = ContactMessage::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->call('confirmDelete', $message->id)
        ->call('cancelDelete');

    expect(ContactMessage::find($message->id))->not->toBeNull();
});

it('ordenar por created_at desc produce orden correcto', function () {
    $user = User::factory()->create();
    $old = ContactMessage::factory()->create(['created_at' => now()->subDays(2)]);
    $new = ContactMessage::factory()->create(['created_at' => now()]);

    $component = Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->call('setReadFilter', 'all');

    // default is created_at desc
    $ids = $component->messages->pluck('id')->toArray();
    expect(array_search($new->id, $ids))->toBeLessThan(array_search($old->id, $ids));
});

it('usa created_at desc como fallback cuando el orden solicitado no es válido', function () {
    $user = User::factory()->create();
    $old = ContactMessage::factory()->create(['created_at' => now()->subDays(2)]);
    $new = ContactMessage::factory()->create(['created_at' => now()]);

    $component = Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->call('setReadFilter', 'all')
        ->set('sortBy', 'invalid-column')
        ->set('sortDir', 'sideways');

    $ids = $component->messages->pluck('id')->toArray();
    expect(array_search($new->id, $ids))->toBeLessThan(array_search($old->id, $ids));
});

it('los mensajes no leídos tienen clase de diferenciación visual en el HTML', function () {
    $user = User::factory()->create();
    ContactMessage::factory()->unread()->create(['subject' => 'Unread subject test']);

    Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->call('setReadFilter', 'unread')
        ->assertSeeHtml('bg-[#edd2c7]/20');
});

it('alterna dirección al ordenar por la misma columna y reinicia al cambiar de columna', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->assertSet('sortBy', 'created_at')
        ->assertSet('sortDir', 'desc')
        ->call('sortBy', 'created_at')
        ->assertSet('sortDir', 'asc')
        ->call('sortBy', 'created_at')
        ->assertSet('sortDir', 'desc')
        ->call('sortBy', 'is_read')
        ->assertSet('sortBy', 'is_read')
        ->assertSet('sortDir', 'desc');
});

it('deleteMessage no hace nada si no hay confirmación activa', function () {
    $user = User::factory()->create();
    $message = ContactMessage::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-message-inbox')
        ->assertSet('confirmingDeleteId', null)
        ->call('deleteMessage');

    expect(ContactMessage::find($message->id))->not->toBeNull();
});

it('por defecto la bandeja muestra todos los mensajes', function () {
    $user = User::factory()->create();
    ContactMessage::factory()->read()->create();
    $unreadMessage = ContactMessage::factory()->unread()->create();

    $component = Livewire::actingAs($user)->test('admin-message-inbox');

    expect($component->get('readFilter'))->toBe('all')
        ->and($component->messages->pluck('id')->toArray())->toContain($unreadMessage->id)
        ->and($component->messages->pluck('id')->toArray())->toContain(ContactMessage::query()->where('is_read', true)->firstOrFail()->id);
});

it('permite buscar por cualquier campo textual del mensaje', function () {
    $user = User::factory()->create();
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
