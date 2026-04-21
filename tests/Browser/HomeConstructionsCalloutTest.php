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
        $browser->resize(1440, 1200)
            ->visit('/eu')
            ->assertPresent('[data-home-callouts]')
            ->assertPresent('[data-home-votings-callout]')
            ->assertPresent('[data-home-constructions-callout]')
            ->assertPresent('[data-home-votings-cta]')
            ->assertPresent('[data-home-construction-link="' . $firstConstruction->slug . '"]')
            ->assertScript(
                'return window.matchMedia("(min-width: 1024px)").matches;',
                true,
            )
            ->assertScript(
                'return document.querySelector("[data-home-callouts]")?.classList.contains("lg:grid-cols-2");',
                true,
            )
            ->assertScript(
                '(function () {'
                    . 'const layout = document.querySelector("[data-home-constructions-layout]");'
                    . 'const actions = document.querySelector("[data-home-constructions-actions]");'
                    . 'const firstButton = document.querySelector("[data-home-construction-link=\"' . $firstConstruction->slug . '\"]");'
                    . 'if (!layout || !actions || !firstButton) return false;'
                    . 'return layout.classList.contains("sm:flex-row")'
                    . ' && layout.classList.contains("sm:items-center")'
                    . ' && actions.classList.contains("sm:self-center")'
                    . ' && actions.classList.contains("sm:items-stretch")'
                    . ' && firstButton.classList.contains("btn-brand")'
                    . ' && firstButton.classList.contains("h-11");'
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
        $browser->resize(1440, 1200)
            ->loginAs($owner->user)
            ->visit('/eu')
            ->assertPresent('[data-home-callouts]')
            ->assertPresent('[data-home-votings-callout]')
            ->assertPresent('[data-home-profile-callout]')
            ->assertPresent('[data-home-constructions-callout]')
            ->assertScript(
                'return window.matchMedia("(min-width: 1024px)").matches;',
                true,
            )
            ->assertScript(
                'return document.querySelector("[data-home-callouts]")?.classList.contains("lg:grid-cols-3");',
                true,
            )
            ->assertPresent('[data-nav-construction-link="' . $firstConstruction->slug . '"]')
            ->assertPresent('[data-nav-construction-link="' . $secondConstruction->slug . '"]')
            ->assertScript(
                '(function () {'
                    . 'const layout = document.querySelector("[data-home-constructions-layout]");'
                    . 'const actions = document.querySelector("[data-home-constructions-actions]");'
                    . 'const constructionButton = document.querySelector("[data-home-construction-link=\"' . $firstConstruction->slug . '\"]");'
                    . 'if (!layout || !actions || !constructionButton) return false;'
                    . 'return layout.classList.contains("grid")'
                    . ' && layout.classList.contains("grid-cols-[auto,1fr]")'
                    . ' && actions.classList.contains("col-start-2")'
                    . ' && constructionButton.classList.contains("w-full")'
                    . ' && constructionButton.classList.contains("h-11");'
                    . '})()',
                true,
            );
    });
});
