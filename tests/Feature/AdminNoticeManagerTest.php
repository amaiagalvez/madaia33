<?php

// Feature: community-web, Task 9: Admin panel — Notice management
// Validates: Requirements 6.1, 6.3, 6.4

use App\Models\Role;
use App\Models\User;
use App\Models\Notice;
use Livewire\Livewire;
use App\Models\Location;
use App\Models\Property;
use App\Models\NoticeTag;
use App\Models\Construction;
use App\Models\NoticeDocument;
use Illuminate\Http\UploadedFile;
use App\Models\NoticeDocumentDownload;
use Illuminate\Support\Facades\Storage;

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

it('stores explicit published_at when creating a public notice', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->set('titleEu', 'Iragarki datarekin')
        ->set('titleEs', 'Aviso con fecha')
        ->set('contentEu', 'Edukia')
        ->set('contentEs', 'Contenido')
        ->set('isPublic', true)
        ->set('publishedAt', '2026-04-10')
        ->call('saveNotice')
        ->assertSee(__('general.messages.saved'));

    $notice = Notice::where('title_eu', 'Iragarki datarekin')->firstOrFail();

    expect($notice->published_at?->toDateString())->toBe('2026-04-10')
        ->and($notice->is_public)->toBeTrue();
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

it('renders reusable edit and delete row actions', function () {
    $user = adminUser();
    Notice::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->assertSeeHtml('data-admin-table-header')
        ->assertSeeHtml('data-admin-action-link="confirm"')
        ->assertSeeHtml('data-admin-action="edit"')
        ->assertSeeHtml('data-admin-action="delete"');
});

it('renders reusable side panel and form footer when form is open', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->call('createNotice')
        ->assertSeeHtml('data-admin-side-panel-form')
        ->assertSeeHtml('data-admin-form-footer-actions');
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
        ->assertSeeHtml('data-admin-field="boolean-toggle"')
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
    $propertyName = 'Property Name Not In Location Selector';

    $location = Location::factory()->create([
        'type' => 'portal',
        'code' => '33-A',
        'name' => 'Portal 33-A',
    ]);

    Property::factory()->create([
        'location_id' => $location->id,
        'name' => $propertyName,
    ]);

    Location::factory()->create([
        'type' => 'storage',
        'code' => 'TR-99',
        'name' => 'Trastero TR-99',
    ]);

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->call('createNotice')
        ->assertSeeHtml('data-admin-field="multi-checkbox-pills"')
        ->assertSee('33-A')
        ->assertDontSee($propertyName)
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

it('updates explicit published_at when editing a public notice', function () {
    $user = adminUser();

    $notice = Notice::factory()->public()->create([
        'title_eu' => 'Data editatzeko',
        'published_at' => now()->subDay(),
    ]);

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->call('editNotice', $notice->id)
        ->set('titleEu', 'Data editatzeko')
        ->set('contentEu', $notice->content_eu)
        ->set('isPublic', true)
        ->set('publishedAt', '2026-04-12')
        ->call('saveNotice');

    expect($notice->fresh()->published_at?->toDateString())->toBe('2026-04-12');
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

it('toggles sort direction for published columns and resets on column change', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->assertSet('sortColumn', 'published_at')
        ->assertSet('sortDir', 'desc')
        ->call('sortBy', 'is_public')
        ->assertSet('sortColumn', 'is_public')
        ->assertSet('sortDir', 'desc')
        ->call('sortBy', 'is_public')
        ->assertSet('sortDir', 'asc')
        ->call('sortBy', 'published_at')
        ->assertSet('sortColumn', 'published_at')
        ->assertSet('sortDir', 'desc');
});

it('uses published_at desc fallback for invalid notice sorting state', function () {
    $user = adminUser();
    $old = Notice::factory()->create([
        'title_eu' => 'Old Notice',
        'published_at' => now()->subDays(2),
    ]);
    $new = Notice::factory()->create([
        'title_eu' => 'New Notice',
        'published_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->set('sortColumn', 'invalid-column')
        ->set('sortDir', 'sideways')
        ->assertSeeInOrder([$new->title_eu, $old->title_eu]);
});

it('shows only global notices to general admin users', function () {
    Role::query()->firstOrCreate(['name' => Role::GENERAL_ADMIN]);

    $generalAdmin = User::factory()->create();
    $generalAdmin->assignRole(Role::GENERAL_ADMIN);

    Notice::factory()->create([
        'title_eu' => 'Iragarki Orokorra',
        'title_es' => 'Aviso Global',
    ]);

    $locatedNotice = Notice::factory()->create([
        'title_eu' => 'Iragarki Kokapenduna',
        'title_es' => 'Aviso con ubicacion',
    ]);

    attachNoticeToLocationCode($locatedNotice, '33-A');

    Livewire::actingAs($generalAdmin)
        ->test('admin-notice-manager')
        ->assertSee('Iragarki Orokorra')
        ->assertDontSee('Iragarki Kokapenduna');
});

it('shows only managed-location notices to community admin users', function () {
    Role::query()->firstOrCreate(['name' => Role::COMMUNITY_ADMIN]);

    $communityAdmin = User::factory()->create();
    $communityAdmin->assignRole(Role::COMMUNITY_ADMIN);

    $managedLocation = Location::factory()->portal()->create(['code' => 'CA-1']);
    $otherLocation = Location::factory()->portal()->create(['code' => 'CA-2']);
    $communityAdmin->managedLocations()->sync([$managedLocation->id]);

    $managedNotice = Notice::factory()->create([
        'title_eu' => 'Iragarki Kudeatua',
    ]);
    $managedNotice->locations()->create(['location_id' => $managedLocation->id]);

    $otherNotice = Notice::factory()->create([
        'title_eu' => 'Iragarki Kanpokoa',
    ]);
    $otherNotice->locations()->create(['location_id' => $otherLocation->id]);

    $globalNotice = Notice::factory()->create([
        'title_eu' => 'Iragarki Orokor CA',
    ]);

    Livewire::actingAs($communityAdmin)
        ->test('admin-notice-manager')
        ->assertSee('Iragarki Kudeatua')
        ->assertDontSee('Iragarki Kanpokoa')
        ->assertDontSee('Iragarki Orokor CA');
});

it('shows only assigned construction tags to construction manager', function () {
    foreach (Role::names() as $roleName) {
        Role::query()->firstOrCreate(['name' => $roleName]);
    }

    $manager = User::factory()->create();
    $manager->assignRole(Role::CONSTRUCTION_MANAGER);

    $assignedConstruction = Construction::factory()->create([
        'title' => 'Obra A',
        'slug' => 'obra-a',
    ]);
    $otherConstruction = Construction::factory()->create([
        'title' => 'Obra B',
        'slug' => 'obra-b',
    ]);

    $manager->constructions()->sync([$assignedConstruction->id]);

    $assignedTag = NoticeTag::query()->where('slug', $assignedConstruction->slug)->firstOrFail();
    $otherTag = NoticeTag::query()->where('slug', $otherConstruction->slug)->firstOrFail();

    Livewire::actingAs($manager)
        ->test('admin-notice-manager')
        ->call('createNotice')
        ->assertSee($assignedTag->name)
        ->assertDontSee($otherTag->name);
});

it('forbids construction manager from assigning unowned construction tag', function () {
    foreach (Role::names() as $roleName) {
        Role::query()->firstOrCreate(['name' => $roleName]);
    }

    $manager = User::factory()->create();
    $manager->assignRole(Role::CONSTRUCTION_MANAGER);

    $assignedConstruction = Construction::factory()->create([
        'slug' => 'baimendua',
    ]);
    $otherConstruction = Construction::factory()->create([
        'slug' => 'debekatua',
    ]);

    $manager->constructions()->sync([$assignedConstruction->id]);

    $otherTag = NoticeTag::query()->where('slug', $otherConstruction->slug)->firstOrFail();

    Livewire::actingAs($manager)
        ->test('admin-notice-manager')
        ->set('titleEu', 'Manager test')
        ->set('contentEu', 'Edukia')
        ->set('selectedTagId', $otherTag->id)
        ->call('saveNotice')
        ->assertForbidden();
});

it('validates unsupported document mime type on notice save', function () {
    Storage::fake('public');

    $user = adminUser();

    $invalidAttachment = UploadedFile::fake()->create('script.exe', 20, 'application/octet-stream');

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->set('titleEu', 'Iragarki MIME')
        ->set('contentEu', 'Edukia')
        ->set('attachments', [$invalidAttachment])
        ->call('saveNotice')
        ->assertHasErrors(['attachments.0' => 'mimes']);
});

it('auto uploads documents when editing and can remove pending attachments in create mode', function () {
    Storage::fake('public');

    $user = adminUser();
    $notice = Notice::factory()->create();

    $editAttachment = UploadedFile::fake()->create('oharra.pdf', 20, 'application/pdf');

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->call('editNotice', $notice->id)
        ->set('attachments', [$editAttachment])
        ->assertSet('attachments', [])
        ->assertSee('oharra.pdf');

    expect(NoticeDocument::query()->where('notice_id', $notice->id)->count())->toBe(1);

    $createAttachment = UploadedFile::fake()->create('zain.pdf', 20, 'application/pdf');

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->call('createNotice')
        ->set('attachments', [$createAttachment])
        ->assertSee('zain.pdf')
        ->call('removePendingAttachment', 0)
        ->assertSet('attachments', []);
});

it('shows notice downloads count in admin list', function () {
    $user = adminUser();

    $notice = Notice::factory()->create([
        'title_eu' => 'Iragarki deskargak',
    ]);

    $document = NoticeDocument::factory()->create([
        'notice_id' => $notice->id,
    ]);

    NoticeDocumentDownload::query()->create([
        'notice_document_id' => $document->id,
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'downloaded_at' => now(),
    ]);

    NoticeDocumentDownload::query()->create([
        'notice_document_id' => $document->id,
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'downloaded_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->assertSeeHtml('data-notice-download-count="' . $notice->id . '"')
        ->assertSee('2');
});

it('filters notices by untagged option in admin list', function () {
    $user = adminUser();

    $tag = NoticeTag::factory()->create();

    $untagged = Notice::factory()->create([
        'title_eu' => 'Iragarki etiketarik gabe',
        'notice_tag_id' => null,
    ]);

    $tagged = Notice::factory()->create([
        'title_eu' => 'Iragarki etiketaduna',
        'notice_tag_id' => $tag->id,
    ]);

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->call('setTagFilter', 'untagged')
        ->assertSee($untagged->title_eu)
        ->assertDontSee($tagged->title_eu);
});

it('filters notices by selected tag in admin list', function () {
    $user = adminUser();

    $tagA = NoticeTag::factory()->create([
        'name_eu' => 'A Etiketa',
    ]);
    $tagB = NoticeTag::factory()->create([
        'name_eu' => 'B Etiketa',
    ]);

    $noticeA = Notice::factory()->create([
        'title_eu' => 'A etiketako iragarkia',
        'notice_tag_id' => $tagA->id,
    ]);

    $noticeB = Notice::factory()->create([
        'title_eu' => 'B etiketako iragarkia',
        'notice_tag_id' => $tagB->id,
    ]);

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->call('setTagFilter', (string) $tagA->id)
        ->assertSee($noticeA->title_eu)
        ->assertDontSee($noticeB->title_eu);
});

it('searches notices by title and content in admin list', function () {
    $user = adminUser();

    $titleMatch = Notice::factory()->create([
        'title_eu' => 'Bilaketa titulua parekatua',
        'content_eu' => 'Edukia arrunta',
    ]);

    $contentMatch = Notice::factory()->create([
        'title_eu' => 'Beste izenburu bat',
        'content_eu' => 'Hemen dago gako-hitza: terminoa',
    ]);

    $nonMatch = Notice::factory()->create([
        'title_eu' => 'Ez dator bat',
        'content_eu' => 'Bestelako testua',
    ]);

    Livewire::actingAs($user)
        ->test('admin-notice-manager')
        ->set('search', 'terminoa')
        ->assertSee($contentMatch->title_eu)
        ->assertDontSee($nonMatch->title_eu)
        ->set('search', 'parekatua')
        ->assertSee($titleMatch->title_eu)
        ->assertDontSee($nonMatch->title_eu);
});
