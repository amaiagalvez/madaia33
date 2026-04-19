<?php

use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;
use App\Models\Construction;
use App\Models\ConstructionInquiry;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConstructionInquiryReplyMail;

beforeEach(function (): void {
  Role::query()->firstOrCreate(['name' => Role::SUPER_ADMIN]);
  Role::query()->firstOrCreate(['name' => Role::GENERAL_ADMIN]);
  Role::query()->firstOrCreate(['name' => Role::CONSTRUCTION_MANAGER]);
});

it('construction manager sees only inquiries for assigned constructions', function () {
  $manager = User::factory()->create();
  $manager->assignRole(Role::CONSTRUCTION_MANAGER);

  $assignedConstruction = Construction::factory()->create();
  $assignedConstruction->managers()->sync([$manager->id]);
  $foreignConstruction = Construction::factory()->create();

  $visibleInquiry = ConstructionInquiry::factory()->create(['construction_id' => $assignedConstruction->id]);
  $hiddenInquiry = ConstructionInquiry::factory()->create(['construction_id' => $foreignConstruction->id]);

  $component = Livewire::actingAs($manager)
    ->test('admin-construction-inquiry-inbox');

  expect($component->inquiries->pluck('id'))
    ->toContain($visibleInquiry->id)
    ->not->toContain($hiddenInquiry->id);
});

it('opening an unread construction inquiry marks it as read', function () {
  $admin = adminUser();
  $construction = Construction::factory()->create();
  $inquiry = ConstructionInquiry::factory()->create([
    'construction_id' => $construction->id,
    'is_read' => false,
    'read_at' => null,
  ]);

  Livewire::actingAs($admin)
    ->test('admin-construction-inquiry-inbox')
    ->call('openInquiry', $inquiry->id);

  expect($inquiry->fresh()->is_read)->toBeTrue()
    ->and($inquiry->fresh()->read_at)->not->toBeNull();
});

it('sending a reply persists it and dispatches the mail', function () {
  Mail::fake();

  $admin = adminUser();
  $construction = Construction::factory()->create();
  $inquiry = ConstructionInquiry::factory()->create([
    'construction_id' => $construction->id,
    'reply' => null,
    'replied_at' => null,
  ]);

  Livewire::actingAs($admin)
    ->test('admin-construction-inquiry-inbox')
    ->call('openReplyModal', $inquiry->id)
    ->set('replyBody', 'Erantzun zehatza bidaliko dugu gaur arratsaldean.')
    ->call('sendReply')
    ->assertHasNoErrors();

  expect($inquiry->fresh()->reply)->toBe('Erantzun zehatza bidaliko dugu gaur arratsaldean.')
    ->and($inquiry->fresh()->replied_at)->not->toBeNull();

  Mail::assertSent(ConstructionInquiryReplyMail::class);
});

it('replying again overwrites the reply and refreshes replied_at', function () {
  Mail::fake();

  $admin = adminUser();
  $construction = Construction::factory()->create();
  $inquiry = ConstructionInquiry::factory()->create([
    'construction_id' => $construction->id,
    'reply' => 'Lehen erantzuna',
    'replied_at' => now()->subDay(),
  ]);
  $previousReplyAt = $inquiry->replied_at;

  Livewire::actingAs($admin)
    ->test('admin-construction-inquiry-inbox')
    ->call('openReplyModal', $inquiry->id)
    ->set('replyBody', 'Erantzun berria gaurko eguneraketarekin.')
    ->call('sendReply')
    ->assertHasNoErrors();

  $refreshedInquiry = $inquiry->fresh();

  expect($refreshedInquiry->reply)->toBe('Erantzun berria gaurko eguneraketarekin.')
    ->and($refreshedInquiry->replied_at)->not->toBeNull()
    ->and($refreshedInquiry->replied_at->gt($previousReplyAt))->toBeTrue();

  Mail::assertSent(ConstructionInquiryReplyMail::class);
});

it('admin construction inquiries route allows construction manager role', function () {
  $manager = User::factory()->create();
  $manager->assignRole(Role::CONSTRUCTION_MANAGER);

  test()->actingAs($manager)
    ->get(route('admin.construction-inquiries'))
    ->assertOk();
});
