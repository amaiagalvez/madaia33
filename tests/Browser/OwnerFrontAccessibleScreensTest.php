<?php

use App\Models\Owner;
use App\Models\Voting;
use Tests\DuskTestCase;
use App\Models\Location;
use App\Models\Property;
use Laravel\Dusk\Browser;
use App\Models\VotingBallot;
use App\Models\VotingOption;
use App\Models\VotingSelection;
use App\Models\VotingOptionTotal;
use App\Models\PropertyAssignment;

it('covers all owner-accessible front screens including votings results and profile tabs', function () {
    $owner = Owner::factory()->create([
        'accepted_terms_at' => now(),
    ]);

    $portal = Location::factory()->portal()->create([
        'code' => '99-Z',
    ]);

    $property = Property::factory()->create([
        'location_id' => $portal->id,
        'community_pct' => 1.25,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    $activeVoting = Voting::factory()->current()->create([
        'is_published' => true,
    ]);

    VotingOption::factory()->create([
        'voting_id' => $activeVoting->id,
        'position' => 1,
        'label_eu' => 'Bai',
        'label_es' => 'Si',
    ]);

    $activeVoting->locations()->create([
        'location_id' => $portal->id,
    ]);

    $resultsVoting = Voting::factory()->create([
        'starts_at' => now()->subDays(10),
        'ends_at' => now()->subDay(),
        'is_published' => true,
        'show_results' => true,
        'name_eu' => 'Emaitzak owner smoke test',
        'name_es' => 'Resultados owner smoke test',
    ]);

    $resultsOption = VotingOption::factory()->create([
        'voting_id' => $resultsVoting->id,
        'position' => 1,
        'label_eu' => 'Aukera 1',
        'label_es' => 'Opcion 1',
    ]);

    $resultsVoting->locations()->create([
        'location_id' => $portal->id,
    ]);

    $ballot = VotingBallot::factory()->create([
        'voting_id' => $resultsVoting->id,
        'owner_id' => $owner->id,
        'cast_by_user_id' => $owner->user_id,
        'is_in_person' => false,
    ]);

    VotingSelection::factory()->create([
        'voting_id' => $resultsVoting->id,
        'voting_ballot_id' => $ballot->id,
        'owner_id' => $owner->id,
        'voting_option_id' => $resultsOption->id,
        'pct_total' => 1.25,
    ]);

    VotingOptionTotal::factory()->create([
        'voting_id' => $resultsVoting->id,
        'voting_option_id' => $resultsOption->id,
        'votes_count' => 1,
        'pct_total' => 1.25,
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($owner, $resultsVoting): void {
        $locales = [
            'eu' => [
                'votings' => 'bozketak',
                'results' => 'emaitzak',
                'profile' => 'profila',
            ],
            'es' => [
                'votings' => 'votaciones',
                'results' => 'resultados',
                'profile' => 'perfil',
            ],
        ];

        $profileTabs = ['overview', 'votings', 'sessions', 'received', 'messages', 'owner'];

        $browser->loginAs($owner->user);

        foreach ($locales as $locale => $paths) {
            $browser->visit('/' . $locale)
                ->dismissCookieConsentBanner()
                ->assertPresent('[data-home-votings-callout]')
                ->assertPresent('[data-home-profile-callout]');

            $browser->visit('/' . $locale . '/' . $paths['votings'])
                ->waitFor('[data-page="votings"]', 10)
                ->assertPresent('[data-votings-content]');

            $browser->visit('/' . $locale . '/' . $paths['votings'] . '/' . $resultsVoting->id . '/' . $paths['results'])
                ->waitFor('[data-page="voting-results"]', 10)
                ->assertPresent('[data-voting-results-title]')
                ->assertPresent('[data-results-charts]');

            foreach ($profileTabs as $tab) {
                $browser->visit('/' . $locale . '/' . $paths['profile'] . '?tab=' . $tab)
                    ->waitFor('[data-profile-panel="' . $tab . '"]', 10)
                    ->assertPresent('[data-profile-panel="' . $tab . '"]');
            }
        }
    });
});

it('redirects guests from all owner protected front screens to private login', function () {
    $resultsVoting = Voting::factory()->create([
        'starts_at' => now()->subDays(10),
        'ends_at' => now()->subDay(),
        'is_published' => true,
        'show_results' => true,
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($resultsVoting): void {
        $isOnPrivateLoginScript = <<<'JS'
            ['/eu/pribatua', '/es/privado'].includes(window.location.pathname)
        JS;

        $locales = [
            'eu' => [
                'votings' => 'bozketak',
                'results' => 'emaitzak',
                'profile' => 'profila',
            ],
            'es' => [
                'votings' => 'votaciones',
                'results' => 'resultados',
                'profile' => 'perfil',
            ],
        ];

        $browser->visit('/_dusk/logout');

        foreach ($locales as $locale => $paths) {
            $browser->visit('/' . $locale . '/' . $paths['votings'])
                ->waitFor('[data-test="login-button"]', 10)
                ->assertScript($isOnPrivateLoginScript, true)
                ->assertPresent('[data-test="login-button"]');

            $browser->visit('/' . $locale . '/' . $paths['votings'] . '/' . $resultsVoting->id . '/' . $paths['results'])
                ->waitFor('[data-test="login-button"]', 10)
                ->assertScript($isOnPrivateLoginScript, true)
                ->assertPresent('[data-test="login-button"]');

            $browser->visit('/' . $locale . '/' . $paths['profile'])
                ->waitFor('[data-test="login-button"]', 10)
                ->assertScript($isOnPrivateLoginScript, true)
                ->assertPresent('[data-test="login-button"]');
        }
    });
});
