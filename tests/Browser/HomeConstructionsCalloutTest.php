<?php

use App\Models\Owner;
use App\Models\Voting;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\Construction;

test('guest home with votings and constructions aligns the first construction button with the voting button', function () {
    Voting::factory()->current()->create([
        'is_published' => true,
    ]);

    $firstConstruction = Construction::factory()->create([
        'title' => 'Lehen obra aktiboa gonbidatua',
        'slug' => 'lehen-obra-aktiboa-gonbidatua-bi',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(3),
        'is_active' => true,
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($firstConstruction): void {
        $browser->visit('/eu')
            ->assertPresent('[data-home-callouts]')
            ->assertPresent('[data-home-votings-callout]')
            ->assertPresent('[data-home-constructions-callout]')
            ->assertScript(
                'return document.querySelector("[data-home-callouts]")?.classList.contains("lg:grid-cols-2");',
                true,
            )
            ->assertPresent('[data-home-construction-link="' . $firstConstruction->slug . '"]')
            ->assertScript(
                '(function () {'
                    . 'const votingButton = document.querySelector("[data-home-votings-cta]");'
                    . 'const firstButton = document.querySelector("[data-home-construction-link=\"' . $firstConstruction->slug . '\"]");'
                    . 'if (!votingButton || !firstButton) return false;'
                    . 'const votingRect = votingButton.getBoundingClientRect();'
                    . 'const firstRect = firstButton.getBoundingClientRect();'
                    . 'return Math.abs(firstRect.top - votingRect.top) <= 6 && firstButton.classList.contains("btn-brand");'
                    . '})()',
                true,
            );
    });
});

test('authenticated owner sees active constructions callout and keeps three-callout buttons at matching height', function () {
    $owner = Owner::factory()->create([
        'accepted_terms_at' => now(),
    ]);

    Voting::factory()->current()->create([
        'is_published' => true,
    ]);

    $firstConstruction = Construction::factory()->create([
        'title' => 'Lehen obra aktiboa erabiltzailea',
        'slug' => 'lehen-obra-aktiboa-erabiltzailea-hiru',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(3),
        'is_active' => true,
    ]);

    $secondConstruction = Construction::factory()->create([
        'title' => 'Bigarren obra aktiboa erabiltzailea',
        'slug' => 'bigarren-obra-aktiboa-erabiltzailea-hiru',
        'starts_at' => now()->subDays(2),
        'ends_at' => now()->addDays(7),
        'is_active' => true,
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($owner, $firstConstruction, $secondConstruction): void {
        $browser->loginAs($owner->user)
            ->visit('/eu')
            ->assertPresent('[data-home-callouts]')
            ->assertPresent('[data-home-votings-callout]')
            ->assertPresent('[data-home-profile-callout]')
            ->assertPresent('[data-home-constructions-callout]')
            ->assertScript(
                'return document.querySelector("[data-home-callouts]")?.classList.contains("lg:grid-cols-3");',
                true,
            )
            ->assertPresent('[data-nav-construction-link="' . $firstConstruction->slug . '"]')
            ->assertPresent('[data-nav-construction-link="' . $secondConstruction->slug . '"]')
            ->assertScript(
                '(function () {'
                    . 'const votingButton = document.querySelector("[data-home-votings-cta]");'
                    . 'const profileButton = document.querySelector("[data-home-profile-cta]");'
                    . 'const constructionButton = document.querySelector("[data-home-construction-link=\"' . $firstConstruction->slug . '\"]");'
                    . 'if (!votingButton || !profileButton || !constructionButton) return false;'
                    . 'const votingRect = votingButton.getBoundingClientRect();'
                    . 'const profileRect = profileButton.getBoundingClientRect();'
                    . 'const constructionRect = constructionButton.getBoundingClientRect();'
                    . 'return Math.abs(votingRect.height - constructionRect.height) <= 2'
                    . ' && Math.abs(profileRect.height - constructionRect.height) <= 2'
                    . ' && constructionButton.classList.contains("w-full");'
                    . '})()',
                true,
            );
    });
});
