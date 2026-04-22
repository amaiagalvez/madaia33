<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Owner;
use App\Models\Voting;
use App\SupportedLocales;
use App\Models\VotingOption;
use App\Models\VotingOptionTotal;
use App\Services\VotingPdfBuilder;

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

    $delegatedSequentialResponse = test()->actingAs($user)
        ->get(route('admin.votings.pdf.delegated_sequential'))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    expect((string) $delegatedSequentialResponse->headers->get('content-disposition'))
        ->toMatch('/(delegad|delegatu).*1/i');

    $inPersonSequentialResponse = test()->actingAs($user)
        ->get(route('admin.votings.pdf.in_person_sequential'))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    expect((string) $inPersonSequentialResponse->headers->get('content-disposition'))
        ->toMatch('/(presencial|presentziala).*1/i');

    test()->actingAs($user)
        ->get(route('admin.votings.pdf.results', ['voting_ids' => []]))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('builds results payload with votes_count and pct_total totals', function () {
    $voting = Voting::factory()->create([
        'name_eu' => 'Emaitzen Bozketa',
        'question_eu' => 'Galdera?',
    ]);

    $firstOption = VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
    ]);

    $secondOption = VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 2,
    ]);

    VotingOptionTotal::factory()->create([
        'voting_id' => $voting->id,
        'voting_option_id' => $firstOption->id,
        'votes_count' => 4,
        'pct_total' => 12.5000,
    ]);

    VotingOptionTotal::factory()->create([
        'voting_id' => $voting->id,
        'voting_option_id' => $secondOption->id,
        'votes_count' => 2,
        'pct_total' => 7.2500,
    ]);

    $payload = app(VotingPdfBuilder::class)->buildResults([$voting->id]);

    expect($payload['votings'])->toHaveCount(1)
        ->and($payload['votings'][0]['total_votes_count'])->toBe(6)
        ->and((float) $payload['votings'][0]['total_pct_sum'])->toBe(19.75)
        ->and($payload['votings'][0]['options'][0]['votes_count'])->toBe(4)
        ->and((float) $payload['votings'][0]['options'][0]['pct_total'])->toBe(12.5)
        ->and($payload['votings'][0]['options'][1]['votes_count'])->toBe(2)
        ->and((float) $payload['votings'][0]['options'][1]['pct_total'])->toBe(7.25);
});

it('orders delegated and in-person pdf votings by start date and name', function () {
    $latestVoting = Voting::factory()->create([
        'name_eu' => 'Beta bozketa',
        'starts_at' => '2026-06-01',
        'ends_at' => '2026-06-10',
    ]);

    $sameDateSecondByName = Voting::factory()->create([
        'name_eu' => 'Zeta bozketa',
        'starts_at' => '2026-05-01',
        'ends_at' => '2026-05-10',
    ]);

    $sameDateFirstByName = Voting::factory()->create([
        'name_eu' => 'Alfa bozketa',
        'starts_at' => '2026-05-01',
        'ends_at' => '2026-05-10',
    ]);

    $selectedIds = [
        $latestVoting->id,
        $sameDateSecondByName->id,
        $sameDateFirstByName->id,
    ];

    $builder = app(VotingPdfBuilder::class);

    $delegatedPayload = $builder->build('delegated', $selectedIds);
    $inPersonPayload = $builder->build('in_person', $selectedIds);

    expect(array_column($delegatedPayload['votings'], 'name_eu'))->toBe([
        'Alfa bozketa',
        'Zeta bozketa',
        'Beta bozketa',
    ])->and(array_column($inPersonPayload['votings'], 'name_eu'))->toBe([
        'Alfa bozketa',
        'Zeta bozketa',
        'Beta bozketa',
    ]);
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
