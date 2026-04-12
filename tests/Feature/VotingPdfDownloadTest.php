<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Owner;
use App\SupportedLocales;

dataset('supported_locales', SupportedLocales::all());

it('allows admin users to download delegated and in-person voting pdfs from admin routes', function () {
  $user = adminUser();

  test()->actingAs($user)
    ->get(route('admin.votings.pdf.delegated'))
    ->assertOk()
    ->assertHeader('content-type', 'application/pdf');

  test()->actingAs($user)
    ->get(route('admin.votings.pdf.in_person'))
    ->assertOk()
    ->assertHeader('content-type', 'application/pdf');
});

it('allows property owners to download delegated and in-person pdfs from localized front routes', function (string $locale) {
  $user = User::factory()->create([
    'language' => $locale,
  ]);
  Role::query()->firstOrCreate(['name' => Role::PROPERTY_OWNER]);
  $user->assignRole(Role::PROPERTY_OWNER);
  Owner::factory()->create(['user_id' => $user->id]);

  $expectedDelegatedFilename = $locale === SupportedLocales::SPANISH
    ? 'voto-delegado'
    : 'boto-delegatua';
  $expectedInPersonFilename = $locale === SupportedLocales::SPANISH
    ? 'voto-presencial'
    : 'boto-presentziala';

  $delegatedResponse = test()->actingAs($user)
    ->get(route(SupportedLocales::routeName('votings.pdf.delegated', $locale)))
    ->assertOk()
    ->assertHeader('content-type', 'application/pdf');

  expect((string) $delegatedResponse->headers->get('content-disposition'))
    ->toContain($expectedDelegatedFilename);

  $inPersonResponse = test()->actingAs($user)
    ->get(route(SupportedLocales::routeName('votings.pdf.in_person', $locale)))
    ->assertOk()
    ->assertHeader('content-type', 'application/pdf');

  expect((string) $inPersonResponse->headers->get('content-disposition'))
    ->toContain($expectedInPersonFilename);
})->with('supported_locales');
